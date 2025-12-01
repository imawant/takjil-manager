<div id="confirmModal" class="modal" style="z-index: 2000;">
    <div class="modal-backdrop" onclick="closeConfirmModal()"></div>
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 id="confirmTitle">Konfirmasi</h3>
            <button class="close-modal" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="confirmMessage" style="margin: 0; line-height: 1.6;"></p>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeConfirmModal()">Batal</button>
            <button class="btn-danger" id="confirmButton">Hapus</button>
        </div>
    </div>
</div>

<script>
let confirmCallback = null;

function showConfirmModal(message, title = 'Konfirmasi', callback) {
    document.getElementById('confirmTitle').innerText = title;
    document.getElementById('confirmMessage').innerText = message;
    document.getElementById('confirmModal').classList.add('active');
    confirmCallback = callback;
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('active');
    confirmCallback = null;
}

document.getElementById('confirmButton').addEventListener('click', function() {
    if (confirmCallback) {
        confirmCallback();
    }
    closeConfirmModal();
});
</script>
