<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'barang_id',
        'event_type',
        'event_reference',
        'change',
        'stock_before',
        'stock_after',
        'keterangan',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class)->withTrashed();
    }

    public static function record(Barang $barang, int $change, string $eventType, ?string $reference = null, ?string $keterangan = null, ?int $stockBefore = null)
    {
        $stockBefore = $stockBefore ?? $barang->stok_akhir;

        return self::create([
            'barang_id' => $barang->id,
            'event_type' => $eventType,
            'event_reference' => $reference,
            'change' => $change,
            'stock_before' => $stockBefore,
            'stock_after' => $stockBefore + $change,
            'keterangan' => $keterangan,
        ]);
    }

    public function getEventLabelAttribute()
    {
        return match ($this->event_type) {
            'initial_stock' => 'Stok Awal',
            'import_csv' => 'Impor CSV',
            'purchase' => 'Pembelian',
            'sale' => 'Penjualan',
            'return_customer' => 'Retur Customer',
            'return_supplier' => 'Retur Supplier',
            'backorder_fulfillment' => 'Pemenuhan Backorder',
            'manual_adjustment' => 'Penyesuaian Manual',
            default => ucwords(str_replace(['_', '-'], ' ', $this->event_type)),
        };
    }
}
