@extends('layouts.app')

@section('content')
<div class="auth-card" style="width: 350px">
    <div class="auth-header">
        <h1>Login Petugas</h1>
        <p>Silakan login untuk mengelola data.</p>
    </div>

    <form action="{{ route('login') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" class="form-control" required autofocus value="{{ old('email') }}">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; display: block; margin: 0 auto; padding: 0.8rem; margin-top: 2rem;">
            Login
        </button>
    </form>
    
    <div style="margin-top: 1rem; text-align: center; font-size: 0.9rem; color: var(--text-muted);">
        <p>Demo Accounts:</p>
        <p>admin@takjil.com / password</p>
        <p>petugas@takjil.com / password</p>
    </div>
</div>
@endsection
