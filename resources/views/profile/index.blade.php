@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <!-- Kartu Identitas Akun -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-4 mb-4">
                <div class="mb-3">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D6EFD&color=fff&size=128"
                         class="rounded-circle shadow-sm" alt="Profile Picture">
                </div>
                <h4 class="fw-bold mb-0">{{ $user->name }}</h4>
                <p class="text-muted small">Akses Sistem: <strong>{{ ucfirst($user->role) }}</strong></p>
                <hr>
                <div class="text-start">
                    <p class="small text-muted mb-1">Status Akun:</p>
                    <span class="badge bg-success w-100 py-2">Aktif</span>

                    <p class="small text-muted mb-1 mt-3">Terdaftar Sejak:</p>
                    <p class="small fw-bold">{{ $user->created_at->format('d F Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Formulir Pengaturan Keamanan -->
        <div class="col-md-7">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-user-shield me-2 text-primary"></i>Pengaturan Akun & Keamanan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label small fw-bold">Nama Lengkap Sesuai Identitas</label>
                                <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label small fw-bold">Alamat Email Perusahaan</label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Kata Sandi Baru</label>
                                <input type="password" name="password" class="form-control" placeholder="Isi hanya jika ingin diganti">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-bold">Konfirmasi Kata Sandi</label>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi baru">
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Perubahan Akun</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
