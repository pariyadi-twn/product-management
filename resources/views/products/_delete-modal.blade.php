<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="deleteProductModalLabel">Konfirmasi Hapus Produk</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-1">Yakin ingin menghapus produk <strong data-delete-product-name>ini</strong>?</p>
                    <p class="text-secondary small mb-0">
                        Produk akan dihapus dari katalog. Data tetap tersimpan untuk keperluan penelusuran aktivitas.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Hapus Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
