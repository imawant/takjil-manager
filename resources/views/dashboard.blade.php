@extends('layouts.app')

@section('content')
<div class="modern-calendar-container">
    {{-- Header --}}
    <div class="calendar-header" style="background: white; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="color: #0F766E; font-weight: 700; font-size: 2rem;">Kalender Takjil Ramadhan 1447&nbsp;H</h1>
            <p style="color: #0D9488;">Februari - Maret 2026</p>
        </div>
        
        {{-- Target Configuration --}}
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; flex: 1; justify-content: flex-end;">
            @auth
            {{-- Authenticated: Inline Form --}}
            <form id="targetForm" action="{{ route('targets.update') }}" method="POST" 
                  style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                @csrf
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label style="font-size: 0.9rem; font-weight: 600; color: #1B4D3E;">Target Nasi:</label>
                    <input type="number" name="target_nasi" id="targetNasiInput" value="{{ $targetNasi }}" min="1" required 
                           style="width: 80px; padding: 0.4rem 0.6rem; border: 2px solid #B8E6BE; border-radius: 6px; font-weight: 600;">
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label style="font-size: 0.9rem; font-weight: 600; color: #0F4C81;">Target Snack:</label>
                    <input type="number" name="target_snack" id="targetSnackInput" value="{{ $targetSnack }}" min="1" required 
                           style="width: 80px; padding: 0.4rem 0.6rem; border: 2px solid #B8DFFF; border-radius: 6px; font-weight: 600;">
                </div>
                <button type="submit" class="btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                    <i class="ri-save-line"></i> Simpan
                </button>
            </form>
            @endauth
            
            @guest
            {{-- Guest: Button to Open Modal --}}
            <button class="btn-secondary" onclick="openTargetModal()" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                <i class="ri-settings-3-line"></i> Atur Target Harian
            </button>
            @endguest
            
            @can('petugas')
            <button class="btn-primary" onclick="openDonationModal()">
                <i class="ri-add-line"></i> Input Donasi
            </button>
            @endcan
        </div>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert success" style="margin: 1rem 0;">
        <i class="ri-check-line"></i>
        {{ session('success') }}
    </div>
    @endif

    {{-- Calendar Grid --}}
    <div class="calendar-grid">
        {{-- Day Names --}}
        <div class="calendar-day-header">AHAD</div>
        <div class="calendar-day-header">SENIN</div>
        <div class="calendar-day-header">SELASA</div>
        <div class="calendar-day-header">RABU</div>
        <div class="calendar-day-header">KAMIS</div>
        <div class="calendar-day-header">JUMAT</div>
        <div class="calendar-day-header">SABTU</div>

        {{-- Empty Offset Cells --}}
        <div></div>
        <div></div>
        <div></div>

        {{-- Calendar Days --}}
        @foreach($days as $day)
            <div class="modern-card" onclick="showDateDetails('{{ $day['date'] }}')" style="cursor: pointer;">
                
                {{-- Mobile Card Header (Day Name) --}}
                <div class="mobile-card-header">
                    {{ \Carbon\Carbon::parse($day['date'])->locale('id')->isoFormat('dddd') }}
                </div>

                {{-- Simplified Date Display --}}
                <div class="calendar-date-display">
                    {{-- Hijri Date (Top Right) --}}
                    <div class="date-gregorian">
                        {{ $day['hijri'] }} Ram
                    </div>

                    {{-- Gregorian Date (Center, Prominent) --}}
                    <div class="date-hijri-container">
                        <span class="date-hijri-number">{{ \Carbon\Carbon::parse($day['date'])->format('d') }}</span>
                        <span class="date-hijri-label">{{ \Carbon\Carbon::parse($day['date'])->locale('id')->isoFormat('MMMM') }}</span>
                    </div>
                </div>

                    {{-- Card Body --}}
                    <div class="card-body-modern">
                        @php
                            $nasiPercentage = $targetNasi > 0 ? min(($day['nasi_total'] / $targetNasi) * 100, 100) : 0;
                            $snackPercentage = $targetSnack > 0 ? min(($day['snack_total'] / $targetSnack) * 100, 100) : 0;
                        @endphp
                        
                        <div class="stat-item-nasi battery-container">
                            <div class="battery-fill battery-fill-nasi" style="width: {{ $nasiPercentage }}%;"></div>
                            <span class="stat-value-nasi">Nasi&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;{{ $day['nasi_total'] }}</span>
                        </div>
                        
                        <div class="stat-item-snack battery-container">
                            <div class="battery-fill battery-fill-snack" style="width: {{ $snackPercentage }}%;"></div>
                            <span class="stat-value-snack">Snack&nbsp;&nbsp;&nbsp;:&nbsp;&nbsp;{{ $day['snack_total'] }}</span>
                        </div>
                    </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Target Settings Modal (for Guests) --}}
<div id="targetModal" class="modal">
    <div class="modal-backdrop" onclick="closeTargetModal()"></div>
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3><i class="ri-settings-3-line"></i> Atur Target Harian</h3>
            <button class="close-modal" onclick="closeTargetModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div style="font-size: 0.9rem; color: #059669; background: #D1FAE5; padding: 0.75rem; border-radius: 6px; margin-bottom: 1.5rem; display: flex; align-items: flex-start; gap: 0.5rem;">
                <i class="ri-information-line" style="font-size: 1.2rem; flex-shrink: 0; margin-top: 0.1rem;"></i>
                <span>Perubahan target hanya berlaku untuk sesi Anda dan tidak mempengaruhi tampilan user lain.</span>
            </div>
            
            <form id="guestTargetForm" onsubmit="return saveGuestTargets(event)">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.95rem; font-weight: 600; color: #1B4D3E; margin-bottom: 0.5rem;">
                            <i class="ri-bowl-line"></i> Target Nasi
                        </label>
                        <input type="number" id="guestTargetNasi" min="1" required 
                               style="width: 100%; padding: 0.6rem; border: 2px solid #B8E6BE; border-radius: 6px; font-size: 1rem; font-weight: 600;"
                               placeholder="Masukkan target nasi">
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 0.95rem; font-weight: 600; color: #0F4C81; margin-bottom: 0.5rem;">
                            <i class="ri-cake-3-line"></i> Target Snack
                        </label>
                        <input type="number" id="guestTargetSnack" min="1" required 
                               style="width: 100%; padding: 0.6rem; border: 2px solid #B8DFFF; border-radius: 6px; font-size: 1rem; font-weight: 600;"
                               placeholder="Masukkan target snack">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="closeTargetModal()">Batal</button>
            <button type="submit" form="guestTargetForm" class="btn-primary">
                <i class="ri-save-line"></i> Simpan Target
            </button>
        </div>
    </div>
</div>

<script>
    // Pass server-side data to JavaScript
    window.isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
    window.initialTargetNasi = {{ $targetNasi }};
    window.initialTargetSnack = {{ $targetSnack }};
</script>
@endsection
