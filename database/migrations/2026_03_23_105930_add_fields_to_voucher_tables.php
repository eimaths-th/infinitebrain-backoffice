<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receipt_vouchers', function (Blueprint $table) {
            $table->string('group_branch_code', 50)->nullable()->after('branch_code');
            $table->string('voucher_code', 20)->nullable()->after('group_branch_code');
        });

        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->string('group_branch_code', 50)->nullable()->after('branch_code');
            $table->string('voucher_code', 20)->nullable()->after('group_branch_code');
        });

        // Migrate existing data for receipt_vouchers
        $receiptVouchers = DB::table('receipt_vouchers')->get();
        foreach ($receiptVouchers as $rv) {
            $voucherCode = '';
            // Extract Prefix from voucher_no (non-numeric part)
            if (preg_match('/^([a-zA-Z]+)/', $rv->voucher_no, $matches)) {
                $voucherCode = $matches[1];
            }

            $branchCode = $rv->branch_code;
            $groupBranchCode = '';

            // Mapping logic for Receipt Vouchers
            if ($branchCode == 'ELRV') { $branchCode = 'EL'; $groupBranchCode = 'EL'; }
            elseif ($branchCode == 'IFRV') { $branchCode = 'IF'; $groupBranchCode = 'IF'; }
            elseif ($branchCode == 'MTRV') { $branchCode = 'META'; $groupBranchCode = 'META'; }
            elseif ($branchCode == 'EiRP') { $branchCode = 'EiRP'; $groupBranchCode = 'EiMaths'; }
            elseif ($branchCode == 'EiBK') { $branchCode = 'EiBK'; $groupBranchCode = 'EiMaths'; }
            elseif ($branchCode == 'EiHQ') { $branchCode = 'EiHQ'; $groupBranchCode = 'EiMaths'; }

            DB::table('receipt_vouchers')->where('id', $rv->id)->update([
                'branch_code' => $branchCode,
                'group_branch_code' => $groupBranchCode,
                'voucher_code' => $voucherCode ?: $rv->branch_code,
            ]);
        }

        // Migrate existing data for payment_vouchers
        $paymentVouchers = DB::table('payment_vouchers')->get();
        foreach ($paymentVouchers as $pv) {
            $voucherCode = '';
            if (preg_match('/^([a-zA-Z]+)/', $pv->voucher_no, $matches)) {
                $voucherCode = $matches[1];
            }

            $branchCode = $pv->branch_code;
            $groupBranchCode = '';

            // Mapping logic for Payment Vouchers
            if (in_array($branchCode, ['EL', 'ELPV', 'ELBF'])) {
                $groupBranchCode = 'EL';
                if ($branchCode == 'ELPV' || $branchCode == 'ELBF' || $branchCode == 'EL') { $branchCode = 'EL'; }
            } elseif (in_array($branchCode, ['IF', 'IFPV', 'IFBF'])) {
                $groupBranchCode = 'IF';
                if ($branchCode == 'IFPV' || $branchCode == 'IFBF' || $branchCode == 'IF') { $branchCode = 'IF'; }
            } elseif (in_array($branchCode, ['MT', 'META'])) {
                $groupBranchCode = 'META';
                $branchCode = 'META';
            } elseif (in_array($branchCode, ['EiRP', 'EiBK', 'EiHQ', 'EiRPRF', 'EiBKBF', 'EiHQBF'])) {
                $groupBranchCode = 'EiMaths';
                if (str_contains($branchCode, 'EiRP')) $branchCode = 'EiRP';
                elseif (str_contains($branchCode, 'EiBK')) $branchCode = 'EiBK';
                elseif (str_contains($branchCode, 'EiHQ')) $branchCode = 'EiHQ';
            }

            DB::table('payment_vouchers')->where('id', $pv->id)->update([
                'branch_code' => $branchCode,
                'group_branch_code' => $groupBranchCode,
                'voucher_code' => $voucherCode ?: $pv->branch_code,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receipt_vouchers', function (Blueprint $table) {
            $table->dropColumn(['group_branch_code', 'voucher_code']);
        });

        Schema::table('payment_vouchers', function (Blueprint $table) {
            $table->dropColumn(['group_branch_code', 'voucher_code']);
        });
    }
};
