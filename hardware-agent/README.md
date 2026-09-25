# COOCA POS — Local Hardware Bridge & Printer Agent

Lightweight background bridge agent enabling web-based POS terminals in modern browsers to interact with local hardware:
- **Thermal Receipt Printers (ESC/POS)**: USB Character Devices, Windows Spooler Drivers, Bluetooth SPP, Serial COM, Network TCP 9100.
- **Cash Drawers (RJ-11/RJ-12)**: Standard ESC/POS solenoid kick pulses via thermal printer.
- **Kitchen / Bar Display & Remote Order Routing**: Automatic multi-station printing.

---

## 1. Quick Start

### Prerequisites
- Node.js (v18 or higher)
- Thermal Printer connected via USB, Bluetooth, or Local Network

### Installation
```bash
cd hardware-agent
npm install
npm start
```

The agent runs locally on:
```text
http://127.0.0.1:9898
```

---

## 2. API Endpoints

### Health Check
```http
GET http://127.0.0.1:9898/health
```
Response:
```json
{
  "status": "online",
  "agent": "COOCA Local POS Hardware Agent",
  "version": "1.0.0",
  "platform": "win32",
  "uptime_seconds": 120
}
```

### Direct Print ESC/POS Stream
```http
POST http://127.0.0.1:9898/print
Content-Type: application/json

{
  "payload_base64": "G0BiAAE...",
  "target_type": "network",
  "target_address": "192.168.1.200",
  "port": 9100
}
```

### Cash Drawer Kick Pulse
```http
POST http://127.0.0.1:9898/cash-drawer/pulse
Content-Type: application/json

{
  "target_type": "network",
  "target_address": "192.168.1.200"
}
```

---

## 3. Production Deployment

### Option A: Windows Background Service (NSSM)
1. Download NSSM (Non-Sucking Service Manager).
2. Install service:
   ```cmd
   nssm install CoocaPosAgent "C:\Program Files\nodejs\node.exe" "C:\cooca\hardware-agent\agent.js"
   nssm start CoocaPosAgent
   ```

### Option B: PM2 Process Manager
```bash
npm install -g pm2
pm2 start agent.js --name cooca-agent
pm2 startup
pm2 save
```

---

## 4. Cloud Polling Configuration (Optional)
To pull print jobs from a cloud COOCA server without exposing local printer ports:
Create `.env` file:
```env
PORT=9898
COOCA_SERVER_URL=https://app.cooca.id
COOCA_DEVICE_TOKEN=dev_tok_abc123xyz
POLL_INTERVAL_MS=3000
```
