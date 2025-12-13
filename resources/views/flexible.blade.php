@extends('layouts.app')

@section('content')
<div class="calendar-header">
    <h1>Donasi Tanggal Bebas</h1>
    @if($donations->count() > 0)
    <div style="display: flex; gap: 0.5rem;">
        <button type="button" id="btnSelectAll" onclick="submitScheduleForm('all')" class="btn-primary" name="schedule_type" value="all">
            <i class="ri-calendar-check-line"></i> Jadwalkan Semua Data Baru
        </button>
        <button type="button" id="btnToggleSelect" onclick="toggleSelectionMode()" class="btn-secondary">
            <i class="ri-checkbox-multiple-line"></i> Pilih Beberapa Data Untuk Dijadwalkan
        </button>
        <button type="button" id="btnScheduleSelected" onclick="submitScheduleForm('selected')" class="btn-primary" style="display: none;">
            <i class="ri-check-double-line"></i> Jadwalkan Yang Dipilih
        </button>
        <button type="button" id="btnCancelSelect" onclick="toggleSelectionMode()" class="btn-secondary" style="display: none;">
            Batal
        </button>
    </div>
    @endif
</div>

<div class="auth-card">
    <div class="search-box">
        <div style="display: flex; gap: 0.5rem; width: 100%;">
            <div style="position: relative; flex: 1;">
                <input type="text" id="searchInput" placeholder="Cari nama donatur atau nomor WhatsApp..." style="width: 100%;" autocomplete="off">
                <div id="searchSuggestions" class="autocomplete-suggestions"></div>
            </div>
            
            <button type="button" id="resetFilters" class="btn-secondary" style="display: flex; align-items: center;">
                Reset
            </button>
        </div>
    </div>
    
    <div style="margin-top: 1rem; display: flex; justify-content: flex-end; align-items: center; gap: 0.5rem;">
        <span style="color: var(--text-muted); font-size: 0.9rem;">Show:</span>
        <a href="#" data-per-page="10" class="btn-text pagination-link">10</a>
        <span style="color: var(--border);">|</span>
        <a href="#" data-per-page="20" class="btn-text pagination-link">20</a>
        <span style="color: var(--border);">|</span>
        <a href="#" data-per-page="50" class="btn-text pagination-link">50</a>
        <span style="color: var(--border);">|</span>
        <a href="#" data-per-page="all" class="btn-text pagination-link">All</a>
    </div>
</div>

<form action="{{ route('donations.schedule') }}" method="POST" id="scheduleForm">
    @csrf
    <input type="hidden" name="schedule_type" id="scheduleTypeInput" value="all">
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="select-mode" style="display: none; width: 40px; text-align: center;">
                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                    </th>
                    <th class="sortable" data-column="donor_name">Nama Donatur <i class="ri-arrow-up-down-line sort-icon"></i></th>
                    <th class="sortable" data-column="donor_whatsapp">WhatsApp <i class="ri-arrow-up-down-line sort-icon"></i></th>
                    <th class="col-alamat sortable" data-column="donor_address">Alamat <i class="ri-arrow-up-down-line sort-icon"></i></th>
                    <th class="sortable" data-column="type">Jenis <i class="ri-arrow-up-down-line sort-icon"></i></th>
                    <th class="sortable" data-column="quantity">Jumlah <i class="ri-arrow-up-down-line sort-icon"></i></th>
                    <th class="col-keterangan">Keterangan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="donationTableBody">
                @forelse($donations as $donation)
                <tr>
                    <td class="select-mode" style="display: none; text-align: center;">
                        @if(is_null($donation->date))
                            <input type="checkbox" name="selected_donations[]" value="{{ $donation->id }}" class="donation-checkbox">
                        @endif
                    </td>
                    <td style="font-weight: 500;">{{ $donation->donor->name }}</td>
                    <td>{{ $donation->donor->whatsapp }}</td>
                    <td class="col-alamat">{{ $donation->donor->address }}</td>
                    <td>
                        @if($donation->type == 'nasi')
                        <span class="stat-badge stat-nasi">Nasi</span>
                        @else
                        <span class="stat-badge stat-snack">Snack</span>
                        @endif
                    </td>
                    <td style="font-weight: 700;">{{ $donation->quantity }} Box</td>
                    <td class="col-keterangan">{{ $donation->description }}</td>
                    <td>
                        @if($donation->date)
                            <span class="stat-badge" style="background-color: #d1fae5; color: #065f46;">Terjadwal: {{ \Carbon\Carbon::parse($donation->date)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</span>
                        @else
                            <span class="stat-badge" style="background-color: #fef3c7; color: #92400e;">Belum Terjadwal</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="button" onclick='openDonationModal(@json($donation, JSON_HEX_APOS | JSON_HEX_QUOT))' class="btn-warning" title="Edit">
                                Edit
                            </button>
                            <!-- Use temporary form id to avoid conflict with outer form -->
                            <button type="button" class="btn-danger" title="Hapus"
                                    onclick="deleteDonationItem({{ $donation->id }})">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 2rem;">
                        Tidak ada donasi tanggal bebas.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</form>

<form id="deleteItemForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

</style>

<!-- Confirmation Modal -->
<div id="scheduleConfirmationModal" class="modal-backdrop" style="display: none;">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3>Konfirmasi Penjadwalan</h3>
            <button onclick="closeScheduleConfirmationModal()" class="btn-text">
                <i class="ri-close-line" style="font-size: 1.5rem;"></i>
            </button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom: 1rem;">Anda akan menjadwalkan donasi berikut. Pastikan data sudah benar.</p>
            <div class="table-container" style="max-height: 400px; overflow-y: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Donatur</th>
                            <th>WhatsApp</th>
                            <th>Jenis</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody id="confirmationTableBody">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeScheduleConfirmationModal()" class="btn-secondary">Batal</button>
            <button onclick="confirmSchedule()" class="btn-primary">
                <i class="ri-check-line"></i> Ya, Jadwalkan
            </button>
        </div>
    </div>
</div>

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

    /* Modal Styles */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s, visibility 0.3s;
    }
    
    .modal-backdrop[style*="display: flex"] {
        opacity: 1;
        visibility: visible;
    }
    
    /* Override inline style from script if needed, but the above attribute selector helps */
</style>

<script>
    // Client-side filtering, sorting and pagination
    let donationsData = @json($donations->items());
    let allDonationsData = @json($donations->items());
    let currentSortColumn = 'quantity';
    let currentSortDirection = 'desc';
    let currentPage = 1;
    let itemsPerPage = 10;
    let globalSelectedIds = new Set();
    const canEdit = {{ auth()->check() && auth()->user()->can('petugas') ? 'true' : 'false' }};
    
    // Function to apply search filter
    function applyFilters() {
        const searchQuery = document.getElementById('searchInput').value.toLowerCase();
        
        donationsData = allDonationsData.filter(donation => {
            if (searchQuery) {
                const matchName = donation.donor.name.toLowerCase().includes(searchQuery);
                const matchWA = donation.donor.whatsapp.toLowerCase().includes(searchQuery);
                if (!matchName && !matchWA) return false;
            }
            return true;
        });
        
        // Re-apply current sort
        if (currentSortColumn) {
            sortTable(currentSortColumn, true);
        }
        
        currentPage = 1;
        renderTable();
    }
    
    // Sort table by column
    function sortTable(column, skipRender = false) {
        if (currentSortColumn === column) {
            currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            currentSortDirection = 'asc';
        }
        
        currentSortColumn = column;
        
        donationsData.sort((a, b) => {
            let valA, valB;
            
            if (column === 'donor_name') {
                valA = a.donor.name;
                valB = b.donor.name;
            } else if (column === 'donor_whatsapp') {
                valA = a.donor.whatsapp;
                valB = b.donor.whatsapp;
            } else if (column === 'donor_address') {
                valA = a.donor.address || '';
                valB = b.donor.address || '';
            } else {
                valA = a[column];
                valB = b[column];
            }
            
            if (valA == null) valA = '';
            if (valB == null) valB = '';
            
            let comparison;
            if (typeof valA === 'string') {
                comparison = valA.localeCompare(valB, 'id-ID', {
                    sensitivity: 'base',
                    numeric: true
                });
            } else {
                comparison = valA - valB;
            }
            
            return currentSortDirection === 'asc' ? comparison : -comparison;
        });
        
        if (!skipRender) {
            renderTable();
            updateSortIndicators();
        }
    }
    
    // Render table with current data
    function renderTable() {
        const tbody = document.getElementById('donationTableBody');
        const startIndex = (currentPage - 1) * (itemsPerPage === 'all' ? donationsData.length : itemsPerPage);
        const endIndex = itemsPerPage === 'all' ? donationsData.length : startIndex + itemsPerPage;
        const paginatedData = donationsData.slice(startIndex, endIndex);
        
        // Check if we're currently in selection mode
        const isSelectionMode = document.getElementById('btnScheduleSelected') && 
                                document.getElementById('btnScheduleSelected').style.display !== 'none';
        const checkboxDisplay = isSelectionMode ? 'table-cell' : 'none';
        
        if (paginatedData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align: center; padding: 2rem;">Tidak ada data ditemukan.</td></tr>';
            return;
        }
        
        tbody.innerHTML = paginatedData.map(donation => {
            const typeBadge = donation.type === 'nasi' 
                ? '<span class="stat-badge stat-nasi">Nasi</span>' 
                : '<span class="stat-badge stat-snack">Snack</span>';
            
            let statusBadge;
            if (donation.date) {
                const date = new Date(donation.date);
                const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
                const formattedDate = date.toLocaleDateString('id-ID', options);
                statusBadge = `<span class="stat-badge" style="background-color: #d1fae5; color: #065f46;">Terjadwal: ${formattedDate}</span>`;
            } else {
                statusBadge = '<span class="stat-badge" style="background-color: #fef3c7; color: #92400e;">Belum Terjadwal</span>';
            }
            
            const isChecked = globalSelectedIds.has(donation.id) ? 'checked' : '';
            const checkbox = donation.date === null
                ? `<input type="checkbox" value="${donation.id}" onchange="toggleSelection(${donation.id}, this)" ${isChecked} class="donation-checkbox">`
                : '';
            
            return `
                <tr>
                    <td class="select-mode" style="display: ${checkboxDisplay}; text-align: center;">
                        ${checkbox}
                    </td>
                    <td style="font-weight: 500;">${donation.donor.name}</td>
                    <td>${donation.donor.whatsapp}</td>
                    <td class="col-alamat">${donation.donor.address || ''}</td>
                    <td>${typeBadge}</td>
                    <td style="font-weight: 700;">${donation.quantity} Box</td>
                    <td class="col-keterangan">${donation.description || ''}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <button type="button" onclick='openDonationModal(${JSON.stringify(donation).replace(/'/g, "&#39;")})' class="btn-warning" title="Edit">
                                Edit
                            </button>
                            <button type="button" class="btn-danger" title="Hapus"
                                    onclick="deleteDonationItem(${donation.id})">
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
        
        // Update select all checkbox state
        updateSelectAllCheckboxState();
    }
    
    function toggleSelection(id, checkbox) {
        if (checkbox.checked) {
            globalSelectedIds.add(id);
        } else {
            globalSelectedIds.delete(id);
        }
        updateSelectAllCheckboxState();
    }
    
    function updateSelectAllCheckboxState() {
        const checkboxes = document.querySelectorAll('.donation-checkbox');
        const selectAll = document.getElementById('selectAllCheckbox');
        if (checkboxes.length === 0) {
            selectAll.checked = false;
            return;
        }
        
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        selectAll.checked = allChecked;
    }
    
    // Update sort indicators
    function updateSortIndicators() {
        document.querySelectorAll('.sortable').forEach(th => {
            th.classList.remove('active');
            const icon = th.querySelector('.sort-icon');
            if (icon) {
                icon.className = 'ri-arrow-up-down-line sort-icon';
            }
        });
        
        if (currentSortColumn) {
            const activeTh = document.querySelector(`[data-column="${currentSortColumn}"]`);
            if (activeTh) {
                activeTh.classList.add('active');
                const icon = activeTh.querySelector('.sort-icon');
                if (icon) {
                    icon.className = currentSortDirection === 'asc' 
                        ? 'ri-arrow-up-line sort-icon' 
                        : 'ri-arrow-down-line sort-icon';
                }
            }
        }
    }
    
    // Set items per page
    function setItemsPerPage(perPage) {
        itemsPerPage = perPage === 'all' ? 'all' : parseInt(perPage);
        currentPage = 1;
        renderTable();
        updatePaginationUI();
    }
    
    // Update pagination UI
    function updatePaginationUI() {
        document.querySelectorAll('.pagination-link').forEach(link => {
            const perPage = link.getAttribute('data-per-page');
            if ((perPage === 'all' && itemsPerPage === 'all') || 
                (perPage !== 'all' && parseInt(perPage) === itemsPerPage)) {
                link.style.fontWeight = 'bold';
                link.style.color = 'var(--primary)';
                link.classList.add('active');
            } else {
                link.style.fontWeight = 'normal';
                link.style.color = 'var(--text-muted)';
                link.classList.remove('active');
            }
        });
    }

    function toggleSelectionMode() {
        const isSelectionMode = document.getElementById('btnSelectAll').style.display === 'none';
        
        if (isSelectionMode) {
            // Switch back to Default Mode
            document.getElementById('btnSelectAll').style.display = 'inline-block';
            document.getElementById('btnToggleSelect').style.display = 'inline-block';
            document.getElementById('btnScheduleSelected').style.display = 'none';
            document.getElementById('btnCancelSelect').style.display = 'none';
            document.getElementById('scheduleTypeInput').value = 'all';
            
            document.querySelectorAll('.select-mode').forEach(el => el.style.display = 'none');
            
            // Clear selections when canceling mode? Maybe better to keep them?
            // User might want to cancel then re-enter. Let's keep them for now, or clear?
            // Typically "Cancel" implies discarding selection.
            globalSelectedIds.clear();
            renderTable();
        } else {
            // Switch to Selection Mode
            document.getElementById('btnSelectAll').style.display = 'none';
            document.getElementById('btnToggleSelect').style.display = 'none';
            document.getElementById('btnScheduleSelected').style.display = 'inline-block';
            document.getElementById('btnCancelSelect').style.display = 'inline-block';
            document.getElementById('scheduleTypeInput').value = 'selected';
            
            document.querySelectorAll('.select-mode').forEach(el => el.style.display = 'table-cell');
        }
    }

    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.donation-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = source.checked;
            const id = parseInt(cb.value);
            if (source.checked) {
                globalSelectedIds.add(id);
            } else {
                globalSelectedIds.delete(id);
            }
        });
    }
    
    function submitScheduleForm(type) {
        document.getElementById('scheduleTypeInput').value = type;
        const form = document.getElementById('scheduleForm');
        let dataToDisplay = [];
        let confirmMessage = '';

        if (type === 'selected') {
            if (globalSelectedIds.size === 0) {
                alert('Pilih setidaknya satu data untuk dijadwalkan.');
                return;
            }
            // Filter by selected IDs
            dataToDisplay = allDonationsData.filter(d => globalSelectedIds.has(d.id));
            confirmMessage = 'Anda akan menjadwalkan donasi yang dipilih berikut. Pastikan data sudah benar.';
        } else if (type === 'all') {
            // Filter by unscheduled status (date is null)
            dataToDisplay = allDonationsData.filter(d => d.date === null);
            if (dataToDisplay.length === 0) {
                alert('Tidak ada data baru yang perlu dijadwalkan.');
                return;
            }
            confirmMessage = 'Anda akan menjadwalkan SEMUA donasi baru berikut. Pastikan data sudah benar.';
        }

        // Populate table
        const tbody = document.getElementById('confirmationTableBody');
        tbody.innerHTML = '';
        
        dataToDisplay.forEach(donation => {
            const typeBadge = donation.type === 'nasi' 
                ? '<span class="stat-badge stat-nasi">Nasi</span>' 
                : '<span class="stat-badge stat-snack">Snack</span>';
                
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight: 500;">${donation.donor.name}</td>
                <td>${donation.donor.whatsapp}</td>
                <td>${typeBadge}</td>
                <td style="font-weight: 700;">${donation.quantity} Box</td>
                <td class="col-keterangan">${donation.description || '-'}</td>
            `;
            tbody.appendChild(tr);
        });
        
        // Update message
        document.querySelector('#scheduleConfirmationModal .modal-body p').textContent = confirmMessage;

        const modal = document.getElementById('scheduleConfirmationModal');
        modal.style.display = 'flex';
    }
    
    function closeScheduleConfirmationModal() {
        document.getElementById('scheduleConfirmationModal').style.display = 'none';
    }
    
    function confirmSchedule() {
        const form = document.getElementById('scheduleForm');
        const type = document.getElementById('scheduleTypeInput').value;
        
        // Remove any existing hidden inputs
        const existingHidden = form.querySelectorAll('input[name="selected_donations[]"]');
        existingHidden.forEach(el => el.remove());
        
        if (type === 'selected') {
            // Append hidden inputs for globalSelectedIds
            globalSelectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_donations[]';
                input.value = id;
                form.appendChild(input);
            });
        }
        
        form.submit();
    }

    function deleteDonationItem(id) {
        showConfirmModal('Yakin ingin menghapus donasi ini?', 'Hapus Donasi', function() { 
            const form = document.getElementById('deleteItemForm');
            form.action = `/donations/${id}`;
            form.submit();
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        let searchTimeout;
        
        // Initial render
        sortTable(currentSortColumn);
        updatePaginationUI();
        
        // Search input with debouncing
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 300);
        });
        
        // Reset filters
        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            applyFilters();
        });
        
        // Sortable columns
        document.querySelectorAll('.sortable').forEach(th => {
            th.addEventListener('click', function() {
                const column = this.getAttribute('data-column');
                sortTable(column);
            });
        });
        
        // Pagination links
        document.querySelectorAll('.pagination-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const perPage = this.getAttribute('data-per-page');
                setItemsPerPage(perPage);
            });
        });
        
        // Donor suggestions (keep existing functionality)
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
                                        applyFilters(); // Use client-side filter instead of form submit
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
@endsection
