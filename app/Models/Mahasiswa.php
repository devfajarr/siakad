<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswas';
    // Allow mass assignment for all except ID (handled by Auto-Increment)
    protected $guarded = ['id'];
    protected $fillable = [
        'id_mahasiswa',
        'id_feeder',
        'nama_mahasiswa',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'id_agama',
        'nik',
        'nisn',
        'nama_ibu_kandung',
        'id_wilayah',
        'kelurahan',
        'handphone',
        'email',
        'id_prodi',
        'user_id',
        'bypass_krs_until',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected $casts = [
        'tanggal_lahir' => 'date',
        'penerima_kps' => 'boolean',
        'is_synced' => 'boolean',
        'last_sync' => 'datetime',
        'tgl_lahir_ayah' => 'date',
        'tgl_lahir_ibu' => 'date',
        'tgl_lahir_wali' => 'date',
        'bypass_krs_until' => 'datetime',
    ];

    /**
     * Mutator: Ensure email is lowercase.
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Mutator: Sanitize Handphone (Numbers only).
     */
    public function setHandphoneAttribute($value)
    {
        $this->attributes['handphone'] = preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Mutator: Sanitize NISN (Numbers only).
     */
    public function setNisnAttribute($value)
    {
        $this->attributes['nisn'] = preg_replace('/[^0-9]/', '', $value);
    }

    public function riwayatPendidikans()
    {
        return $this->hasMany(RiwayatPendidikan::class, 'id_mahasiswa', 'id');
    }

    public function riwayatAktif()
    {
        return $this->hasOne(RiwayatPendidikan::class, 'id_mahasiswa', 'id')
            ->latest('id_periode_masuk'); // Assuming logic for "active" is latest period or based on status logic if available
    }

    // Relationships needed for Detail View
    public function agama()
    {
        return $this->belongsTo(Agama::class, 'id_agama', 'id_agama');
    }

    /**
     * Accessor: Get NIM from active education history.
     */
    public function getNimAttribute()
    {
        return $this->riwayatAktif?->nim;
    }

    public function jenisTinggal()
    {
        return $this->belongsTo(JenisTinggal::class, 'id_jenis_tinggal', 'id_jenis_tinggal');
    }

    public function alatTransportasi()
    {
        return $this->belongsTo(AlatTransportasi::class, 'id_alat_transportasi', 'id_alat_transportasi');
    }

    public function wilayah()
    {
        return $this->belongsTo(ReferenceWilayah::class, 'id_wilayah', 'id_wilayah');
    }

    /**
     * Dapatkan Dosen PA (Dinamis per Semester Aktif & Prodi).
     */
    public function getDosenPembimbingAttribute()
    {
        $activeSemesterId = getActiveSemesterId();
        $idProdi = $this->riwayatAktif?->id_prodi;

        if (!$idProdi || !$activeSemesterId)
            return null;

        $pa = PembimbingAkademik::where('id_prodi', $idProdi)
            ->where('id_semester', $activeSemesterId)
            ->first();

        return $pa ? $pa->dosen : null;
    }

    // Note: Parent education/job/income are stored as IDs on this table (e.g. id_pekerjaan_ayah), 
    // so we don't usually map them as 'pekerjaan' (singular) for the student.
    // If needed specifically for ayah/ibu, we can add:
    // public function pekerjaanAyah() { return $this->belongsTo(Pekerjaan::class, 'id_pekerjaan_ayah', 'id_pekerjaan'); }
}
