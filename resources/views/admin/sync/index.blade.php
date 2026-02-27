@extends('layouts.app')

@section('title', 'Centralized Sync Manager')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Monitoring Sinkronisasi Neo Feeder</h5>
                    <span class="badge bg-label-primary rounded-pill"> <i class="ri-refresh-line me-1"></i> Background
                        Process</span>
                </div>
                <div class="card-body">
                    <p class="mb-0">Pilih entitas di bawah ini untuk memulai penarikan data massal dari server Neo Feeder ke
                        sistem lokal. Proses ini berjalan secara asinkron di latar belakang.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @foreach ($entities as $entity)
            <div class="col-md-6 col-xl-3">
                <div class="card h-100 shadow-sm border-0" id="card-{{ $entity['name'] }}">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="ri-{{ $entity['icon'] }}-line ri-24px"></i>
                                </span>
                            </div>
                            <h6 class="mb-0">{{ preg_replace('/(?<!^)[A-Z]/', ' $0', $entity['name']) }}</h6>
                        </div>

                        @if ($entity['name'] === 'Nilai')
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="sync-all-nilai">
                                <label class="form-check-label small" for="sync-all-nilai">Tarik Seluruh History (Abaikan Semester
                                    Aktif)</label>
                            </div>
                        @endif

                        <div id="progress-container-{{ $entity['name'] }}" style="display: none;">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted fw-medium" id="status-text-{{ $entity['name'] }}">Processing...</small>
                                <small class="text-muted fw-medium" id="percent-{{ $entity['name'] }}">0%</small>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                    role="progressbar" id="bar-{{ $entity['name'] }}" style="width: 0%" aria-valuenow="0"
                                    aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="button" class="btn btn-primary btn-sync" data-entity="{{ $entity['name'] }}"
                                id="btn-{{ $entity['name'] }}">
                                <i class="ri-download-cloud-2-line me-1"></i> Tarik Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            $('.btn-sync').on('click', function () {
                const btn = $(this);
                const entity = btn.data('entity');
                const btnOriginalHtml = btn.html();

                // Disable button
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Preparing...');

                const filters = {};
                if (entity === 'Nilai' && $('#sync-all-nilai').is(':checked')) {
                    filters.all = true;
                }

                $.ajax({
                    url: "{{ route('admin.sync-manager.dispatch') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        entity: entity,
                        filters: filters
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            if (response.batchId) {
                                startPolling(entity, response.batchId);
                            } else {
                                Swal.fire('Info', response.message, 'info');
                                resetBtn(btn, btnOriginalHtml);
                            }
                        } else {
                            Swal.fire('Error', response.message, 'error');
                            resetBtn(btn, btnOriginalHtml);
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Internal Server Error';
                        Swal.fire('Error', msg, 'error');
                        resetBtn(btn, btnOriginalHtml);
                    }
                });
            });

            function startPolling(entity, batchId) {
                const container = $(`#progress-container-${entity}`);
                const bar = $(`#bar-${entity}`);
                const percentText = $(`#percent-${entity}`);
                const statusText = $(`#status-text-${entity}`);
                const btn = $(`#btn-${entity}`);
                let isFinished = false; // Flag to prevent multiple handling

                container.show();
                btn.prop('disabled', true).html('<i class="ri-loader-4-line ri-spin me-1"></i> Syncing...');

                function poll() {
                    if (isFinished) return;

                    $.get(`/admin/sync-manager/batch/${batchId}`, function (batch) {
                        const progress = batch.progress;
                        bar.css('width', progress + '%').attr('aria-valuenow', progress);
                        percentText.text(progress + '%');
                        statusText.text(`Jobs: ${batch.pending_jobs} pending`);

                        if (batch.finished && !isFinished) {
                            isFinished = true;
                            bar.removeClass('progress-bar-animated progress-bar-striped').addClass('bg-success');
                            statusText.text('Selesai!');
                            btn.removeClass('btn-primary').addClass('btn-success').html('<i class="ri-check-line me-1"></i> Selesai');

                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses',
                                text: `Sinkronisasi data ${entity} berhasil diselesaikan!`,
                                timer: 3000
                            });
                        } else if (batch.cancelled && !isFinished) {
                            isFinished = true;
                            Swal.fire('Warning', 'Batch dibatalkan oleh sistem.', 'warning');
                            resetBtn(btn, '<i class="ri-download-cloud-2-line me-1"></i> Tarik Data');
                        } else if (!isFinished) {
                            // Only schedule next poll if not finished
                            setTimeout(poll, 3000);
                        }
                    }).fail(function () {
                        if (!isFinished) {
                            isFinished = true;
                            Swal.fire('Error', 'Gagal memantau progres sinkronisasi.', 'error');
                            resetBtn(btn, '<i class="ri-download-cloud-2-line me-1"></i> Tarik Data');
                        }
                    });
                }

                poll(); // Start first poll
            }

            function resetBtn(btn, html) {
                btn.prop('disabled', false).html(html);
            }
        });
    </script>
@endpush