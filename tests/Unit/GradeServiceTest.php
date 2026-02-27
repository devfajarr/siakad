<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Akademik\GradeService;

class GradeServiceTest extends TestCase
{
    private GradeService $gradeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gradeService = new GradeService();
    }

    /**
     * Test perfect score scenario (100 for all components and full attendance).
     */
    public function test_calculate_final_score_perfect(): void
    {
        $components = [
            'tugas1' => 100,
            'tugas2' => 100,
            'tugas3' => 100,
            'tugas4' => 100,
            'tugas5' => 100,
            'aktif' => 100,
            'etika' => 100,
            'uts' => 100,
            'uas' => 100,
        ];

        $result = $this->gradeService->calculateFinalScore($components, 14, 14);

        $this->assertEquals(100.00, $result);
    }

    /**
     * Test attendance impact (half attendance).
     * Expected: Loss of 7.5 points (50% of 15% weight).
     */
    public function test_calculate_final_score_half_attendance(): void
    {
        $components = [
            'tugas1' => 100,
            'tugas2' => 100,
            'tugas3' => 100,
            'tugas4' => 100,
            'tugas5' => 100,
            'aktif' => 100,
            'etika' => 100,
            'uts' => 100,
            'uas' => 100,
        ];

        $result = $this->gradeService->calculateFinalScore($components, 7, 14);

        $this->assertEquals(92.50, $result);
    }

    /**
     * Test partial components with full attendance.
     */
    public function test_calculate_final_score_partial_components(): void
    {
        $components = [
            'tugas1' => 80,
            'tugas2' => 80,
            'tugas3' => 80,
            'tugas4' => 80,
            'tugas5' => 80,
            'aktif' => 90,
            'etika' => 90,
            'uts' => 70,
            'uas' => 75,
        ];

        // Calc:
        // Tugas: 80 * 0.25 = 20
        // Aktif: 90 * 0.05 = 4.5
        // Etika: 90 * 0.05 = 4.5
        // Presensi: (14/14) * 15 = 15
        // UTS: 70 * 0.25 = 17.5
        // UAS: 75 * 0.25 = 18.75
        // Total: 20 + 4.5 + 4.5 + 15 + 17.5 + 18.75 = 80.25

        $result = $this->gradeService->calculateFinalScore($components, 14, 14);

        $this->assertEquals(80.25, $result);
    }

    /**
     * Test capping logic for attendance.
     * Even if student attends 16 times (over target 14), score should remain max 15.
     */
    public function test_calculate_final_score_attendance_capping(): void
    {
        $components = [
            'tugas1' => 100,
            'tugas2' => 100,
            'tugas3' => 100,
            'tugas4' => 100,
            'tugas5' => 100,
            'aktif' => 100,
            'etika' => 100,
            'uts' => 100,
            'uas' => 100,
        ];

        $result = $this->gradeService->calculateFinalScore($components, 16, 14);

        $this->assertEquals(100.00, $result);
    }

    /**
     * Test all zero components and zero attendance.
     */
    public function test_calculate_final_score_zero(): void
    {
        $components = [
            'tugas1' => 0,
            'tugas2' => 0,
            'tugas3' => 0,
            'tugas4' => 0,
            'tugas5' => 0,
            'aktif' => 0,
            'etika' => 0,
            'uts' => 0,
            'uas' => 0,
        ];

        $result = $this->gradeService->calculateFinalScore($components, 0, 14);

        $this->assertEquals(0.00, $result);
    }
}
