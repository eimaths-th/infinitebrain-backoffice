<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\PromotionCampaign;
use App\Models\PromotionReward;
use App\Models\PromotionRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function CheckPermission()
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user->can('subjects.view')) {
                return;
            }
        }

        abort(403, 'Unauthorized action.');
    }

    public function Index(Request $request)
    {
        $this->CheckPermission();

        if ($request->ajax()) {
            $promotions = Promotion::select(
                'promotions.id as id',
                'promotions.name as name',
                'promotions.is_active as is_active'
            )
                ->orderBy('promotions.id', 'desc')
                ->get();

            return datatables()->of($promotions)
                ->editColumn('is_active', function (Promotion $promotion) {
                    if ((int) $promotion->is_active === 1) {
                        return '<span class="badge bg-success">Active</span>';
                    }

                    return '<span class="badge bg-secondary">Inactive</span>';
                })
                ->addColumn('action', function (Promotion $promotion) {
                    $toggle_label = ((int) $promotion->is_active === 1) ? 'Disable' : 'Enable';
                    $toggle_class = ((int) $promotion->is_active === 1) ? 'btn-secondary' : 'btn-success';

                    $html = '<a href="#" class="btn btn-xs btn-warning btn-edit" id="getEditData" data-id="' . $promotion->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" class="btn btn-xs ' . $toggle_class . ' btn-toggle-status" data-id="' . $promotion->id . '"><i class="fa fa-power-off"></i> ' . $toggle_label . '</a>';

                    return $html;
                })
                ->rawColumns(['is_active', 'action'])
                ->toJson();
        }

        $promotion_options = Promotion::orderBy('name', 'asc')->get();
        $page_mode = 'promotions';

        return view('promotions.index', compact('promotion_options', 'page_mode'));
    }

    public function CampaignsPage()
    {
        $this->CheckPermission();

        $promotion_options = Promotion::orderBy('name', 'asc')->get();
        $page_mode = 'campaigns';

        return view('promotions.index', compact('promotion_options', 'page_mode'));
    }

    public function Store(Request $request)
    {
        $this->CheckPermission();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:promotions,name',
            'is_active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        Promotion::create([
            'name' => trim($request->get('name')),
            'is_active' => $request->get('is_active'),
        ]);

        return response()->json(['success' => 'Promotion Add successfully']);
    }

    public function Edit($id)
    {
        $this->CheckPermission();

        $promotion = Promotion::findOrFail($id);

        return response()->json(['data' => $promotion]);
    }

    public function Update(Request $request, $id)
    {
        $this->CheckPermission();

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('promotions', 'name')->ignore($id),
            ],
            'is_active' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        $promotion = Promotion::findOrFail($id);
        $promotion->update([
            'name' => trim($request->get('name')),
            'is_active' => $request->get('is_active'),
        ]);

        return response()->json(['success' => 'Promotion updated successfully']);
    }

    public function ToggleStatus($id)
    {
        $this->CheckPermission();

        $promotion = Promotion::findOrFail($id);
        $promotion->update([
            'is_active' => ((int) $promotion->is_active === 1) ? 0 : 1,
        ]);

        return response()->json(['success' => 'Promotion status updated successfully']);
    }

    public function CampaignsIndex(Request $request)
    {
        $this->CheckPermission();

        if ($request->ajax()) {
            $campaigns = PromotionCampaign::with(['promotion', 'rules', 'rewards'])
                ->orderBy('priority', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            return datatables()->of($campaigns)
                ->editColumn('is_active', function (PromotionCampaign $campaign) {
                    if ((int) $campaign->is_active === 1) {
                        return '<span class="badge bg-success">Active</span>';
                    }

                    return '<span class="badge bg-secondary">Inactive</span>';
                })
                ->editColumn('reward_summary', function (PromotionCampaign $campaign) {
                    return $this->BuildCampaignRewardSummary($campaign);
                })
                ->editColumn('rule_summary', function (PromotionCampaign $campaign) {
                    return $this->BuildCampaignRuleSummary($campaign);
                })
                ->addColumn('linked_promotion_name', function (PromotionCampaign $campaign) {
                    return $campaign->promotion ? $campaign->promotion->name : 'Global';
                })
                ->addColumn('action', function (PromotionCampaign $campaign) {
                    $toggle_label = ((int) $campaign->is_active === 1) ? 'Disable' : 'Enable';
                    $toggle_class = ((int) $campaign->is_active === 1) ? 'btn-secondary' : 'btn-success';

                    $html = '<a href="#" class="btn btn-xs btn-warning btn-campaign-edit" data-id="' . $campaign->id . '"><i class="fa fa-edit"></i> Edit</a> ';
                    $html .= '<a href="#" class="btn btn-xs ' . $toggle_class . ' btn-campaign-toggle-status" data-id="' . $campaign->id . '"><i class="fa fa-power-off"></i> ' . $toggle_label . '</a>';

                    return $html;
                })
                ->rawColumns(['is_active', 'rule_summary', 'reward_summary', 'action'])
                ->toJson();
        }

        return response()->json(['errors' => ['Invalid request']]);
    }

    public function CampaignStore(Request $request)
    {
        $this->CheckPermission();

        $validator = $this->ValidateCampaignRequest($request);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        DB::transaction(function () use ($request) {
            $campaign = PromotionCampaign::create([
                'promotion_id' => $request->get('linked_promotion_id') ?: null,
                'name' => trim($request->get('campaign_name')),
                'promotion_type' => $request->get('promotion_type'),
                'is_active' => $request->get('campaign_is_active'),
                'priority' => $request->get('priority', 0),
            ]);

            $this->ReplaceCampaignRuleAndReward($campaign, $request);
        });

        return response()->json(['success' => 'Calculated promotion campaign added successfully']);
    }

    public function CampaignEdit($id)
    {
        $this->CheckPermission();

        $campaign = PromotionCampaign::with(['rules', 'rewards'])->findOrFail($id);
        $rule = $campaign->rules->first();
        $reward = $campaign->rewards->first();

        return response()->json([
            'data' => [
                'id' => $campaign->id,
                'linked_promotion_id' => $campaign->promotion_id,
                'campaign_name' => $campaign->name,
                'promotion_type' => $campaign->promotion_type,
                'campaign_is_active' => (int) $campaign->is_active,
                'priority' => $campaign->priority,
                'rule_config_json' => $rule ? $rule->rule_config_json : [],
                'campaign_rules' => $campaign->rules->flatMap(function (PromotionRule $promotion_rule) {
                    if ($promotion_rule->rule_type === PromotionRule::TYPE_NEW_STUDENT_MIN_COURSES) {
                        $rule_config = $promotion_rule->rule_config_json ?? [];

                        return [
                            [
                                'rule_type' => PromotionRule::TYPE_NEW_STUDENT,
                                'rule_config_json' => [
                                    'requires_new_student' => true,
                                ],
                            ],
                            [
                                'rule_type' => PromotionRule::TYPE_MIN_COURSES,
                                'rule_config_json' => [
                                    'minimum_courses' => $rule_config['minimum_courses'] ?? 3,
                                ],
                            ],
                        ];
                    }

                    return [
                        [
                            'rule_type' => $promotion_rule->rule_type,
                            'rule_config_json' => $promotion_rule->rule_config_json ?? [],
                        ],
                    ];
                })->values(),
                'campaign_rewards' => $campaign->rewards->map(function (PromotionReward $promotion_reward) {
                    return [
                        'reward_type' => $promotion_reward->reward_type,
                        'discount_amount' => $promotion_reward->discount_amount,
                        'discount_percent' => $promotion_reward->discount_percent,
                        'max_discount_amount' => $promotion_reward->max_discount_amount,
                        'reward_config_json' => $promotion_reward->reward_config_json ?? [],
                    ];
                })->values(),
                'reward_type' => $reward ? $reward->reward_type : null,
                'discount_amount' => $reward ? $reward->discount_amount : null,
                'discount_percent' => $reward ? $reward->discount_percent : null,
                'max_discount_amount' => $reward ? $reward->max_discount_amount : null,
                'reward_config_json' => $reward ? $reward->reward_config_json : [],
            ],
        ]);
    }

    public function CampaignUpdate(Request $request, $id)
    {
        $this->CheckPermission();

        $campaign = PromotionCampaign::findOrFail($id);
        $validator = $this->ValidateCampaignRequest($request, $campaign->id);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }

        DB::transaction(function () use ($request, $campaign) {
            $campaign->update([
                'promotion_id' => $request->get('linked_promotion_id') ?: null,
                'name' => trim($request->get('campaign_name')),
                'promotion_type' => $request->get('promotion_type'),
                'is_active' => $request->get('campaign_is_active'),
                'priority' => $request->get('priority', 0),
            ]);

            $this->ReplaceCampaignRuleAndReward($campaign, $request);
        });

        return response()->json(['success' => 'Calculated promotion campaign updated successfully']);
    }

    public function CampaignToggleStatus($id)
    {
        $this->CheckPermission();

        $campaign = PromotionCampaign::findOrFail($id);
        $campaign->update([
            'is_active' => ((int) $campaign->is_active === 1) ? 0 : 1,
        ]);

        return response()->json(['success' => 'Calculated promotion campaign status updated successfully']);
    }

    private function ValidateCampaignRequest(Request $request, $ignore_id = null)
    {
        $valid_types = [
            PromotionCampaign::TYPE_TWO_SUBJECTS,
            PromotionCampaign::TYPE_REFERRAL,
            PromotionCampaign::TYPE_NEW_STUDENT_MULTI_COURSE,
            PromotionCampaign::TYPE_NEW_STUDENT_FREE_ENTRANCE_GIFT,
            PromotionCampaign::TYPE_NEW_STUDENT_FREE_ENTRANCE,
            PromotionCampaign::TYPE_NEW_STUDENT_GIFT,
            PromotionCampaign::TYPE_CUSTOM,
        ];

        $name_rule = Rule::unique('promotion_campaigns', 'name');
        if ($ignore_id) {
            $name_rule = $name_rule->ignore($ignore_id);
        }

        $rules = [
            'campaign_name' => ['required', 'string', 'max:255', $name_rule],
            'linked_promotion_id' => 'nullable|integer|exists:promotions,id',
            'promotion_type' => ['required', Rule::in($valid_types)],
            'campaign_is_active' => 'required|in:0,1',
            'priority' => 'required|integer|min:0|max:999',
            'campaign_rules' => 'required|array|min:1',
            'campaign_rules.*.rule_type' => [
                'required',
                Rule::in([
                    PromotionRule::TYPE_SUBJECT_COMBINATION,
                    PromotionRule::TYPE_REFERRAL,
                    PromotionRule::TYPE_NEW_STUDENT,
                    PromotionRule::TYPE_MIN_COURSES,
                    PromotionRule::TYPE_MIN_MONTHS,
                    PromotionRule::TYPE_EXACT_MONTHS,
                ]),
            ],
            'campaign_rewards' => 'required_without:reward_type|array|min:1',
            'campaign_rewards.*.reward_type' => [
                'required_with:campaign_rewards',
                Rule::in([
                    PromotionReward::TYPE_FIXED_DISCOUNT,
                    PromotionReward::TYPE_PERCENT_DISCOUNT,
                    PromotionReward::TYPE_CAPPED_DISCOUNT,
                    PromotionReward::TYPE_FREE_ENTRANCE_FEE,
                    PromotionReward::TYPE_GIFT,
                ]),
            ],
        ];

        foreach ($request->get('campaign_rules', []) as $index => $campaign_rule) {
            if (($campaign_rule['rule_type'] ?? null) === PromotionRule::TYPE_SUBJECT_COMBINATION) {
                $rules["campaign_rules.$index.subject_groups"] = 'required|string|max:255';
            }

            if (($campaign_rule['rule_type'] ?? null) === PromotionRule::TYPE_MIN_COURSES) {
                $rules["campaign_rules.$index.minimum_courses"] = 'required|integer|min:1|max:99';
            }

            if (($campaign_rule['rule_type'] ?? null) === PromotionRule::TYPE_MIN_MONTHS) {
                $rules["campaign_rules.$index.minimum_months"] = 'required|integer|min:1|max:120';
            }

            if (($campaign_rule['rule_type'] ?? null) === PromotionRule::TYPE_EXACT_MONTHS) {
                $rules["campaign_rules.$index.exact_months"] = 'required|string|max:255';
            }
        }

        foreach ($this->GetRequestCampaignRewards($request) as $index => $campaign_reward) {
            if (($campaign_reward['reward_type'] ?? null) === PromotionReward::TYPE_FIXED_DISCOUNT) {
                $rules["campaign_rewards.$index.fixed_discount_amount"] = 'required|numeric|min:0';
            }

            if (($campaign_reward['reward_type'] ?? null) === PromotionReward::TYPE_PERCENT_DISCOUNT) {
                $rules["campaign_rewards.$index.discount_percent"] = 'required|numeric|min:0|max:100';
            }

            if (($campaign_reward['reward_type'] ?? null) === PromotionReward::TYPE_CAPPED_DISCOUNT) {
                $rules["campaign_rewards.$index.fixed_discount_amount"] = 'required|numeric|min:0';
                $rules["campaign_rewards.$index.max_discount_amount"] = 'required|numeric|min:0';
            }

            if (($campaign_reward['reward_type'] ?? null) === PromotionReward::TYPE_GIFT) {
                $rules["campaign_rewards.$index.gift_name"] = 'required|string|max:255';
            }
        }

        return Validator::make($request->all(), $rules);
    }

    private function ReplaceCampaignRuleAndReward(PromotionCampaign $campaign, Request $request)
    {
        $campaign->rules()->delete();
        $campaign->rewards()->delete();

        foreach ($request->get('campaign_rules', []) as $campaign_rule) {
            $campaign->rules()->create([
                'rule_type' => $campaign_rule['rule_type'],
                'rule_config_json' => $this->BuildRuleConfig($campaign_rule),
            ]);
        }

        foreach ($this->GetRequestCampaignRewards($request) as $campaign_reward) {
            $campaign->rewards()->create($this->BuildRewardData($campaign_reward));
        }
    }

    private function BuildRuleConfig(array $campaign_rule)
    {
        if ($campaign_rule['rule_type'] === PromotionRule::TYPE_SUBJECT_COMBINATION) {
            $subject_groups = collect(explode(',', $campaign_rule['subject_groups']))
                ->map(function ($subject_group) {
                    return trim($subject_group);
                })
                ->filter()
                ->values()
                ->all();

            return ['subject_groups' => $subject_groups];
        }

        if ($campaign_rule['rule_type'] === PromotionRule::TYPE_REFERRAL) {
            return ['requires_referral' => true];
        }

        if ($campaign_rule['rule_type'] === PromotionRule::TYPE_NEW_STUDENT) {
            return ['requires_new_student' => true];
        }

        if ($campaign_rule['rule_type'] === PromotionRule::TYPE_MIN_COURSES) {
            return ['minimum_courses' => (int) $campaign_rule['minimum_courses']];
        }

        if ($campaign_rule['rule_type'] === PromotionRule::TYPE_MIN_MONTHS) {
            return ['minimum_months' => (int) $campaign_rule['minimum_months']];
        }

        if ($campaign_rule['rule_type'] === PromotionRule::TYPE_EXACT_MONTHS) {
            $months = collect(explode(',', $campaign_rule['exact_months']))
                ->map(function ($month) {
                    return (int) trim($month);
                })
                ->filter(function ($month) {
                    return $month > 0;
                })
                ->unique()
                ->values()
                ->all();

            return ['months' => $months];
        }

        return [];
    }

    private function GetRequestCampaignRewards(Request $request)
    {
        $campaign_rewards = $request->get('campaign_rewards');

        if (is_array($campaign_rewards) && !empty($campaign_rewards)) {
            return $campaign_rewards;
        }

        if ($request->filled('reward_type')) {
            return [[
                'reward_type' => $request->get('reward_type'),
                'fixed_discount_amount' => $request->get('fixed_discount_amount'),
                'discount_percent' => $request->get('discount_percent'),
                'max_discount_amount' => $request->get('max_discount_amount'),
            ]];
        }

        return [];
    }

    private function BuildRewardData(array $campaign_reward)
    {
        if ($campaign_reward['reward_type'] === PromotionReward::TYPE_FIXED_DISCOUNT) {
            return [
                'reward_type' => PromotionReward::TYPE_FIXED_DISCOUNT,
                'discount_amount' => $campaign_reward['fixed_discount_amount'],
            ];
        }

        if ($campaign_reward['reward_type'] === PromotionReward::TYPE_PERCENT_DISCOUNT) {
            return [
                'reward_type' => PromotionReward::TYPE_PERCENT_DISCOUNT,
                'discount_percent' => $campaign_reward['discount_percent'],
            ];
        }

        if ($campaign_reward['reward_type'] === PromotionReward::TYPE_FREE_ENTRANCE_FEE) {
            return [
                'reward_type' => PromotionReward::TYPE_FREE_ENTRANCE_FEE,
            ];
        }

        if ($campaign_reward['reward_type'] === PromotionReward::TYPE_GIFT) {
            return [
                'reward_type' => PromotionReward::TYPE_GIFT,
                'reward_config_json' => [
                    'gift_name' => trim($campaign_reward['gift_name']),
                ],
            ];
        }

        return [
            'reward_type' => PromotionReward::TYPE_CAPPED_DISCOUNT,
            'discount_amount' => $campaign_reward['fixed_discount_amount'],
            'max_discount_amount' => $campaign_reward['max_discount_amount'],
            'reward_config_json' => [
                'discount_amount' => (float) $campaign_reward['fixed_discount_amount'],
            ],
        ];
    }

    private function BuildCampaignRewardSummary(PromotionCampaign $campaign)
    {
        if ($campaign->rewards->isEmpty()) {
            return '-';
        }

        return $campaign->rewards->map(function (PromotionReward $reward) {
            if ($reward->reward_type === PromotionReward::TYPE_FIXED_DISCOUNT) {
                return 'Fixed ' . number_format((float) $reward->discount_amount, 2) . ' baht';
            }

            if ($reward->reward_type === PromotionReward::TYPE_PERCENT_DISCOUNT) {
                return number_format((float) $reward->discount_percent, 2) . '%';
            }

            if ($reward->reward_type === PromotionReward::TYPE_CAPPED_DISCOUNT) {
                return 'Discount ' . number_format((float) $reward->discount_amount, 2) .
                    ' baht, cap ' . number_format((float) $reward->max_discount_amount, 2) . ' baht';
            }

            if ($reward->reward_type === PromotionReward::TYPE_FREE_ENTRANCE_FEE) {
                return 'Free entrance fee';
            }

            if ($reward->reward_type === PromotionReward::TYPE_GIFT) {
                $reward_config = $reward->reward_config_json ?? [];
                return $reward_config['gift_name'] ?? 'Gift';
            }

            return $reward->reward_type;
        })->implode('<br>');
    }

    private function BuildCampaignRuleSummary(PromotionCampaign $campaign)
    {
        if ($campaign->rules->isEmpty()) {
            return '-';
        }

        return $campaign->rules->map(function (PromotionRule $promotion_rule) {
            $rule_config = $promotion_rule->rule_config_json ?? [];

            if ($promotion_rule->rule_type === PromotionRule::TYPE_SUBJECT_COMBINATION) {
                return 'Subject groups: ' . implode(', ', $rule_config['subject_groups'] ?? []);
            }

            if ($promotion_rule->rule_type === PromotionRule::TYPE_REFERRAL) {
                return 'Referral';
            }

            if ($promotion_rule->rule_type === PromotionRule::TYPE_NEW_STUDENT) {
                return 'New student';
            }

            if ($promotion_rule->rule_type === PromotionRule::TYPE_MIN_COURSES) {
                return 'Courses >= ' . ($rule_config['minimum_courses'] ?? '-');
            }

            if ($promotion_rule->rule_type === PromotionRule::TYPE_MIN_MONTHS) {
                return 'Months >= ' . ($rule_config['minimum_months'] ?? '-');
            }

            if ($promotion_rule->rule_type === PromotionRule::TYPE_EXACT_MONTHS) {
                return 'Months = ' . implode(', ', $rule_config['months'] ?? []);
            }

            if ($promotion_rule->rule_type === PromotionRule::TYPE_NEW_STUDENT_MIN_COURSES) {
                return 'New student and courses >= ' . ($rule_config['minimum_courses'] ?? '-');
            }

            return $promotion_rule->rule_type;
        })->implode('<br>');
    }
}
