<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Penjualan;
use Illuminate\Support\Facades\Storage;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Customer::query();

        if ($search) {
            $query->where('id_cust', 'like', "%{$search}%")
                  ->orWhere('nama_customer', 'like', "%{$search}%");
        }

        $customers = $query->orderBy('created_at', 'asc')->get();
        return view('customer.index', compact('customers', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_customer' => 'required|string|max:255',
            'no_telp'       => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
            'ktp'           => 'nullable|string|max:50',
            'npwp'          => 'nullable|string|max:50',
            'plafon'        => 'nullable|numeric|min:0',
            'foto_ktp'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_npwp'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_toko'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['nama_customer', 'no_telp', 'alamat', 'ktp', 'npwp', 'tempo_hari']);
        $data['id_cust'] = 'CS-' . date('YmdHis');
        $data['tingkat_customer'] = 'Bronze'; // Default awal
        $data['plafon'] = $request->plafon ?? 0;
        $data['tempo_hari'] = $request->tempo_hari ?? 30;

        // Proses Upload Foto (Disimpan di folder storage/app/public/customers)
        if ($request->hasFile('foto_ktp')) {
            $data['foto_ktp'] = $request->file('foto_ktp')->store('customers', 'public');
        }
        if ($request->hasFile('foto_npwp')) {
            $data['foto_npwp'] = $request->file('foto_npwp')->store('customers', 'public');
        }
        if ($request->hasFile('foto_toko')) {
            $data['foto_toko'] = $request->file('foto_toko')->store('customers', 'public');
        }

        $customer = Customer::create($data);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'TAMBAH CUSTOMER',
            'description' => Auth::user()->name . ' menambahkan customer baru: ' . $customer->id_cust . ' - ' . $customer->nama_customer,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Data Customer baru beserta dokumen berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'nama_customer' => 'required|string|max:255',
            'no_telp'       => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
            'ktp'           => 'nullable|string|max:50',
            'npwp'          => 'nullable|string|max:50',
            'plafon'        => 'nullable|numeric|min:0',
            'foto_ktp'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_npwp'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_toko'     => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->only(['nama_customer', 'no_telp', 'alamat', 'ktp', 'npwp', 'tempo_hari']);
        $data['plafon'] = $request->plafon ?? 0;
        $data['tempo_hari'] = $request->tempo_hari ?? 30;

        // Proses Update Foto: Hapus foto lama jika ada upload foto baru
        if ($request->hasFile('foto_ktp')) {
            if ($customer->foto_ktp) { Storage::disk('public')->delete($customer->foto_ktp); }
            $data['foto_ktp'] = $request->file('foto_ktp')->store('customers', 'public');
        }
        if ($request->hasFile('foto_npwp')) {
            if ($customer->foto_npwp) { Storage::disk('public')->delete($customer->foto_npwp); }
            $data['foto_npwp'] = $request->file('foto_npwp')->store('customers', 'public');
        }
        if ($request->hasFile('foto_toko')) {
            if ($customer->foto_toko) { Storage::disk('public')->delete($customer->foto_toko); }
            $data['foto_toko'] = $request->file('foto_toko')->store('customers', 'public');
        }

        $customer->update($data);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'EDIT CUSTOMER',
            'description' => Auth::user()->name . ' memperbarui data customer: ' . $customer->id_cust . ' - ' . $customer->nama_customer,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Profil dan Dokumen Customer ' . $customer->nama_customer . ' berhasil diperbarui!');
    }

    public function updateTier(Request $request, $id)
    {
        $request->validate([
            'tingkat_customer' => 'required|in:Bronze,Silver,Gold',
        ]);

        try {
            $customer = Customer::findOrFail($id);
            $tingkatBaru = $request->tingkat_customer;

            $customer->update(['tingkat_customer' => $tingkatBaru]);

            $totalBelanja = Penjualan::where('customer_id', $id)
                                ->where('status_approval', 'disetujui')
                                ->sum('total_semua');

            return back()->with('success', "Tingkat {$customer->nama_customer} berhasil diubah menjadi " . strtoupper($tingkatBaru) . " (Total Belanja Saat Ini: Rp " . number_format($totalBelanja, 0, ',', '.') . ").");
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal mengubah tingkatan: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            
            $namaCustomer = $customer->nama_customer;
            $idCust = $customer->id_cust;
            $customer->delete(); // Soft delete

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'HAPUS CUSTOMER',
                'description' => Auth::user()->name . ' menghapus (soft delete) data customer: ' . $idCust . ' - ' . $namaCustomer,
                'ip_address' => request()->ip(),
            ]);

            return back()->with('success', 'Customer berhasil dihapus dari daftar aktif.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}