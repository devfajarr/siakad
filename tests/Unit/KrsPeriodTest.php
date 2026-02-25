<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\KrsPeriod;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class KrsPeriodTest extends TestCase
{
    /**
     * Test isOpen accessor logic.
     */
    public function test_krs_period_is_open_logic(): void
    {
        // 1. Inactive Period
        $period = new KrsPeriod([
            'is_active' => false,
            'tgl_mulai' => now()->subDay(),
            'tgl_selesai' => now()->addDay(),
        ]);
        $this->assertFalse($period->is_open);

        // 2. Active but not yet started
        $period = new KrsPeriod([
            'is_active' => true,
            'tgl_mulai' => now()->addDay(),
            'tgl_selesai' => now()->addDays(2),
        ]);
        $this->assertFalse($period->is_open);

        // 3. Active and within range
        $period = new KrsPeriod([
            'is_active' => true,
            'tgl_mulai' => now()->subDay(),
            'tgl_selesai' => now()->addDay(),
        ]);
        $this->assertTrue($period->is_open);

        // 4. Active but already ended
        $period = new KrsPeriod([
            'is_active' => true,
            'tgl_mulai' => now()->subDays(2),
            'tgl_selesai' => now()->subDay(),
        ]);
        $this->assertFalse($period->is_open);
    }
}
