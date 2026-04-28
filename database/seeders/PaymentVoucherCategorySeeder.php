<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentVoucherCategory;
use App\Models\PaymentVoucher;

class PaymentVoucherCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = PaymentVoucher::$categoryLabels;
        
        $order = 1;
        foreach ($categories as $key => $label) {
            PaymentVoucherCategory::firstOrCreate(
                ['key' => $key],
                [
                    'label' => $label,
                    'is_active' => true,
                    'sort_order' => $order++
                ]
            );
        }
        
        $this->command->info('Payment Voucher Categories seeded successfully.');
    }
}
