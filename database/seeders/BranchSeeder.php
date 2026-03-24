<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groups = [
            'EL' => [
                'name' => 'Eye Level',
                'description' => 'กลุ่มสถาบัน Eye Level',
                'branches' => [
                    [
                        'code' => 'EL',
                        'name' => 'Eye Level (Main)',
                        'rv_prefix' => 'ELRV',
                        'pv_prefix' => 'ELPV',
                        'bf_prefix' => 'ELBF',
                    ]
                ]
            ],
            'IF' => [
                'name' => 'Infinite Brain',
                'description' => 'กลุ่มสถาบัน Infinite Brain',
                'branches' => [
                    [
                        'code' => 'IF',
                        'name' => 'Infinite Brain (Main)',
                        'rv_prefix' => 'IFRV',
                        'pv_prefix' => 'IFPV',
                        'bf_prefix' => 'IFBF',
                    ]
                ]
            ],
            'META' => [
                'name' => 'META Knowledge',
                'description' => 'กลุ่มสถาบัน META Knowledge',
                'branches' => [
                    [
                        'code' => 'META',
                        'name' => 'META Knowledge (Main)',
                        'rv_prefix' => 'MTRV',
                        'pv_prefix' => 'MT',
                        'bf_prefix' => 'MT',
                    ]
                ]
            ],
            'EiMaths' => [
                'name' => 'eiMaths',
                'description' => 'กลุ่มสถาบัน eiMaths',
                'branches' => [
                    [
                        'code' => 'EiRP',
                        'name' => 'eiMaths RP',
                        'rv_prefix' => 'EiRP',
                        'pv_prefix' => 'EiRP',
                        'bf_prefix' => 'EiRPRF',
                    ],
                    [
                        'code' => 'EiBK',
                        'name' => 'eiMaths BK',
                        'rv_prefix' => 'EiBK',
                        'pv_prefix' => 'EiBK',
                        'bf_prefix' => 'EiBKBF',
                    ],
                    [
                        'code' => 'EiHQ',
                        'name' => 'eiMaths HQ',
                        'rv_prefix' => 'EiHQ',
                        'pv_prefix' => 'EiHQ',
                        'bf_prefix' => 'EiHQBF',
                    ],
                ]
            ],
        ];

        foreach ($groups as $groupCode => $groupData) {
            $group = \App\Models\BranchGroup::updateOrCreate(
                ['code' => $groupCode],
                [
                    'name' => $groupData['name'],
                    'description' => $groupData['description'],
                ]
            );

            foreach ($groupData['branches'] as $branchData) {
                \App\Models\Branch::updateOrCreate(
                    ['code' => $branchData['code']],
                    [
                        'group_id' => $group->id,
                        'name' => $branchData['name'],
                        'rv_prefix' => $branchData['rv_prefix'],
                        'pv_prefix' => $branchData['pv_prefix'],
                        'bf_prefix' => $branchData['bf_prefix'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
