<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        
        // Ambil role utama dan bersihkan spasinya
        $userRole = strtolower(trim($user->role)); 
        
        // Ambil data array hak_akses (jika kosong/null, jadikan array kosong)
        $hakAkses = $user->hak_akses ?? [];

        // 2. Akses Mutlak untuk Direktur/Superadmin (Full Access Bypass)
        if (in_array($userRole, ['direktur', 'superadmin'])) {
            return $next($request);
        }

        // 3. Akses khusus untuk Admin General (Jika digunakan)
        if ($userRole === 'admin') {
            return $next($request);
        }

        // 4. Normalisasi syarat role/hak akses dari rute (web.php) agar seragam (huruf kecil semua)
        $lowercaseRoles = array_map(function($role) {
            return strtolower(trim($role));
        }, $roles);

        // 5. PENGECEKAN TAHAP 1: Cek apakah Role Utama diizinkan masuk
        if (in_array($userRole, $lowercaseRoles)) {
            return $next($request);
        }

        // 6. PENGECEKAN TAHAP 2 (SISTEM BARU): Cek Granular Access (Centangan Hak Akses)
        // Mengecek apakah ada irisan (kecocokan) antara array hak_akses user dengan syarat rute
        $punyaAksesEkstra = array_intersect($hakAkses, $lowercaseRoles);
        
        if (!empty($punyaAksesEkstra)) {
            return $next($request);
        }

        // Jika sampai di sini, artinya akses benar-benar ditolak mutlak
        abort(403, 'Akses ditolak. Role Anda (' . strtoupper($userRole) . ') tidak memiliki izin untuk halaman ini.');
    }
}