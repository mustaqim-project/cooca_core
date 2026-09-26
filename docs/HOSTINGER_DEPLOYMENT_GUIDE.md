# Panduan Setup Server & Deployment Cooca ERP

Dokumen ini berisi panduan komprehensif untuk melakukan instalasi dan setup server aplikasi **Cooca ERP** baik di lingkungan **Shared Hosting / Cloud Hosting (Hostinger / cPanel)** maupun di **Cloud VPS (Ubuntu / Debian + Nginx)**.

---

## 📋 1. Persyaratan Sistem (System Requirements)

- **PHP:** Versi 8.2 atau 8.3 (Sangat disarankan **PHP 8.3**)
- **PHP Extensions Wajib:**
  - `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `tokenizer`, `xml`, `gd` atau `imagick`
- **Database:** MySQL 8.0+ atau MariaDB 10.6+
- **Composer:** Versi 2.x
- **Node.js & NPM:** Node.js v18+ & NPM v9+ (untuk kompilasi asset Vite/Tailwind)
- **Web Server:** Nginx (VPS) atau LiteSpeed / Apache dengan `mod_rewrite` aktif

---

## 🌐 2. Setup di Hostinger / Shared Hosting (cPanel / hPanel)

Repository Cooca sudah dioptimalkan untuk struktur hosting Hostinger (`cooca.id`).

### Langkah 1: Akses SSH & Masuk ke Direktori Web
Login via SSH ke akun hosting Anda:
```bash
ssh u218101292@cooca.id -p 65002
cd /home/u218101292/domains/cooca.id/public_html
```

### Langkah 2: Tarik Kode Terbaru dari Git
Jika repository belum di-clone:
```bash
git clone https://github.com/mustaqim-project/cooca_core.git .
```
Jika sudah ada, tarik perubahan terbaru:
```bash
git pull origin main
```

### Langkah 3: Konfigurasi File `.env`
Salin template environment jika belum ada:
```bash
cp .env.example .env
```
Sesuaikan konfigurasi produksi pada file `.env`:
```env
APP_NAME="Cooca ERP"
APP_ENV=production
APP_KEY=base64:... #(generate dengan php artisan key:generate)
APP_DEBUG=false
APP_URL=https://cooca.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u218101292_cooca
DB_USERNAME=u218101292_user
DB_PASSWORD=PasswordDatabaseAnda

CACHE_STORE=file
SESSION_DRIVER=database
QUEUE_CONNECTION=sync
```

### Langkah 4: Install Dependensi Composer
Gunakan binary PHP 8.3 hosting (Hostinger CLI path: `/opt/alt/php83/usr/bin/php`):
```bash
/opt/alt/php83/usr/bin/php /usr/local/bin/composer install --no-dev --optimize-autoloader
```

### Langkah 5: Jalankan Migrasi & Database Seeder
```bash
/opt/alt/php83/usr/bin/php artisan migrate --force
/opt/alt/php83/usr/bin/php artisan db:seed --class=RbacSeeder --force
/opt/alt/php83/usr/bin/php artisan db:seed --class=DefaultUnitSeeder --force
/opt/alt/php83/usr/bin/php artisan db:seed --class=DefaultCostCategorySeeder --force
/opt/alt/php83/usr/bin/php artisan db:seed --class=BusinessTemplateSeeder --force
/opt/alt/php83/usr/bin/php artisan db:seed --class=PostSeeder --force
```

### Langkah 6: Symlink Storage & Izin Folder
```bash
/opt/alt/php83/usr/bin/php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### Langkah 7: Optimasi Cache Laravel
```bash
/opt/alt/php83/usr/bin/php artisan optimize:clear
/opt/alt/php83/usr/bin/php artisan config:cache
/opt/alt/php83/usr/bin/php artisan route:cache
/opt/alt/php83/usr/bin/php artisan view:cache
```

### Langkah 8: Menggunakan Script Deploy Otomatis (`deploy.sh`)
Di dalam repository telah disediakan script `deploy.sh` yang menjalankan langkah 2 s/d 7 secara otomatis:
```bash
chmod +x deploy.sh
./deploy.sh
```

### Langkah 9: Setup Cron Job (Laravel Scheduler)
Buka menu **Cron Jobs** di hPanel Hostinger / cPanel, lalu tambahkan jadwal **Setiap Menit (`* * * * *`)**:
```bash
cd /home/u218101292/domains/cooca.id/public_html && ./cron.sh >> /dev/null 2>&1
```
Atau langsung memanggil PHP CLI:
```bash
* * * * * /opt/alt/php83/usr/bin/php /home/u218101292/domains/cooca.id/public_html/artisan schedule:run >> /dev/null 2>&1
```

---

## 🖥️ 3. Setup di Cloud VPS (Ubuntu 22.04 / 24.04 + Nginx)

Jika Anda mendeploy di VPS mandiri (DigitalOcean, AWS EC2, GCP, Linode, IDCloudHost):

### Langkah 1: Update Server & Install Paket Dasar
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip zip supervisor nginx software-properties-common
```

### Langkah 2: Install PHP 8.3 & Ekstensi
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-mbstring \
    php8.3-xml php8.3-bcmath php8.3-zip php8.3-gd php8.3-intl php8.3-redis
```

### Langkah 3: Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Langkah 4: Clone Repository ke `/var/www/cooca`
```bash
sudo mkdir -p /var/www/cooca
sudo chown -R $USER:$USER /var/www/cooca
git clone https://github.com/mustaqim-project/cooca_core.git /var/www/cooca
cd /var/www/cooca
```

### Langkah 5: Setup Environment & Dependensi
```bash
cp .env.example .env
composer install --no-dev --optimize-autoloader
php artisan key:generate
# Edit database credentials di file .env
nano .env
php artisan migrate --seed --force
php artisan storage:link
```

### Langkah 6: Atur Kepemilikan & Izin Web Server
```bash
sudo chown -R www-data:www-data /var/www/cooca
sudo chmod -R 775 /var/www/cooca/storage /var/www/cooca/bootstrap/cache
```

### Langkah 7: Konfigurasi Virtual Host Nginx
Buat file konfigurasi `/etc/nginx/sites-available/cooca`:
```nginx
server {
    listen 80;
    server_name cooca.id www.cooca.id;
    root /var/www/cooca/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
Aktifkan konfigurasi dan restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/cooca /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Langkah 8: Pasang SSL Gratis (Let's Encrypt / Certbot)
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d cooca.id -d www.cooca.id
```

### Langkah 9: Setup Supervisor untuk Queue Worker
Buat file `/etc/supervisor/conf.d/cooca-worker.conf`:
```ini
[program:cooca-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/cooca/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/cooca/storage/logs/worker.log
stopwaitsecs=3600
```
Update & jalankan worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start cooca-worker:*
```

### Langkah 10: Setup Cron Job di VPS
Jalankan `crontab -e -u www-data` dan tambahkan:
```bash
* * * * * php /var/www/cooca/artisan schedule:run >> /dev/null 2>&1
```

---

## 💻 4. Setup Server Lokal (Development - Windows / Laragon)

Jika ingin menjalankan aplikasi di laptop lokal untuk pengembangan:

1. Letakkan folder di `c:\laragon\www\cooca_core`.
2. Buka terminal di folder project:
   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   ```
3. Buat database `cooca_core` di MySQL lokal (Laragon/HeidiSQL).
4. Jalankan migrasi & data awal:
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```
5. Jalankan server lokal:
   ```bash
   php artisan serve --port=1986
   # Di tab terminal lain untuk asset:
   npm run dev
   ```
6. Buka di browser: `http://127.0.0.1:1986` atau `http://cooca_core.test` (jika menggunakan Laragon virtual host).

---

## 🛠️ 5. Perintah Rutin Maintenance Server

| Perintah | Deskripsi |
| :--- | :--- |
| `./deploy.sh` | Update kode Git, migrasi, dan refresh cache produksi otomatis |
| `php artisan optimize:clear` | Menghapus seluruh cache (config, route, views, event) |
| `php artisan config:cache` | Membuat cache file konfigurasi untuk mempercepat response time |
| `php artisan route:cache` | Membuat cache routing sistem |
| `php artisan view:cache` | Mengompilasi seluruh template Blade ke PHP bytecode |
| `php artisan schedule:run` | Menjalankan scheduler (pembaharuan status langganan, cron reminder) |
