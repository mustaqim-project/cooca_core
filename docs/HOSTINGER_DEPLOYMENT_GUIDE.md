# PANDUAN DEPLOYMENT HOSTINGER — COOCA CORE (umkm.cooca.id)

Dokumen ini berisi panduan teknis langkah demi langkah untuk melakukan deploy backend web **Cooca UMKM** ke hosting **Hostinger** (domain `http://umkm.cooca.id` / `https://umkm.cooca.id`) dan menghubungkan **Aplikasi Mobile (Flutter)** ke server production.

---

## 1. Konfigurasi Subdomain di Hostinger (hPanel)

1. Masuk ke **Hostinger hPanel** $\rightarrow$ Pilih Menu **Domains / Subdomains**.
2. Buat Subdomain baru:
   - **Subdomain Name:** `umkm`
   - **Domain:** `cooca.id`
   - **Custom folder for subdomain:** Centang opsi ini, lalu arahkan folder root ke:
     `public_html/umkm.cooca.id` (atau default `public_html/umkm`)
3. Pastikan **SSL Certificate (Let's Encrypt / Hostinger SSL)** telah diaktifkan untuk `umkm.cooca.id` agar mendukung protokol aman `https://umkm.cooca.id`.

---

## 2. Upload File & Konfigurasi Web Server

### Opsi A: Menggunakan SSH / Git (Direkomendasikan)
1. Aktifkan akses **SSH** di hPanel $\rightarrow$ **Advanced** $\rightarrow$ **SSH Access**.
2. Hubungkan terminal SSH Anda:
   ```bash
   ssh -p 65002 u123456789@umkm.cooca.id
   ```
3. Clone repository atau upload project ke folder root subdomain:
   ```bash
   cd ~/domains/cooca.id/public_html/app
   # atau folder subdomain Anda
   ```
4. Install dependensi Composer dan NPM:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   ```

### Opsi B: Menggunakan File Manager / ZIP
1. Kompres seluruh file project ke dalam file ZIP (kecuali folder `node_modules` dan `.git`).
2. Upload dan Extract file ZIP di folder subdomain hPanel.
3. Struktur `.htaccess` di root project telah disiapkan untuk meneruskan trafik ke folder `/public` secara otomatis:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteCond %{HTTP:Authorization} .
       RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
       RewriteCond %{REQUEST_URI} !^/public/
       RewriteRule ^(.*)$ public/$1 [L]
   </IfModule>
   ```

---

## 3. Konfigurasi Database MySQL & Environment (.env)

1. Buat Database MySQL baru di hPanel $\rightarrow$ **Databases** $\rightarrow$ **MySQL Databases**:
   - Catat: **Database Name**, **Username**, dan **Password**.
2. Salin file [.env.hostinger.example](file:///c:/laragon/www/cooca_core/.env.hostinger.example) menjadi `.env` di server:
   ```bash
   cp .env.hostinger.example .env
   ```
3. Sesuaikan isi `.env` server:
   ```ini
   APP_NAME="Cooca UMKM"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://umkm.cooca.id
   ASSET_URL=https://umkm.cooca.id

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=u123456789_coocadb
   DB_USERNAME=u123456789_coocauser
   DB_PASSWORD=PasswordDatabaseAnda
   ```
4. Jalankan migrasi dan seeder awal:
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   ```
5. Optimasi Cache Laravel Production:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## 4. Konfigurasi Aplikasi Mobile (Flutter)

Aplikasi Mobile Cooca UMKM telah dikonfigurasi secara default menggunakan endpoint production:
- **Default Base URL:** `https://umkm.cooca.id/api/v1` (pada [api_endpoints.dart](file:///c:/laragon/www/cooca_core/mobile_app/lib/core/constants/api_endpoints.dart)).

### Opsi Mengubah Server di Aplikasi:
Pengguna dapat membuka menu:
**Menu & Pengaturan** $\rightarrow$ **Pengaturan Server & Koneksi** $\rightarrow$ Klik chip preset **☁️ Production (umkm.cooca.id)** atau masukkan URL server custom kapan saja.

### Membangun File APK / Release Production:
Jalankan perintah berikut di folder `mobile_app`:
```bash
cd mobile_app
flutter clean
flutter pub get
flutter build apk --release
```
File APK siap instalasi akan berada di: `mobile_app/build/app/outputs/flutter-apk/app-release.apk`.

---

## 5. Integrasi WhatsApp Gateway (Render.com)

Aplikasi COOCA di Hostinger terhubung secara seamless dengan microservice WhatsApp (`wa-server`) yang di-deploy di **Render.com**.

### Konfigurasi di `.env` Hostinger:
```env
# URL publik Web Service Render Anda (HTTPS tanpa trailing slash)
WA_SERVER_URL=https://cooca-wa-server.onrender.com

# Token autentikasi aman (wajib sama dengan Environment Variables di Render)
WA_WORKER_TOKEN=cooca_secret_worker_token_production
```

### Konfigurasi di Dashboard Render.com:
1. Masuk ke **Environment Variables** service `cooca-wa-server` di Render:
   - `NODE_ENV` = `production`
   - `WA_WORKER_TOKEN` = `cooca_secret_worker_token_production` (sama persis dengan di Hostinger)
   - `LARAVEL_API_URL` = `https://umkm.cooca.id` (URL Hostinger Anda)
2. Pastikan Health Check Path di Render diatur ke `/health`.
3. Setelah deploy Render aktif, jalankan clear config di Hostinger:
   ```bash
   php artisan config:clear
   ```
4. Buka menu **WhatsApp** di dashboard web `https://umkm.cooca.id/app/whatsapp` untuk scan QR Code dan mengaktifkan notifikasi kasir, struk POS, dan pesan blast otomatis.
