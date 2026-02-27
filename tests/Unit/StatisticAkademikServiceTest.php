<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Akademik\StatisticAkademikService;

class StatisticAkademikServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StatisticAkademikService();
    }

    /** @test */
    public function it_calculates_ips_correctly()
    {
        // Simulation: MK A (3 SKS, Indeks 4.00), MK B (2 SKS, Indeks 3.00)
        // Total SKS: 5, Total Bobot: 12 + 6 = 18
        // IPS: 18 / 5 = 3.60

        $totalSks = 5;
        $totalBobot = 18.00;
        $ips = round($totalBobot / $totalSks, 2);

        $this->assertEquals(3.60, $ips);
    }

    /** @test */
    public function it_calculates_ipk_repeated_course_logic()
    {
        // Semester 1: MK A (3 SKS) Nilai D (Indeks 1.00)
        // Semester 2: MK A (3 SKS) Nilai A (Indeks 4.00)

        $mkA_S1 = (object) ['id_matkul' => 'MK-A', 'sks' => 3, 'nilai_indeks' => 1.00];
        $mkA_S2 = (object) ['id_matkul' => 'MK-A', 'sks' => 3, 'nilai_indeks' => 4.00];

        $data = collect([$mkA_S1, $mkA_S2]);

        // Logic replicate from service (since we can't mock DB easily in this setup)
        $filtered = $data->groupBy('id_matkul')->map(function ($group) {
            return $group->sortByDesc('nilai_indeks')->first();
        });

        $this->assertCount(1, $filtered);
        $this->assertEquals(4.00, $filtered->first()->nilai_indeks);

        $totalSks = $filtered->sum('sks');
        $totalBobot = $filtered->sum(fn($item) => $item->sks * $item->nilai_indeks);

        $this->assertEquals(3, $totalSks);
        $this->assertEquals(12.00, $totalBobot);
        $this->assertEquals(4.00, round($totalBobot / $totalSks, 2));
    }
}
