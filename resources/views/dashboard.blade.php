@extends('layouts.app')

@section('content')
<div class="modern-calendar-container">
    {{-- Header --}}
    <div class="calendar-header" style="background: white;">
        <div>
            <h1 style="color: #0F766E; font-weight: 700; font-size: 2rem;">Kalender Takjil Ramadhan 1447&nbsp;H</h1>
            <p style="color: #0D9488;">Februari - Maret 2026</p>
        </div>
        @can('petugas')
        <button class="btn-primary" onclick="openDonationModal()">
            <i class="ri-add-line"></i> Input Donasi
        </button>
        @endcan
    </div>

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
                    {{-- Gregorian Date (Top Right) --}}
                    <div class="date-gregorian">
                        {{ \Carbon\Carbon::parse($day['date'])->format('d M') }}
                    </div>

                    {{-- Hijri Date (Center, Prominent) --}}
                    <div class="date-hijri-container">
                        <span class="date-hijri-number">{{ $day['hijri'] }}</span>
                        <span class="date-hijri-label">Ramadhan</span>
                    </div>
                </div>

                {{-- Card Body --}}
                <div class="card-body-modern">
                    @if($day['nasi_total'] > 0)
                        <div class="stat-item-nasi">
                            <span class="stat-label-nasi">Nasi :</span>
                            <span class="stat-value-nasi">{{ $day['nasi_total'] }}</span>
                        </div>
                    @endif
                    
                    @if($day['snack_total'] > 0)
                        <div class="stat-item-snack">
                            <span class="stat-label-snack">Snack :</span>
                            <span class="stat-value-snack">{{ $day['snack_total'] }}</span>
                        </div>
                    @endif

                    @if($day['nasi_total'] == 0 && $day['snack_total'] == 0)
                        <div class="empty-state">Belum ada donasi</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
