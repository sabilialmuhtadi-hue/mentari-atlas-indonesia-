<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanExport implements FromView, ShouldAutoSize
{
    protected $groupedData;
    protected $judulLaporan;
    protected $periodeLabel;
    protected $kategori;

    public function __construct($groupedData, $judulLaporan, $periodeLabel, $kategori)
    {
        $this->groupedData = $groupedData;
        $this->judulLaporan = $judulLaporan;
        $this->periodeLabel = $periodeLabel;
        $this->kategori = $kategori;
    }

    public function view(): View
    {
        return view('laporan.excel_kustom', [
            'groupedData' => $this->groupedData,
            'judulLaporan' => $this->judulLaporan,
            'periodeLabel' => $this->periodeLabel,
            'kategori'     => $this->kategori,
        ]);
    }
}