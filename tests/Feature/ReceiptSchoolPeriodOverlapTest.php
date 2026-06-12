<?php

namespace Tests\Feature;

use App\Http\Controllers\ReceiptSchoolController;
use App\Models\SchoolPrice;
use App\Services\PromotionEligibilityService;
use App\Services\ReceiptPromotionUsageService;
use App\Services\StatusService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ReceiptSchoolPeriodOverlapTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        Schema::create('school_price', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('student_id')->nullable();
            $table->string('subject')->nullable();
            $table->string('period')->nullable();
            $table->integer('course_type')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('students');
        Schema::dropIfExists('school_price');

        Mockery::close();

        parent::tearDown();
    }

    /** @test */
    public function PeriodOverlapIgnoresBookOnlyRecords()
    {
        SchoolPrice::create([
            'student_id' => 10,
            'subject' => 20,
            'period' => '2026-06 - 2026-06',
            'course_type' => 2,
        ]);

        $overlap = $this->InvokeCheckPeriodOverlap(
            10,
            20,
            '2026-06 - 2026-06'
        );

        $this->assertNull($overlap);
    }

    /** @test */
    public function PeriodOverlapStillDetectsCourseRecords()
    {
        SchoolPrice::create([
            'student_id' => 10,
            'subject' => 20,
            'period' => '2026-06 - 2026-06',
            'course_type' => 1,
        ]);

        $overlap = $this->InvokeCheckPeriodOverlap(
            10,
            20,
            '2026-06 - 2026-06'
        );

        $this->assertNotNull($overlap);
        $this->assertSame('2026-06 - 2026-06', $overlap['existing_period']);
    }

    private function InvokeCheckPeriodOverlap($student_id, $subject_id, $period)
    {
        $controller = new ReceiptSchoolController(
            Mockery::mock(StatusService::class),
            Mockery::mock(PromotionEligibilityService::class),
            Mockery::mock(ReceiptPromotionUsageService::class)
        );

        $method = new \ReflectionMethod($controller, 'CheckPeriodOverlap');
        $method->setAccessible(true);

        return $method->invoke($controller, $student_id, $subject_id, $period);
    }
}
