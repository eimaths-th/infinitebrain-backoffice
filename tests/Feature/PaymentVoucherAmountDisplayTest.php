<?php

namespace Tests\Feature;

use App\Models\PaymentVoucher;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaymentVoucherAmountDisplayTest extends TestCase
{
    private string $temporary_view_path;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        Schema::create('payment_voucher_categories', function (Blueprint $table_schema) {
            $table_schema->id();
            $table_schema->string('key')->unique();
            $table_schema->string('label');
            $table_schema->boolean('is_active')->default(true);
            $table_schema->integer('sort_order')->default(0);
            $table_schema->timestamps();
            $table_schema->softDeletes();
        });

        Schema::create('activity_logs', function (Blueprint $table_schema) {
            $table_schema->id();
            $table_schema->unsignedBigInteger('user_id')->nullable();
            $table_schema->string('action');
            $table_schema->text('description')->nullable();
            $table_schema->string('ip_address')->nullable();
            $table_schema->string('user_agent')->nullable();
            $table_schema->json('data_before')->nullable();
            $table_schema->json('data_after')->nullable();
            $table_schema->string('model_type')->nullable();
            $table_schema->string('model_id')->nullable();
            $table_schema->timestamps();
        });

        $test_user = new class([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]) extends User {
            public function checkPermissionTo($permission, $guard_name = null): bool
            {
                return false;
            }
        };
        $test_user->id = 1;
        $test_user->setRelation('roles', collect());
        Auth::login($test_user);

        $this->temporary_view_path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'payment-voucher-views-' . uniqid();
        mkdir($this->temporary_view_path . DIRECTORY_SEPARATOR . 'layouts', 0777, true);
        file_put_contents(
            $this->temporary_view_path . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main_template.blade.php',
            "@yield('content')\n@yield('script')"
        );
        $this->app['view']->getFinder()->prependLocation($this->temporary_view_path);
    }

    protected function tearDown(): void
    {
        Auth::logout();
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('payment_voucher_categories');

        $layout_path = $this->temporary_view_path . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'main_template.blade.php';
        if (file_exists($layout_path)) {
            unlink($layout_path);
        }
        $layouts_path = dirname($layout_path);
        if (is_dir($layouts_path)) {
            rmdir($layouts_path);
        }
        if (is_dir($this->temporary_view_path)) {
            rmdir($this->temporary_view_path);
        }

        parent::tearDown();
    }

    /** @test */
    public function IndexDisplaysNetAmountInTheVoucherAmountColumn(): void
    {
        $payment_voucher = new PaymentVoucher([
            'voucher_no' => 'EiRP20260902',
            'voucher_date' => '2026-09-03',
            'group_branch_code' => 'EiMaths',
            'pay_to' => 'คุณธัญชัย ลาทอง',
            'total_amount' => '4400.00',
            'total_deduction_amount' => '0.00',
            'withholding_tax_amount' => '204.19',
            'net_amount' => '4195.81',
            'status' => PaymentVoucher::STATUS_PENDING,
            'is_salary' => false,
            'is_evaluation' => false,
            'created_by_name' => 'Test User',
        ]);
        $payment_voucher->id = 742;

        $payment_vouchers = new LengthAwarePaginator(collect([$payment_voucher]), 1, 20, 1, [
            'path' => '/payment-voucher',
        ]);

        $rendered_html = view('payment_voucher.index', [
            'vouchers' => $payment_vouchers,
            'branchCodes' => [],
            'groupBranchCodes' => [],
        ])->render();

        $this->assertStringContainsString('4,195.81', $rendered_html);
        $this->assertStringNotContainsString('4,400.00', $rendered_html);
    }
}
