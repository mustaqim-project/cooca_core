<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PURCHASE ORDER #{{ $po->po_number }} — {{ $business->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        body { background: #f8fafc; color: #1e293b; padding: 40px; }
        .invoice-box { max-width: 800px; margin: auto; background: #ffffff; padding: 36px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 24px; }
        .brand-title { font-size: 24px; font-weight: 800; color: #0f172a; }
        .brand-subtitle { font-size: 12px; color: #64748b; margin-top: 4px; }
        .doc-title { text-align: right; }
        .doc-title h1 { font-size: 26px; font-weight: 900; color: #f59e0b; }
        .doc-title p { font-size: 13px; color: #64748b; font-weight: 600; margin-top: 4px; }
        .meta-grid { display: flex; justify-content: space-between; margin-bottom: 30px; font-size: 13px; }
        .meta-col { width: 48%; }
        .meta-col h3 { font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 6px; }
        .meta-col p { font-size: 14px; font-weight: 600; color: #1e293b; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 13px; }
        th { background: #f1f5f9; color: #475569; font-weight: 700; text-align: left; padding: 12px; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; color: #334155; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .summary-box { margin-left: auto; width: 320px; }
        .summary-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; color: #64748b; }
        .summary-row.total { font-size: 18px; font-weight: 800; color: #0f172a; border-top: 2px solid #e2e8f0; padding-top: 10px; margin-top: 6px; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 12px; color: #94a3b8; }
        @media print {
            body { background: #ffffff; padding: 0; }
            .invoice-box { box-shadow: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="max-width: 800px; margin: 0 auto 16px auto; text-align: right;">
        <button onclick="window.print()" style="background: #f59e0b; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">
            🖨️ Cetak PO
        </button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div>
                <div class="brand-title">{{ $business->name }}</div>
                <div class="brand-subtitle">{{ $business->address ?? 'Operasional Bisnis' }}</div>
                @if($business->phone)
                    <div class="brand-subtitle">Telp/WA: {{ $business->phone }}</div>
                @endif
            </div>
            <div class="doc-title">
                <h1>SURAT PESANAN (PO)</h1>
                <p>#{{ $po->po_number }}</p>
            </div>
        </div>

        <div class="meta-grid">
            <div class="meta-col">
                <h3>Pemasok / Supplier:</h3>
                <p>{{ $supplier->name ?? 'Supplier' }}</p>
                @if($supplier && $supplier->phone)
                    <p style="color: #64748b; font-size: 13px;">Telp: {{ $supplier->phone }}</p>
                @endif
            </div>
            <div class="meta-col text-right">
                <h3>Informasi Pesanan:</h3>
                <p>Tanggal PO: {{ $po->po_date ? \Carbon\Carbon::parse($po->po_date)->format('d M Y') : date('d M Y') }}</p>
                <p style="color: #64748b; font-size: 13px;">Status: {{ strtoupper($po->status ?? 'DRAFT') }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 50%;">Nama Bahan / Barang</th>
                    <th class="text-center" style="width: 15%;">Qty</th>
                    <th class="text-right" style="width: 15%;">Harga Beli</th>
                    <th class="text-right" style="width: 15%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $item->item_name ?? 'Material' }}</strong></td>
                    <td class="text-center">{{ (float)$item->quantity }}</td>
                    <td class="text-right">Rp {{ number_format((float)$item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format((float)$item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; padding: 20px;">Tidak ada item pesanan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="summary-box">
            <div class="summary-row total">
                <span>Total Estimasi PO</span>
                <span>Rp {{ number_format((float)$po->total_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer">
            Dibuat secara resmi melalui <strong>Cooca Core</strong> — cooca.id
        </div>
    </div>
</body>
</html>
