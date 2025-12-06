@extends('layouts.app')

@section('content')
<div class="calendar-header">   
    <h1>Rekap Donatur</h1>
</div>

<div class="auth-card">
    <div class="search-box">
        <div style="display: flex; gap: 0.5rem; width: 100%;">
            <div style="position: relative; flex: 1;">
                <input type="text" id="searchInput" placeholder="Cari nama donatur atau nomor WhatsApp..." style="width: 100%;" autocomplete="off">
                <div id="searchSuggestions" class="autocomplete-suggestions"></div>
            </div>
            
            <button type="button" id="resetSearch" class="btn-secondary" style="display: flex; align-items: center;">
                Reset
            </button>
        </div>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th class="sortable" data-column="name">Nama Donatur <i class="ri-arrow-up-down-line sort-icon"></i></th>
                <th class="sortable" data-column="whatsapp">WhatsApp <i class="ri-arrow-up-down-line sort-icon"></i></th>
                <th class="col-alamat sortable" data-column="address">Alamat <i class="ri-arrow-up-down-line sort-icon"></i></th>
                <th class="sortable" data-column="total_nasi">Total Nasi <i class="ri-arrow-up-down-line sort-icon"></i></th>
                <th class="sortable" data-column="total_snack">Total Snack <i class="ri-arrow-up-down-line sort-icon"></i></th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="donorTableBody">
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

<style>
    .sortable {
        cursor: pointer;
        user-select: none;
        transition: background-color 0.2s;
    }
    
    .sortable:hover {
        background-color: rgba(99, 102, 241, 0.1);
    }
    
    .sortable.active {
        background-color: rgba(99, 102, 241, 0.15);
        color: var(--primary);
    }
    
    .sort-icon {
        font-size: 0.9rem;
        opacity: 0.5;
        transition: opacity 0.2s;
    }
    
    .sortable:hover .sort-icon {
        opacity: 0.8;
    }
    
    .sortable.active .sort-icon {
        opacity: 1;
    }
    
    tbody tr {
        transition: background-color 0.15s;
    }
</style>

<script>
    // Client-side stable sort for donors table
    let donorsData = @json($donors);
    let allDonorsData = @json($donors); // Keep original for filtering
    let currentSortColumn = null;
    let currentSortDirection = 'asc';
    const canEdit = {{ auth()->check() && auth()->user()->can('petugas') ? 'true' : 'false' }};
    
    // Search functionality
    function applySearch() {
        const searchQuery = document.getElementById('searchInput').value.toLowerCase();
        
        // Filter from all data
        donorsData = allDonorsData.filter(donor => {
            if (searchQuery) {
                const matchName = donor.name.toLowerCase().includes(searchQuery);
                const matchWA = donor.whatsapp.toLowerCase().includes(searchQuery);
                return matchName || matchWA;
            }
            return true;
        });
        
        // Re-apply current sort if any
        if (currentSortColumn) {
            sortTable(currentSortColumn, true); // true = skip re-render
        }
        
        renderTable();
    }
    
    function sortTable(column, skipRender = false) {
        // Toggle direction if same column, otherwise reset to asc
        if (currentSortColumn === column) {
            currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            currentSortDirection = 'asc';
        }
        
        currentSortColumn = column;
        
        // Stable sort with locale support
        donorsData.sort((a, b) => {
            let valA = a[column];
            let valB = b[column];
            
            // Handle null/undefined
            if (valA == null) valA = '';
            if (valB == null) valB = '';
            
            let comparison;
            
            // String comparison with Indonesian locale
            if (typeof valA === 'string') {
                comparison = valA.localeCompare(valB, 'id-ID', {
                    sensitivity: 'base',
                    numeric: true // "10" > "2"
                });
            } else {
                // Numeric comparison
                comparison = valA - valB;
            }
            
            return currentSortDirection === 'asc' ? comparison : -comparison;
        });
        
        if (!skipRender) {
            renderTable();
            updateSortIndicators();
        }
    }
    
    function renderTable() {
        const tbody = document.getElementById('donorTableBody');
        
        if (donorsData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 2rem;">Belum ada data donatur.</td></tr>';
            return;
        }
        
        tbody.innerHTML = donorsData.map(donor => {
            const nasiBox = donor.total_nasi > 0 
                ? `<span class="stat-badge stat-nasi">${donor.total_nasi} Box</span>` 
                : '-';
            const snackBox = donor.total_snack > 0 
                ? `<span class="stat-badge stat-snack">${donor.total_snack} Box</span>` 
                : '-';
            
            const editButton = canEdit 
                ? `<button class="btn-text" style="color: var(--warning);" onclick="showEditDonorModal(${donor.id}, '${donor.name.replace(/'/g, "\\'").replace(/"/g, '&quot;')}', '${donor.whatsapp}', '${(donor.address || '').replace(/'/g, "\\'").replace(/"/g, '&quot;')}')">
                    <i class="ri-edit-line"></i> Edit Info
                </button>` 
                : '';
            
            return `
                <tr>
                    <td style="font-weight: 500;">${donor.name}</td>
                    <td>${donor.whatsapp}</td>
                    <td class="col-alamat">${donor.address || ''}</td>
                    <td>${nasiBox}</td>
                    <td>${snackBox}</td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <button class="btn-text" style="color: var(--primary);" onclick="showDonorDetails(${donor.id})">
                                <i class="ri-eye-line"></i> Detail
                            </button>
                            ${editButton}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }
    
    function updateSortIndicators() {
        // Remove all active states
        document.querySelectorAll('.sortable').forEach(th => {
            th.classList.remove('active');
            const icon = th.querySelector('.sort-icon');
            icon.className = 'ri-arrow-up-down-line sort-icon';
        });
        
        // Add active state to current column
        if (currentSortColumn) {
            const activeTh = document.querySelector(`[data-column="${currentSortColumn}"]`);
            if (activeTh) {
                activeTh.classList.add('active');
                const icon = activeTh.querySelector('.sort-icon');
                icon.className = currentSortDirection === 'asc' 
                    ? 'ri-arrow-up-line sort-icon' 
                    : 'ri-arrow-down-line sort-icon';
            }
        }
    }
    
    // Add click handlers to sortable columns
    document.addEventListener('DOMContentLoaded', function() {
        let searchTimeout;
        
        // Search input with debouncing
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applySearch();
            }, 300); // 300ms debounce
        });
        
        // Reset button
        document.getElementById('resetSearch').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            applySearch();
        });
        
        // Sortable columns
        document.querySelectorAll('.sortable').forEach(th => {
            th.addEventListener('click', function() {
                const column = this.getAttribute('data-column');
                sortTable(column);
            });
        });
    });
</script>

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

