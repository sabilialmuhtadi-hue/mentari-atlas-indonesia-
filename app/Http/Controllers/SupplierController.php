<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $query = Supplier::query();

        if ($search) {
            $query->where('kode_supplier', 'like', "%{$search}%")
                  ->orWhere('nama_supplier', 'like', "%{$search}%");
        }

        // Sudah diubah menjadi 'asc' (Terlama ke Terbaru)
        $suppliers = $query->orderBy('id', 'asc')->get();
        
        return view('supplier.index', compact('suppliers', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'ktp'           => 'nullable|string|max:50',
            'npwp'          => 'nullable|string|max:50',
            'telepon'       => 'nullable|string|max:50',
            'alamat'        => 'nullable|string',
            'jatuh_tempo_hari' => 'nullable|integer|min:0'
        ]);

        $data = $request->all();
        $data['kode_supplier'] = 'SUP-' . date('YmdHis');

        $supplier = Supplier::create($data);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'TAMBAH SUPPLIER',
            'description' => Auth::user()->name . ' menambahkan supplier baru: ' . $supplier->kode_supplier . ' - ' . $supplier->nama_supplier,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Data Supplier berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'ktp'           => 'nullable|string|max:50',
            'npwp'          => 'nullable|string|max:50',
            'telepon'       => 'nullable|string|max:50',
            'alamat'        => 'nullable|string',
            'jatuh_tempo_hari' => 'nullable|integer|min:0'
        ]);

        $supplier = Supplier::findOrFail($id);
        $data = $request->except(['kode_supplier']); // Hindari pengubahan kode jika di-submit
        $supplier->update($data);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'EDIT SUPPLIER',
            'description' => Auth::user()->name . ' memperbarui data supplier: ' . $supplier->kode_supplier . ' - ' . $supplier->nama_supplier,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Data Supplier berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        $kodeSupplier = $supplier->kode_supplier;
        $namaSupplier = $supplier->nama_supplier;
        $supplier->delete(); // Soft delete

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'HAPUS SUPPLIER',
            'description' => Auth::user()->name . ' menghapus (soft delete) data supplier: ' . $kodeSupplier . ' - ' . $namaSupplier,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Data Supplier berhasil dihapus dari daftar aktif.');
    }
}