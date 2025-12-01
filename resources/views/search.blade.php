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
        <form action="{{ route('donations.search') }}" method="GET" style="display: flex; gap: 0.5rem; width: 100%;">
            <div style="position: relative; flex: 1;">
                <input type="text" id="searchInput" name="search" placeholder="Cari nama donatur atau nomor WhatsApp..." value="{{ request('search') }}" style="width: 100%;" autocomplete="off">
                <div id="searchSuggestions" class="autocomplete-suggestions"></div>
            </div>
            
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <select name="type" onchange="this.form.submit()" style="width: auto;">
                    <option value="">Semua Jenis</option>
                    <option value="nasi" {{ request('type') == 'nasi' ? 'selected' : '' }}>Nasi</option>
                    <option value="snack" {{ request('type') == 'snack' ? 'selected' : '' }}>Snack</option>
                </select>
                
                <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="this.form.submit()" style="width: auto;">
                <span style="color: var(--text-muted);">-</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()" style="width: auto;">
            </div>

            <button type="submit" class="btn-primary">
                <i class="ri-search-line"></i> Cari
            </button>
            <a href="{{ route('donations.search') }}" class="btn-secondary" style="text-decoration: none; display: flex; align-items: center;">
                Reset
            </a>
        </form>
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
            @foreach([10, 20, 50, 'all'] as $size)
                @php
                    $isSearch = request()->filled('search');
                    $reqPerPage = request('per_page');
                    $isActive = false;

                    if ($size == 'all') {
                        if ($reqPerPage == 'all' || $reqPerPage == 100000 || ($isSearch && !$reqPerPage)) {
                            $isActive = true;
                        }
                    } else {
                        if ($reqPerPage == $size || (!$isSearch && !$reqPerPage && $size == 10)) {
                            $isActive = true;
                        }
                    }
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['per_page' => $size]) }}" 
                   class="btn-text {{ $isActive ? 'active' : '' }}"
                   style="{{ $isActive ? 'font-weight: bold; color: var(--primary);' : 'color: var(--text-muted);' }}">
                   {{ ucfirst($size) }}
                </a>
                @if(!$loop->last) <span style="color: var(--border);">|</span> @endif
            @endforeach
        </div>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'date', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Tanggal <i class="ri-sort-asc"></i></a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'donor_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Nama Donatur <i class="ri-sort-asc"></i></a></th>
                <th class="col-alamat column-hidden">Alamat</th>
                <th class="col-whatsapp column-hidden">WhatsApp</th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'type', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Jenis <i class="ri-sort-asc"></i></a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'quantity', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Jumlah <i class="ri-sort-asc"></i></a></th>
                <th class="col-keterangan">Keterangan</th>
                @can('petugas')
                <th>Aksi</th>
                @endcan
            </tr>
        </thead>
        <tbody>
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
                                    // Highlight match
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
                }, 300); // Debounce 300ms
            });

            // Close suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                    suggestionsBox.style.display = 'none';
                }
            });
        }
    });
</script>
