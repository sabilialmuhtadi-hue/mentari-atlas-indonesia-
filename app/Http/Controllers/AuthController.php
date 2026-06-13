<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog; // <-- TAMBAHAN SENSOR AUDIT TRAIL

class AuthController extends Controller
{
    // Menampilkan halaman login
    public function showLoginForm()
    {
        // Jika user sudah login, langsung arahkan ke dashboard masing-masing role
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }
        return view('auth.login');
    }

    // Memproses data login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // SENSOR ACTIVITY LOG: Catat user login
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'LOGIN',
                'description' => Auth::user()->name . ' berhasil login ke dalam sistem Mentari Atlas.',
                'ip_address' => $request->ip(),
            ]);
            
            // Mengarahkan sesuai role setelah sukses login
            return $this->redirectByRole(Auth::user()->role);
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    // Memproses logout
    public function logout(Request $request)
    {
        // SENSOR ACTIVITY LOG: Catat user logout (Opsional tapi bagus untuk keamanan)
        if(Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'LOGOUT',
                'description' => Auth::user()->name . ' telah keluar dari sistem.',
                'ip_address' => $request->ip(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // PERBAIKAN: Mengalihkan langsung ke halaman form login, bukan ke halaman bawaan Laravel
        return redirect('/login');
    }

    /**
     * Helper fungsi khusus untuk memisahkan rute dashboard berdasarkan role (Sesuai Poin 6 & 10)
     */
    private function redirectByRole($userRole)
    {
        $role = strtolower($userRole);

        // 1. Admin Warehouse / Gudang
        if (in_array($role, ['admin_warehouse', 'warehouse', 'admin warehouse', 'gudang'])) {
            return redirect()->route('warehouse.dashboard');
        } 
        
        // 2. Admin Piutang / Keuangan
        if (in_array($role, ['admin_piutang', 'piutang', 'admin_keuangan', 'keuangan', 'admin keuangan'])) {
            return redirect()->route('keuangan.dashboard');
        }

        // 3. Sales
        if (in_array($role, ['sales', 'marketing'])) {
            return redirect()->route('sales.dashboard');
        }

        // 4. Default Direktur (Dapat mengakses seluruh fitur utama sistem)
        return redirect('/dashboard');
    }
}