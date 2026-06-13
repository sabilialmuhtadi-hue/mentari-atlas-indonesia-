<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        // Menampilkan seluruh daftar user aktif agar Direktur bisa memantau semuanya
        // Diurutkan berdasarkan alfabet role agar rapi kelompoknya
        $users = User::orderBy('role', 'asc')->get();
                     
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,direktur,sales,admin_warehouse,admin_keuangan',
            'hak_akses' => 'nullable|array', // Tambahkan validasi array untuk hak akses
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            // Jika ada centangan, simpan. Jika kosong/tidak dicentang, simpan array kosong []
            'hak_akses' => $request->hak_akses ?? [], 
        ]);

        return back()->with('success', 'User baru berhasil ditambahkan.');
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,direktur,sales,admin_warehouse,admin_keuangan',
            'hak_akses' => 'nullable|array', // Tambahkan validasi array untuk hak akses
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            // Jika ada centangan, simpan. Jika kosong/tidak dicentang, simpan array kosong []
            'hak_akses' => $request->hak_akses ?? [], 
        ];

        // Jika password diisi, maka update passwordnya
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'User ' . $user->name . ' berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // Mencegah menghapus diri sendiri
        if (Auth::id() == $user->id) {
            return back()->withErrors(['error' => 'Anda tidak bisa menghapus akun Anda sendiri!']);
        }

        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }
}