@extends('layouts.app')

@section('content')
<div class="calendar-header">
    <h1>Donasi Tanggal Bebas</h1>
    @if($donations->count() > 0)
    <form action="{{ route('donations.schedule') }}" method="POST">
        @csrf
        <button type="submit" class="btn-primary">
            <i class="ri-calendar-check-line"></i> Jadwalkan Semua Donasi Tanggal Bebas
        </button>
    </form>
    @endif
</div>

<div class="auth-card">
    <div class="search-box">
        <form action="{{ route('donations.flexible') }}" method="GET" style="display: flex; gap: 0.5rem; width: 100%;">
            <div style="position: relative; flex: 1;">
                <input type="text" id="searchInput" name="search" placeholder="Cari nama donatur atau nomor WhatsApp..." value="{{ request('search') }}" style="width: 100%;" autocomplete="off">
                <div id="searchSuggestions" class="autocomplete-suggestions"></div>
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="ri-search-line"></i> Cari
            </button>
            <a href="{{ route('donations.flexible') }}" class="btn-secondary" style="text-decoration: none; display: flex; align-items: center;">
                Reset
            </a>
        </form>
    </div>
    
    <div style="margin-top: 1rem; display: flex; justify-content: flex-end; align-items: center; gap: 0.5rem;">
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

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'donor_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Nama Donatur <i class="ri-sort-asc"></i></a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'donor_whatsapp', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">WhatsApp <i class="ri-sort-asc"></i></a></th>
                <th class="col-alamat"><a href="{{ request()->fullUrlWithQuery(['sort' => 'donor_address', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Alamat <i class="ri-sort-asc"></i></a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'type', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Jenis <i class="ri-sort-asc"></i></a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'quantity', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Jumlah <i class="ri-sort-asc"></i></a></th>
                <th class="col-keterangan">Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($donations as $donation)
            <tr>
                <td style="font-weight: 500;">{{ $donation->donor_name }}</td>
                <td>{{ $donation->donor_whatsapp }}</td>
                <td class="col-alamat">{{ $donation->donor_address }}</td>
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
                    <div style="display: flex; gap: 0.5rem;">
                        <button onclick='openDonationModal(@json($donation, JSON_HEX_APOS | JSON_HEX_QUOT))' class="btn-warning" title="Edit">
                            Edit
                        </button>
                        <form action="{{ route('donations.destroy', $donation) }}" method="POST" id="deleteForm{{ $donation->id }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn-danger" title="Hapus"
                                    onclick="showConfirmModal('Yakin ingin menghapus donasi ini?', 'Hapus Donasi', function() { document.getElementById('deleteForm{{ $donation->id }}').submit(); })">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 2rem;">
                    Tidak ada donasi tanggal bebas yang belum dijadwalkan.
                </td>
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
                                    const name = donor.donor_name;
                                    const wa = donor.donor_whatsapp || '';
                                    
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
