<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const MONITORED_TABLES = [
        'mata_kuliahs',
        'kurikulums',
        'matkul_kurikulums',
        'kelas_kuliah',
        'kelas_dosen',
        'dosen_pengajar_kelas_kuliah',
        'peserta_kelas_kuliah',
    ];

    private const SOURCE_DEFAULTS = [
        'mata_kuliahs' => 'server',
        'kurikulums' => 'lokal',
        'matkul_kurikulums' => 'server',
        'kelas_kuliah' => 'lokal',
        'kelas_dosen' => 'lokal',
        'dosen_pengajar_kelas_kuliah' => 'lokal',
        'peserta_kelas_kuliah' => 'lokal',
    ];

    private const STATUS_DEFAULTS = [
        'mata_kuliahs' => 'synced',
        'kurikulums' => 'created_local',
        'matkul_kurikulums' => 'synced',
        'kelas_kuliah' => 'created_local',
        'kelas_dosen' => 'created_local',
        'dosen_pengajar_kelas_kuliah' => 'created_local',
        'peserta_kelas_kuliah' => 'created_local',
    ];

    private const TABLES_MISSING_SYNC_ACTION = [
        'mata_kuliahs',
        'kurikulums',
        'matkul_kurikulums',
        'kelas_kuliah',
        'dosen_pengajar_kelas_kuliah',
        'peserta_kelas_kuliah',
    ];

    public function up(): void
    {
        $this->addUniversalMissingColumns();
        $this->completeMatkulKurikulumColumns();
        $this->standardizeMonitoringTypes();
        $this->addMonitoringIndexes();
    }

    public function down(): void
    {
        $this->dropMonitoringIndexes();
        $this->revertMatkulKurikulumErrorColumn();
        $this->revertMonitoringTypes();
        $this->dropUniversalColumns();
    }

    private function addUniversalMissingColumns(): void
    {
        foreach (self::TABLES_MISSING_SYNC_ACTION as $tableName) {
            if ($this->tableHasNoColumn($tableName, 'sync_action')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('sync_action', 20)->default('insert');
                });
            }
        }

        foreach (self::MONITORED_TABLES as $tableName) {
            if ($this->tableHasNoColumn($tableName, 'is_local_change')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->boolean('is_local_change')->default(false);
                });
            }

            if ($this->tableHasNoColumn($tableName, 'is_deleted_local')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->boolean('is_deleted_local')->default(false);
                });
            }
        }
    }

    private function completeMatkulKurikulumColumns(): void
    {
        $tableName = 'matkul_kurikulums';

        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (! Schema::hasColumn($tableName, 'sumber_data')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('sumber_data', 20)->default('server');
            });
        }

        if (! Schema::hasColumn($tableName, 'last_synced_at')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->timestamp('last_synced_at')->nullable();
            });
        }

        if (! Schema::hasColumn($tableName, 'last_push_at')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->timestamp('last_push_at')->nullable();
            });
        }

        if (Schema::hasColumn($tableName, 'sync_error_message') && ! Schema::hasColumn($tableName, 'error_message')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('sync_error_message', 'error_message');
            });
        }

        if (! Schema::hasColumn($tableName, 'error_message')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->text('error_message')->nullable();
            });
        }
    }

    private function standardizeMonitoringTypes(): void
    {
        $this->normalizeKelasDosenSourceColumnName();

        foreach (self::STATUS_DEFAULTS as $tableName => $defaultStatus) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'status_sinkronisasi')) {
                Schema::table($tableName, function (Blueprint $table) use ($defaultStatus) {
                    $table->string('status_sinkronisasi', 50)->default($defaultStatus)->change();
                });
            }
        }

        foreach (self::SOURCE_DEFAULTS as $tableName => $defaultSource) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'sumber_data')) {
                Schema::table($tableName, function (Blueprint $table) use ($defaultSource) {
                    $table->string('sumber_data', 20)->default($defaultSource)->change();
                });
            }
        }

        $this->normalizeStatusValues();
        $this->normalizeSourceValues();
    }

    private function normalizeKelasDosenSourceColumnName(): void
    {
        $tableName = 'kelas_dosen';

        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (Schema::hasColumn($tableName, 'is_from_server') && ! Schema::hasColumn($tableName, 'sumber_data')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('is_from_server', 'sumber_data');
            });
        }
    }

    private function normalizeStatusValues(): void
    {
        foreach (self::MONITORED_TABLES as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'status_sinkronisasi')) {
                continue;
            }

            DB::table($tableName)
                ->where('status_sinkronisasi', 'failed')
                ->update(['status_sinkronisasi' => 'push_failed']);
        }

        if (Schema::hasTable('kelas_dosen') && Schema::hasColumn('kelas_dosen', 'status_sinkronisasi')) {
            DB::table('kelas_dosen')
                ->where('status_sinkronisasi', 'pending')
                ->update(['status_sinkronisasi' => 'created_local']);
        }
    }

    private function normalizeSourceValues(): void
    {
        foreach (self::MONITORED_TABLES as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'sumber_data')) {
                continue;
            }

            DB::table($tableName)
                ->whereIn('sumber_data', [1, '1', true, 'true', 'TRUE', 'server', 'SERVER', 'Server', 'pusat', 'PUSAT', 'Pusat'])
                ->update(['sumber_data' => 'server']);

            DB::table($tableName)
                ->whereIn('sumber_data', ['lokal', 'LOKAL', 'Lokal', 0, '0', false, 'false', 'FALSE', 'local', 'LOCAL', 'Local'])
                ->update(['sumber_data' => 'lokal']);

            DB::table($tableName)
                ->where(function ($query) {
                    $query->whereNull('sumber_data')
                        ->orWhereNotIn('sumber_data', ['server', 'lokal']);
                })
                ->update(['sumber_data' => 'lokal']);
        }
    }

    private function addMonitoringIndexes(): void
    {
        foreach (self::MONITORED_TABLES as $tableName) {
            $this->addIndexIfMissing(
                $tableName,
                'status_sinkronisasi',
                "idx_{$tableName}_status_sync"
            );

            $this->addIndexIfMissing(
                $tableName,
                'sumber_data',
                "idx_{$tableName}_sumber_data"
            );
        }
    }

    private function dropMonitoringIndexes(): void
    {
        foreach (self::MONITORED_TABLES as $tableName) {
            $this->dropIndexIfExists($tableName, "idx_{$tableName}_status_sync");
            $this->dropIndexIfExists($tableName, "idx_{$tableName}_sumber_data");
        }
    }

    private function addIndexIfMissing(string $tableName, string $columnName, string $indexName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        if (Schema::hasIndex($tableName, [$columnName])) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName, $indexName) {
            $table->index($columnName, $indexName);
        });
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (! Schema::hasIndex($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function revertMatkulKurikulumErrorColumn(): void
    {
        $tableName = 'matkul_kurikulums';

        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (Schema::hasColumn($tableName, 'error_message') && ! Schema::hasColumn($tableName, 'sync_error_message')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('error_message', 'sync_error_message');
            });
        }
    }

    private function revertMonitoringTypes(): void
    {
        $this->revertMataKuliahStatusType();
        $this->revertSourceTypes();
        $this->revertKelasDosenColumns();
    }

    private function revertMataKuliahStatusType(): void
    {
        $tableName = 'mata_kuliahs';

        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'status_sinkronisasi')) {
            return;
        }

        DB::table($tableName)
            ->whereIn('status_sinkronisasi', ['push_failed', 'conflict'])
            ->update(['status_sinkronisasi' => 'pending_push']);

        Schema::table($tableName, function (Blueprint $table) {
            $table->enum('status_sinkronisasi', [
                'synced',
                'created_local',
                'updated_local',
                'deleted_local',
                'pending_push',
            ])->default('synced')->change();
        });
    }

    private function revertSourceTypes(): void
    {
        $tables = [
            'mata_kuliahs' => 'server',
            'kurikulums' => 'lokal',
            'matkul_kurikulums' => 'server',
            'kelas_kuliah' => 'lokal',
            'dosen_pengajar_kelas_kuliah' => 'lokal',
            'peserta_kelas_kuliah' => 'lokal',
        ];

        foreach ($tables as $tableName => $defaultSource) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'sumber_data')) {
                continue;
            }

            DB::table($tableName)
                ->where(function ($query) {
                    $query->whereNull('sumber_data')
                        ->orWhereNotIn('sumber_data', ['server', 'lokal']);
                })
                ->update(['sumber_data' => $defaultSource]);

            Schema::table($tableName, function (Blueprint $table) use ($defaultSource) {
                $table->enum('sumber_data', ['server', 'lokal'])->default($defaultSource)->change();
            });
        }
    }

    private function revertKelasDosenColumns(): void
    {
        $tableName = 'kelas_dosen';

        if (! Schema::hasTable($tableName)) {
            return;
        }

        if (Schema::hasColumn($tableName, 'status_sinkronisasi')) {
            DB::table($tableName)
                ->where('status_sinkronisasi', 'created_local')
                ->update(['status_sinkronisasi' => 'pending']);

            DB::table($tableName)
                ->where('status_sinkronisasi', 'push_failed')
                ->update(['status_sinkronisasi' => 'failed']);

            DB::table($tableName)
                ->where('status_sinkronisasi', 'conflict')
                ->update(['status_sinkronisasi' => 'updated_local']);

            Schema::table($tableName, function (Blueprint $table) {
                $table->enum('status_sinkronisasi', ['pending', 'synced', 'updated_local', 'deleted_local', 'failed'])
                    ->default('pending')
                    ->change();
            });
        }

        if (Schema::hasColumn($tableName, 'sumber_data')) {
            DB::table($tableName)
                ->where('sumber_data', 'server')
                ->update(['sumber_data' => '1']);

            DB::table($tableName)
                ->where(function ($query) {
                    $query->whereNull('sumber_data')
                        ->orWhere('sumber_data', '!=', '1');
                })
                ->update(['sumber_data' => '0']);

            Schema::table($tableName, function (Blueprint $table) {
                $table->boolean('sumber_data')->default(false)->change();
            });

            if (! Schema::hasColumn($tableName, 'is_from_server')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('sumber_data', 'is_from_server');
                });
            }
        }
    }

    private function dropUniversalColumns(): void
    {
        foreach (self::TABLES_MISSING_SYNC_ACTION as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'sync_action')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('sync_action');
                });
            }
        }

        foreach (self::MONITORED_TABLES as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'is_local_change')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('is_local_change');
                });
            }

            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'is_deleted_local')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('is_deleted_local');
                });
            }
        }
    }

    private function tableHasNoColumn(string $tableName, string $columnName): bool
    {
        return Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, $columnName);
    }
};
