@extends('layouts.main_template')

@section('style')
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css">
    <style>
        .campaign-config-section {
            border: 1px solid #d8e2ef;
            border-radius: 8px;
            background: #f8fbff;
            padding: 14px;
            margin-bottom: 14px;
        }

        .campaign-config-section-title {
            color: #23364d;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .campaign-config-section-subtitle {
            color: #66758a;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .campaign-rule-row {
            background: #ffffff;
            border-color: #d8e2ef !important;
        }

        .campaign-rule-row label,
        #CampaignModal label {
            color: #40556f;
            font-weight: 600;
        }

    </style>
@endsection

@section('content')
    @php
        $page_mode = $page_mode ?? 'promotions';
        $is_campaign_page = $page_mode === 'campaigns';
    @endphp
    <div class="row">
        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{ $message }}</p>
            </div>
        @endif

        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-ticket icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>
                        {{ $is_campaign_page ? 'Campaigns' : 'Promotion' }}
                        <div class="page-title-subheading">
                            {{ $is_campaign_page ? 'Campaign rules and rewards for school receipts' : 'Promotion master data for school receipts' }}
                        </div>
                    </div>
                </div>
                @if (!$is_campaign_page)
                <div class="page-title-actions">
                    <div class="d-inline-block dropdown">
                        <a class="btn btn-success" data-toggle="modal" data-target=".bd-example-modal-lg"
                            id="CreateButton"><i class="fa fa-plus"></i> Create New Promotion</a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        @if (!$is_campaign_page)
        <div class="main-card mb-3 card" id="x_content">
            <div class="card-body">
                <h5 class="card-title">Promotion Tracking</h5>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <table style="width: 100%;" id="Listview"
                    class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                    <thead>
                        <tr>
                            <th width="80px">ID</th>
                            <th>Name</th>
                            <th width="120px">Status</th>
                            <th width="260px">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        @endif

        @if ($is_campaign_page)
        <div class="main-card mb-3 card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="card-title mb-1">Calculated Campaigns</h5>
                        <div class="text-muted small">Configure promotion rules and rewards used by school receipt preview.</div>
                    </div>
                    <button type="button" class="btn btn-primary" id="CreateCampaignButton">
                        <i class="fa fa-plus"></i> Create Campaign
                    </button>
                </div>
                <table style="width: 100%;" id="CampaignListview"
                    class="table table-striped table-hover table-bordered responsive-utilities jambo_table bulk_action">
                    <thead>
                        <tr>
                            <th width="80px">ID</th>
                            <th>Name</th>
                            <th width="220px">Linked Promotion</th>
                            <th width="220px">Type</th>
                            <th width="260px">Rules</th>
                            <th width="220px">Reward</th>
                            <th width="100px">Priority</th>
                            <th width="120px">Status</th>
                            <th width="180px">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        @endif
    </div>

    @if (!$is_campaign_page)
    <div class="fade modal bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true" id="CreateModal">
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Add Promotion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"></span>
                            </button>
                        </div>
                        <div class="form-group">
                            <label for="AddName">Promotion Name:</label>
                            <input type="text" class="form-control" name="name" id="AddName" value="">
                        </div>
                        <div class="form-group">
                            <label for="AddIsActive">Status:</label>
                            <select name="is_active" id="AddIsActive" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="SubmitCreateForm">Save</button>
                        <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"
        id="EditModal">
        <div class="modal-dialog modal-lg" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLongTitle">Edit Promotion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true"></span>
                            </button>
                        </div>
                        <div class="form-group">
                            <label for="EditName">Promotion Name:</label>
                            <input type="text" class="form-control" name="name" id="EditName" value="">
                        </div>
                        <div class="form-group">
                            <label for="EditIsActive">Status:</label>
                            <select name="is_active" id="EditIsActive" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="SubmitEditForm">Update</button>
                        <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if ($is_campaign_page)
    <div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"
        id="CampaignModal">
        <div class="modal-dialog modal-xl" role="document">
            <form class="form" action="" method="POST">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="CampaignModalTitle">Add Calculated Campaign</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger campaign-alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <input type="hidden" id="CampaignMode" value="create">
                        <input type="hidden" id="CampaignId" value="">

                        <div class="campaign-config-section">
                            <div class="campaign-config-section-title">ข้อมูล Campaign</div>
                            <div class="campaign-config-section-subtitle">ชื่อ สถานะ และ Promotion ที่เชื่อมกับ Campaign</div>

                            <div class="form-row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="CampaignName">Campaign Name:</label>
                                        <input type="text" class="form-control" id="CampaignName" value="">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="CampaignIsActive">Status:</label>
                                        <select id="CampaignIsActive" class="form-control">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="CampaignLinkedPromotion">Linked Promotion:</label>
                                        <select id="CampaignLinkedPromotion" class="form-control">
                                            <option value="">Global campaign</option>
                                            @foreach ($promotion_options as $promotion_option)
                                                <option value="{{ $promotion_option->id }}">{{ $promotion_option->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="CampaignPromotionType">Campaign Category:</label>
                                        <select id="CampaignPromotionType" class="form-control">
                                            <option value="CUSTOM">Custom</option>
                                            <option value="TWO_SUBJECTS">Math + English</option>
                                            <option value="REFERRAL">Referral</option>
                                            <option value="NEW_STUDENT_MULTI_COURSE">New student multi-course</option>
                                            <option value="NEW_STUDENT_FREE_ENTRANCE_GIFT">New student free entrance + gift</option>
                                            <option value="NEW_STUDENT_FREE_ENTRANCE">New student free entrance</option>
                                            <option value="NEW_STUDENT_GIFT">New student gift</option>
                                            <option value="SPECIAL">Special</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="CampaignPriority">Priority:</label>
                                        <input type="number" class="form-control" id="CampaignPriority" value="0" min="0" max="999">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="campaign-config-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="campaign-config-section-title">เงื่อนไขการเข้า Campaign</div>
                                    <div class="campaign-config-section-subtitle mb-0">ชุดเงื่อนไขสำหรับการแสดง Campaign ในใบเสร็จ</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="AddCampaignRule">
                                    <i class="fa fa-plus"></i> Add Rule
                                </button>
                            </div>
                            <div id="CampaignRules"></div>
                        </div>

                        <div class="campaign-config-section">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="campaign-config-section-title">สิทธิ์ที่ได้รับ</div>
                                    <div class="campaign-config-section-subtitle mb-0">หนึ่ง Campaign เพิ่มได้หลายสิทธิ์ เช่น ฟรีค่าลงทะเบียน + กระเป๋านักเรียน</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="AddCampaignReward">
                                    <i class="fa fa-plus"></i> Add Reward
                                </button>
                            </div>
                            <div id="CampaignRewards"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" id="SubmitCampaignForm">Save</button>
                        <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            @if (!$is_campaign_page)
            var table = $('#Listview').DataTable({
                ajax: '',
                serverSide: true,
                processing: true,
                language: {
                    loadingRecords: '&nbsp;',
                    processing: '<div class="spinner"></div>'
                },
                aaSorting: [
                    [0, "desc"]
                ],
                iDisplayLength: 10,
                lengthMenu: [10, 20, 50, 75, 100],
                stateSave: true,
                sPaginationType: "full_numbers",
                dom: 'T<"clear">lfrtip',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
            @endif

            @if ($is_campaign_page)
            var campaignTable = $('#CampaignListview').DataTable({
                ajax: "{{ route('promotions.campaigns') }}",
                serverSide: true,
                processing: true,
                language: {
                    loadingRecords: '&nbsp;',
                    processing: '<div class="spinner"></div>'
                },
                aaSorting: [
                    [6, "desc"]
                ],
                iDisplayLength: 10,
                lengthMenu: [10, 20, 50, 75, 100],
                stateSave: true,
                sPaginationType: "full_numbers",
                dom: 'T<"clear">lfrtip',
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'linked_promotion_name',
                        name: 'linked_promotion_name',
                        orderable: false
                    },
                    {
                        data: 'promotion_type',
                        name: 'promotion_type'
                    },
                    {
                        data: 'rule_summary',
                        name: 'rule_summary',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'reward_summary',
                        name: 'reward_summary',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'priority',
                        name: 'priority'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });
            @endif

            function ShowErrors(errors) {
                $('.alert-danger').html('');
                $.each(errors, function(key, value) {
                    $('.alert-danger').show();
                    $('.alert-danger').append('<strong><li>' + value + '</li></strong>');
                });
            }

            function ResetAlerts() {
                $('.alert-danger').html('');
                $('.alert-danger').hide();
                $('.alert-success').html('');
                $('.alert-success').hide();
            }

            function ShowCampaignErrors(errors) {
                $('.campaign-alert-danger').html('');
                $.each(errors, function(key, value) {
                    $('.campaign-alert-danger').show();
                    $('.campaign-alert-danger').append('<strong><li>' + value + '</li></strong>');
                });
            }

            function ResetCampaignAlerts() {
                $('.campaign-alert-danger').html('');
                $('.campaign-alert-danger').hide();
            }

            function BuildCampaignRuleRow(ruleData) {
                var safeRuleData = ruleData || {};
                var ruleConfig = safeRuleData.rule_config_json || {};
                var subjectGroups = (ruleConfig.subject_groups || ['M', 'E']).join(', ');
                var minimumCourses = ruleConfig.minimum_courses || 3;
                var minimumMonths = ruleConfig.minimum_months || 4;
                var exactMonths = (ruleConfig.months || ruleConfig.allowed_months || [1]).join(', ');
                var ruleType = safeRuleData.rule_type || 'SUBJECT_COMBINATION';
                var rowId = 'campaign_rule_' + Date.now() + '_' + Math.floor(Math.random() * 1000);

                var html = '<div class="campaign-rule-row border rounded p-2 mb-2" data-row-id="' + rowId + '">';
                html += '<div class="form-row align-items-end">';
                html += '<div class="col-md-4">';
                html += '<label>Rule Template:</label>';
                html += '<select class="form-control campaign-rule-type">';
                html += '<option value="SUBJECT_COMBINATION">Subject groups include</option>';
                html += '<option value="REFERRAL">Referral</option>';
                html += '<option value="NEW_STUDENT">New student</option>';
                html += '<option value="MIN_COURSES">Minimum courses</option>';
                html += '<option value="MIN_MONTHS">Minimum months</option>';
                html += '<option value="EXACT_MONTHS">Exact months</option>';
                html += '</select>';
                html += '</div>';
                html += '<div class="col-md-5 campaign-rule-subject-groups-group">';
                html += '<label>Subject Groups:</label>';
                html += '<input type="text" class="form-control campaign-rule-subject-groups" value="' + subjectGroups + '" placeholder="M, E">';
                html += '</div>';
                html += '<div class="col-md-3 campaign-rule-minimum-courses-group">';
                html += '<label>Minimum Courses:</label>';
                html += '<input type="number" class="form-control campaign-rule-minimum-courses" value="' + minimumCourses + '" min="1" max="99">';
                html += '</div>';
                html += '<div class="col-md-3 campaign-rule-minimum-months-group">';
                html += '<label>Minimum Months:</label>';
                html += '<input type="number" class="form-control campaign-rule-minimum-months" value="' + minimumMonths + '" min="1" max="120">';
                html += '</div>';
                html += '<div class="col-md-3 campaign-rule-exact-months-group">';
                html += '<label>Exact Months:</label>';
                html += '<input type="text" class="form-control campaign-rule-exact-months" value="' + exactMonths + '" placeholder="1, 2">';
                html += '</div>';
                html += '<div class="col-md-2">';
                html += '<button type="button" class="btn btn-sm btn-outline-danger remove-campaign-rule">Remove</button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                $('#CampaignRules').append(html);
                var row = $('#CampaignRules').find('[data-row-id="' + rowId + '"]');
                row.find('.campaign-rule-type').val(ruleType);
                UpdateCampaignRuleRowVisibility(row);
            }

            function UpdateCampaignRuleRowVisibility(row) {
                var ruleType = row.find('.campaign-rule-type').val();

                row.find('.campaign-rule-subject-groups-group').hide();
                row.find('.campaign-rule-minimum-courses-group').hide();
                row.find('.campaign-rule-minimum-months-group').hide();
                row.find('.campaign-rule-exact-months-group').hide();

                if (ruleType === 'SUBJECT_COMBINATION') {
                    row.find('.campaign-rule-subject-groups-group').show();
                }

                if (ruleType === 'MIN_COURSES') {
                    row.find('.campaign-rule-minimum-courses-group').show();
                }

                if (ruleType === 'MIN_MONTHS') {
                    row.find('.campaign-rule-minimum-months-group').show();
                }

                if (ruleType === 'EXACT_MONTHS') {
                    row.find('.campaign-rule-exact-months-group').show();
                }
            }

            function BuildCampaignRewardRow(rewardData) {
                var safeRewardData = rewardData || {};
                var rewardConfig = safeRewardData.reward_config_json || {};
                var rewardType = safeRewardData.reward_type || 'FIXED_DISCOUNT';
                var rowId = 'campaign_reward_' + Date.now() + '_' + Math.floor(Math.random() * 1000);

                var html = '<div class="campaign-reward-row border rounded p-2 mb-2" data-row-id="' + rowId + '">';
                html += '<div class="form-row align-items-end">';
                html += '<div class="col-md-3">';
                html += '<label>Reward Type:</label>';
                html += '<select class="form-control campaign-reward-type">';
                html += '<option value="FIXED_DISCOUNT">Fixed discount</option>';
                html += '<option value="PERCENT_DISCOUNT">Percent discount</option>';
                html += '<option value="CAPPED_DISCOUNT">Capped fixed discount</option>';
                html += '<option value="FREE_ENTRANCE_FEE">Free entrance fee</option>';
                html += '<option value="GIFT">Gift</option>';
                html += '</select>';
                html += '</div>';
                html += '<div class="col-md-3 campaign-reward-fixed-discount-group">';
                html += '<label>Discount Amount:</label>';
                html += '<input type="number" class="form-control campaign-reward-fixed-discount-amount" value="' + (safeRewardData.discount_amount || rewardConfig.discount_amount || 0) + '" min="0" step="0.01">';
                html += '</div>';
                html += '<div class="col-md-3 campaign-reward-percent-discount-group">';
                html += '<label>Discount Percent:</label>';
                html += '<input type="number" class="form-control campaign-reward-discount-percent" value="' + (safeRewardData.discount_percent || rewardConfig.discount_percent || 0) + '" min="0" max="100" step="0.01">';
                html += '</div>';
                html += '<div class="col-md-3 campaign-reward-max-discount-group">';
                html += '<label>Max Discount:</label>';
                html += '<input type="number" class="form-control campaign-reward-max-discount-amount" value="' + (safeRewardData.max_discount_amount || 0) + '" min="0" step="0.01">';
                html += '</div>';
                html += '<div class="col-md-5 campaign-reward-gift-name-group">';
                html += '<label>Gift Name:</label>';
                html += '<input type="text" class="form-control campaign-reward-gift-name" value="' + (rewardConfig.gift_name || '') + '" placeholder="กระเป๋านักเรียน 1 ใบ">';
                html += '</div>';
                html += '<div class="col-md-2">';
                html += '<button type="button" class="btn btn-sm btn-outline-danger remove-campaign-reward">Remove</button>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                $('#CampaignRewards').append(html);
                var row = $('#CampaignRewards').find('[data-row-id="' + rowId + '"]');
                row.find('.campaign-reward-type').val(rewardType);
                UpdateCampaignRewardRowVisibility(row);
            }

            function UpdateCampaignRewardRowVisibility(row) {
                var rewardType = row.find('.campaign-reward-type').val();

                row.find('.campaign-reward-fixed-discount-group').hide();
                row.find('.campaign-reward-percent-discount-group').hide();
                row.find('.campaign-reward-max-discount-group').hide();
                row.find('.campaign-reward-gift-name-group').hide();

                if (rewardType === 'FIXED_DISCOUNT') {
                    row.find('.campaign-reward-fixed-discount-group').show();
                }

                if (rewardType === 'PERCENT_DISCOUNT') {
                    row.find('.campaign-reward-percent-discount-group').show();
                }

                if (rewardType === 'CAPPED_DISCOUNT') {
                    row.find('.campaign-reward-fixed-discount-group').show();
                    row.find('.campaign-reward-max-discount-group').show();
                }

                if (rewardType === 'GIFT') {
                    row.find('.campaign-reward-gift-name-group').show();
                }
            }

            function SyncSpecialCampaignRuleControls(campaignType) {
                var isSpecialCampaign = campaignType === 'SPECIAL';
                $('#AddCampaignRule').prop('disabled', isSpecialCampaign);
                $('#AddCampaignRule').toggle(!isSpecialCampaign);
            }

            function ApplyCampaignPreset(campaignType) {
                $('#CampaignRules').html('');
                $('#CampaignRewards').html('');
                SyncSpecialCampaignRuleControls(campaignType);

                if (campaignType === 'TWO_SUBJECTS') {
                    BuildCampaignRuleRow({
                        rule_type: 'SUBJECT_COMBINATION',
                        rule_config_json: {
                            subject_groups: ['M', 'E']
                        }
                    });
                    BuildCampaignRewardRow({
                        reward_type: 'FIXED_DISCOUNT',
                        discount_amount: 200
                    });
                } else if (campaignType === 'REFERRAL') {
                    BuildCampaignRuleRow({
                        rule_type: 'REFERRAL',
                        rule_config_json: {}
                    });
                    BuildCampaignRewardRow({
                        reward_type: 'PERCENT_DISCOUNT',
                        discount_percent: 10
                    });
                } else if (campaignType === 'NEW_STUDENT_MULTI_COURSE') {
                    BuildCampaignRuleRow({
                        rule_type: 'NEW_STUDENT',
                        rule_config_json: {}
                    });
                    BuildCampaignRuleRow({
                        rule_type: 'MIN_COURSES',
                        rule_config_json: {
                            minimum_courses: 3
                        }
                    });
                    BuildCampaignRewardRow({
                        reward_type: 'CAPPED_DISCOUNT',
                        discount_amount: 900,
                        max_discount_amount: 900
                    });
                } else if (campaignType === 'NEW_STUDENT_FREE_ENTRANCE_GIFT') {
                    BuildCampaignRuleRow({
                        rule_type: 'NEW_STUDENT',
                        rule_config_json: {}
                    });
                    BuildCampaignRuleRow({
                        rule_type: 'MIN_MONTHS',
                        rule_config_json: {
                            minimum_months: 4
                        }
                    });
                    BuildCampaignRewardRow({
                        reward_type: 'FREE_ENTRANCE_FEE'
                    });
                    BuildCampaignRewardRow({
                        reward_type: 'GIFT',
                        reward_config_json: {
                            gift_name: 'กระเป๋านักเรียน 1 ใบ'
                        }
                    });
                } else if (campaignType === 'NEW_STUDENT_FREE_ENTRANCE') {
                    BuildCampaignRuleRow({
                        rule_type: 'NEW_STUDENT',
                        rule_config_json: {}
                    });
                    BuildCampaignRuleRow({
                        rule_type: 'EXACT_MONTHS',
                        rule_config_json: {
                            months: [2]
                        }
                    });
                    BuildCampaignRewardRow({
                        reward_type: 'FREE_ENTRANCE_FEE'
                    });
                } else if (campaignType === 'NEW_STUDENT_GIFT') {
                    BuildCampaignRuleRow({
                        rule_type: 'NEW_STUDENT',
                        rule_config_json: {}
                    });
                    BuildCampaignRuleRow({
                        rule_type: 'EXACT_MONTHS',
                        rule_config_json: {
                            months: [1]
                        }
                    });
                    BuildCampaignRewardRow({
                        reward_type: 'GIFT',
                        reward_config_json: {
                            gift_name: 'กระเป๋านักเรียน 1 ใบ'
                        }
                    });
                } else if (campaignType === 'SPECIAL') {
                    BuildCampaignRewardRow({
                        reward_type: 'PERCENT_DISCOUNT',
                        discount_percent: 10
                    });
                } else {
                    BuildCampaignRuleRow();
                    BuildCampaignRewardRow();
                }
            }

            function ResetCampaignForm() {
                ResetCampaignAlerts();
                $('#CampaignMode').val('create');
                $('#CampaignId').val('');
                $('#CampaignModalTitle').text('Add Calculated Campaign');
                $('#CampaignName').val('');
                $('#CampaignLinkedPromotion').val('');
                $('#CampaignPromotionType').val('CUSTOM');
                $('#CampaignIsActive').val('1');
                $('#CampaignPriority').val('0');
                $('#CampaignRules').html('');
                $('#CampaignRewards').html('');
                ApplyCampaignPreset('CUSTOM');
            }

            function BuildCampaignPayload() {
                var campaignRules = [];
                var campaignRewards = [];
                var promotionType = $('#CampaignPromotionType').val();

                if (promotionType !== 'SPECIAL') {
                    $('.campaign-rule-row').each(function(index, row) {
                        var ruleType = $(row).find('.campaign-rule-type').val();
                        var campaignRule = {
                            rule_type: ruleType
                        };

                        if (ruleType === 'SUBJECT_COMBINATION') {
                            campaignRule.subject_groups = $(row).find('.campaign-rule-subject-groups').val();
                        }

                        if (ruleType === 'MIN_COURSES') {
                            campaignRule.minimum_courses = $(row).find('.campaign-rule-minimum-courses').val();
                        }

                        if (ruleType === 'MIN_MONTHS') {
                            campaignRule.minimum_months = $(row).find('.campaign-rule-minimum-months').val();
                        }

                        if (ruleType === 'EXACT_MONTHS') {
                            campaignRule.exact_months = $(row).find('.campaign-rule-exact-months').val();
                        }

                        campaignRules.push(campaignRule);
                    });
                }

                $('.campaign-reward-row').each(function(index, row) {
                    var rewardType = $(row).find('.campaign-reward-type').val();
                    campaignRewards.push({
                        reward_type: rewardType,
                        fixed_discount_amount: $(row).find('.campaign-reward-fixed-discount-amount').val(),
                        discount_percent: $(row).find('.campaign-reward-discount-percent').val(),
                        max_discount_amount: $(row).find('.campaign-reward-max-discount-amount').val(),
                        gift_name: $(row).find('.campaign-reward-gift-name').val()
                    });
                });

                return {
                    campaign_name: $('#CampaignName').val(),
                    linked_promotion_id: $('#CampaignLinkedPromotion').val(),
                    promotion_type: promotionType,
                    campaign_is_active: $('#CampaignIsActive').val(),
                    priority: $('#CampaignPriority').val(),
                    campaign_rules: campaignRules,
                    campaign_rewards: campaignRewards,
                };
            }

            $(document).on('click', '#CreateButton', function(e) {
                e.preventDefault();
                ResetAlerts();
                $('#CreateModal').modal('show');
            });

            $(document).on('click', '#CreateCampaignButton', function(e) {
                e.preventDefault();
                ResetCampaignForm();
                $('#CampaignModal').modal('show');
            });

            $('#CampaignPromotionType').change(function() {
                ApplyCampaignPreset($(this).val());
            });

            $('#AddCampaignRule').click(function() {
                if ($('#CampaignPromotionType').val() === 'SPECIAL') {
                    return;
                }

                BuildCampaignRuleRow();
            });

            $('#AddCampaignReward').click(function() {
                BuildCampaignRewardRow();
            });

            $(document).on('change', '.campaign-rule-type', function() {
                UpdateCampaignRuleRowVisibility($(this).closest('.campaign-rule-row'));
            });

            $(document).on('change', '.campaign-reward-type', function() {
                UpdateCampaignRewardRowVisibility($(this).closest('.campaign-reward-row'));
            });

            $(document).on('click', '.remove-campaign-rule', function() {
                $(this).closest('.campaign-rule-row').remove();
            });

            $(document).on('click', '.remove-campaign-reward', function() {
                $(this).closest('.campaign-reward-row').remove();
            });

            let id;
            $(document).on('click', '#getEditData', function(e) {
                e.preventDefault();
                ResetAlerts();

                id = $(this).data('id');
                $.ajax({
                    url: "{{ url('promotions/edit') }}/" + id,
                    method: 'GET',
                    success: function(res) {
                        $('#EditName').val(res.data.name);
                        $('#EditIsActive').val(res.data.is_active);
                        $('#EditModal').modal('show');
                    }
                });
            });

            $('#SubmitCreateForm').click(function(e) {
                e.preventDefault();
                ResetAlerts();

                $.ajax({
                    url: "{{ route('promotions.store') }}",
                    method: 'post',
                    data: {
                        name: $('#AddName').val(),
                        is_active: $('#AddIsActive').val(),
                    },
                    success: function(result) {
                        if (result.errors) {
                            ShowErrors(result.errors);
                        } else {
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            $('#Listview').DataTable().ajax.reload();
                            $('.form').trigger('reset');
                            $('#CreateModal').modal('hide');
                        }
                    }
                });
            });

            $('#SubmitEditForm').click(function(e) {
                e.preventDefault();
                ResetAlerts();

                $.ajax({
                    url: "{{ url('promotions/save') }}/" + id,
                    method: 'PUT',
                    data: {
                        name: $('#EditName').val(),
                        is_active: $('#EditIsActive').val(),
                    },
                    success: function(result) {
                        if (result.errors) {
                            ShowErrors(result.errors);
                        } else {
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            $('#Listview').DataTable().ajax.reload();
                            $('#EditModal').modal('hide');
                        }
                    }
                });
            });

            $(document).on('click', '.btn-toggle-status', function(e) {
                e.preventDefault();

                if (!confirm("Update promotion status?")) {
                    return;
                }

                let promotion_id = $(this).data('id');
                $.ajax({
                    url: "{{ url('promotions/toggle-status') }}/" + promotion_id,
                    method: 'PUT',
                    success: function(result) {
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                    }
                });
            });

            $(document).on('click', '.btn-delete-promotion', function(e) {
                e.preventDefault();
                ResetAlerts();

                if (!confirm("Delete this promotion?")) {
                    return;
                }

                var promotion_id = $(this).data('id');
                $.ajax({
                    url: "{{ url('promotions/destroy') }}/" + promotion_id,
                    method: 'DELETE',
                    success: function(result) {
                        if (result.errors) {
                            ShowErrors(result.errors);
                            return;
                        }

                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                    }
                });
            });

            $(document).on('click', '.btn-campaign-edit', function(e) {
                e.preventDefault();
                ResetCampaignForm();

                let campaign_id = $(this).data('id');
                $.ajax({
                    url: "{{ url('promotions/campaigns/edit') }}/" + campaign_id,
                    method: 'GET',
                    success: function(res) {
                        var campaign = res.data;
                        var ruleConfig = campaign.rule_config_json || {};
                        var rewardConfig = campaign.reward_config_json || {};

                        $('#CampaignMode').val('edit');
                        $('#CampaignId').val(campaign.id);
                        $('#CampaignModalTitle').text('Edit Calculated Campaign');
                        $('#CampaignName').val(campaign.campaign_name);
                        $('#CampaignLinkedPromotion').val(campaign.linked_promotion_id || '');
                        $('#CampaignPromotionType').val(campaign.promotion_type);
                        $('#CampaignIsActive').val(campaign.campaign_is_active);
                        $('#CampaignPriority').val(campaign.priority);
                        $('#CampaignRules').html('');
                        $('#CampaignRewards').html('');
                        $.each(campaign.campaign_rules || [], function(index, campaignRule) {
                            BuildCampaignRuleRow(campaignRule);
                        });
                        if ((campaign.campaign_rules || []).length === 0 && campaign.promotion_type !== 'SPECIAL') {
                            BuildCampaignRuleRow();
                        }
                        SyncSpecialCampaignRuleControls(campaign.promotion_type);
                        $.each(campaign.campaign_rewards || [], function(index, campaignReward) {
                            BuildCampaignRewardRow(campaignReward);
                        });
                        if ((campaign.campaign_rewards || []).length === 0) {
                            BuildCampaignRewardRow({
                                reward_type: campaign.reward_type || 'FIXED_DISCOUNT',
                                discount_amount: campaign.discount_amount || rewardConfig.discount_amount || 0,
                                discount_percent: campaign.discount_percent || 0,
                                max_discount_amount: campaign.max_discount_amount || 0,
                                reward_config_json: rewardConfig
                            });
                        }
                        $('#CampaignModal').modal('show');
                    }
                });
            });

            $('#SubmitCampaignForm').click(function(e) {
                e.preventDefault();
                ResetCampaignAlerts();

                var mode = $('#CampaignMode').val();
                var campaign_id = $('#CampaignId').val();
                var url = mode === 'edit'
                    ? "{{ url('promotions/campaigns/save') }}/" + campaign_id
                    : "{{ route('promotions.campaigns.store') }}";
                var method = mode === 'edit' ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    method: method,
                    data: BuildCampaignPayload(),
                    success: function(result) {
                        if (result.errors) {
                            ShowCampaignErrors(result.errors);
                        } else {
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            $('#CampaignListview').DataTable().ajax.reload();
                            $('#CampaignModal').modal('hide');
                        }
                    }
                });
            });

            $(document).on('click', '.btn-campaign-toggle-status', function(e) {
                e.preventDefault();

                if (!confirm("Update calculated campaign status?")) {
                    return;
                }

                let campaign_id = $(this).data('id');
                $.ajax({
                    url: "{{ url('promotions/campaigns/toggle-status') }}/" + campaign_id,
                    method: 'PUT',
                    success: function(result) {
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#CampaignListview').DataTable().ajax.reload();
                    }
                });
            });
        });
    </script>
@endsection
