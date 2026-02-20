<?php

namespace Modules\Akademiks\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pegawai extends Model
{
    use SoftDeletes;

    protected $table = 'pegawais';

    protected $guarded = ['id'];

    public function user()
    {
        return $this->morphOne(User::class, 'profileable');
    }
}
