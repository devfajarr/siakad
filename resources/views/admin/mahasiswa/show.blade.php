@extends('layouts.app')

@section('title', 'Detail Mahasiswa')

@section('content')
    <div class="row">
        <!-- Header -->
        <div class="col-12 mb-4">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Mahasiswa /</span> Detail Mahasiswa</h4>
        </div>

        <!-- Submenu -->
        <div class="col-12 mb-4">
            @include('admin.mahasiswa.partials._submenu')
        </div>

        <!-- Content based on Route -->
        @if(request()->routeIs('admin.mahasiswa.detail'))
            @include('admin.mahasiswa.partials._detail')
        @elseif(request()->routeIs('admin.mahasiswa.histori'))
            <div class="col-12 mb-4">
                @include('admin.mahasiswa.partials._histori')
            </div>
        @elseif(request()->routeIs('admin.mahasiswa.krs'))
            <div class="col-12 mb-4">
                @include('admin.mahasiswa.partials._krs')
            </div>
        @elseif(request()->routeIs('admin.mahasiswa.akun'))
            <div class="col-12 mb-4">
                @include('admin.mahasiswa.partials._akun')
            </div>
        @endif

    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/flatpickr/flatpickr.css') }}" />
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/libs/flatpickr/flatpickr.js') }}"></script>
    <script>
        $(function () {
            // Initialize Flatpickr if present
            if ($('.flatpickr-input').length) {
                $('.flatpickr-input').flatpickr({
                    dateFormat: 'Y-m-d',
                    monthSelectorType: 'static'
                });
            }
        });
    </script>
    {{-- Stack additional scripts from partials if needed --}}
    @stack('partial_scripts')
@endpush