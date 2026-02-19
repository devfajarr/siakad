<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KelasKuliah extends Controller
{
    public function index()
    {
        return view('kelas-kuliah.index');
    }
}
