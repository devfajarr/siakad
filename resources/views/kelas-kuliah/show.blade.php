@extends('layouts.app')

@section('title', 'Detail Kelas Kuliah')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Perkuliahan / Kelas Kuliah /</span> Detail
    </h4>

    @include('kelas-kuliah._detail', [
        'kelasKuliah' => $kelasKuliah,
        'isEditMode' => $isEditMode ?? false,
    ])
@endsection

