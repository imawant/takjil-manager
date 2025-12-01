<div id="donationModal" class="modal">
    <div class="modal-backdrop" onclick="closeDonationModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Input Donasi Baru</h3>
            <button class="close-modal" onclick="closeDonationModal()">&times;</button>
        </div>
        <form action="{{ route('donations.store') }}" method="POST" id="donationForm">
            @csrf
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="form-group">
                    <label for="date">Tanggal (Ramadhan 1447 H)</label>
                    <div style="margin-bottom: 0.5rem;">
                        <label class="radio-label" style="font-weight: normal;">
                            <input type="checkbox" name="is_flexible_date" id="is_flexible_date" value="1" onchange="toggleDateInput()">
                            <span>Tanggal Bebas (Serahkan ke sistem)</span>
                        </label>
                    </div>
                    <div id="dateInputContainer">
                        <input type="date" name="date" id="date" class="form-control" required 
                               min="2026-02-18" max="2026-03-19">
                        <small class="text-muted">Perkiraan: 18 Feb - 19 Mar 2026</small>
                    </div>
                </div>

                <script>
                    function toggleDateInput() {
                        const isFlexible = document.getElementById('is_flexible_date').checked;
                        const dateInput = document.getElementById('date');
                        const dateContainer = document.getElementById('dateInputContainer');
                        
                        if (isFlexible) {
                            dateInput.removeAttribute('required');
                            dateContainer.style.display = 'none';
                            dateInput.value = '';
                        } else {
                            dateInput.setAttribute('required', 'required');
                            dateContainer.style.display = 'block';
                        }
                    }
                </script>

                <div class="form-group" style="position: relative;">
                    <label for="donor_name">Nama Donatur</label>
                    <input type="text" name="donor_name" id="donor_name" class="form-control" required placeholder="Nama donatur..." autocomplete="off">
                    <div id="donor-suggestions" class="autocomplete-suggestions"></div>
                </div>

                <div class="form-group">
                    <label for="donor_whatsapp">Nomor WhatsApp</label>
                    <input type="text" name="donor_whatsapp" id="donor_whatsapp" class="form-control" required 
                           pattern="^08[0-9]{9,11}$" minlength="11" maxlength="13" 
                           title="Nomor WhatsApp harus diawali 08 dan terdiri dari 11-13 digit"
                           placeholder="Contoh: 081234567890">
                </div>

                <div class="form-group">
                    <label for="donor_address">Alamat</label>
                    <textarea name="donor_address" id="donor_address" class="form-control" rows="2" placeholder="Contoh: Jl. Dupak Bangunsari III No. 99"></textarea>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Jenis Donasi</label>
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="type" value="nasi" checked> 
                                    <span>Nasi</span>
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="type" value="snack"> 
                                    <span>Snack</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="quantity">Jumlah</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" required min="1" value="10">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Keterangan (Opsional)</label>
                    <textarea name="description" id="description" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeDonationModal()">Batal</button>
                <button type="submit" class="btn-primary">Simpan Donasi</button>
            </div>
        </form>
    </div>
</div>
