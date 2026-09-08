# PANDUAN DEPLOYMENT WHATSAPP GATEWAY (BAILEYS) DI GOOGLE CLOUD FOREVER FREE VPS (e2-micro)

Panduan teknis lengkap untuk meng-hosting microservice **WhatsApp Gateway (`wa-server`)** COOCA secara **GRATIS SELAMANYA (Always Free Tier)** di **Google Cloud Platform (GCP)** menggunakan instance **e2-micro** (1 GB RAM, 30 GB Disk), lalu menghubungkannya dengan backend Laravel COOCA di Hostinger/Production.

---

## 1. Arsitektur Multi-Server Hemat Biaya

```text
┌────────────────────────────────────────────────────────┐
│                   HOSTINGER / CLOUD                    │
│   COOCA Core Web Application (Laravel 12 + MySQL)      │
│   Domain: https://umkm.cooca.id                        │
└────────────────────────┬───────────────────────────────┘
                         │ 
                         │ HTTP API Request (JSON + Token)
                         ▼
┌────────────────────────────────────────────────────────┐
│               GOOGLE CLOUD "ALWAYS FREE"               │
│   Instance: e2-micro (1GB RAM + 2GB Swap, 30GB Disk)   │
│   Microservice: Node.js + @whiskeysockets/baileys      │
│   Port: 3000 (Protected by WA_WORKER_TOKEN / UFW)     │
└────────────────────────────────────────────────────────┘
```

> [!TIP]
> **Kenapa Baileys Sangat Cocok untuk RAM 1GB?**
> Berbeda dengan *whatsapp-web.js* / Puppeteer yang menjalankan browser Chromium penuh dan membutuhkan minimal 2–4 GB RAM, gateway `wa-server` COOCA menggunakan **`@whiskeysockets/baileys`** yang merupakan koneksi direct WebSocket langsung ke server WhatsApp. Penggunaan memorinya sangat irit (hanya ~60 MB – 120 MB per sesi aktif), sehingga dapat berjalan 24/7 dengan stabil di Google Cloud e2-micro gratis selamanya.

---

## 2. Ketentuan Google Cloud "Always Free" (Wajib Diperhatikan)

Agar tagihan Google Cloud Anda **Rp 0 (100% Gratis Selamanya)**, Anda **WAJIB** memilih parameter berikut saat membuat Compute Engine VM:

| Parameter | Pengaturan Wajib (Free Tier) | Catatan Penting |
| :--- | :--- | :--- |
| **Region** | `us-central1` (Iowa), `us-east1` (South Carolina), atau `us-west1` (Oregon) | **JANGAN** pilih region Jakarta/Asia karena tidak masuk program Always Free. |
| **Zone** | `us-central1-a` (atau bebas dalam 3 region di atas) | - |
| **Series** | `E2` | - |
| **Machine type** | `e2-micro` (2 vCPU burstable, 1 GB memory) | Jangan pilih e2-small atau e2-medium. |
| **Boot disk type** | **Standard persistent disk** (HDD) | **JANGAN** pilih SSD Persistent Disk / Balanced Persistent Disk. |
| **Boot disk size** | **30 GB** | Kuota gratis GCP adalah 30 GB standard disk per bulan. |
| **OS Image** | **Ubuntu 22.04 LTS** atau **Ubuntu 24.04 LTS** | Bebas biaya lisensi. |

---

## 3. Langkah Pembuatan VM di Google Cloud Console

1. Buka [Google Cloud Console](https://console.cloud.google.com/).
2. Masuk ke menu **Compute Engine** $\rightarrow$ **VM instances** $\rightarrow$ Klik **Create Instance**.
3. Atur konfigurasi sesuai tabel di atas:
   - **Name:** `cooca-wa-gateway`
   - **Region:** `us-central1 (Iowa)`
   - **Zone:** `us-central1-a`
   - **Machine Configuration:** General-purpose $\rightarrow$ Series: **E2** $\rightarrow$ Machine type: **e2-micro (2 vCPU, 1 GB memory)**.
4. Pada bagian **Boot disk**:
   - Klik **Change**.
   - Operating system: **Ubuntu**.
   - Version: **Ubuntu 22.04 LTS**.
   - Boot disk type: **Standard persistent disk** *(PENTING!)*.
   - Size: **30 GB**.
   - Klik **Select**.
5. Pada bagian **Firewall**:
   - Centang **Allow HTTP traffic** dan **Allow HTTPS traffic**.
6. Klik **Create** dan tunggu hingga instance berstatus centang hijau (Running). Catat **External IP** (misal: `34.123.45.67`).

---

## 4. Konfigurasi Firewall Rule Port 3000 di GCP

Agar backend Laravel COOCA dapat berkomunikasi dengan WhatsApp gateway pada port 3000:

1. Di GCP Console, cari menu **VPC network** $\rightarrow$ **Firewall**.
2. Klik **Create Firewall Rule**:
   - **Name:** `allow-wa-gateway-port-3000`
   - **Targets:** All instances in the network (atau Specified target tags: `wa-server`)
   - **Source IPv4 ranges:** `0.0.0.0/0` (atau lebih aman: masukkan IP Server Hostinger Anda)
   - **Protocols and ports:** Centang **Specified protocols and ports** $\rightarrow$ centang **TCP** $\rightarrow$ ketik `3000`.
3. Klik **Create**.

---

## 5. Setup Server Otomatis (Hanya 1 Perintah)

1. Buka SSH ke VM Anda (bisa klik tombol **SSH** langsung di web browser Google Cloud Console).
2. Upload atau clone folder `wa-server` ke dalam VM, atau buat folder baru:
   ```bash
   mkdir -p ~/cooca-wa && cd ~/cooca-wa
   ```
3. Salin file-file dari folder `wa-server/` ke dalam folder tersebut (`server.js`, `SessionManager.js`, `package.json`, `ecosystem.config.js`, `deploy-gcp.sh`, `.env.example`).
4. Berikan izin eksekusi dan jalankan script otomatis:
   ```bash
   chmod +x deploy-gcp.sh
   ./deploy-gcp.sh
   ```

Script `deploy-gcp.sh` akan otomatis:
- ✅ Membuat **Swap Memory 2 GB** (krusial agar VM 1 GB RAM tidak kehabisan memori).
- ✅ Mengoptimasi `swappiness` ke nilai 10.
- ✅ Menginstal **Node.js 20 LTS** & PM2 Process Manager.
- ✅ Menginstal seluruh dependensi Baileys & Express.
- ✅ Mengonfigurasi auto-start systemd agar server WA otomatis hidup kembali jika VM di-restart.

---

## 6. Konfigurasi `.env` di VM Google Cloud

Edit file `.env` di VM:
```bash
nano ~/cooca-wa/.env
```
Isi dengan pengaturan berikut:
```env
PORT=3000
WA_SERVER_PORT=3000

# Buat token rahasia yang sulit ditebak
WA_WORKER_TOKEN=gcp_wa_super_secret_token_cooca_2026

# URL domain aplikasi COOCA Anda di Hostinger
LARAVEL_API_URL=https://umkm.cooca.id
```
Simpan (`Ctrl + O`, `Enter`, `Ctrl + X`), lalu restart service PM2:
```bash
pm2 restart cooca-wa-server
```

---

## 7. Konfigurasi `.env` di Backend Laravel (Hostinger / Local)

Buka file `.env` pada project Laravel COOCA Anda di Hostinger:
```env
# URL WhatsApp Server di Google Cloud Free VPS
WA_SERVER_URL=http://<EXTERNAL_IP_GCP_ANDA>:3000

# Token yang sama persis dengan yang Anda tulis di VM GCP
WA_WORKER_TOKEN=gcp_wa_super_secret_token_cooca_2026
```

Lakukan clear config di terminal Laravel:
```bash
php artisan config:clear
```

---

## 8. Verifikasi Koneksi & Scan QR WhatsApp

1. Buka aplikasi web COOCA Anda di browser:
   `https://umkm.cooca.id/admin/whatsapp` atau menu **WhatsApp Gateway** bisnis.
2. Klik tombol **Hubungkan WhatsApp** / **Mulai Sesi**.
3. Sistem akan memanggil API GCP VPS dan menampilkan **QR Code WhatsApp**.
4. Buka aplikasi WhatsApp di HP Anda:
   - Pilih menu **Perangkat Tertaut (Linked Devices)** $\rightarrow$ **Tautkan Perangkat**.
   - Scan QR code yang tampil di layar COOCA.
5. Status akan langsung berubah menjadi **Connected / Terhubung**!
6. Anda sekarang siap mengirim WhatsApp Broadcast, Reminder Tagihan POS, dan Notifikasi Otomatis **100% GRATIS SELAMANYA** tanpa biaya server tambahan.

---

## 9. Perintah Maintenance Berguna di VM GCP

| Kebutuhan | Perintah di VM GCP |
| :--- | :--- |
| **Cek status running** | `pm2 status` |
| **Lihat live logs / pesan masuk** | `pm2 logs cooca-wa-server` |
| **Restart server WA** | `pm2 restart cooca-wa-server` |
| **Cek sisa RAM & Swap** | `free -h` |
| **Cek sisa disk 30 GB** | `df -h` |
