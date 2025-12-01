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
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'donor_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Nama Donatur <i class="ri-sort-asc"></i></a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'donor_whatsapp', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">WhatsApp <i class="ri-sort-asc"></i></a></th>
                <th class="col-alamat"><a href="{{ request()->fullUrlWithQuery(['sort' => 'donor_address', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Alamat <i class="ri-sort-asc"></i></a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'total_nasi', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Total Nasi <i class="ri-sort-asc"></i></a></th>
                <th><a href="{{ request()->fullUrlWithQuery(['sort' => 'total_snack', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc']) }}">Total Snack <i class="ri-sort-asc"></i></a></th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($donors as $donor)
            <tr>
                <td style="font-weight: 500;">{{ $donor->donor_name }}</td>
                <td>{{ $donor->donor_whatsapp }}</td>
                <td class="col-alamat">{{ $donor->donor_address }}</td>
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
                    <button class="btn-text" style="color: var(--primary);" onclick="showDonorDetails('{{ addslashes($donor->donor_name) }}')">
                        <i class="ri-eye-line"></i> Detail
                    </button>
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
