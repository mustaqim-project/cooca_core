import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/settings_provider.dart';

class AppSettingsScreen extends StatefulWidget {
  const AppSettingsScreen({super.key});

  @override
  State<AppSettingsScreen> createState() => _AppSettingsScreenState();
}

class _AppSettingsScreenState extends State<AppSettingsScreen> {
  late TextEditingController _urlCtrl;
  late TextEditingController _printerIpCtrl;
  bool _isSaved = false;

  @override
  void initState() {
    super.initState();
    final settings = context.read<SettingsProvider>();
    _urlCtrl = TextEditingController(text: settings.baseUrl);
    _printerIpCtrl = TextEditingController(text: settings.printerIp ?? '');
  }

  @override
  void dispose() {
    _urlCtrl.dispose();
    _printerIpCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final settings = context.read<SettingsProvider>();
    await settings.setBaseUrl(_urlCtrl.text.trim());
    if (_printerIpCtrl.text.trim().isNotEmpty) {
      await settings.setPrinterIp(_printerIpCtrl.text.trim());
    }
    setState(() => _isSaved = true);
    await Future.delayed(const Duration(seconds: 2));
    if (mounted) setState(() => _isSaved = false);
  }

  @override
  Widget build(BuildContext context) {
    final settings = context.watch<SettingsProvider>();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textPrimary, size: 20),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text('Pengaturan Aplikasi', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w700)),
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          // API Server Section
          _section('🌐 Server & Koneksi', [
            _settingTile(
              icon: Icons.dns_rounded,
              title: 'API Base URL',
              subtitle: 'Alamat server Laravel backend Cooca UMKM',
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Padding(
                    padding: const EdgeInsets.only(top: 12),
                    child: TextField(
                      controller: _urlCtrl,
                      style: const TextStyle(color: AppColors.textPrimary, fontSize: 13),
                      decoration: InputDecoration(
                        hintText: 'https://app.cooca.id/api/v1',
                        hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                        filled: true,
                        fillColor: AppColors.border,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      ActionChip(
                        label: const Text('☁️ Production (app.cooca.id)', style: TextStyle(fontSize: 11, color: AppColors.primaryLight)),
                        backgroundColor: AppColors.primary.withValues(alpha: 0.15),
                        side: const BorderSide(color: AppColors.primary, width: 0.5),
                        onPressed: () {
                          setState(() {
                            _urlCtrl.text = 'https://app.cooca.id/api/v1';
                          });
                        },
                      ),
                      ActionChip(
                        label: const Text('💻 Local Emulator (10.0.2.2)', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                        backgroundColor: AppColors.surfaceElevated,
                        side: BorderSide.none,
                        onPressed: () {
                          setState(() {
                            _urlCtrl.text = 'http://10.0.2.2:8000/api/v1';
                          });
                        },
                      ),
                      ActionChip(
                        label: const Text('🏠 Localhost', style: TextStyle(fontSize: 11, color: AppColors.textSecondary)),
                        backgroundColor: AppColors.surfaceElevated,
                        side: BorderSide.none,
                        onPressed: () {
                          setState(() {
                            _urlCtrl.text = 'http://localhost:8000/api/v1';
                          });
                        },
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ]),
          const SizedBox(height: 20),

          // Printer Section
          _section('🖨️ Printer Thermal', [
            _settingTile(
              icon: Icons.wifi_rounded,
              title: 'IP Address Printer',
              subtitle: 'Alamat IP printer Bluetooth/WiFi (opsional)',
              child: Padding(
                padding: const EdgeInsets.only(top: 12),
                child: TextField(
                  controller: _printerIpCtrl,
                  keyboardType: TextInputType.number,
                  style: const TextStyle(color: AppColors.textPrimary, fontSize: 13),
                  decoration: InputDecoration(
                    hintText: '192.168.1.100',
                    hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                    filled: true,
                    fillColor: AppColors.border,
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                ),
              ),
            ),
            _settingTile(
              icon: Icons.straighten_rounded,
              title: 'Ukuran Kertas',
              subtitle: 'Pilih lebar kertas printer thermal Anda',
              child: Padding(
                padding: const EdgeInsets.only(top: 8),
                child: Row(
                  children: [58, 80].map((size) {
                    final isSelected = settings.printerPaperSize == size;
                    return Expanded(
                      child: Padding(
                        padding: EdgeInsets.only(right: size == 58 ? 8 : 0),
                        child: GestureDetector(
                          onTap: () => settings.setPrinterPaperSize(size),
                          child: Container(
                            padding: const EdgeInsets.symmetric(vertical: 10),
                            decoration: BoxDecoration(
                              color: isSelected ? AppColors.primary : AppColors.border,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            alignment: Alignment.center,
                            child: Text(
                              '${size}mm',
                              style: TextStyle(
                                color: isSelected ? Colors.white : AppColors.textSecondary,
                                fontWeight: isSelected ? FontWeight.w700 : FontWeight.normal,
                              ),
                            ),
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ),
            ),
          ]),
          const SizedBox(height: 20),

          // App Info
          _section('ℹ️ Informasi Aplikasi', [
            _settingTile(
              icon: Icons.info_outline_rounded,
              title: 'Versi Aplikasi',
              subtitle: 'Cooca POS Mobile v1.0.0',
            ),
            _settingTile(
              icon: Icons.business_center_outlined,
              title: 'Powered By',
              subtitle: 'Cooca UMKM — Platform Bisnis UMKM Indonesia',
            ),
          ]),
          const SizedBox(height: 32),

          // Save Button
          SizedBox(
            width: double.infinity,
            height: 56,
            child: AnimatedSwitcher(
              duration: const Duration(milliseconds: 300),
              child: _isSaved
                  ? ElevatedButton.icon(
                      key: const ValueKey('saved'),
                      onPressed: null,
                      icon: const Icon(Icons.check_circle_rounded, color: Colors.white),
                      label: const Text('Pengaturan Disimpan!', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.success,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      ),
                    )
                  : ElevatedButton.icon(
                      key: const ValueKey('save'),
                      onPressed: _save,
                      icon: const Icon(Icons.save_rounded, color: Colors.white),
                      label: const Text('Simpan Pengaturan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      ),
                    ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _section(String title, List<Widget> children) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: const TextStyle(color: AppColors.textSecondary, fontWeight: FontWeight.w600, fontSize: 13)),
        const SizedBox(height: 10),
        Container(
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppColors.border),
          ),
          child: Column(children: children),
        ),
      ],
    );
  }

  Widget _settingTile({required IconData icon, required String title, required String subtitle, Widget? child}) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: AppColors.accent, size: 20),
              const SizedBox(width: 10),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w600, fontSize: 14)),
                  Text(subtitle, style: const TextStyle(color: AppColors.textMuted, fontSize: 11)),
                ],
              ),
            ],
          ),
          if (child != null) child,
        ],
      ),
    );
  }
}

