@extends('layouts.app')

@section('content')
<div class="calendar-header">
    <h1>Kelola Petugas</h1>
    <button class="btn-primary" style="margin: 0;" onclick="document.getElementById('addUserModal').classList.add('active')">
        <i class="ri-user-add-line"></i> Tambah User
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td style="font-weight: 500;">{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <span class="stat-badge" style="background: #F1F5F9; color: #475569;">{{ ucfirst($user->role) }}</span>
                </td>
                <td>
                    <form action="{{ route('users.destroy', $user) }}" method="POST" id="deleteUserForm{{ $user->id }}" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn-text" style="color: #EF4444;" 
                                onclick="showConfirmModal('Yakin ingin menghapus user ini?', 'Hapus User', function() { document.getElementById('deleteUserForm{{ $user->id }}').submit(); })">
                            <i class="ri-delete-bin-line"></i> Hapus
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-backdrop" onclick="this.parentElement.classList.remove('active')"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Tambah User Baru</h3>
            <button class="close-modal" onclick="this.parentElement.parentElement.parentElement.classList.remove('active')">&times;</button>
        </div>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="petugas">Petugas</option>
                        <option value="guest">Guest</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="this.parentElement.parentElement.parentElement.classList.remove('active')">Batal</button>
                <button type="submit" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
