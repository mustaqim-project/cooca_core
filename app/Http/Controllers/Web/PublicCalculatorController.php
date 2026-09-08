<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PublicCalculatorController extends Controller
{
    /**
     * Index of all free business calculators.
     */
    public function index(): View
    {
        return view('public.calculators.index');
    }

    /**
     * 1. Kalkulator HPP & Harga Jual
     */
    public function hpp(): View
    {
        return view('public.calculators.hpp');
    }

    /**
     * 2. Kalkulator Break Even Point (BEP)
     */
    public function bep(): View
    {
        return view('public.calculators.bep');
    }

    /**
     * 3. Kalkulator Harga Jual (Markup vs Margin)
     */
    public function hargaJual(): View
    {
        return view('public.calculators.harga_jual');
    }

    /**
     * 4. Kalkulator Laba Bersih Usaha
     */
    public function labaBersih(): View
    {
        return view('public.calculators.laba_bersih');
    }

    /**
     * 5. Kalkulator Gaji Karyawan & Upah Harian
     */
    public function gajiKaryawan(): View
    {
        return view('public.calculators.gaji_karyawan');
    }

    /**
     * 6. Kalkulator PPh Final UMKM 0.5% (PP 55/2022)
     */
    public function pphFinal(): View
    {
        return view('public.calculators.pph_final');
    }

    /**
     * 7. Kalkulator Target Omzet Harian
     */
    public function omzetHarian(): View
    {
        return view('public.calculators.omzet_harian');
    }

    /**
     * 8. Simulasi What-If Sensitivitas Biaya
     */
    public function simulasiWhatIf(): View
    {
        return view('public.calculators.simulasi_what_if');
    }
}
