@extends('layouts.app')

@section('title', 'Detail Mahasiswa')

@section('content')
    <div class="row">
        <!-- Header -->
        <div class="col-12 mb-4">
            <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Mahasiswa /</span> Detail Mahasiswa</h4>
        </div>

        <!-- Submenu -->
        @include('mahasiswa.partials._submenu')

        <!-- Content based on Route -->
        @if(request()->routeIs('admin.mahasiswa.detail'))
            @include('mahasiswa.partials._detail')
        @elseif(request()->routeIs('admin.mahasiswa.histori'))
            @include('mahasiswa.partials._histori')
        @elseif(request()->routeIs('admin.mahasiswa.krs'))
            @include('mahasiswa.partials._krs')
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