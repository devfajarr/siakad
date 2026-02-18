<!-- View Dosen Modal -->
<div class="modal fade" id="viewDosenModal" tabindex="-1" aria-labelledby="viewDosenModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDosenModalLabel">
                    <i class="ri-eye-line me-2"></i>Detail Dosen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('dosen._form', ['dosen' => null, 'readonly' => true])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>