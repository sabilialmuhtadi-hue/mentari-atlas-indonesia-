@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f8fafc !important; }
    .text-slate-dark { color: #0f172a !important; }
    .text-slate-muted { color: #64748b !important; }
    .card-custom { border: 1px solid #e2e8f0; border-radius: 1.25rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); overflow: hidden; }
    
    .profile-avatar {
        width: 100px; height: 100px;
        border: 4px solid #f8fafc;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }
    .btn-emerald-custom {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white; border: none; transition: all 0.3s ease;
    }
    .btn-emerald-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(16, 185, 129, 0.3);
        color: white;
    }
    .form-control-custom {
        border-radius: 0.75rem; padding: 0.75rem 1rem; border: 1px solid #cbd5e1;
        background-color: #f8fafc; transition: all 0.3s;
    }
    .form-control-custom:focus {
        background-color: #ffffff; border-color: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        outline: none;
    }
    .input-group-text-custom {
        background-color: #f8fafc; border-color: #cbd5e1; border-top-left-radius: 0.75rem; border-bottom-left-radius: 0.75rem;
    }
    .form-control-custom:focus ~ .input-group-text-custom,
    .input-group:focus-within .input-group-text-custom {
        background-color: #ffffff; border-color: #10b981;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-slate-dark fw-bold">Profil Akun Saya</h1>
            <p class="text-slate-muted small mb-0 mt-1">Kelola identitas dan pengaturan keamanan akun Anda.</p>
        </div>
    </div>

    <div class="row justify-content-center g-4">
        <!-- Kartu Identitas Akun -->
        <div class="col-lg-4 col-md-5">
            <div class="card card-custom border-0 bg-white">
                <div class="card-body text-center pt-5 pb-4 px-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=10b981&color=fff&size=128&bold=true"
                         class="rounded-circle profile-avatar mb-3" alt="Profile Picture">
                    
                    <h4 class="fw-bolder text-slate-dark mb-1" style="letter-spacing: -0.5px;">{{ $user->name }}</h4>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2 rounded-pill text-uppercase mb-4" style="letter-spacing: 1px;">
                        {{ str_replace('_', ' ', $user->role) }}
                    </span>
                    
                    <div class="text-start bg-light rounded-4 p-3 border border-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted fw-semibold">Status Akun:</span>
                            <span class="badge bg-success rounded-pill px-2 py-1"><i class="fas fa-check-circle me-1"></i>Aktif</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-semibold">Terdaftar Sejak:</span>
                            <span class="small fw-bold text-slate-dark">{{ $user->created_at->format('d F Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulir Pengaturan Keamanan -->
        <div class="col-lg-8 col-md-7">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 rounded-3 d-flex align-items-center" style="background-color: #d1fae5; color: #065f46;">
                    <i class="fas fa-check-circle fs-4 me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-0">Berhasil!</h6>
                        <span class="small">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <div class="card card-custom border-0 bg-white h-100">
                <div class="card-header bg-white py-4 px-4 border-bottom border-light">
                    <h5 class="mb-0 fw-bold text-slate-dark"><i class="fas fa-user-shield me-2" style="color: #10b981;"></i>Pengaturan & Keamanan</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-slate-dark">Nama Lengkap Sesuai Identitas</label>
                                <div class="input-group shadow-sm" style="border-radius: 0.75rem;">
                                    <span class="input-group-text border-end-0 bg-transparent text-muted input-group-text-custom"><i class="fas fa-user"></i></span>
                                    <input type="text" name="name" class="form-control form-control-custom border-start-0 ps-0" value="{{ $user->name }}" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-slate-dark">Alamat Email Perusahaan</label>
                                <div class="input-group shadow-sm" style="border-radius: 0.75rem;">
                                    <span class="input-group-text border-end-0 bg-transparent text-muted input-group-text-custom"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control form-control-custom border-start-0 ps-0" value="{{ $user->email }}" required>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 border-light">

                        <h6 class="fw-bold text-slate-dark mb-3"><i class="fas fa-lock me-2 text-muted"></i>Ganti Kata Sandi</h6>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-slate-dark">Kata Sandi Baru</label>
                                <div class="input-group shadow-sm" style="border-radius: 0.75rem;">
                                    <span class="input-group-text border-end-0 bg-transparent text-muted input-group-text-custom"><i class="fas fa-key"></i></span>
                                    <input type="password" name="password" class="form-control form-control-custom border-start-0 ps-0" placeholder="Kosongkan jika tak diubah">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-slate-dark">Konfirmasi Kata Sandi</label>
                                <div class="input-group shadow-sm" style="border-radius: 0.75rem;">
                                    <span class="input-group-text border-end-0 bg-transparent text-muted input-group-text-custom"><i class="fas fa-check-double"></i></span>
                                    <input type="password" name="password_confirmation" class="form-control form-control-custom border-start-0 ps-0" placeholder="Ulangi kata sandi baru">
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4 pt-3 border-top border-light">
                            <button type="submit" class="btn btn-emerald-custom px-4 py-2 fw-bold rounded-pill shadow-sm">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
