<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class Dosen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'external_id',
        'nidn',
        'nip',
        'nama',
        'nama_alias',
        'email',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'id_agama',
        'id_status_aktif',
        'status_sinkronisasi',
        'is_active',
        'is_struktural',
        'is_pengajar',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_struktural' => 'boolean',
        'is_pengajar' => 'boolean',
        'tanggal_lahir' => 'date',
    ];

    // Scopes
    public function scopeLokal($query)
    {
        return $query->where('status_sinkronisasi', 'lokal');
    }

    public function scopePusat($query)
    {
        return $query->where('status_sinkronisasi', 'pusat');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    // Relations
    public function penugasans(): HasMany
    {
        return $this->hasMany(DosenPenugasan::class, 'id_dosen');
    }

    public function riwayatFungsionals(): HasMany
    {
        return $this->hasMany(DosenRiwayatFungsional::class, 'id_dosen');
    }

    public function riwayatPangkats(): HasMany
    {
        return $this->hasMany(DosenRiwayatPangkat::class, 'id_dosen');
    }

    public function riwayatPendidikans(): HasMany
    {
        return $this->hasMany(DosenRiwayatPendidikan::class, 'id_dosen');
    }

    public function riwayatSertifikasis(): HasMany
    {
        return $this->hasMany(DosenRiwayatSertifikasi::class, 'id_dosen');
    }

    public function riwayatPenelitians(): HasMany
    {
        return $this->hasMany(DosenRiwayatPenelitian::class, 'id_dosen');
    }

    public function pengajaranKelas(): HasMany
    {
        return $this->hasMany(DosenPengajarKelasKuliah::class, 'id_dosen');
    }

    /**
     * Relasi ke Jabatan BPMI.
     */
    public function bpmi()
    {
        return $this->hasOne(Bpmi::class, 'id_dosen');
    }

    /**
     * Relasi ke Pembimbing Akademik (Bridge).
     */
    public function pembimbingAkademik(): HasMany
    {
        return $this->hasMany(PembimbingAkademik::class, 'id_dosen');
    }

    /**
     * Relasi langsung ke Mahasiswa Bimbingan.
     */
    /**
     * Data Mahasiswa Bimbingan (Dinamis per Semester Aktif).
     */
    public function mahasiswaBimbingan()
    {
        $activeSemesterId = getActiveSemesterId();

        // Dapatkan daftar Prodi yang dibimbing oleh dosen ini di semester aktif
        $prodiIds = PembimbingAkademik::where('id_dosen', $this->id)
            ->where('id_semester', $activeSemesterId)
            ->pluck('id_prodi');

        return Mahasiswa::whereHas('riwayatAktif', function ($q) use ($prodiIds) {
            $q->whereIn('id_prodi', $prodiIds);
        });
    }

    public function akun()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Presensi yang diinput oleh Dosen ini.
     */
    /**
     * Relasi ke Presensi yang diinput oleh Dosen ini.
     */
    public function presensiPertemuans(): HasMany
    {
        return $this->hasMany(PresensiPertemuan::class, 'id_dosen');
    }

    /**
     * Accessor untuk nama tampilan dosen berdasarkan role user yang login.
     * Admin melihat nama asli, Mahasiswa/Dosen melihat alias jika ada.
     */
    public function getNamaTampilanAttribute(): string
    {
        $user = auth()->user();
        $namaAsli = $this->nama;
        $namaAlias = $this->nama_alias;

        // Jika Admin, tampilkan nama asli
        if ($user && $user->hasRole('admin')) {
            return $namaAsli;
        }

        // Selain Admin, tampilkan alias jika tidak kosong
        return !empty($namaAlias) ? $namaAlias : $namaAsli;
    }
    /**
     * Accessor untuk nama tampilan dosen khusus di halaman Admin.
     * Format: Nama Asli (Nama Alias) jika ada alias, atau Nama Asli jika tidak ada.
     */
    public function getNamaAdminDisplayAttribute(): string
    {
        $namaAsli = $this->nama;
        $namaAlias = $this->nama_alias;

        if (!empty($namaAlias)) {
            return "{$namaAsli} ({$namaAlias})";
        }

        return $namaAsli;
    }

    /**
     * Helper Method: Memastikan Dosen memiliki User Login.
     * Dipanggil oleh DosenObserver ATAU Observer Jabatan (BPMI, Kaprodi, PA).
     */
    public function generateUserIfNotExists()
    {
        // 1. Cek apakah sudah terhubung dengan User
        if ($this->akun) {
            return $this->akun;
        }

        // 2. Tentukan Login ID (Username/Password Default)
        $loginId = $this->nidn ?? $this->nip ?? strtolower(Str::random(10));
        $email = $this->email ?? ($loginId . '@polsa.ac.id');

        // 3. Cek eksistensi User berdasarkan kredensial yang mirip di database
        $user = User::where('username', $loginId)
            ->orWhere('email', $email)
            ->first();

        // 4. Jika tetap tidak ada, Buat User Baru
        if (!$user) {
            $user = User::create([
                'name' => $this->nama,
                'username' => $loginId,
                'email' => $email,
                'password' => Hash::make($loginId), // Default pass
            ]);
        }

        // 5. Sambungkan user ke entitas dosen ini dan simpan
        $this->updateQuietly(['user_id' => $user->id]);

        // 6. Pasang Base Role 'Dosen'
        $roleDosen = Role::firstOrCreate(['name' => 'Dosen']);
        $user->assignRole($roleDosen);

        return $user;
    }
}
