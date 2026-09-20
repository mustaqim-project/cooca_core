# PRD-07: Toko Online Multi-Page dengan 20 Template Tema Industri Otentik & Auto-Hide Navigation

**ID Dokumen:** `PRD-07-STOREFRONT-MULTIPAGE-20-THEMES`  
**Modul:** Etalase Toko Online (Storefront), Website Builder & CMS Mini-Site  
**Penanggung Jawab:** Principal Frontend Architect & Creative Design Director  
**Status:** READY FOR IMPLEMENTATION  
**Target Pengguna:** Calon Pembeli Retail, Klien B2B, Pemilik Bisnis (Owner)  

---

## 1. Audit Sistem Existing (As-Is State)

### A. File & Komponen Terkait
* **Tampilan Monolitik Tunggal:** [`resources/views/public/business_landing.blade.php`](file:///c:/laragon/www/cooca_core/resources/views/public/business_landing.blade.php) (574 KB, 1 file raksasa)
* **Controller:** [`app/Http/Controllers/Web/PublicBusinessLandingController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/PublicBusinessLandingController.php), [`app/Http/Controllers/Web/LandingPage/BusinessLandingPageWebController.php`](file:///c:/laragon/www/cooca_core/app/Http/Controllers/Web/LandingPage/BusinessLandingPageWebController.php)
* **Model:** [`app/Models/BusinessLandingPage.php`](file:///c:/laragon/www/cooca_core/app/Models/BusinessLandingPage.php), [`app/Models/CommerceStoreSetting.php`](file:///c:/laragon/www/cooca_core/app/Models/CommerceStoreSetting.php)

### B. Masalah & Kendala Fatal Sistem Existing
1. **Monolitik 574 KB & "Modal Hell" (Semua Serba Pop-Up):**
   - Halaman publik saat ini memuat seluruh fitur di 1 halaman yang sangat panjang.
   - Klik kartu produk $\rightarrow$ buka pop-up modal.
   - Buka keranjang belanja $\rightarrow$ buka pop-up modal.
   - Lanjut ke pembayaran checkout $\rightarrow$ buka pop-up modal lagi di atas pop-up sebelumnya (*nested modal*).
   - Akibatnya di smartphone: layout terasa sempit, form terpotong, dan jika pembeli tidak sengaja menyentuh latar belakang gelap, modal tertutup dan data belanjaan hilang!
2. **Sangat Merugikan SEO & Pemasaran:**
   - Semua produk berada di satu URL (`cooca.id/{slug}`).
   - Merchant tidak bisa membagikan tautan produk spesifik ke WhatsApp atau Instagram Story dengan pratinjau gambar dan harga yang memikat (*rich link OpenGraph preview*).
3. **Template Monoton & Tidak Otentik:**
   - Bengkel motor, toko material bangunan, apotek obat, salon kecantikan, dan kafe kopi memiliki struktur visual yang sama persis, hanya dibedakan oleh kode warna tombol.

---

## 2. Perubahan & Penambahan Sistem (To-Be State)

### A. Arsitektur 8 Halaman Dedicated Penuh (Full Standalone Pages)
Seluruh alur interaksi dipecah menjadi halaman-halaman mandiri berstandar internasional:

1. **Halaman Beranda (`cooca.id/{slug}`):** Hero sinematik, kategori populer, produk terlaris, cerita brand, dan ulasan pembeli.
2. **Halaman Katalog Toko (`cooca.id/{slug}/katalog`):** Grid katalog belanja penuh dengan filter kategori dinamis, pencarian instan (*live search*), filter harga, dan badge stok.
3. **Halaman Detail Produk / PDP (`cooca.id/{slug}/produk/{slug-produk}`):** Galeri foto resolusi tinggi + zoom, pemilih varian warna/ukuran, kalkulasi stok live, tombol beli cepat & chat WhatsApp. Memiliki tag meta OpenGraph mandiri untuk preview media sosial yang memikat.
4. **Halaman Checkout Penuh (`cooca.id/{slug}/checkout`):** Pengalaman checkout 2 kolom lega: formulir alamat & kurir di kiri, rincian pembayaran di kanan. Bebas modal pop-up!
5. **Halaman Tentang Kami (`cooca.id/{slug}/tentang-kami`):** Cerita asal-usul brand, foto pendiri, sertifikasi Halal / izin BPOM, dan galeri cabang.
6. **Halaman Reservasi & Booking (`cooca.id/{slug}/reservasi`):** Formulir bertahap (*stepper*) untuk booking meja makan resto atau janji temu salon/servis bengkel.
7. **Halaman Cabang & Kontak (`cooca.id/{slug}/kontak`):** Direktori seluruh gerai fisik, jam buka per hari, dan peta Google Maps interaktif.
8. **Halaman Artikel & Berita Promo (`cooca.id/{slug}/artikel`):** Halaman blog edukasi & tips untuk mendongkrak peringkat SEO toko di Google.

---

### B. Konsep "Auto-Hide Dynamic Navigation"

Di dasbor merchant ([`/landing-page`](file:///c:/laragon/www/cooca_core/resources/views/app/landing_page/edit.blade.php)), pemilik bisnis memiliki kontrol sakelar (*toggle switch*) untuk setiap halaman:

```json
{
  "active_pages": {
    "home": true,
    "catalog": true,
    "about": true,
    "reservation": false,
    "contact": true,
    "blog": false,
    "order_tracking": true
  },
  "custom_labels": {
    "catalog": "Katalog Sparepart",
    "reservation": "Booking Servis Motor"
  }
}
```

* **Perilaku Sistem:**
  - Header Navbar & Footer toko publik **HANYA merender link halaman yang statusnya `true` (aktif)**.
  - Jika halaman diset `false` (nonaktif), tombol menu **OTOMATIS HIDE 100%** dari navigasi.
  - Jika ada pengunjung yang mengetikkan URL langsung (cth: `cooca.id/{slug}/reservasi` padahal toko menonaktifkannya), sistem secara cerdas mengalihkan (*redirect*) ke Beranda toko tanpa memunculkan halaman rusak.

---

### C. 20 Template Tema Industri Otentik

Setiap tema memiliki struktur header, gaya hero section, tata letak kartu produk, dan tipografi Google Fonts yang dirancang khusus:

1. **Kafe & Kopi:** *Artisan Brew* (Header gelap transparan, aroma earthy, Playfair Display + Inter).
2. **Restoran Nusantara:** *Nusantara Feast* (Terracotta hangat, layout piring lebar, DM Serif + Plus Jakarta).
3. **Fast Food & Snack:** *Neon Crunch* (Merah cerah berenergi, badge diskon besar, Outfit Bold).
4. **Bakery & Toko Kue:** *Velvet Patisserie* (Pastel pink melengkung anggun, kalender pre-order cake ultah, Cormorant + Poppins).
5. **Katering & Meal Prep:** *Epicurean Box* (Hijau emerald higienis, tabel jadwal menu harian, Plus Jakarta Sans).
6. **Fashion & Butik:** *Vogue Minimalist* (Monokrom hitam/krem, layout majalah mode, Cormorant Garamond).
7. **Elektronik & Gadget:** *Nexus Dark Cyber* (Dark mode pekat `#0B0F19`, aksen cyan/violet, Space Grotesk).
8. **Minimarket & Sembako:** *Fresh Mart Express* (Densitas produk rapat, tombol `+ / -` keranjang cepat, Plus Jakarta Sans).
9. **Bengkel & Otomotif:** *Apex Velocity* (Dark carbon & oranye balap, pencarian suku cadang tipe motor, Chakra Petch).
10. **Apotek & Toko Obat:** *Clinical Pure Trust* (Putih klinis & toska medis, upload resep dokter, Inter Modern).
11. **Klinik Kecantikan/Salon:** *Aura Glamour* (Marmer putih & rose gold, portofolio treatment, Playfair Display).
12. **Laundry Kiloan & Sepatu:** *Aqua Bubble Clean* (Biru laut segar, kalkulator timbangan kiloan cuci, Plus Jakarta Sans).
13. **Toko Bahan Bangunan:** *Ironclad Builder* (Abu-abu semen kokoh & kuning safety, kalkulator tonase truk, Barlow Semi Condensed).
14. **Percetakan & Printing:** *Pixel & Print Studio* (Layout studio ungu/neon, drag-drop file desain pelanggan, Syne + Inter).
15. **Toko Buku & Stationery:** *Bibliotheca* (Nuansa kertas perkamen hangat, kutipan cuplikan bab, Merriweather + DM Sans).
16. **Pet Shop & Perawatan:** *Playful Paws* (Cokelat biskuit ramah hewan, booking salon anjing/kucing, Quicksand Rounded).
17. **Cuci Mobil & Detailing:** *Hydro Shield Gloss* (Hitam pekat kilap air, komparasi nano ceramic, antrean live, Orbitron).
18. **Toko Bunga (Florist):** *Flora Romance* (Hijau botani & blush pink, kartu ucapan custom, Bodoni Moda).
19. **Fitness & Gym Studio:** *Titan Kinetic* (Matte black & acid green, tabel komparasi membership bulanan, Bebas Neue).
20. **Konsultan & Jasa B2B:** *Sovereign Enterprise* (Navy korporat & platinum gold, form proposal RFQ, Cormorant Garamond).

---

## 3. Struktur Direktori Blade Modular Baru

```
resources/views/public/
  ├── themes/
  │   ├── artisan_brew/
  │   │   ├── layouts/app.blade.php
  │   │   ├── home.blade.php
  │   │   ├── catalog.blade.php
  │   │   ├── product_detail.blade.php
  │   │   ├── checkout.blade.php
  │   │   ├── about.blade.php
  │   │   ├── reservation.blade.php
  │   │   └── contact.blade.php
  │   ├── vogue_minimalist/
  │   ├── apex_velocity/
  │   └── [17 tema industri lainnya...]
```

---

## 4. Kriteria Keberhasilan (Definition of Done)
1. File raksasa 574 KB tereliminasi dan digantikan oleh arsitektur multi-page modular.
2. Seluruh proses belanja (katalog, detail produk, keranjang, checkout) berjalan pada halaman penuh tanpa ada modal pop-up sempit.
3. Halaman yang dinonaktifkan oleh pemilik bisnis otomatis lenyap dari navbar dan footer publik.
4. Setiap halaman detail produk memiliki tag OpenGraph unik sehingga tautan yang dishare menampilkan foto produk dan harga resmi.
5. Pemilik toko dapat mengganti tema industri dengan 1-klik di menu Desain Halaman Toko dan langsung melihat perubahan visualnya.
