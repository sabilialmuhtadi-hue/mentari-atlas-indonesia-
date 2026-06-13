<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Barang;
use App\Models\Customer;
use App\Models\Piutang;
use App\Models\StockHistory;
use App\Models\BackOrder; 
use App\Models\ActivityLog; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PenjualanController extends Controller
{
    // =========================================================================
    // 1. FUNGSI "OTAK" SPK (SAW Method)
    // =========================================================================
    private function hitungSkorSpk($penjualan) 
    {
        $penjualan = $penjualan->fresh(['details.barang', 'customer']);
        
        $akumulasiSkor = 0;
        $count = $penjualan->details->count();

        if ($count == 0) return 0; 

        foreach ($penjualan->details as $detail) {
            $barang = $detail->barang;
            if (!$barang) continue;
            
            $hargaNetto = $detail->harga_satuan - ($detail->diskon ?? 0);
            $profitMargin = (($hargaNetto - $barang->harga_beli) / ($barang->harga_beli ?: 1)) * 100;
            
            $skorMargin = ($profitMargin >= 20) ? 100 : (($profitMargin >= 5) ? 60 : 20);
            
            $persenSisa = (($barang->stok_akhir - $detail->jumlah) / ($barang->stok_awal ?: 1)) * 100;
            $skorStok = ($persenSisa >= 20) ? 100 : ($persenSisa >= 5 ? 50 : 10);
            
            $skorCust = ($penjualan->customer->npwp && $penjualan->customer->ktp) ? 100 : 50;
            
            $trackRecord = Penjualan::where('user_id', $penjualan->user_id)
                                    ->where('status_approval', 'disetujui')->count();
            $skorSales = ($trackRecord >= 5) ? 100 : 50;

            $akumulasiSkor += ($skorMargin * 0.4) + ($skorStok * 0.3) + ($skorCust * 0.2) + ($skorSales * 0.1);
        }

        $rataRataSkor = ($akumulasiSkor / $count);
        
        $penjualan->skor_spk = $rataRataSkor;
        $penjualan->save();

        return $rataRataSkor; 
    }

    // =========================================================================
    // 2. TAMPILAN INDEX & SHOW
    // =========================================================================
    public function index()
    {
        $query = Penjualan::with(['customer', 'user', 'details.barang']);
        $role = strtolower(Auth::user()->role);
        
        if (in_array($role, ['sales', 'marketing'])) {
            $query->where('user_id', Auth::id());
        }
        
        $pengajuan = $query->orderBy('created_at', 'asc')->get();
        return view('penjualan.index', compact('pengajuan'));
    }

    public function show($id)
    {
        $penjualan = Penjualan::with(['customer', 'details.barang', 'user'])->findOrFail($id);
        $role = strtolower(Auth::user()->role);

        if (in_array($role, ['sales', 'marketing']) && $penjualan->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda hanya dapat melihat dokumen milik Anda sendiri.');
        }
        
        return view('penjualan.show', compact('penjualan'));
    }

    public function create()
    {
        $barangs = Barang::orderBy('kode_barang', 'asc')->get(); 
        $customers = Customer::orderBy('nama_customer', 'asc')->get();
        return view('penjualan.buat', compact('barangs', 'customers'));
    }

    // =========================================================================
    // 3. BUAT PESANAN BARU (STATUS AWAL: DRAFT)
    // =========================================================================
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'barang_id' => 'required|array',
            'jumlah' => 'required|array',
            'harga_satuan' => 'required|array',
        ]);

        try {
            DB::beginTransaction();
            
            $customer = Customer::findOrFail($request->customer_id);
            if ($request->npwp || $request->ktp) {
                $customer->update([
                    'npwp' => $request->npwp ?? $customer->npwp,
                    'ktp' => $request->ktp ?? $customer->ktp,
                ]);
            }

            $no_so = 'SO-' . date('Ymd') . '-' . str_pad(Penjualan::count() + 1, 4, '0', STR_PAD_LEFT);
            
            $penjualan = Penjualan::create([
                'no_so' => $no_so,
                'tanggal_order' => date('Y-m-d'),
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'total_semua' => 0,
                'status_approval' => 'pending',
                'status' => 'draft',
                'sales_created_at' => now(),
            ]);

            $total_semua = 0;
            foreach ($request->barang_id as $index => $id_barang) {
                $barang = Barang::findOrFail($id_barang);
                $diskonItem = $request->diskon[$index] ?? 0;
                $subtotal = $request->jumlah[$index] * ($request->harga_satuan[$index] - $diskonItem);
                $total_semua += $subtotal;

                PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'barang_id' => $barang->id,
                    'hpp' => $barang->harga_beli ?? 0, // <--- KUNCI HPP DIAMBIL DARI MASTER BARANG SAAT INI
                    'harga_satuan' => $request->harga_satuan[$index],
                    'diskon' => $diskonItem,
                    'jumlah' => $request->jumlah[$index],
                    'subtotal' => $subtotal,
                ]);
            }

            $penjualan->total_semua = $total_semua;
            $penjualan->save();
            
            $skorFinal = $this->hitungSkorSpk($penjualan);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'BUAT SO',
                'description' => Auth::user()->name . ' membuat dokumen Sales Order baru: ' . $penjualan->no_so,
                'ip_address' => $request->ip(),
            ]);

            DB::commit();
            
            return redirect()->route('penjualan.index')
                             ->with('success', "Order berhasil diajukan dengan skor kelayakan " . number_format($skorFinal, 0) . "%");
                             
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // =========================================================================
    // 4. APPROVAL DIREKTUR 
    // =========================================================================
    public function approvalList()
    {
        $pengajuan = Penjualan::with(['customer', 'details.barang', 'user'])->orderBy('created_at', 'asc')->get();
        return view('penjualan.approval', compact('pengajuan'));
    }

    public function approve(Request $request, $id)
    {
        $penjualan = Penjualan::findOrFail($id);
        
        try {
            DB::beginTransaction();

            if ($request->status == 'disetujui' && $request->has('harga_edit')) {
                $total_keseluruhan_baru = 0;
                foreach ($request->harga_edit as $detail_id => $harga_baru) {
                    $detail = PenjualanDetail::findOrFail($detail_id);
                    $detail->harga_satuan = $harga_baru; 
                    
                    if ($request->has('jumlah_edit') && isset($request->jumlah_edit[$detail_id])) {
                        $detail->jumlah = $request->jumlah_edit[$detail_id];
                    }

                    $subtotal_baru = $detail->jumlah * $harga_baru;
                    $detail->subtotal = $subtotal_baru;
                    $detail->save();
                    
                    $total_keseluruhan_baru += $subtotal_baru;
                }
                $penjualan->total_semua = $total_keseluruhan_baru;
            }

            if ($request->status == 'disetujui') {
                $penjualan->status_approval = 'disetujui';
                $penjualan->approved_at = now();
                $penjualan->catatan = $request->catatan;
            } else {
                $penjualan->status_approval = 'ditolak';
                $penjualan->catatan = $request->catatan;
            }
            
            $penjualan->save();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => ($request->status == 'disetujui') ? 'SETUJUI SO' : 'TOLAK SO',
                'description' => Auth::user()->name . ' telah ' . strtoupper($request->status) . ' dokumen: ' . $penjualan->no_so,
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return back()->with('success', 'Keputusan berhasil disimpan! Silakan lanjut ke proses Packing.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses persetujuan: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // 5. FUNGSI WAREHOUSE PACKING (MEMOTONG STOK & MEMBUAT BACK ORDER)
    // =========================================================================
    public function updateToPackingSelesai($id)
    {
        $penjualan = Penjualan::with('details')->findOrFail($id);
        
        if ($penjualan->status_approval !== 'disetujui') {
            return back()->with('error', 'Gagal: Order harus disetujui oleh Direktur terlebih dahulu.');
        }

        if ($penjualan->status === 'ready_to_invoice') {
             return back()->with('error', 'Gagal: Barang ini sudah dipacking sebelumnya.');
        }

        try {
            DB::beginTransaction();

            $penjualan->status = 'ready_to_invoice';
            $penjualan->save();
            
            $adaBackOrder = false;

            foreach ($penjualan->details as $detail) {
                $barang = Barang::findOrFail($detail->barang_id);
                $stokSebelumnya = $barang->stok_akhir;

                // Logika Parsial: Kirim seadanya stok saat ini
                $qtyDikirimSekarang = ($barang->stok_akhir >= $detail->jumlah) ? $detail->jumlah : max(0, $barang->stok_akhir);
                $jumlahKurang = $detail->jumlah - $qtyDikirimSekarang;

                if ($jumlahKurang > 0) {
                    BackOrder::create([
                        'penjualan_id' => $penjualan->id,
                        'barang_id' => $barang->id,
                        'jumlah_diminta' => $detail->jumlah, 
                        'jumlah_kurang' => $jumlahKurang,
                        'status_bo' => 'antrean'
                    ]);
                    $adaBackOrder = true;
                }

                if ($qtyDikirimSekarang > 0) {
                    $barang->update([
                        'stok_akhir'   => $barang->stok_akhir - $qtyDikirimSekarang,
                        'barang_keluar' => $barang->barang_keluar + $qtyDikirimSekarang,
                    ]);

                    StockHistory::record(
                        $barang,
                        -$qtyDikirimSekarang,
                        'sale',
                        $penjualan->no_so,
                        'Pengiriman Packing (Parsial/Full).',
                        $stokSebelumnya
                    );
                }
            }

            $no_invoice = str_replace('SO', 'INV', $penjualan->no_so);
            
            Piutang::firstOrCreate(
                ['no_invoice' => $no_invoice], 
                [
                    'penjualan_id' => $penjualan->id,
                    'total_tagihan' => $penjualan->total_semua,
                    'potongan' => 0, 
                    'total_dibayar' => 0,
                    'status_bayar' => 'Belum Lunas',
                    'jatuh_tempo' => \Carbon\Carbon::now()->addDays(30),
                    'diinput_by' => Auth::id(),
                ]
            );

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE STATUS',
                'description' => Auth::user()->name . ' menyelesaikan proses packing untuk SO: ' . $penjualan->no_so,
                'ip_address' => request()->ip(),
            ]);
            
            DB::commit();

            if ($adaBackOrder) {
                return back()->with('success', 'Packing Parsial berhasil! Stok yang ada sudah dipotong. Barang yang kurang otomatis masuk ke antrean Back Order.');
            } else {
                return back()->with('success', 'Packing Selesai! Semua pesanan lengkap dan stok telah berhasil dipotong 100%.');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses packing: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // 6. PRINT SURAT & FAKTUR
    // =========================================================================
    public function printFaktur($id)
    {
        $penjualan = Penjualan::findOrFail($id);
        if ($penjualan->status !== 'ready_to_invoice') abort(403);
        return view('penjualan.faktur_print', compact('penjualan'));
    }

    public function printSuratJalan($id)
    {
        $penjualan = Penjualan::findOrFail($id);
        if ($penjualan->status === 'draft') abort(403);
        return view('penjualan.surat_jalan_print', compact('penjualan'));
    }

    // =========================================================================
    // 7. EDIT, UPDATE, DESTROY
    // =========================================================================
    public function edit($id)
    {
        $penjualan = Penjualan::with(['customer', 'details.barang'])->findOrFail($id);
        if ($penjualan->status !== 'draft') return redirect()->route('penjualan.index')->with('error', 'Tidak dapat diedit.');
        return view('penjualan.edit', compact('penjualan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['harga_awal' => 'required|array', 'harga_satuan' => 'required|array', 'jumlah' => 'required|array']);
        try {
            DB::beginTransaction();
            $penjualan = Penjualan::findOrFail($id);
            if ($penjualan->status !== 'draft') throw new \Exception('Pesanan sudah diproses.');
            $total_semua = 0;
            foreach ($request->detail_id as $index => $detailId) {
                $detail = PenjualanDetail::findOrFail($detailId);
                $diskonItem = $request->diskon[$index] ?? 0;
                $subtotal = $request->jumlah[$index] * ($request->harga_satuan[$index] - $diskonItem);
                $detail->update(['diskon' => $diskonItem, 'harga_satuan' => $request->harga_satuan[$index], 'jumlah' => $request->jumlah[$index], 'subtotal' => $subtotal]);
                $total_semua += $subtotal;
            }
            $penjualan->total_semua = $total_semua;
            $penjualan->save();
            
            $this->hitungSkorSpk($penjualan);
            
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'EDIT SO',
                'description' => Auth::user()->name . ' mengubah rincian draf Sales Order: ' . $penjualan->no_so,
                'ip_address' => $request->ip(),
            ]);

            DB::commit();
            return redirect()->route('penjualan.index')->with('success', 'Perubahan berhasil.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::findOrFail($id);
        
        if ($penjualan->status_approval !== 'pending') {
            return back()->with('error', 'Tidak dapat menghapus Sales Order yang sudah disetujui atau ditolak.');
        }
        
        $no_so_terhapus = $penjualan->no_so;
        $penjualan->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'HAPUS SO',
            'description' => Auth::user()->name . ' menghapus permanen draf Sales Order: ' . $no_so_terhapus,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Data berhasil dihapus.');
    }
}