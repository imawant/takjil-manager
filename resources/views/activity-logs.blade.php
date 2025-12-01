@extends('layouts.app')

@section('content')
<div class="calendar-header">
    <h1>Log Aktivitas Sistem</h1>
    <p style="color: var(--text-muted); font-size: 0.95rem;">Riwayat semua aktivitas pengguna dalam sistem</p>
</div>

{{-- Filter Box --}}
<div class="auth-card">
    <div class="search-box">
        <form action="{{ route('activity-logs.index') }}" method="GET">
            <div style="position: relative; flex: 1; min-width: 200px;">
                <select name="user_id" onchange="this.form.submit()">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->role }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="position: relative; flex: 1; min-width: 180px;">
                <select name="action" onchange="this.form.submit()">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="position: relative; flex: 1; min-width: 180px;">
                <select name="model" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    @foreach($models as $model)
                        <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                            {{ $model }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="this.form.submit()" placeholder="Dari tanggal">
                <span style="color: var(--text-muted);">-</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()" placeholder="Sampai tanggal">
            </div>

            <a href="{{ route('activity-logs.index') }}" class="btn-secondary" style="text-decoration: none; display: flex; align-items: center;">
                <i class="ri-refresh-line"></i> Reset
            </a>
        </form>
    </div>
</div>

{{-- Activity Log Table --}}
<div class="table-container">
    <table class="activity-table">
        <thead>
            <tr>
                <th style="width: 180px;">Waktu</th>
                <th style="width: 150px;">User</th>
                <th style="width: 100px;">Aksi</th>
                <th>Detail</th>
                <th style="width: 130px;">IP Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>
                    <div class="activity-time">
                        <div style="font-weight: 600; color: var(--text);">
                            {{ $log->created_at->locale('id')->isoFormat('D MMM YYYY') }}
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">
                            {{ $log->created_at->format('H:i:s') }}
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <i class="ri-user-line" style="color: var(--primary);"></i>
                        <div>
                            <div style="font-weight: 500;">{{ $log->user_name }}</div>
                            @if($log->user)
                                <div style="font-size: 0.8rem; color: var(--text-muted);">
                                    {{ $log->user->role }}
                                </div>
                            @endif
                        </div>
                    </div>
                </td>
                <td>
                    <span class="activity-icon action-{{ $log->action }}">
                        @if($log->action == 'created')
                            <i class="ri-add-circle-line"></i>
                        @elseif($log->action == 'updated')
                            <i class="ri-edit-line"></i>
                        @elseif($log->action == 'deleted')
                            <i class="ri-delete-bin-line"></i>
                        @elseif($log->action == 'login')
                            <i class="ri-login-box-line"></i>
                        @elseif($log->action == 'logout')
                            <i class="ri-logout-box-line"></i>
                        @elseif($log->action == 'scheduled')
                            <i class="ri-calendar-check-line"></i>
                        @else
                            <i class="ri-information-line"></i>
                        @endif
                        {{ ucfirst($log->action) }}
                    </span>
                </td>
                <td>
                    <div class="activity-description">
                        {{ $log->description }}
                        @if($log->model)
                            <span style="font-size: 0.8rem; color: var(--text-muted); margin-left: 0.5rem;">
                                ({{ $log->model }})
                            </span>
                        @endif
                    </div>
                </td>
                <td style="font-family: monospace; font-size: 0.85rem; color: var(--text-muted);">
                    {{ $log->ip_address }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                    <i class="ri-inbox-line" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.3;"></i>
                    Belum ada aktivitas yang tercatat.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($logs->hasPages())
<div style="margin-top: 2rem; display: flex; justify-content: center;">
    {{ $logs->links() }}
</div>
@endif

@endsection
