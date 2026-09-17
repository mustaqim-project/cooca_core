# Aturan Bisnis & Validasi Kapabilitas Konten

## 1. Aturan Bisnis COOCA: Maksimal 5 Tagar Unik (Strict Hashtag Rule)
Sistem COOCA memberlakukan aturan bisnis khusus untuk menjaga kualitas estetika dan efektivitas algoritma konten UMKM:
- **Setiap postingan atau saluran hanya diizinkan memuat maksimal 5 tagar unik.**
- Mesin validasi `SocialMediaContentValidator` mengekstrak seluruh tagar dengan ekspresi reguler `/^#([a-zA-Z0-9_\x{0080}-\x{FFFF}]+)/u`.
- Dilakukan normalisasi huruf kecil (*lowercase*) untuk mendeteksi duplikasi secara tidak sensitif huruf besar/kecil (contoh: `#UMKM`, `#umkm`, dan `#Umkm` dihitung sebagai 1 tagar unik).
- Jika jumlah tagar unik melebihi 5, sistem menolak postingan baik pada validasi antarmuka frontend (indikator counter dinamis merah) maupun pada lapisan backend Laravel sebelum penyimpanan database.

## 2. Batas Karakter Caption Resmi (Official API Limits)
Perhitungan karakter dilakukan menggunakan fungsi multi-byte aman `mb_strlen($caption, 'UTF-8')`:
- **Threads**: Maksimal **500 karakter**.
- **Instagram**: Maksimal **2.200 karakter**.
- **TikTok**: Maksimal **2.200 karakter**.
- **Facebook**: Maksimal **63.206 karakter** (dengan rekomendasi COOCA di bawah 5.000 karakter).

## 3. Platform Caption Override
Merchant dapat menulis caption global di editor utama, kemudian mengaktifkan akordion kustomisasi per saluran untuk menyunting narasi yang lebih pas dengan karakteristik audiens masing-masing platform:
- Contoh: Menulis caption santai / POV untuk TikTok, dan caption formal dengan rincian harga untuk Facebook.
- Custom caption tetap divalidasi dengan aturan maksimal 5 tagar unik.
