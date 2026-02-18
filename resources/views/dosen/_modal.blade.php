<!-- Add/Edit Dosen Modal -->
<div class="modal fade" id="dosenModal" tabindex="-1" aria-labelledby="dosenModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dosenModalLabel">
                    <i class="ri-user-add-line me-2"></i><span>Tambah Dosen Baru</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formDosen" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-body">
                    @include('dosen._form', ['dosen' => null, 'readonly' => false])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>