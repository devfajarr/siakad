@extends('layouts.app')

@push('css')
    <style>
        .form-builder-card {
            border-left: 5px solid #696cff;
            transition: all 0.3s ease;
        }
        .form-builder-card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }
        .draggable-handle {
            cursor: move;
            color: #b5b6ba;
        }
        .draggable-handle:hover {
            color: #696cff;
        }
    </style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h4 class="fw-bold mb-1">
                <a href="{{ route('dosen.kuisioner.index') }}" class="text-muted fw-light">Kuisioner /</a> Form Builder
            </h4>
            <div class="d-flex align-items-center gap-2 mt-2">
                <span class="badge bg-label-primary fs-6">{{ $kuisioner->judul }}</span>
                @if($kuisioner->status === 'published')
                    <span class="badge bg-label-success"><i class="ri-checkbox-circle-line me-1"></i> Active</span>
                @elseif($kuisioner->status === 'draft')
                    <span class="badge bg-label-secondary">Draft Mode</span>
                @else
                    <span class="badge bg-danger">Closed</span>
                @endif
                <span class="text-muted fs-tiny"><i class="ri-calendar-line me-1"></i>{{ $kuisioner->semester->nama_semester ?? '' }}</span>
            </div>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('dosen.kuisioner.index') }}" class="btn btn-outline-secondary me-2">
                <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
            @if($kuisioner->status === 'draft')
                <!-- Tombol Add Pertanyaan Baru yang akan trigger via JS -->
                <button type="button" class="btn btn-primary" onclick="tambahBlokPertanyaan()">
                    <i class="ri-add-line me-1"></i> Tambah Pertanyaan
                </button>
            @endif
        </div>
    </div>

    @include('components.alert')
    
    @if($kuisioner->status === 'published')
        <div class="alert alert-warning alert-dismissible mb-4" role="alert">
            <h6 class="alert-heading d-flex align-items-center mb-1"><i class="ri-alert-line me-2"></i>Kuesioner Sedang Aktif!</h6>
            <p class="mb-0">Kuesioner saat ini berstatus <strong>Published</strong>. Formulir dikunci dan Anda tidak dapat menambah/mengedit pertanyaan untuk menjaga integritas data jawaban mahasiswa yang masuk.</p>
        </div>
    @endif

    <div class="row">
        <!-- Form Kuesioner Area -->
        <div class="col-12" id="kumpulan-pertanyaan">
            <form action="{{ route('dosen.kuisioner.pertanyaan.sync', $kuisioner->id) }}" method="POST" id="form-builder">
                @csrf
                <div id="drag-container">
                    @forelse($kuisioner->pertanyaans as $index => $q)
                        <div class="card mb-3 form-builder-card pertanyaan-item" data-id="{{ $q->id }}">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-1 text-center py-2 draggable-handle @if($kuisioner->status === 'published') d-none @endif" title="Drag to reorder">
                                        <i class="ri-draggable ri-2x"></i>
                                        <input type="hidden" name="pertanyaan[{{ $index }}][id]" value="{{ $q->id }}">
                                        <input type="hidden" name="pertanyaan[{{ $index }}][urutan]" value="{{ $q->urutan }}" class="input-urutan">
                                    </div>
                                    <div class="{{ $kuisioner->status === 'published' ? 'col-12' : 'col-11' }}">
                                        <div class="row g-3">
                                            <div class="col-md-8">
                                                <input type="text" class="form-control form-control-lg fw-medium input-teks-pertanyaan" 
                                                       name="pertanyaan[{{ $index }}][teks_pertanyaan]" 
                                                       value="{{ $q->teks_pertanyaan }}" 
                                                       placeholder="Ketik pertanyaan disini..." required 
                                                       {{ $kuisioner->status === 'published' ? 'readonly' : '' }}>
                                            </div>
                                            <div class="col-md-4">
                                                <select class="form-select select-tipe-input" 
                                                        name="pertanyaan[{{ $index }}][tipe_input]" 
                                                        {{ $kuisioner->status === 'published' ? 'disabled' : '' }}>
                                                    <option value="likert" {{ $q->tipe_input === 'likert' ? 'selected' : '' }}>🌟 Skala Likert (1-5)</option>
                                                    <option value="pilihan_ganda" {{ $q->tipe_input === 'pilihan_ganda' ? 'selected' : '' }}>◉ Pilihan Ganda</option>
                                                    <option value="esai" {{ $q->tipe_input === 'esai' ? 'selected' : '' }}>📝 Teks Bebas (Esai)</option>
                                                </select>
                                                <!-- Hidden input needed because disabled select doesn't POST -->
                                                @if($kuisioner->status === 'published')
                                                    <input type="hidden" name="pertanyaan[{{ $index }}][tipe_input]" value="{{ $q->tipe_input }}">
                                                @endif
                                            </div>
                                            
                                            <!-- Container Jawaban Dinamis (Bergantung pada Dropdown) -->
                                            <div class="col-12 mt-3 opsi-container">
                                                @if($q->tipe_input === 'likert')
                                                    <div class="alert alert-secondary py-2 mb-0 likert-preview">
                                                        <div class="d-flex justify-content-between text-muted fs-tiny mb-1">
                                                            <span>Sangat Kurang (1)</span>
                                                            <span>Sangat Baik (5)</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">1</label></div>
                                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">2</label></div>
                                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">3</label></div>
                                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">4</label></div>
                                                            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">5</label></div>
                                                        </div>
                                                    </div>
                                                @elseif($q->tipe_input === 'esai')
                                                     <textarea class="form-control" rows="2" placeholder="Jawaban panjang oleh mahasiswa..." disabled></textarea>
                                                @elseif($q->tipe_input === 'pilihan_ganda')
                                                    <div class="pilihan-ganda-wrapper">
                                                        <label class="form-label fs-tiny text-muted">Definisikan Opsi (Pisahkan dengan Koma)</label>
                                                        <input type="text" class="form-control" name="pertanyaan[{{ $index }}][opsi_jawaban]" 
                                                               value="{{ is_array($q->opsi_jawaban) ? implode(',', $q->opsi_jawaban) : '' }}" 
                                                               placeholder="Misal: Ya, Tidak, Mungkin" 
                                                               {{ $kuisioner->status === 'published' ? 'readonly' : '' }}>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if($kuisioner->status !== 'published')
                                    <div class="d-flex justify-content-end border-top pt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-pertanyaan" onclick="hapusBlok(this)">
                                            <i class="ri-delete-bin-line me-1"></i> Hapus
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5" id="empty-state">
                            <i class="ri-survey-line ri-4x text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">Awali Formulir Kuesioner Ini</h5>
                            <p class="text-muted">Klik tombol "Tambah Pertanyaan" di pojok kanan atas untuk mulai menyusun lembar kinerja dosen atau instrumen pelayanan akademik.</p>
                        </div>
                    @endforelse
                </div>

                @if($kuisioner->status === 'draft')
                    <div class="card mt-4 shadow-none bg-transparent mb-5 pb-5">
                        <div class="card-body text-end p-0">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="ri-save-3-line me-1"></i> Simpan Desain Kuesioner
                            </button>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Template Kosong untuk Javascript Injection -->
<template id="template-pertanyaan">
    <div class="card mb-3 form-builder-card pertanyaan-item">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-1 text-center py-2 draggable-handle" title="Drag to reorder">
                    <i class="ri-draggable ri-2x"></i>
                    <input type="hidden" name="pertanyaan[XX][id]" value="">
                    <input type="hidden" name="pertanyaan[XX][urutan]" value="" class="input-urutan">
                </div>
                <div class="col-11">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <input type="text" class="form-control form-control-lg fw-medium input-teks-pertanyaan" 
                                   name="pertanyaan[XX][teks_pertanyaan]" 
                                   placeholder="Ketik pertanyaan disini..." required>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select select-tipe-input" name="pertanyaan[XX][tipe_input]">
                                <option value="likert">🌟 Skala Likert (1-5)</option>
                                <option value="pilihan_ganda">◉ Pilihan Ganda</option>
                                <option value="esai">📝 Teks Bebas (Esai)</option>
                            </select>
                        </div>
                        
                        <!-- Container Preview Opsi -->
                        <div class="col-12 mt-3 opsi-container">
                             <!-- Default Likert Template -->
                             <div class="alert alert-secondary py-2 mb-0 likert-preview">
                                <div class="d-flex justify-content-between text-muted fs-tiny mb-1">
                                    <span>Sangat Kurang (1)</span>
                                    <span>Sangat Baik (5)</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">1</label></div>
                                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">2</label></div>
                                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">3</label></div>
                                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">4</label></div>
                                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">5</label></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end border-top pt-2">
                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-pertanyaan" onclick="hapusBlok(this)">
                    <i class="ri-delete-bin-line me-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>
</template>

<!-- HTML Templates for different input kinds -->
<template id="tpl-likert">
    <div class="alert alert-secondary py-2 mb-0 likert-preview">
        <div class="d-flex justify-content-between text-muted fs-tiny mb-1">
            <span>Sangat Kurang (1)</span>
            <span>Sangat Baik (5)</span>
        </div>
        <div class="d-flex justify-content-between">
            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">1</label></div>
            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">2</label></div>
            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">3</label></div>
            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">4</label></div>
            <div class="form-check form-check-inline"><input class="form-check-input" type="radio" disabled><label class="form-check-label">5</label></div>
        </div>
    </div>
</template>

<template id="tpl-esai">
    <textarea class="form-control" rows="2" placeholder="Jawaban panjang oleh mahasiswa..." disabled></textarea>
</template>

<template id="tpl-pilihan_ganda">
    <div class="pilihan-ganda-wrapper">
        <label class="form-label fs-tiny text-muted">Definisikan Opsi (Pisahkan dengan Koma)</label>
        <input type="text" class="form-control" name="pertanyaan[XX][opsi_jawaban]" placeholder="Misal: Ya, Tidak, Mungkin">
        <div class="form-text">Contoh penulisan: Sangat Setuju, Setuju, Netral, Tidak Setuju</div>
    </div>
</template>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    let questionCounter = {{ count($kuisioner->pertanyaans) }};

    function updateUrutanAndNames() {
        $('.pertanyaan-item').each(function(index) {
            // Update hidden ID urutannya
            $(this).find('.input-urutan').val(index + 1);
            
            // Re-index their input names to preserve nested array structure
            $(this).find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    name = name.replace(/pertanyaan\[\d+\]/, 'pertanyaan[' + index + ']');
                    name = name.replace(/pertanyaan\[XX\]/, 'pertanyaan[' + index + ']'); // Catch new ones
                    $(this).attr('name', name);
                }
            });
        });
    }

    function hapusBlok(btn) {
        $(btn).closest('.pertanyaan-item').slideUp(300, function() {
            $(this).remove();
            updateUrutanAndNames();
            
            if ($('.pertanyaan-item').length === 0) {
                $('#empty-state').show();
            }
        });
    }

    function tambahBlokPertanyaan() {
        $('#empty-state').hide();
        
        var template = document.getElementById('template-pertanyaan').innerHTML;
        // Ganti token XX dengan counter saat ini
        var html = template.replace(/XX/g, questionCounter);
        
        // Append animasi smooth
        var $newEl = $(html).hide();
        $('#drag-container').append($newEl);
        $newEl.slideDown(300);
        
        questionCounter++;
        updateUrutanAndNames();
        bindDropdownChangeEvent($newEl);
        
        // Scroll ke bawah
        $('html, body').animate({
            scrollTop: $newEl.offset().top - 100
        }, 500);
    }

    function bindDropdownChangeEvent(context) {
        // Deteksi perubahan tipe pertanyaan untuk ngerender input opsi
        $(context).find('.select-tipe-input').on('change', function() {
            var selectedType = $(this).val();
            var $container = $(this).closest('.row').find('.opsi-container');
            var masterIndex = $(this).closest('.pertanyaan-item').find('.input-urutan').val() - 1; // get actual arr index

            // Clear container
            $container.empty();

            // Inject matching template
            var templateHtml = document.getElementById('tpl-' + selectedType).innerHTML;
            
            // Replace XX format jika template butuh input name spesifik
            templateHtml = templateHtml.replace(/XX/g, masterIndex);
            
            $container.hide().html(templateHtml).slideDown(200);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize existing dropdown binders
        bindDropdownChangeEvent(document);

        @if($kuisioner->status === 'draft')
        // Inisialisasi Sortable JS untuk Drag and Drop jika mode DRAFT
        var el = document.getElementById('drag-container');
        if (el) {
            var sortable = Sortable.create(el, {
                handle: '.draggable-handle',
                animation: 150,
                ghostClass: 'bg-label-primary',
                onEnd: function () {
                    // Update index urutan setiap kali posisinya di drop
                    updateUrutanAndNames();
                }
            });
        }
        @endif
        
        // Prevent form submission if empty
        $('#form-builder').on('submit', function(e) {
            if ($('.pertanyaan-item').length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Formulir Kosong',
                    text: 'Anda harus menambahkan minimal 1 pertanyaan sebelum menyimpan!',
                    confirmButtonText: 'Mengerti'
                });
            }
        });
    });
</script>
@endpush
