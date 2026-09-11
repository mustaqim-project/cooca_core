# Audit Teknis Implementasi WhatsApp Server

Tanggal audit: 2026-09-11

## Ringkasan eksekutif

Implementasi runtime yang ditemukan adalah **satu proses Node.js + Express** yang memuat banyak socket WhatsApp Baileys. Setiap bisnis memiliki satu session Baileys sendiri, tetapi bukan satu process dan bukan satu browser.

- Library runtime: `@whiskeysockets/baileys@7.0.0-rc13`.
- Framework HTTP: Express `5.2.1`.
- Node minimum: `>=20.0.0`.
- Auth Baileys: filesystem, `useMultiFileAuthState`, satu direktori per session.
- State aktif: `Map` in-memory pada `SessionManager`.
- Database: Laravel menyimpan metadata session, log pesan, campaign, dan recipient; Node tidak memakai database.
- Queue Redis: tidak dipakai oleh gateway. Broadcast dan reminder WhatsApp dijalankan sinkron oleh Laravel.
- Browser: tidak ada Chromium, Puppeteer, atau Playwright pada `wa-server` runtime.
- PM2: tersedia sebagai konfigurasi/deployment option, satu instance.
- WebSocket: dipakai Baileys secara internal untuk koneksi ke WhatsApp; API gateway sendiri memakai HTTP/REST.

Folder `whatsapp-web.js/` adalah checkout/library terpisah dengan dependency Puppeteer, tetapi source `wa-server/` tidak meng-import atau menjalankannya. Jadi folder tersebut bukan bukti bahwa runtime aktif memakai `whatsapp-web.js`.

## 1. Komponen dan bukti source

| Komponen | Kondisi aktual | Bukti |
|---|---|---|
| WhatsApp library | Baileys | `wa-server/SessionManager.js`: `makeWASocket`, `useMultiFileAuthState`, `DisconnectReason` |
| Versi | `7.0.0-rc13` | `wa-server/package.json`, `wa-server/package-lock.json`, `npm ls` |
| HTTP framework | Express `5.2.1` | `wa-server/server.js`, `wa-server/package.json` |
| HTTP client | Axios `1.18.1` | `SessionManager.js` untuk webhook/media, package manifest |
| QR rendering | `qrcode` `1.5.4` | `SessionManager.js`: `QRCode.toDataURL()` |
| Logging | Pino `10.3.1` | `makeWASocket({ logger: pino(...) })` |
| Database Node | Tidak ada | Tidak ada driver/ORM/database call pada `wa-server` |
| Database Laravel | Ada | `whatsapp_sessions`, `whatsapp_message_logs`, broadcast tables dan model Eloquent |
| Redis | Tidak digunakan langsung untuk WhatsApp | Laravel menyediakan konfigurasi Redis, tetapi gateway memakai filesystem dan Map; lock receipt mengikuti konfigurasi Cache Laravel |
| Queue | Tidak digunakan untuk send/broadcast WA | `WhatsAppBroadcastWebController::store()` memanggil `sendBroadcast()` langsung; command reminder juga mengirim langsung |
| Filesystem auth | Ya | `AUTH_DIR`, default `wa-server/.baileys_auth`, `session_<sessionId>` |
| Browser automation | Tidak pada runtime aktif | Tidak ada import Puppeteer/Playwright/Chromium di `wa-server` |
| WebSocket | Ya, internal Baileys | `makeWASocket()` membuka koneksi WhatsApp; tidak ada server WebSocket custom |
| REST API | Ya | Express routes di `wa-server/server.js`; Laravel memanggil dengan `Http` |
| Process manager | PM2 tersedia | `wa-server/ecosystem.config.js`, `setup-vps.sh`, `deploy-gcp.sh`; config `instances: 1` |
| Background process | Node gateway persistent; scheduler Laravel terpisah | PM2 option, `cron-worker.bat`, `routes/console.php` |

`qrcode-terminal` terpasang tetapi tidak digunakan oleh `SessionManager.js`.

## 2. Alur aktual: klik Connect sampai siap

### Alur bisnis

```text
User klik "Mulai Scan QR"
  -> POST Laravel /whatsapp/start
  -> WhatsAppWebController::startSession()
  -> WhatsAppGatewayService::startSession()
  -> POST Node /api/sessions/start
  -> SessionManager::initSession(biz_<8-char-business-id>)
  -> makeWASocket() ke WhatsApp
  -> event connection.update membawa qr
  -> QR raw + Data URL disimpan di memory Node
  -> Laravel/browser GET /whatsapp/qr setiap 2.5 detik
  -> GET Node /api/sessions/:id/qr
  -> browser menampilkan qrDataUrl
  -> user scan QR
  -> Baileys menerima credential dan memicu creds.update
  -> saveCreds menulis auth ke filesystem
  -> event connection.update dengan connection=open
  -> status Node menjadi CONNECTED
  -> polling Laravel membaca CONNECTED dan memperbarui metadata DB
```

### Endpoint Laravel

Route berada di `routes/web.php` dalam group `whatsapp`:

- `POST /whatsapp/start` -> `WhatsAppWebController::startSession`
- `GET /whatsapp/qr` -> `getQr`
- `GET /whatsapp/status` -> `checkStatus`
- `POST /whatsapp/disconnect` -> `disconnect`
- `POST /whatsapp/test` -> `testSend`

UI berada di `resources/views/app/whatsapp/index.blade.php`. `startSession()` memulai polling 2,5 detik. `init()` melakukan cek awal lewat route QR, tetapi tidak membuat session baru otomatis.

### Session ID

Untuk bisnis, `WhatsAppGatewayService::sessionId()` membuat:

```text
biz_ + 8 karakter awal business UUID tanpa tanda hubung
```

Untuk admin, `AdminWhatsAppService::getSessionId()` memakai `admin_platform` secara default.

### QR dan autentikasi

`SessionManager::initSession()`:

1. Membuat `AUTH_DIR/session_<sessionId>`.
2. Memanggil `useMultiFileAuthState(sessionDir)`.
3. Memanggil `fetchLatestBaileysVersion()` dengan fallback version jika gagal.
4. Membuat socket dengan `makeWASocket()`.
5. Mendaftarkan `creds.update -> saveCreds`.
6. Pada `connection.update` yang memiliki `qr`, mengubah status ke `SCAN_QR`, menyimpan QR raw dan data URL di object session.
7. Pada `connection === 'open'`, mengubah status ke `CONNECTED`, menghapus QR dari memory, dan menyimpan `sock.user`.

QR tidak disimpan sebagai record database dan tidak dipersistenkan ke filesystem. QR hanya tersedia selama session aktif menunggu scan.

### Bagaimana sistem mengetahui online

Sumber kebenaran runtime adalah `sessionData.status` di Node. Status diambil melalui endpoint Node `/api/sessions/:sessionId/status` atau `/qr`.

Database Laravel bukan sumber live state otomatis. Controller Laravel hanya menyinkronkan sebagian status ketika browser melakukan polling. Node tidak mengirim event status connected/disconnected ke webhook.

## 3. Session lifecycle

### Penyimpanan

```text
wa-server/.baileys_auth/
  session_biz_xxxxxxxx/
    <file-file multi-file auth Baileys>
  session_admin_platform/
    <file-file multi-file auth Baileys>
```

Direktori dapat diganti dengan `AUTH_DIR`. Credential tidak disimpan di `whatsapp_sessions`; tabel itu hanya berisi `business_id`, session ID, nomor, device, status, setting receipt, dan timestamp.

### Restore setelah restart

Saat `server.js` mulai, semua child directory yang namanya diawali `session_` dibaca. Untuk tiap directory, server menjalankan `manager.initSession(sessionId)` tanpa menunggu satu session selesai sebelum memulai yang lain.

Konsekuensinya:

- Session yang auth-nya masih valid dapat login kembali tanpa scan QR.
- Session yang belum selesai pairing/corrupt dapat gagal atau membuat flow QR baru, tergantung respons Baileys.
- Restore tidak membaca `whatsapp_sessions` untuk mencari daftar bisnis; hanya folder auth yang ada.
- Jika auth directory hilang, session tidak dipulihkan dan harus dimulai lagi melalui API.

### Reconnect

Pada `connection.update` dengan `connection === 'close'`:

- status di memory diubah menjadi `DISCONNECTED`;
- QR memory dihapus;
- reconnect dilakukan kecuali `DisconnectReason.loggedOut`;
- reconnect dijadwalkan tetap 3 detik kemudian melalui `initSession()`.

Yang tidak ditemukan:

- retry counter;
- batas retry;
- exponential backoff;
- jitter;
- circuit breaker;
- koordinasi antar-session;
- queue reconnect.

Ini membuat reconnect storm memungkinkan: banyak session yang putus pada waktu berdekatan akan membuat banyak socket baru hampir bersamaan, terutama setelah restart atau gangguan jaringan bersama.

### Logout dan session corrupt

`DELETE /api/sessions/:sessionId` memanggil `sock.logout()`, `sock.end()`, menghapus entry Map, lalu `fs.rmSync(sessionDir, { recursive: true, force: true })`. Ini adalah logout eksplisit dan menghapus credential.

Jika Baileys memberi status `loggedOut`, handler close memanggil `deleteSession()`, sehingga auth directory juga dihapus.

Tidak ada handler khusus yang mengisolasi/mengarantina auth corrupt. Error init hanya mengubah status object menjadi `DISCONNECTED` dan melempar error; penanganan berikutnya bergantung pada request/reconnect berikutnya.

## 4. Alur pengiriman pesan

### Pesan test, receipt, dan business message

```text
Laravel UI/POS/controller
  -> WhatsAppGatewayService::sendMessage()
  -> sendRawMessage(sessionId, phone, message, options)
  -> Laravel Http POST /send-message
  -> Node server.js route /send-message
  -> SessionManager::sendMessage()
  -> cek Map session dan status CONNECTED
  -> normalisasi nomor 0... menjadi 62...
  -> sock.onWhatsApp(jid)
  -> sock.sendMessage(exists.jid, payload)
  -> response JSON kembali ke Laravel
  -> Laravel menulis log untuk flow yang memang memiliki logging
```

Node menerima session dari `body.session`, atau `x-device-token`, atau default `default`. Laravel business selalu mengirim session ID `biz_...`; admin mengirim `admin_platform` melalui `sendRawMessage()`.

`SessionManager::sendMessage()` mendukung text, image, video, audio, document, location, dan vCard. URL media terlebih dahulu dapat diperiksa dengan `axios.head()` dengan batas `16 MB` jika `Content-Length` tersedia. File lokal dibaca langsung ke memory.

### Synchronous/asynchronous

Request kirim bersifat **synchronous terhadap caller**: Laravel menunggu respons HTTP Node, dan Node menunggu `onWhatsApp()` serta `sock.sendMessage()` selesai. Tidak ada queue/job untuk satu pesan.

Broadcast juga synchronous: `sendBroadcast()` mengambil seluruh customer dengan `get()`, mengirim satu per satu, dan `usleep(1_500_000)` di antara pesan. Reminder admin menggunakan pola throttle yang sama dari command scheduler.

### Error, offline, timeout, retry

- Jika session tidak ada/offline, Node melempar error dan endpoint `/send-message` mengembalikan HTTP 500.
- Laravel `sendRawMessage()` menangkap exception HTTP dan mengembalikan `{success:false,error:...}`.
- Client admin memakai `retry(2, 600ms)`, tetapi `WhatsAppGatewayService::client()` tidak memakai retry.
- Timeout kirim business/test: 15 detik pada Laravel; admin client default 25 detik atau 30 detik untuk start.
- Tidak ada retry level pesan pada gateway.
- Tidak ada outbox/pending queue untuk dicoba lagi ketika session offline.
- Receipt dan test menulis `whatsapp_message_logs`; broadcast menulis recipient log; reminder menulis tabel reminder.
- `sendMessage()` mengembalikan hasil Baileys ke caller, tetapi tidak ada penyimpanan message ID yang terstruktur.
- Tidak ditemukan listener `messages.update`, delivery/read receipt, atau status `delivered/read`. Status `sent` di database berarti request/gateway berhasil, bukan bukti delivered/read oleh penerima.

### Incoming webhook

Event aktif adalah `messages.upsert` untuk tipe `notify` dan `append`. Hanya teks `conversation` atau `extendedTextMessage.text` yang diteruskan. Media/non-text tanpa text diabaikan.

Payload dikirim Axios ke webhook Laravel dengan Bearer worker token. Laravel `WhatsAppWebhookController::handle()` memvalidasi token, mengabaikan `fromMe`, lalu membuat log `incoming` jika session ID menemukan row `whatsapp_sessions`.

Tidak ada event status connection yang dikirim ke webhook. Ada kode untuk field `status` jika webhook menerima payload demikian, tetapi `SessionManager` tidak mengirim payload status tersebut.

## 5. Model multi-user/multi-WhatsApp

Model aktualnya adalah **banyak session/socket dalam satu process Node.js**:

```text
                    Node.js process: cooca-wa-server
                    Express + SessionManager
                              |
          +-------------------+-------------------+
          |                   |                   |
   Map: biz_A       Map: biz_B       Map: admin_platform
   Baileys socket   Baileys socket   Baileys socket
   auth/session_A   auth/session_B   auth/session_admin
          |                   |                   |
       WA #1               WA #2               WA admin
```

Bukan:

- satu user satu process;
- satu user satu Chromium;
- satu database connection per WhatsApp;
- satu worker process per session.

Database memiliki unique `business_id` dan unique `session_id`, sehingga model bisnis yang dimaksud adalah satu row/nomor per business. Runtime tidak memaksa hal itu selain session ID deterministik; business yang sama memetakan ke session ID yang sama.

Untuk 1.000 business dengan satu nomor masing-masing, secara aktual akan ada kira-kira 1.000 object session/socket Baileys dalam satu process, sekitar 1.000 direktori auth, koneksi jaringan ke WhatsApp, dan webhook/event listener. PM2 tetap hanya menjalankan satu instance kecuali konfigurasi diubah.

## 6. Resource usage dan batas praktis

### Per session

Source menunjukkan setiap session memiliki:

- socket WebSocket Baileys ke WhatsApp;
- timer reconnect saat putus;
- listener event connection dan message;
- object auth state serta file auth di disk;
- kemungkinan request HTTP webhook;
- kemungkinan buffer media ketika pengiriman file.

Source tidak menjalankan Chromium, Puppeteer, Playwright, worker thread, atau child process per session. Beban utama adalah koneksi Baileys, crypto/protocol, event processing, file I/O auth, dan jaringan.

### Resource scaling

Beban yang pasti bertambah mendekati jumlah session:

- RAM: object socket, auth state, event listeners, dan buffer sementara;
- file descriptors: file auth/log plus socket HTTP/WebSocket;
- koneksi TCP: minimal koneksi aktif ke WhatsApp per session, ditambah request API/webhook;
- network: keep-alive/sync traffic dan message traffic setiap account;
- disk: multi-file credentials dan log;
- CPU: crypto, serialisasi event, QR, dan media processing.

README menyebut estimasi 60-120 MB RAM per session, tetapi angka itu adalah klaim dokumentasi, bukan hasil benchmark pada source ini. Karena itu angka tersebut tidak boleh dipakai sebagai kapasitas produksi tanpa pengukuran. Jika angka itu dipakai secara kasar, 100 session berarti 6-12 GB hanya untuk session, tetapi overhead aktual Baileys/Node dan pola trafik dapat berbeda.

Media URL/file dapat menambah RAM dan latency per request. `fs.readFileSync()` membaca file lokal penuh ke memory; payload media juga diproses oleh Baileys.

### Database

Node tidak membuka connection pool database. Laravel membuka koneksi sesuai request web/command dan menyimpan metadata/log. Redis hanya mungkin terlibat melalui driver Cache Laravel untuk lock receipt jika environment mengarah ke Redis; tidak ada Redis client pada `wa-server`.

## 7. Skenario restart

### 100 connected

1. Process menerima shutdown atau mati.
2. `handleShutdown()` mencoba `sock.end()` untuk semua session, lalu process keluar.
3. PM2 dapat menghidupkan ulang process jika PM2 benar-benar dipakai; Docker/hosting dapat memiliki mekanisme restart sendiri.
4. Startup membaca 100 direktori auth dan memanggil 100 `initSession()` tanpa batch/concurrency limit.
5. Semua session membuka koneksi hampir bersamaan.
6. Auth valid dapat restore tanpa QR; auth invalid/expired dapat disconnect atau meminta pairing ulang.

Bottleneck utama: burst CPU/crypto, file I/O, network handshake, rate/limit di host dan WhatsApp, serta webhook/status polling yang tertunda.

### 1.000 connected

Langkahnya sama, tetapi burst restore menjadi sekitar 1.000 socket dalam satu event loop. Tidak ada startup scheduler/batch, admission control, atau restore concurrency limit. Kemungkinan praktisnya adalah latency tinggi, OOM, file descriptor limit, koneksi yang gagal/reconnect berulang, dan proses restart berantai. Source tidak menyediakan mekanisme untuk menjamin 1.000 session dapat pulih serentak.

## 8. Kecocokan dengan Hostinger Shared Hosting

Berdasarkan source, arsitektur ini membutuhkan **process Node persistent** yang:

- mendengarkan port TCP;
- menjaga banyak koneksi WebSocket keluar;
- menulis auth multi-file secara terus-menerus;
- dapat menjalankan reconnect timer tanpa batas tetap;
- membutuhkan process hidup 24/7.

Itu berbeda dari request PHP/Laravel stateless biasa. Kesesuaian di Hostinger bergantung pada produk Node.js yang benar-benar diberikan: apakah process persistent, port publik/internal, batas RAM/CPU, batas process/file descriptors, izin write filesystem, dan apakah process dihentikan ketika idle atau melewati quota.

Risiko aktual jika shared hosting tidak menyediakan karakteristik tersebut:

- Node process dihentikan atau direstart oleh platform;
- auth directory tidak persisten atau tidak dapat ditulis;
- port tidak dapat diakses Laravel;
- koneksi WebSocket outbound dibatasi;
- burst reconnect membuat process melewati CPU/RAM quota;
- satu process mati menyebabkan seluruh session pada process itu disconnect;
- server restart tanpa persistent auth memaksa seluruh nomor scan ulang.

`wa-server/Dockerfile`, `render.yaml`, dan script PM2 menunjukkan deployment yang dirancang untuk service Node terpisah. Itu bukan bukti bahwa konfigurasi Hostinger saat ini memenuhi kebutuhan tersebut. `render.yaml` bahkan memakai free plan dan persistent disk-nya dikomentari; jika filesystem ephemeral, credential dapat hilang saat redeploy/restart.

## 9. Penilaian skenario skala

Penilaian berikut adalah konsekuensi source, bukan hasil load test:

| Jumlah WA | Penilaian aktual |
|---:|---|
| 10 | Secara desain paling ringan: satu process, 10 socket, 10 auth directory. Masih ada risiko reconnect serentak dan broadcast sinkron, tetapi tidak terlihat browser overhead. |
| 50 | Mulai sensitif terhadap RAM, file descriptors, WebSocket stability, dan burst restore. Broadcast/reminder masih dapat memblokir request lama karena 1,5 detik per pesan. |
| 100 | Satu process menjadi titik kegagalan bersama. Restart memulai 100 init paralel; resource host dan persistent storage mulai menjadi faktor utama. DB status dapat stale setelah disconnect. |
| 500 | Sangat berisiko tanpa benchmark kapasitas host. Startup/reconnect storm, file descriptor limit, CPU/IO, memory, dan network burst kemungkinan menjadi bottleneck. Tidak ada sharding atau concurrency control. |
| 1.000 | Source tidak menunjukkan mekanisme yang mengisolasi atau mengorkestrasi skala ini. Satu process memuat sekitar 1.000 socket dan restore paralel; OOM, throttling, restart cascade, dan disconnect massal adalah risiko tinggi. |

Yang membedakan 1.000 WA di sini bukan 1.000 process, melainkan 1.000 session Baileys dalam satu process Node. Semua session berbagi resource process dan satu titik restart.

## 10. Temuan perilaku penting

1. **Status database dapat stale.** Node mengubah status live di memory, tetapi tidak mengirim disconnect webhook. Laravel hanya memperbarui status pada beberapa polling sukses. Setelah gateway offline, row dapat tetap `connected`.
2. **Kirim tetap dapat gagal walaupun DB connected.** `sendReceipt()` dan broadcast memeriksa status DB; Node kemudian memeriksa status live dan mengembalikan error jika session sebenarnya offline.
3. **Broadcast memblokir request web.** Setiap recipient menambah `1,5` detik, belum termasuk HTTP/WhatsApp latency. Daftar besar dapat melewati timeout PHP/web server.
4. **Tidak ada delivery/read tracking.** Log `sent` berasal dari hasil request gateway, bukan status WhatsApp penerima.
5. **Reconnect tidak dibatasi.** Setiap close non-logout dijadwalkan ulang 3 detik, tanpa backoff, jitter, atau retry budget.
6. **Restore tidak dibatasi concurrency.** `server.js` memulai semua session directory secara langsung saat startup.
7. **Credential bergantung pada filesystem gateway.** Metadata Laravel saja tidak cukup untuk restore.
8. **Token default tidak mengamankan gateway.** `server.js` sengaja mengizinkan semua API non-health jika `WA_WORKER_TOKEN` kosong atau masih `secret-worker-token`. `.env.example` menggunakan nilai default itu.
9. **CORS wildcard aktif.** Express mengatur `Access-Control-Allow-Origin: *`; proteksi praktis bergantung pada token, yang pada default justru dilewati.
10. **Dokumentasi endpoint tidak sepenuhnya sinkron.** README menyebut `POST /api/sessions/:sessionId/disconnect`, sedangkan runtime yang ada hanya `DELETE /api/sessions/:sessionId`.
11. **`whatsapp-web.js` tidak aktif.** Dependency Puppeteer di folder tersebut tidak berarti setiap session menyalakan browser; `wa-server` memakai Baileys direct socket.

## Kesimpulan faktual

Alur aktifnya adalah Laravel HTTP -> satu Node Express gateway -> banyak socket Baileys -> WhatsApp. QR dan status live berada di memory Node, credential berada di filesystem gateway, metadata/log berada di database Laravel, dan pengiriman pesan dilakukan synchronous tanpa queue gateway. Model multi-tenant yang benar-benar diimplementasikan adalah banyak session dalam satu process, dengan restore/reconnect paralel dan tanpa mekanisme pembatasan skala.

Dokumen ini tidak menetapkan arsitektur pengganti. Kapasitas nyata tetap memerlukan pengukuran pada ukuran host, limit file descriptor, persistent storage, dan trafik per session yang benar-benar digunakan.
