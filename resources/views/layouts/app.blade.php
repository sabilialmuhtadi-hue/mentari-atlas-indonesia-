<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem ERP - Mentari Atlas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- SweetAlert2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-page: #f8fafc;
            --bg-panel: #ffffff;
            --border-panel: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --accent: #10b981;
            --accent-hover: #059669;
            --accent-soft: #d1fae5;
            --accent-muted: #ecfdf5;
            --shadow-soft: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --radius: 12px;
        }

        html, body {
            overflow-x: hidden !important;
            width: 100%;
            margin: 0;
            padding: 0;
            min-height: 100%;
            background-color: var(--bg-page);
            color: var(--text-primary);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        #top-loader {
            position: fixed; top: 0; left: 0; height: 3px;
            background: var(--accent); z-index: 9999; width: 0;
            transition: width 0.4s ease; box-shadow: 0 0 10px var(--accent);
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-panel);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03);
            padding: 0.8rem 0 !important; transition: all 0.3s ease;
        }
        .navbar:hover { box-shadow: 0 4px 25px -2px rgba(0, 0, 0, 0.06); }

        .navbar-brand {
            font-size: 1.35rem; font-weight: 800; letter-spacing: -0.02em;
            color: var(--accent) !important; transition: transform 0.2s;
        }
        .navbar-brand:hover { transform: scale(1.02); }
        .text-success-custom { color: var(--accent) !important; }

        .nav-link-custom {
            color: var(--text-secondary) !important; font-weight: 600;
            font-size: 0.75rem; padding: 0.5rem 0.85rem !important;
            border-radius: var(--radius); transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; text-align: center; min-width: 75px;
            line-height: 1.3; text-decoration: none; white-space: nowrap; border: 1px solid transparent;
        }
        .nav-link-custom i { font-size: 1.1rem; margin-bottom: 5px; color: #94a3b8; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .nav-link-custom:hover { color: var(--accent) !important; background-color: var(--accent-muted); transform: translateY(-2px); }
        .nav-link-custom:hover i { color: var(--accent); transform: scale(1.1); }

        .active-link-custom {
            color: var(--accent) !important; background-color: var(--accent-soft) !important;
            border: 1px solid rgba(16, 185, 129, 0.2) !important; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.1);
        }
        .active-link-custom i { color: var(--accent) !important; }

        .nav-approval-highlight { background-color: #e0f2fe !important; border: 1px solid #bae6fd; }
        .nav-approval-highlight:hover { background-color: #bae6fd !important; box-shadow: 0 4px 10px rgba(14, 165, 233, 0.15); }
        .nav-approval-highlight i { color: #0284c7 !important; }

        .dropdown-menu {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border-panel); padding: 0.5rem;
            border-radius: 14px; background-color: #ffffff;
            animation: fadeInDropdown 0.2s ease forwards; transform-origin: top;
        }
        @keyframes fadeInDropdown { from { opacity: 0; transform: scaleY(0.95); } to { opacity: 1; transform: scaleY(1); } }
        .dropdown-item { border-radius: 8px; transition: background-color 0.2s, color 0.2s; font-weight: 500; }
        .dropdown-item:hover { background-color: var(--bg-page); }

        .btn-profile-dropdown {
            background-color: #ffffff; border: 1px solid var(--border-panel);
            padding: 0.35rem 0.85rem 0.35rem 0.35rem; border-radius: 999px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: var(--shadow-soft); cursor: pointer;
        }
        .btn-profile-dropdown:hover, .btn-profile-dropdown[aria-expanded="true"] {
            background-color: var(--bg-page); border-color: #cbd5e1; box-shadow: var(--shadow-hover);
        }

        .navbar-collapse { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
        .navbar-menu-grid { flex: 1 1 0; min-width: 0; }
        .navbar-nav {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            justify-content: center; gap: 0.5rem !important; justify-items: center;
            align-items: center; width: 100%; margin-bottom: 0; padding-left: 0; list-style: none;
        }
        .navbar-nav .nav-item { width: 100%; }
        .navbar-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; white-space: nowrap; }

        @media (max-width: 991.98px) {
            .navbar-collapse { overflow-x: auto; padding-top: 15px; padding-bottom: 10px; -webkit-overflow-scrolling: touch; }
            .navbar-nav { display: flex !important; flex-wrap: nowrap !important; justify-content: flex-start !important; width: 100%; overflow-x: auto; gap: 8px !important; padding-bottom: 10px; }
            .navbar-menu-grid { width: 100%; }
        }
        
        main.container { animation: fadeInContent 0.5s ease-out forwards; }
        @keyframes fadeInContent { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* MODAL PREMIUM STYLING */
        .modal-content.premium-modal { border: none; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; }
        .modal-header.bg-emerald-soft { background-color: #ecfdf5 !important; border-bottom: 1px solid #d1fae5 !important; padding: 1.25rem 1.5rem; }
        .modal-body.bg-slate-50 { background-color: #f8fafc !important; padding: 1.5rem; }

        /* TEMA TABEL PREMIUM MENTARI ATLAS */
        .table-wrapper-mentari { border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; border: 1px solid #e2e8f0; background-color: white; margin-bottom: 1.5rem; }
        .table-mentari { width: 100%; margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
        .table-mentari thead th { background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; color: #ffffff !important; font-weight: 600 !important; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem 1.25rem !important; border-bottom: none !important; white-space: nowrap; }
        .table-mentari tbody tr:nth-child(even) { background-color: #f8fafc !important; }
        .table-mentari tbody tr:hover { background-color: #ecfdf5 !important; transition: background-color 0.2s ease-in-out; }
        .table-mentari tbody td { padding: 1rem 1.25rem; color: #334155; vertical-align: middle; font-size: 0.85rem; border-bottom: 1px solid #e2e8f0; }
        .table-mentari tbody tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>

    <div id="top-loader"></div>

    @auth
    @php
        $userRole = strtolower(Auth::user()->role);
        $hakAkses = Auth::user()->hak_akses ?? [];
        $isDirektur = ($userRole == 'direktur' || $userRole == 'superadmin');
        $isSales = ($userRole == 'sales');
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top mb-4">
        <div class="container-fluid px-3 px-lg-4">
            
            <a class="navbar-brand d-flex align-items-center me-3 flex-shrink-0" href="{{ url('/dashboard') }}">
                <div class="bg-emerald-soft rounded-circle d-flex justify-content-center align-items-center me-2 shadow-sm" style="width: 36px; height: 36px;">
                    <i class="fas fa-layer-group fs-5 text-success-custom"></i>
                </div>
                <span class="fw-bold text-slate-dark">Mentari<span class="text-success-custom">Atlas</span></span>
            </a>
            
            <button class="navbar-toggler border-secondary-subtle shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon" style="opacity: 0.6;"></span>
            </button>

            <div class="collapse navbar-collapse align-items-center" id="navbarNav">
                <div class="navbar-menu-grid">
                    <ul class="navbar-nav mb-0">
                    
                    {{-- 1. Dashboard Utama --}}
                    <li class="nav-item">
                        @if(in_array($userRole, ['admin_warehouse', 'warehouse', 'admin warehouse']))
                            <a class="nav-link-custom {{ request()->is('warehouse/dashboard') ? 'active-link-custom' : '' }}" href="{{ route('warehouse.dashboard') }}">
                                <i class="fas fa-chart-pie"></i><span>Ruang Gudang</span>
                            </a>
                        @elseif(in_array($userRole, ['admin_keuangan', 'keuangan', 'admin keuangan']))
                            <a class="nav-link-custom {{ request()->is('keuangan/dashboard') ? 'active-link-custom' : '' }}" href="{{ route('keuangan.dashboard') }}">
                                <i class="fas fa-chart-line"></i><span>Ruang Keuangan</span>
                            </a>
                        @else
                            <a class="nav-link-custom {{ request()->is('dashboard') ? 'active-link-custom' : '' }}" href="{{ url('/dashboard') }}">
                                <i class="fas fa-th-large"></i><span>Dashboard Utama</span>
                            </a>
                        @endif
                    </li>

                    {{-- 2. Approval SO --}}
                    @if($isDirektur)
                    <li class="nav-item">
                        <a class="nav-link-custom nav-approval-highlight {{ request()->is('penjualan/approval*') ? 'active-link-custom' : '' }}" href="{{ route('penjualan.approval') }}">
                            <i class="fas fa-stamp"></i><span class="text-info fw-bold">Approval SO</span>
                        </a>
                    </li>
                    @endif

                    {{-- 3. Riwayat SO --}}
                    @if($isDirektur || $isSales || in_array('riwayat_so', $hakAkses))
                    <li class="nav-item">
                        <a class="nav-link-custom {{ request()->is('penjualan') || (request()->is('penjualan/*') && !request()->is('penjualan/approval*') && !request()->is('penjualan/buat*') && !request()->is('penjualan/edit*')) ? 'active-link-custom' : '' }}" href="{{ route('penjualan.index') }}">
                            <i class="fas fa-file-invoice"></i><span>Riwayat SO</span>
                        </a>
                    </li>
                    @endif

                    {{-- 4. Buat Order --}}
                    @if($isSales)
                    <li class="nav-item">
                        <a class="nav-link-custom {{ request()->is('penjualan/buat*') ? 'active-link-custom' : '' }}" href="{{ url('/penjualan/buat') }}">
                            <i class="fas fa-plus-circle"></i><span>Buat Order</span>
                        </a>
                    </li>
                    @endif

                    {{-- 5. Data Barang --}}
                    @if(!$isSales && ($isDirektur || in_array('data_barang', $hakAkses) || in_array($userRole, ['admin_warehouse', 'warehouse', 'admin warehouse'])))
                    <li class="nav-item">
                        <a class="nav-link-custom {{ request()->is('barang*') ? 'active-link-custom' : '' }}" href="{{ url('/barang') }}">
                            <i class="fas fa-boxes"></i><span>Data Barang</span>
                        </a>
                    </li>
                    @endif

                    {{-- 6. Back Order --}}
                    @if(!$isSales && ($isDirektur || in_array('backorder', $hakAkses) || in_array($userRole, ['admin_warehouse', 'warehouse', 'admin warehouse'])))
                    <li class="nav-item">
                        <a class="nav-link-custom {{ request()->routeIs('backorder.*') ? 'active-link-custom' : '' }}" href="{{ route('backorder.index') }}">
                            <i class="fas fa-hourglass-half"></i><span>Back Order</span>
                        </a>
                    </li>
                    @endif

                    {{-- 7. Pembelian Stok --}}
                    @if(!$isSales && ($isDirektur || in_array('pembelian_stok', $hakAkses)))
                    <li class="nav-item">
                        <a class="nav-link-custom {{ request()->is('pembelian*') ? 'active-link-custom' : '' }}" href="{{ url('/pembelian') }}">
                            <i class="fas fa-shopping-bag"></i><span>Pembelian Stok</span>
                        </a>
                    </li>
                    @endif

                    {{-- 8. Keuangan --}}
                    @if(!$isSales && ($isDirektur || in_array('keuangan', $hakAkses) || in_array($userRole, ['admin_keuangan', 'keuangan', 'admin keuangan'])))
                    <li class="nav-item dropdown">
                        <a class="nav-link-custom dropdown-toggle {{ request()->is('*keuangan*') ? 'active-link-custom' : '' }}" href="#" id="dropdownKeuangan" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-vault"></i><span>Keuangan</span>
                        </a>
                        <ul class="dropdown-menu shadow-lg mt-2" aria-labelledby="dropdownKeuangan">
                            <li><a class="dropdown-item py-2 px-3 text-slate-dark" href="{{ route('keuangan.piutang.index') }}" style="font-size: 0.85rem;"><i class="fas fa-hand-holding-usd me-2" style="color: #0284c7 !important;"></i>Piutang Customer</a></li>
                            <li><a class="dropdown-item py-2 px-3 text-slate-dark" href="{{ route('keuangan.utang.index') }}" style="font-size: 0.85rem;"><i class="fas fa-file-invoice-dollar me-2" style="color: #e11d48 !important;"></i>Utang Supplier</a></li>
                        </ul>
                    </li>
                    @endif

                    {{-- 9. Return Barang --}}
                    @if(!$isSales && ($isDirektur || in_array('return_barang', $hakAkses) || in_array($userRole, ['admin_warehouse', 'warehouse', 'admin warehouse'])))
                    <li class="nav-item dropdown">
                        <a class="nav-link-custom dropdown-toggle {{ request()->is('*retur*') ? 'active-link-custom' : '' }}" href="#" id="dropdownRetur" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-arrow-rotate-left"></i><span>Return Barang</span>
                        </a>
                        <ul class="dropdown-menu shadow-lg mt-2" aria-labelledby="dropdownRetur">
                            <li><a class="dropdown-item py-2 px-3 text-slate-dark" href="{{ route('retur.penjualan.index') }}" style="font-size: 0.85rem;"><i class="fas fa-undo me-2" style="color: #0284c7 !important;"></i>Return Penjualan</a></li>
                            <li><a class="dropdown-item py-2 px-3 text-slate-dark" href="{{ route('retur.pembelian.index') }}" style="font-size: 0.85rem;"><i class="fas fa-truck-loading me-2" style="color: #e11d48 !important;"></i>Return Pembelian</a></li>
                        </ul>
                    </li>
                    @endif

                    {{-- 10. Data Cust --}}
                    @if(!$isSales && ($isDirektur || in_array('tingkat_cust', $hakAkses)))
                    <li class="nav-item">
                        <a class="nav-link-custom {{ request()->is('customer*') ? 'active-link-custom' : '' }}" href="{{ route('customer.index') }}">
                            <i class="fas fa-users-cog text-success-custom"></i><span>Data Cust</span>
                        </a>
                    </li>
                    @endif

                    {{-- 11. Data Supplier --}}
                    @if(!$isSales && ($isDirektur || in_array('pembelian_stok', $hakAkses)))
                    <li class="nav-item">
                        <a class="nav-link-custom {{ request()->is('supplier*') ? 'active-link-custom' : '' }}" href="{{ route('supplier.index') }}">
                            <i class="fas fa-truck text-success-custom"></i><span>Data Supplier</span>
                        </a>
                    </li>
                    @endif

                    {{-- 12. Akun Staf --}}
                    @if(!$isSales && ($isDirektur || in_array('akun_staf', $hakAkses)))
                    <li class="nav-item">
                        <a class="nav-link-custom {{ request()->is('users*') ? 'active-link-custom' : '' }}" href="{{ url('/users') }}">
                            <i class="fas fa-user-gear"></i><span>Akun Staf</span>
                        </a>
                    </li>
                    @endif

                    {{-- 13. Analisis Laba --}}
                    @if(!$isSales && ($isDirektur || in_array('keuangan', $hakAkses)))
                    <li class="nav-item">
                        <a class="nav-link-custom {{ request()->is('laporan/laba*') ? 'active-link-custom' : '' }}" href="{{ route('laba.index') }}">
                            <i class="fas fa-chart-line" style="color: #10b981;"></i><span style="color: #10b981;">Profit / Laba</span>
                        </a>
                    </li>
                    @endif

                    {{-- 14. UNDUH LAPORAN (Perbaikan Link ke Pusat Laporan Baru) --}}
                    @if(!$isSales && ($isDirektur || in_array('unduh_laporan', $hakAkses)))
                    <li class="nav-item">
                        <a class="nav-link-custom {{ request()->routeIs('laporan.hub') ? 'active-link-custom' : '' }}" href="{{ route('laporan.hub') }}">
                            <i class="fas fa-file-export" style="color: #0ea5e9;"></i><span style="color: #0ea5e9;">Unduh Laporan</span>
                        </a>
                    </li>
                    @endif

                </ul>
                </div>

                <div class="navbar-right flex-shrink-0">
                    <div class="dropdown mt-2 mt-lg-0">
                        <a class="dropdown-toggle d-flex align-items-center btn-profile-dropdown text-decoration-none" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="text-white rounded-circle d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 32px; height: 32px; background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
                                <i class="fas fa-user-tie fa-sm"></i>
                            </div> 
                            <div class="d-flex flex-column align-items-start me-2">
                                <span class="fw-bold text-slate-dark lh-1" style="font-size: 0.85rem;">{{ Auth::user()->name }}</span>
                                <span class="text-slate-muted lh-1 mt-1" style="font-size: 0.65rem;">{{ str_replace('_', ' ', Auth::user()->role) }}</span>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end mt-3 p-2 shadow-lg" style="min-width: 200px;">
                            <li>
                                <a class="dropdown-item py-2 px-3 rounded-2 text-slate-dark d-flex align-items-center" href="{{ url('/profile') }}" style="font-size: 0.85rem;">
                                    <div class="bg-light rounded p-2 me-3"><i class="fas fa-id-card text-emerald-custom"></i></div>
                                    <span class="fw-medium">Profil Saya</span>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider border-light my-2"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 px-3 rounded-2 text-danger fw-bold d-flex align-items-center" style="font-size: 0.85rem;">
                                        <div class="bg-danger-subtle rounded p-2 me-3"><i class="fas fa-power-off text-danger"></i></div>
                                        <span>Keluar Aplikasi</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <main class="container pb-5">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    {{-- Script Validasi & UI Lainnya --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{{ session('success') }}', confirmButtonColor: '#10b981', timer: 3000, timerProgressBar: true, showConfirmButton: false });
            @endif
            @if(session('error'))
                Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session('error') }}', confirmButtonColor: '#ef4444' });
            @endif
        });

        document.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.btn-delete');
            if (deleteBtn) {
                e.preventDefault(); const form = deleteBtn.closest('form'); 
                Swal.fire({ title: 'Apakah Anda yakin?', text: "Data yang dihapus tidak dapat dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b', confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus Data', cancelButtonText: 'Batal', reverseButtons: true })
                .then((result) => { if (result.isConfirmed) { form.submit(); } });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => { document.body.appendChild(modal); });
        });
    </script>
</body>
</html>