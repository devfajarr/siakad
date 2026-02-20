@extends('layouts.app')

@section('title', 'Edit Kelas Kuliah')

@section('content')
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Perkuliahan / Kelas Kuliah /</span> Edit
    </h4>

    @include('kelas-kuliah._detail', [
        'kelasKuliah' => $kelasKuliah,
        'isEditMode' => $isEditMode ?? true,
    ])
@endsection

