<?php

namespace App\Console\Commands;

use App\Models\ReferenceWilayah;
use Illuminate\Console\Command;

class TestWilayahHierarchy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wilayah:test-hierarchy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Wilayah Model Hierarchy and Scopes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Wilayah Hierarchy Test...');

        // 1. Test Scope Provinsi
        $this->info('1. Testing scopeProvinsi()...');
        $provinsi = ReferenceWilayah::provinsi()->limit(5)->get();
        if ($provinsi->isEmpty()) {
            $this->warn('No Provinsi data found. Please run sync:wilayah first.');
            return Command::FAILURE;
        }

        $this->table(['ID', 'Nama', 'Level'], $provinsi->map(fn($item) => [
            $item->id_wilayah,
            $item->nama_wilayah,
            $item->id_level_wilayah
        ]));
        $this->newLine();

        // 2. Pick one random Provinsi and find its Kabupatens
        $selectedProvinsi = $provinsi->random();
        $this->info("2. Testing children() & scopeKabupaten() for Provinsi: {$selectedProvinsi->nama_wilayah} ({$selectedProvinsi->id_wilayah})...");

        // Method 1: Via Relationship
        $kabupatensRel = $selectedProvinsi->children()->where('id_level_wilayah', 2)->limit(5)->get();
        // Method 2: Via Helper Static
        $kabupatensStatic = ReferenceWilayah::getKabupatenByProvinsi($selectedProvinsi->id_wilayah)->take(5);

        $this->info("   > Via Relationship children() (First 5):");
        $this->table(['ID', 'Nama', 'Parent ID'], $kabupatensRel->map(fn($item) => [
            $item->id_wilayah,
            $item->nama_wilayah,
            $item->id_induk_wilayah
        ]));

        $this->info("   > Via Static Helper getKabupatenByProvinsi() (First 5):");
        $this->table(['ID', 'Nama', 'Parent ID'], $kabupatensStatic->map(fn($item) => [
            $item->id_wilayah,
            $item->nama_wilayah,
            $item->id_induk_wilayah
        ]));
        $this->newLine();

        if ($kabupatensRel->isEmpty()) {
            $this->warn("No Kabupaten found for Provinsi {$selectedProvinsi->nama_wilayah}. Assuming data incomplete or this is a special region.");
        } else {
            // 3. Pick one random Kabupaten and find its Kecamatans
            $selectedKabupaten = $kabupatensRel->random();
            $this->info("3. Testing children() & scopeKecamatan() for Kabupaten: {$selectedKabupaten->nama_wilayah} ({$selectedKabupaten->id_wilayah})...");

            // Method 1: Via Relationship
            $kecamatansRel = $selectedKabupaten->children()->where('id_level_wilayah', 3)->limit(5)->get();
            // Method 2: Via Helper Static
            $kecamatansStatic = ReferenceWilayah::getKecamatanByKabupaten($selectedKabupaten->id_wilayah)->take(5);


            $this->info("   > Via Relationship children() (First 5):");
            $this->table(['ID', 'Nama', 'Parent ID'], $kecamatansRel->map(fn($item) => [
                $item->id_wilayah,
                $item->nama_wilayah,
                $item->id_induk_wilayah
            ]));

            $this->info("   > Via Static Helper getKecamatanByKabupaten() (First 5):");
            $this->table(['ID', 'Nama', 'Parent ID'], $kecamatansStatic->map(fn($item) => [
                $item->id_wilayah,
                $item->nama_wilayah,
                $item->id_induk_wilayah
            ]));

            // 4. Test Reverse Relationship (Parent)
            if ($kecamatansRel->isNotEmpty()) {
                $selectedKecamatan = $kecamatansRel->first();
                $this->newLine();
                $this->info("4. Testing parent() relationship for Kecamatan: {$selectedKecamatan->nama_wilayah}...");
                $parent = $selectedKecamatan->parent;
                if ($parent) {
                    $this->info("   > Parent found: {$parent->nama_wilayah} ({$parent->id_wilayah}) - Should match Kabupaten {$selectedKabupaten->nama_wilayah}");
                } else {
                    $this->error("   > Parent NOT found!");
                }
            }
        }

        $this->newLine();
        $this->info('Wilayah Hierarchy Test Completed.');
        return Command::SUCCESS;
    }
}
