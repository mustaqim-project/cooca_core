import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../core/constants/app_colors.dart';
import '../../core/services/printer_service.dart';

class PrinterSettingsScreen extends StatefulWidget {
  const PrinterSettingsScreen({super.key});

  @override
  State<PrinterSettingsScreen> createState() => _PrinterSettingsScreenState();
}

class _PrinterSettingsScreenState extends State<PrinterSettingsScreen> {
  final _printerService = PrinterService();
  bool _isScanning = false;
  final List<String> _dummyDevices = ['Thermal Printer 58mm (BT)', 'POS-80 Bluetooth', 'EP5802AI Mobile'];

  void _testPrint() async {
    final sample = _printerService.generateReceiptText(
      businessName: 'Toko Contoh Cooca',
      invoiceNo: 'TEST-001',
      items: [
        {'name': 'Kopi Susu Gula Aren', 'qty': 2, 'price': 18000.0, 'subtotal': 36000.0},
        {'name': 'Croissant Butter', 'qty': 1, 'price': 22000.0, 'subtotal': 22000.0},
      ],
      subtotal: 58000.0,
      discount: 0.0,
      tax: 0.0,
      total: 58000.0,
      paymentMethod: 'QRIS',
    );

    await _printerService.printReceipt(sample);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Uji cetak struk berhasil dikirim ke printer!')),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.glassNav,
        title: Text(
          'Printer Thermal Kasir',
          style: GoogleFonts.plusJakartaSans(
            fontSize: 18,
            fontWeight: FontWeight.w800,
            color: AppColors.textPrimary,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Connection Status Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.glassCard,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.glassBorder),
            ),
            child: Row(
              children: [
                Icon(
                  _printerService.isConnected ? Icons.print_rounded : Icons.print_disabled_rounded,
                  color: _printerService.isConnected ? AppColors.primary : AppColors.rose,
                  size: 32,
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        _printerService.isConnected
                            ? 'Terhubung: ${_printerService.connectedDeviceName}'
                            : 'Belum Terhubung ke Printer',
                        style: GoogleFonts.plusJakartaSans(
                          fontWeight: FontWeight.w700,
                          color: AppColors.textPrimary,
                          fontSize: 14,
                        ),
                      ),
                      Text(
                        'Mendukung Bluetooth / USB 58mm & 80mm',
                        style: GoogleFonts.plusJakartaSans(
                          color: AppColors.textMuted,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Action Buttons
          Row(
            children: [
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () {
                    setState(() => _isScanning = true);
                    Future.delayed(const Duration(seconds: 1), () {
                      if (mounted) setState(() => _isScanning = false);
                    });
                  },
                  icon: _isScanning
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Icon(Icons.bluetooth_searching_rounded),
                  label: const Text('Pindai Perangkat'),
                  style: ElevatedButton.styleFrom(backgroundColor: AppColors.teal),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: _testPrint,
                  icon: const Icon(Icons.receipt_long_rounded),
                  label: const Text('Uji Cetak Struk'),
                ),
              ),
            ],
          ),
          const SizedBox(height: 24),

          Text(
            'Perangkat Bluetooth Ditemukan',
            style: GoogleFonts.plusJakartaSans(
              fontSize: 14,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 12),

          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _dummyDevices.length,
            separatorBuilder: (_, __) => const SizedBox(height: 8),
            itemBuilder: (context, index) {
              final dev = _dummyDevices[index];
              final isConnected = _printerService.connectedDeviceName == dev;

              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: AppColors.glassCard,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: isConnected ? AppColors.primary : AppColors.glassBorder),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.bluetooth_rounded, color: AppColors.teal),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        dev,
                        style: GoogleFonts.plusJakartaSans(
                          fontWeight: FontWeight.w600,
                          color: AppColors.textPrimary,
                        ),
                      ),
                    ),
                    ElevatedButton(
                      onPressed: () async {
                        if (isConnected) {
                          await _printerService.disconnect();
                        } else {
                          await _printerService.connectToPrinter(dev);
                        }
                        setState(() {});
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: isConnected ? AppColors.rose : AppColors.primary,
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                        minimumSize: const Size(80, 36),
                      ),
                      child: Text(
                        isConnected ? 'Putus' : 'Sambungkan',
                        style: GoogleFonts.plusJakartaSans(fontSize: 11, fontWeight: FontWeight.w700),
                      ),
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}

