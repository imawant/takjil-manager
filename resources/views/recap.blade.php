@extends('layouts.app')

@section('content')
<div class="calendar-header">   
    <h1>Rekap Donatur</h1>
</div>

<div class="auth-card">
    <div class="search-box">
        <form action="{{ route('donations.recap') }}" method="GET" style="display: flex; gap: 0.5rem; width: 100%;">
            <div style="position: relative; flex: 1;">
                <input type="text" id="searchInput" name="search" placeholder="Cari nama donatur atau nomor WhatsApp..." value="{{ request('search') }}" style="width: 100%;" autocomplete="off">
                <div id="searchSuggestions" class="autocomplete-suggestions"></div>
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="ri-search-line"></i> Cari
            </button>
            <a href="{{ route('donations.recap') }}" class="btn-secondary" style="text-decoration: none; display: flex; align-items: center;">
                Reset
            </a>
        </form>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Nama Donatur <i class="ri-sort-asc"></i></a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'whatsapp', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">WhatsApp <i class="ri-sort-asc"></i></a></th>
                <th class="col-alamat"><a href="{{ request()->fullUrlWithQuery(['sort' => 'address', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Alamat <i class="ri-sort-asc"></i></a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'total_nasi', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Total Nasi <i class="ri-sort-asc"></i></a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'total_snack', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Total Snack <i class="ri-sort-asc"></i></a></th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($donors as $donor)
            <tr>
                <td style="font-weight: 500;">{{ $donor->name }}</td>
                <td>{{ $donor->whatsapp }}</td>
                <td class="col-alamat">{{ $donor->address }}</td>
                <td>
                    @if($donor->total_nasi > 0)
                    <span class="stat-badge stat-nasi">{{ $donor->total_nasi }} Box</span>
                    @else
                    -
                    @endif
                </td>
                <td>
                    @if($donor->total_snack > 0)
                    <span class="stat-badge stat-snack">{{ $donor->total_snack }} Box</span>
                    @else
                    -
                    @endif
                </td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn-text" style="color: var(--primary);" onclick="showDonorDetails({{ $donor->id }})">
                            <i class="ri-eye-line"></i> Detail
                        </button>
                        @can('petugas')
                        <button class="btn-text" style="color: var(--warning);" onclick="showEditDonorModal({{ $donor->id }}, '{{ addslashes($donor->name) }}', '{{ $donor->whatsapp }}', '{{ addslashes($donor->address) }}')">
                            <i class="ri-edit-line"></i> Edit Info
                        </button>
                        @endcan
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 2rem;">Belum ada data donatur.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Edit Donor Modal -->
<div id="editDonorModal" class="modal">
    <div class="modal-backdrop" onclick="closeEditDonorModal()"></div>
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="editDonorModalTitle">Edit Info Donatur</h3>
            <button class="close-modal" onclick="closeEditDonorModal()">&times;</button>
        </div>
        <form id="editDonorForm" onsubmit="updateDonorInfo(event)">
            <div class="modal-body">
                <input type="hidden" id="edit_donor_id">
                
                <div class="form-group">
                    <label for="edit_donor_name">Nama Donatur</label>
                    <input type="text" id="edit_donor_name" class="form-control" required placeholder="Nama donatur..." readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                    <small style="color: var(--text-muted); font-size: 0.85rem;">Nama tidak bisa diubah</small>
                </div>

                <div class="form-group">
                    <label for="edit_donor_whatsapp">Nomor WhatsApp</label>
                    <input type="text" id="edit_donor_whatsapp" class="form-control" required 
                           pattern="08[0-9]{9,11}" 
                           placeholder="08xxxxxxxxxx"
                           title="Format: 08xxxxxxxxxx (11-13 digit)">
                </div>

                <div class="form-group">
                    <label for="edit_donor_address">Alamat</label>
                    <textarea id="edit_donor_address" class="form-control" rows="3" placeholder="Alamat lengkap (opsional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeEditDonorModal()">Batal</button>
                <button type="submit" class="btn-primary">
                    <i class="ri-save-line"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

<script>
    function showEditDonorModal(donorId, donorName, donorWhatsapp, donorAddress) {
        document.getElementById('edit_donor_id').value = donorId;
        document.getElementById('edit_donor_name').value = donorName;
        document.getElementById('edit_donor_whatsapp').value = donorWhatsapp;
        document.getElementById('edit_donor_address').value = donorAddress || '';
        
        document.getElementById('editDonorModal').classList.add('active');
    }

    function closeEditDonorModal() {
        document.getElementById('editDonorModal').classList.remove('active');
        document.getElementById('editDonorForm').reset();
    }

    function updateDonorInfo(event) {
        event.preventDefault();

        const donorId = document.getElementById('edit_donor_id').value;
        const donorName = document.getElementById('edit_donor_name').value;
        const donorWhatsapp = document.getElementById('edit_donor_whatsapp').value;
        const donorAddress = document.getElementById('edit_donor_address').value;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Show loading state
        const submitButton = event.target.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="ri-loader-4-line"></i> Menyimpan...';

        fetch(`/donors/${donorId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: donorName,
                whatsapp: donorWhatsapp,
                address: donorAddress
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            alert(data.message || 'Info donatur berhasil diperbarui!');
            closeEditDonorModal();
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'Terjadi kesalahan. Silakan coba lagi.');
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeEditDonorModal();
        }
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const suggestionsBox = document.getElementById('searchSuggestions');
        let timeoutId;

        if (searchInput && suggestionsBox) {
            searchInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                const query = this.value;
                
                if (query.length < 2) {
                    suggestionsBox.style.display = 'none';
                    return;
                }

                timeoutId = setTimeout(() => {
                    fetch(`{{ route('donations.donor-suggestions') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            suggestionsBox.innerHTML = '';
                            if (data.length > 0) {
                                data.forEach(donor => {
                                    const div = document.createElement('div');
                                    div.className = 'autocomplete-item';
                                    const name = donor.name;
                                    const wa = donor.whatsapp || '';
                                    
                                    div.innerHTML = `
                                        <div style="font-weight: 500; color: var(--text);">${name}</div>
                                        ${wa ? `<div style="font-size: 0.85rem; color: var(--text-muted);"><i class="ri-whatsapp-line"></i> ${wa}</div>` : ''}
                                    `;
                                    
                                    div.onclick = function() {
                                        searchInput.value = name;
                                        suggestionsBox.style.display = 'none';
                                        searchInput.form.submit();
                                    };
                                    suggestionsBox.appendChild(div);
                                });
                                suggestionsBox.style.display = 'block';
                            } else {
                                suggestionsBox.style.display = 'none';
                            }
                        })
                        .catch(err => console.error('Error fetching suggestions:', err));
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                    suggestionsBox.style.display = 'none';
                }
            });
        }
    });
</script>
