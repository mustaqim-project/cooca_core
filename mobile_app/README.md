# Cooca POS Mobile Application 📱

Aplikasi Kasir POS & Manajemen Transaksi Mobile berbasis **Flutter (Android & iOS)** yang terhubung penuh secara native ke backend **Laravel REST API (V1)** via Clean Architecture & Provider State Management.

---

## ✨ Fitur Utama

1. **Autentikasi & Multi-Outlet**:
   - Login aman menggunakan Laravel Sanctum Bearer Token.
   - Pilihan dan pergantian outlet bisnis secara instan (`X-Business-Id`).
2. **Katalog & Kasir Cepat**:
   - Grid produk modern dengan indikator stok realtime & tab kategori.
   - Pencarian cepat (nama, SKU, barcode).
   - Keranjang interaktif dengan stepper (+ / -), diskon per item, dan catatan khusus (contoh: *tanpa es*).
3. **Pembayaran Multi-Metode**:
   - **Tunai**: Keypad angka cepat, kalkulasi kembalian realtime, dan tombol uang pas/pecahan instan.
   - **QRIS Dinamis**: Tampilan QR code siap scan.
   - **Kartu Debit / Kredit & Transfer Bank**.
   - **Poin Loyalitas Pelanggan**: Potong poin member CRM langsung di kasir.
4. **Parkir Pesanan (Hold & Resume)**:
   - Simpan pesanan gantung dengan catatan (contoh: *Meja 4*) dan lanjutkan transaksi kapan saja.
5. **Manajemen Shift & Laci Kas (Cash Drawer)**:
   - Buka shift dengan input modal awal.
   - Catat mutasi kas masuk/keluar (*petty cash*).
   - Tutup shift dengan rekonsiliasi kas aktual dan selisih kas otomatis.
6. **Riwayat Transaksi & Void**:
   - Daftar transaksi selesai.
   - Pembatalan transaksi (*Void*) dengan konfirmasi aman.
7. **Cetak Struk Thermal (ESC/POS)**:
   - Preview visual struk belanja dengan layout 58mm / 80mm.
   - Dukungan koneksi printer thermal Bluetooth/WiFi.

---

## 🏗️ Struktur Proyek (Clean Architecture)

```
mobile_app/
├── lib/
│   ├── main.dart                      # Entry Point & MultiProvider
│   ├── app.dart                       # MaterialApp & Routing
│   ├── core/
│   │   ├── constants/                 # AppColors, ApiEndpoints
│   │   ├── network/                   # ApiClient (HTTP + Auth & Tenant Headers)
│   │   ├── storage/                   # LocalStorage (SharedPreferences)
│   │   └── utils/                     # CurrencyFormatter (IDR), ReceiptGenerator
│   ├── models/                        # UserModel, BusinessModel, ProductModel, CartItemModel, etc.
│   ├── providers/                     # AuthProvider, PosProvider, ProductProvider, ShiftProvider, SettingsProvider
│   ├── screens/
│   │   ├── auth/                      # LoginScreen, BusinessSelectScreen
│   │   ├── pos/                       # PosCashierScreen, CheckoutPaymentScreen, PaymentSuccessScreen, etc.
│   │   ├── shift/                     # OpenShiftScreen, ActiveShiftScreen
│   │   ├── crm/                       # CustomerPickerScreen
│   │   └── settings/                  # AppSettingsScreen
│   └── widgets/                       # ProductCard, CartItemTile, QuickCashButtons, NumericKeypad
└── pubspec.yaml
```

---

## 🚀 Cara Menjalankan Aplikasi

### 1. Prasyarat
- Pastikan Flutter SDK (>= 3.0.0) telah terpasang di komputer Anda.
- Jalankan server backend Laravel:
  ```bash
  cd c:\laragon\www\calculator-hpp
  php artisan serve --host=0.0.0.0 --port=8000
  ```

### 2. Install Dependencies
```bash
cd mobile_app
flutter pub get
```

### 3. Konfigurasi Endpoint API
Buka aplikasi, atau sesuaikan di menu **Pengaturan Aplikasi (Settings)**:
- **Android Emulator**: `http://10.0.2.2:8000/api/v1`
- **iOS Simulator / Web**: `http://localhost:8000/api/v1`
- **Perangkat HP Fisik**: `http://<IP_KOMPUTER_ANDA>:8000/api/v1` (contoh: `http://192.168.1.10:8000/api/v1`)

### 4. Jalankan Aplikasi
```bash
# Jalankan di Android/iOS/Chrome
flutter run
```

### 5. Build File APK (Release)
```bash
flutter build apk --release
```
File APK siap instal akan berada di: `build/app/outputs/flutter-apk/app-release.apk`.
