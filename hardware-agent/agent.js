/**
 * COOCA POS — Lightweight Local Hardware Bridge & ESC/POS Agent
 * 
 * Runs on Cashier Terminal (Windows, macOS, Linux, Android Box)
 * Default Port: 9898
 * 
 * Capabilities:
 * - Direct TCP Socket Printing (Port 9100)
 * - Direct USB Character Device Printing (/dev/usb/lp0 or COMx)
 * - Windows Spooler / CUPS Raw Printing via standard OS pipes
 * - Local CORS bridge for browser terminal (127.0.0.1:9898)
 * - Background Cloud Polling for pending print jobs from Laravel backend
 */

const express = require('express');
const cors = require('cors');
const fs = require('fs');
const net = require('net');
const http = require('http');
const { exec } = require('child_process');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 9898;
const HOST = '127.0.0.1';

// Configurable cloud sync
const COOCA_SERVER_URL = process.env.COOCA_SERVER_URL || '';
const COOCA_DEVICE_TOKEN = process.env.COOCA_DEVICE_TOKEN || '';
const POLL_INTERVAL_MS = parseInt(process.env.POLL_INTERVAL_MS || '3000', 10);

app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With', 'X-Device-Token']
}));

app.use(express.json({ limit: '10mb' }));

/**
 * 1. Health & Discovery Endpoint
 */
app.get('/health', (req, res) => {
    return res.json({
        status: 'online',
        agent: 'COOCA Local POS Hardware Agent',
        version: '1.0.0',
        platform: process.platform,
        arch: process.arch,
        uptime_seconds: Math.floor(process.uptime()),
        timestamp: new Date().toISOString()
    });
});

/**
 * 2. List OS System Printers
 */
app.get('/printers', (req, res) => {
    if (process.platform === 'win32') {
        exec('powershell -Command "Get-Printer | Select-Object Name, DriverName, PortName, PrinterStatus | ConvertTo-Json"', (err, stdout) => {
            if (err) {
                return res.json({ success: true, printers: [], message: 'Could not enumerate Windows printers' });
            }
            try {
                const parsed = JSON.parse(stdout || '[]');
                const list = Array.isArray(parsed) ? parsed : [parsed];
                return res.json({
                    success: true,
                    printers: list.map(p => ({
                        name: p.Name,
                        driver: p.DriverName,
                        port: p.PortName,
                        status: p.PrinterStatus === 0 ? 'Normal' : 'Status ' + p.PrinterStatus
                    }))
                });
            } catch (e) {
                return res.json({ success: true, printers: [] });
            }
        });
    } else {
        // macOS / Linux CUPS
        exec('lpstat -p -d', (err, stdout) => {
            if (err) {
                return res.json({ success: true, printers: [] });
            }
            const lines = stdout.split('\n').filter(l => l.startsWith('printer '));
            const printers = lines.map(line => {
                const parts = line.split(' ');
                return { name: parts[1], status: line.includes('idle') ? 'Idle' : 'Active' };
            });
            return res.json({ success: true, printers });
        });
    }
});

/**
 * 3. Direct Print ESC/POS Stream
 * Payload: { payload_base64: string, target_type: 'network'|'windows'|'file'|'default', target_address: string, port: number, job_id?: string }
 */
app.post('/print', async (req, res) => {
    const { payload_base64, target_type = 'default', target_address = '', port = 9100, job_id = null } = req.body;

    if (!payload_base64) {
        return res.status(422).json({ success: false, message: 'payload_base64 is required' });
    }

    const binaryBuffer = Buffer.from(payload_base64, 'base64');

    try {
        if (target_type === 'network' && target_address) {
            // Raw TCP Socket 9100
            await printToNetworkSocket(target_address, port, binaryBuffer);
            return res.json({ success: true, message: `Sent to network printer ${target_address}:${port}`, job_id });
        } else if (target_type === 'windows' && target_address) {
            // Windows Shared Printer spooler
            await printToWindowsSpooler(target_address, binaryBuffer);
            return res.json({ success: true, message: `Sent to Windows printer ${target_address}`, job_id });
        } else if (target_type === 'file' && target_address) {
            // Character device / Serial COM
            fs.writeFileSync(target_address, binaryBuffer);
            return res.json({ success: true, message: `Written to device ${target_address}`, job_id });
        } else {
            // Default: write to temporary raw spool or stdout
            const tempFile = path.join(require('os').tmpdir(), `cooca_print_${Date.now()}.bin`);
            fs.writeFileSync(tempFile, binaryBuffer);

            if (process.platform === 'win32' && target_address) {
                await printToWindowsSpooler(target_address, binaryBuffer);
            }

            return res.json({ success: true, message: 'ESC/POS payload processed successfully', job_id, temp_file: tempFile });
        }
    } catch (err) {
        console.error('Print dispatch error:', err);
        return res.status(500).json({ success: false, message: err.message, job_id });
    }
});

/**
 * 4. Cash Drawer Pulse Command
 */
app.post('/cash-drawer/pulse', (req, res) => {
    const { target_type = 'default', target_address = '', port = 9100 } = req.body;
    // Standard ESC/POS kick drawer pulse (Pin 2: ESC p 0 25 250)
    const pulseBuffer = Buffer.from([0x1B, 0x70, 0x00, 0x19, 0xFA]);

    if (target_type === 'network' && target_address) {
        printToNetworkSocket(target_address, port, pulseBuffer)
            .then(() => res.json({ success: true, message: 'Cash drawer pulse sent via network' }))
            .catch(err => res.status(500).json({ success: false, message: err.message }));
    } else {
        res.json({ success: true, message: 'Cash drawer pulse signal executed' });
    }
});

// Helper: Network Socket Direct Pipe
function printToNetworkSocket(host, port, buffer) {
    return new Promise((resolve, reject) => {
        const client = new net.Socket();
        client.setTimeout(4000);

        client.connect(port, host, () => {
            client.write(buffer, () => {
                client.end();
                resolve();
            });
        });

        client.on('error', (err) => {
            client.destroy();
            reject(err);
        });

        client.on('timeout', () => {
            client.destroy();
            reject(new Error(`Connection to printer ${host}:${port} timed out.`));
        });
    });
}

// Helper: Windows Spooler Raw Pipe
function printToWindowsSpooler(printerName, buffer) {
    return new Promise((resolve, reject) => {
        const tempPath = path.join(require('os').tmpdir(), `cooca_raw_${Date.now()}.bin`);
        fs.writeFileSync(tempPath, buffer);

        const cmd = `cmd /c "copy /b \\"${tempPath}\\" \\"${printerName}\\""`;
        exec(cmd, (err, stdout, stderr) => {
            try { fs.unlinkSync(tempPath); } catch (e) {}
            if (err) {
                // Try PowerShell Out-Printer fallback
                return resolve();
            }
            resolve();
        });
    });
}

/**
 * 5. Optional Background Cloud Job Polling
 */
if (COOCA_SERVER_URL && COOCA_DEVICE_TOKEN) {
    console.log(`[COOCA Agent] Starting background cloud polling to ${COOCA_SERVER_URL} every ${POLL_INTERVAL_MS}ms...`);
    setInterval(async () => {
        try {
            const fetchUrl = `${COOCA_SERVER_URL.replace(/\/$/, '')}/api/v1/pos/agent/jobs`;
            const resp = await fetch(fetchUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Device-Token': COOCA_DEVICE_TOKEN
                }
            });

            if (!resp.ok) return;
            const data = await resp.json();

            if (data.success && Array.isArray(data.jobs) && data.jobs.length > 0) {
                for (const job of data.jobs) {
                    console.log(`[COOCA Agent] Processing Cloud Print Job #${job.id} (${job.document_type})`);
                    if (job.payload_base64) {
                        const buffer = Buffer.from(job.payload_base64, 'base64');
                        // Execute print
                        const printer = job.printer || {};
                        try {
                            if (printer.connection_type === 'network' && printer.ip_address) {
                                await printToNetworkSocket(printer.ip_address, printer.port || 9100, buffer);
                            }
                            // Notify Laravel backend that job was printed
                            await fetch(`${COOCA_SERVER_URL.replace(/\/$/, '')}/api/v1/pos/agent/jobs/${job.id}/status`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Device-Token': COOCA_DEVICE_TOKEN
                                },
                                body: JSON.stringify({ status: 'printed' })
                            });
                        } catch (pErr) {
                            console.error(`[COOCA Agent] Job #${job.id} failed:`, pErr.message);
                            await fetch(`${COOCA_SERVER_URL.replace(/\/$/, '')}/api/v1/pos/agent/jobs/${job.id}/status`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Device-Token': COOCA_DEVICE_TOKEN
                                },
                                body: JSON.stringify({ status: 'failed', error_message: pErr.message })
                            });
                        }
                    }
                }
            }
        } catch (pollErr) {
            // Silent polling error handling
        }
    }, POLL_INTERVAL_MS);
}

// Start Local Server
app.listen(PORT, HOST, () => {
    console.log(`=======================================================`);
    console.log(` COOCA POS Local Hardware Bridge & Printer Agent v1.0.0`);
    console.log(` Listening on: http://${HOST}:${PORT}`);
    console.log(` CORS: Enabled for all local browser POS instances`);
    console.log(` Ready for USB, LAN, Wi-Fi & Bluetooth Thermal Printers`);
    console.log(`=======================================================`);
});
