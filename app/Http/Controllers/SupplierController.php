<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

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
            'kode_supplier' => 'required|unique:suppliers,kode_supplier',
            'nama_supplier' => 'required|string|max:255',
            'ktp'           => 'nullable|string|max:50',
            'npwp'          => 'nullable|string|max:50',
            'telepon'       => 'nullable|string|max:50',
            'alamat'        => 'nullable|string'
        ]);

        Supplier::create($request->all());

        return back()->with('success', 'Data Supplier berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_supplier' => 'required|unique:suppliers,kode_supplier,'.$id,
            'nama_supplier' => 'required|string|max:255',
            'ktp'           => 'nullable|string|max:50',
            'npwp'          => 'nullable|string|max:50',
            'telepon'       => 'nullable|string|max:50',
            'alamat'        => 'nullable|string'
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());

        return back()->with('success', 'Data Supplier berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        // Proteksi: Mencegah penghapusan jika supplier sudah punya riwayat di tabel pembelian
        if ($supplier->pembelians()->exists()) {
            return back()->withErrors(['error' => 'Gagal! Supplier tidak dapat dihapus karena sudah memiliki riwayat transaksi pembelian stok.']);
        }

        $supplier->delete();
        return back()->with('success', 'Data Supplier berhasil dihapus.');
    }
}