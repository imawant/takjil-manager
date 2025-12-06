@extends('layouts.app')

@section('content')
<div class="calendar-header">
    <h1>Cari Data Donasi</h1>
</div>

<style>
    .toggle-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        user-select: none;
        font-size: 0.9rem;
        color: var(--text-muted);
    }
    
    .toggle-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .toggle-text {
        font-weight: 500;
    }
    
    .column-hidden {
        display: none !important;
    }
</style>

<script>
    function toggleColumn(column) {
        const headers = document.querySelectorAll(`th.col-${column}`);
        const cells = document.querySelectorAll(`td.col-${column}`);
        
        headers.forEach(header => {
            header.classList.toggle('column-hidden');
        });
        
        cells.forEach(cell => {
            cell.classList.toggle('column-hidden');
        });
    }
</script>

<div class="auth-card">
    <div class="search-box">
        <div style="display: flex; gap: 0.5rem; width: 100%;">
            <div style="position: relative; flex: 1;">
                <input type="text" id="searchInput" placeholder="Cari nama donatur atau nomor WhatsApp..." style="width: 100%;" autocomplete="off">
                <div id="searchSuggestions" class="autocomplete-suggestions"></div>
            </div>
            
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <select id="typeFilter" style="width: auto;">
                    <option value="">Semua Jenis</option>
                    <option value="nasi">Nasi</option>
                    <option value="snack">Snack</option>
                </select>
                
                <input type="date" id="startDateFilter" style="width: auto;">
                <span style="color: var(--text-muted);">-</span>
                <input type="date" id="endDateFilter" style="width: auto;">
            </div>

            <button type="button" id="resetFilters" class="btn-secondary" style="display: flex; align-items: center;">
                Reset
            </button>
        </div>
    </div>
    
    <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: center;">
            <span style="font-weight: 600; color: var(--text);">Tampilkan Kolom:</span>
            <label class="toggle-label">
                <input type="checkbox" onchange="toggleColumn('alamat')">
                <span class="toggle-text">Alamat</span>
            </label>
            <label class="toggle-label">
                <input type="checkbox" onchange="toggleColumn('whatsapp')">
                <span class="toggle-text">WhatsApp</span>
            </label>
            <label class="toggle-label">
                <input type="checkbox" checked onchange="toggleColumn('keterangan')">
                <span class="toggle-text">Keterangan</span>
            </label>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem;">
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
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th class="sortable" data-column="date">Tanggal <i class="ri-arrow-up-down-line sort-icon"></i></th>
                <th class="sortable" data-column="donor_name">Nama Donatur <i class="ri-arrow-up-down-line sort-icon"></i></th>
                <th class="col-alamat column-hidden">Alamat</th>
                <th class="col-whatsapp column-hidden">WhatsApp</th>
                <th class="sortable" data-column="type">Jenis <i class="ri-arrow-up-down-line sort-icon"></i></th>
                <th class="sortable" data-column="quantity">Jumlah <i class="ri-arrow-up-down-line sort-icon"></i></th>
                <th class="col-keterangan">Keterangan</th>
                @can('petugas')
                <th>Aksi</th>
                @endcan
            </tr>
        </thead>
        <tbody id="donationTableBody">
            @forelse($donations as $donation)
            <tr>
                <td>
                    @if($donation->is_flexible_date)
                        <span class="stat-badge" style="background: #F3F4F6; color: #374151;">Tanggal Bebas</span>
                    @else
                        {{ \Carbon\Carbon::parse($donation->date)->locale('id')->isoFormat('dddd, D MMM YYYY') }}
                    @endif
                </td>
                <td style="font-weight: 500;">{{ $donation->donor->name }}</td>
                <td class="col-alamat column-hidden">{{ $donation->donor->address }}</td>
                <td class="col-whatsapp column-hidden">{{ $donation->donor->whatsapp }}</td>
                <td>
                    <span class="stat-badge {{ $donation->type == 'nasi' ? 'stat-nasi' : 'stat-snack' }}">
                        {{ ucfirst($donation->type) }}
                    </span>
                </td>
                <td>{{ $donation->quantity }} Box</td>
                <td class="col-keterangan">{{ $donation->description }}</td>
                @can('petugas')
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <button onclick='openDonationModal(@json($donation, JSON_HEX_APOS | JSON_HEX_QUOT))' class="btn-warning" title="Edit">
                            Edit
                        </button>
                        <form action="{{ route('donations.destroy', $donation) }}" method="POST" id="deleteSearchForm{{ $donation->id }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn-danger" title="Hapus"
                                    onclick="showConfirmModal('Yakin ingin menghapus donasi ini?', 'Hapus Donasi', function() { document.getElementById('deleteSearchForm{{ $donation->id }}').submit(); })">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
                @endcan
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 2rem;">Tidak ada data ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
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
    // Client-side stable sort and pagination for donations table
    let donationsData = @json($donations);
    let allDonationsData = @json($donations); // Keep original for filtering
    let currentSortColumn = null;
    let currentSortDirection = 'asc';
    let currentPage = 1;
    let itemsPerPage = 10;
    const canEdit = {{ auth()->check() && auth()->user()->can('petugas') ? 'true' : 'false' }};
    
    // Filter and search functionality
    function applyFilters() {
        const searchQuery = document.getElementById('searchInput').value.toLowerCase();
        const typeFilter = document.getElementById('typeFilter').value;
        const startDate = document.getElementById('startDateFilter').value;
        const endDate = document.getElementById('endDateFilter').value;
        
        // Filter from all data
        donationsData = allDonationsData.filter(donation => {
            // Search filter (name or whatsapp)
            if (searchQuery) {
                const matchName = donation.donor.name.toLowerCase().includes(searchQuery);
                const matchWA = donation.donor.whatsapp.toLowerCase().includes(searchQuery);
                if (!matchName && !matchWA) return false;
            }
            
            // Type filter
            if (typeFilter && donation.type !== typeFilter) return false;
            
            // Date range filter
            if (startDate || endDate) {
                if (donation.is_flexible_date) return false; // Exclude flexible dates from date filter
                
                const donationDate = donation.date;
                if (startDate && donationDate < startDate) return false;
                if (endDate && donationDate > endDate) return false;
            }
            
            return true;
        });
        
        // Re-apply current sort if any
        if (currentSortColumn) {
            sortTable(currentSortColumn, true); // true = skip re-render, we'll do it after
        }
        
        // Reset to page 1 and render
        currentPage = 1;
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
        donationsData.sort((a, b) => {
            let valA, valB;
            
            // Special handling for different columns
            if (column === 'donor_name') {
                valA = a.donor.name;
                valB = b.donor.name;
            } else if (column === 'date') {
                // Sort flexible dates to the end
                if (a.is_flexible_date && !b.is_flexible_date) return 1;
                if (!a.is_flexible_date && b.is_flexible_date) return -1;
                if (a.is_flexible_date && b.is_flexible_date) return 0;
                
                valA = a.date;
                valB = b.date;
            } else {
                valA = a[column];
                valB = b[column];
            }
            
            // Handle null/undefined
            if (valA == null) valA = '';
            if (valB == null) valB = '';
            
            let comparison;
            
            // String comparison with Indonesian locale
            if (typeof valA === 'string') {
                comparison = valA.localeCompare(valB, 'id-ID', {
                    sensitivity: 'base',
                    numeric: true
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
        const tbody = document.getElementById('donationTableBody');
        
        // Calculate pagination
        const startIndex = (currentPage - 1) * (itemsPerPage === 'all' ? donationsData.length : itemsPerPage);
        const endIndex = itemsPerPage === 'all' ? donationsData.length : startIndex + itemsPerPage;
        const paginatedData = donationsData.slice(startIndex, endIndex);
        
        if (paginatedData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem;">Tidak ada data ditemukan.</td></tr>';
            return;
        }
        
        tbody.innerHTML = paginatedData.map(donation => {
            // Format date
            let dateHtml;
            if (donation.is_flexible_date) {
                dateHtml = '<span class="stat-badge" style="background: #F3F4F6; color: #374151;">Tanggal Bebas</span>';
            } else {
                // Format the date using JavaScript
                const date = new Date(donation.date);
                const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
                dateHtml = date.toLocaleDateString('id-ID', options);
            }
            
            const typeBadge = donation.type === 'nasi' 
                ? '<span class="stat-badge stat-nasi">Nasi</span>' 
                : '<span class="stat-badge stat-snack">Snack</span>';
            
            const actionButtons = canEdit 
                ? `<td>
                    <div style="display: flex; gap: 0.5rem;">
                        <button onclick='openDonationModal(${JSON.stringify(donation).replace(/'/g, "&#39;")})' class="btn-warning" title="Edit">
                            Edit
                        </button>
                        <form action="/donations/${donation.id}" method="POST" id="deleteSearchForm${donation.id}" style="display: inline;">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="button" class="btn-danger" title="Hapus"
                                    onclick="showConfirmModal('Yakin ingin menghapus donasi ini?', 'Hapus Donasi', function() { document.getElementById('deleteSearchForm${donation.id}').submit(); })">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>` 
                : '';
            
            return `
                <tr>
                    <td>${dateHtml}</td>
                    <td style="font-weight: 500;">${donation.donor.name}</td>
                    <td class="col-alamat column-hidden">${donation.donor.address || ''}</td>
                    <td class="col-whatsapp column-hidden">${donation.donor.whatsapp || ''}</td>
                    <td>${typeBadge}</td>
                    <td>${donation.quantity} Box</td>
                    <td class="col-keterangan">${donation.description || ''}</td>
                    ${actionButtons}
                </tr>
            `;
        }).join('');
    }
    
    function updateSortIndicators() {
        // Remove all active states
        document.querySelectorAll('.sortable').forEach(th => {
            th.classList.remove('active');
            const icon = th.querySelector('.sort-icon');
            if (icon) {
                icon.className = 'ri-arrow-up-down-line sort-icon';
            }
        });
        
        // Add active state to current column
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
    
    // Pagination functions
    function setItemsPerPage(perPage) {
        itemsPerPage = perPage === 'all' ? 'all' : parseInt(perPage);
        currentPage = 1; // Reset to first page
        renderTable();
        updatePaginationUI();
    }
    
    function updatePaginationUI() {
        // Update active state on pagination links
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
    
    // Add click handlers to sortable columns and pagination
    document.addEventListener('DOMContentLoaded', function() {
        let searchTimeout;
        
        // Search input with debouncing
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 300); // 300ms debounce
        });
        
        // Type filter
        document.getElementById('typeFilter').addEventListener('change', function() {
            applyFilters();
        });
        
        // Date filters
        document.getElementById('startDateFilter').addEventListener('change', function() {
            applyFilters();
        });
        
        document.getElementById('endDateFilter').addEventListener('change', function() {
            applyFilters();
        });
        
        // Reset button
        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            document.getElementById('typeFilter').value = '';
            document.getElementById('startDateFilter').value = '';
            document.getElementById('endDateFilter').value = '';
            applyFilters();
        });
        
        // Sortable columns
        document.querySelectorAll('.sortable').forEach(th => {
            th.addEventListener('click', function() {
                const column = this.getAttribute('data-column');
                sortTable(column);
            });
        });
        
        // Add pagination click handlers
        document.querySelectorAll('.pagination-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const perPage = this.getAttribute('data-per-page');
                setItemsPerPage(perPage);
            });
        });
        
        // Set initial pagination UI
        updatePaginationUI();
    });
</script>

