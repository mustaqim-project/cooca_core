import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/subscription_provider.dart';
import '../settings/team_management_screen.dart';

class MoreScreen extends StatefulWidget {
  const MoreScreen({super.key});

  @override
  State<MoreScreen> createState() => _MoreScreenState();
}

class _MoreScreenState extends State<MoreScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<SubscriptionProvider>().fetchSubscriptionInfo();
    });
  }

  void _showUpgradeModal() {
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                gradient: AppColors.purpleGradient,
                borderRadius: BorderRadius.circular(16),
              ),
              child: const Icon(Icons.stars_rounded, color: Colors.white, size: 32),
            ),
            const SizedBox(height: 16),
            Text(
              'Upgrade ke Cooca Core',
              style: GoogleFonts.plusJakartaSans(
                fontSize: 20,
                fontWeight: FontWeight.w800,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Akses fitur bisnis tanpa batas: Multi-bisnis, AI Token 10M/bulan, WhatsApp, dan Transaksi Tanpa Batas.',
              textAlign: TextAlign.center,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 13,
                color: AppColors.textMuted,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(ctx);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Silakan hubungi administrator atau kunjungi web cooca.id untuk proses pembayaran.')),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.purple,
                minimumSize: const Size(double.infinity, 50),
              ),
              child: const Text('Langganan Rp 129.000 / Bulan'),
            ),
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final sub = context.watch<SubscriptionProvider>();
    final user = auth.user;
    final biz = auth.activeBusiness;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.glassNav,
        title: Text(
          'Menu & Pengaturan',
          style: GoogleFonts.plusJakartaSans(
            fontSize: 18,
            fontWeight: FontWeight.w800,
            color: AppColors.textPrimary,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // Business & User Profile Card
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: AppColors.glassCard,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.glassBorder),
              ),
              child: Row(
                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      gradient: AppColors.logoGradient,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: const Icon(Icons.storefront_rounded, color: Colors.white, size: 28),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          biz?.name ?? 'Bisnis Aktif',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        Text(
                          user?.email ?? 'user@cooca.id',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 12,
                            color: AppColors.textMuted,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: sub.isCore ? AppColors.primaryGlow : AppColors.amberGlow,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            sub.isCore ? 'PLAN: CORE' : 'PLAN: FREE',
                            style: GoogleFonts.plusJakartaSans(
                              fontSize: 10,
                              fontWeight: FontWeight.w700,
                              color: sub.isCore ? AppColors.primaryLight : AppColors.amber,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.swap_horiz_rounded, color: AppColors.primaryLight),
                    onPressed: () {
                      Navigator.pushNamed(context, '/business-select');
                    },
                    tooltip: 'Ganti Bisnis',
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Upgrade Banner if Free
            if (!sub.isCore)
              InkWell(
                onTap: _showUpgradeModal,
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF6B21A8), Color(0xFF3B0764)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.purple.withValues(alpha: 0.5)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.bolt_rounded, color: AppColors.amber, size: 28),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Upgrade ke Cooca Core',
                              style: GoogleFonts.plusJakartaSans(
                                fontWeight: FontWeight.w800,
                                color: Colors.white,
                                fontSize: 14,
                              ),
                            ),
                            Text(
                              'Buka kuota tak terbatas & fitur AI',
                              style: GoogleFonts.plusJakartaSans(
                                color: AppColors.textSecondary,
                                fontSize: 11,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white, size: 14),
                    ],
                  ),
                ),
              ),
            const SizedBox(height: 20),

            // Module Navigation Group
            _buildSectionHeader('Modul Bisnis & Operasional'),

            // Team Management (Owner / Admin)
            if (user?.canManageTeam == true)
              _buildMenuItem(
                icon: Icons.people_outline_rounded,
                color: AppColors.teal,
                title: 'Manajemen Tim & Karyawan',
                subtitle: 'Kelola kasir, staf gudang, peran akses & kuota',
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => const TeamManagementScreen(),
                    ),
                  );
                },
              ),

            // Costing & HPP Calculator (Costing / Owner permission)
            if (user?.canViewMargin == true)
              _buildMenuItem(
                icon: Icons.calculate_rounded,
                color: AppColors.primary,
                title: 'Kalkulator HPP 3-Pilar',
                subtitle: 'Hitung biaya bahan baku, tenaga kerja & overhead',
                onTap: () => Navigator.pushNamed(context, '/calculator/mobile'),
              ),

            // CRM & Loyalty
            _buildMenuItem(
              icon: Icons.card_giftcard_rounded,
              color: AppColors.indigo,
              title: 'Pelanggan & CRM Loyalty',
              subtitle: 'Kelola data pembeli, poin reward & voucher',
              onTap: () => Navigator.pushNamed(context, '/crm/customer-picker'),
            ),

            // AI Assistant
            if (user?.canAccessAi == true)
              _buildMenuItem(
                icon: Icons.psychology_rounded,
                color: AppColors.purple,
                title: 'AI Business Assistant',
                subtitle: 'Prediksi omzet 14 hari, fraud check & tanya AI',
                onTap: () => Navigator.pushNamed(context, '/ai/chat'),
              ),

            const SizedBox(height: 20),

            // Hardware & App Settings Group
            _buildSectionHeader('Sistem & Pengaturan'),
            _buildMenuItem(
              icon: Icons.print_rounded,
              color: AppColors.cyan,
              title: 'Printer Struk Kasir',
              subtitle: 'Konfigurasi printer thermal Bluetooth / USB (58mm/80mm)',
              onTap: () => Navigator.pushNamed(context, '/settings/printer'),
            ),

            if (user?.canAccessBilling == true)
              _buildMenuItem(
                icon: Icons.stars_rounded,
                color: AppColors.purple,
                title: 'Paket & Langganan',
                subtitle: 'Periksa penggunaan kuota & upgrade Cooca Core',
                onTap: () => Navigator.pushNamed(context, '/billing/upgrade'),
              ),

            _buildMenuItem(
              icon: Icons.dns_rounded,
              color: AppColors.textSecondary,
              title: 'Pengaturan Server & Koneksi',
              subtitle: 'Atur endpoint server backend Laravel API',
              onTap: () => Navigator.pushNamed(context, '/settings'),
            ),
            const SizedBox(height: 20),

            // Logout Button
            ListTile(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              tileColor: AppColors.rose.withValues(alpha: 0.1),
              leading: const Icon(Icons.logout_rounded, color: AppColors.rose),
              title: Text(
                'Keluar dari Akun',
                style: GoogleFonts.plusJakartaSans(
                  fontWeight: FontWeight.w700,
                  color: AppColors.rose,
                ),
              ),
              onTap: () async {
                await auth.logout();
                if (!mounted) return;
                Navigator.pushReplacementNamed(context, '/login');
              },
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Align(
      alignment: Alignment.centerLeft,
      child: Padding(
        padding: const EdgeInsets.only(bottom: 8, left: 4),
        child: Text(
          title,
          style: GoogleFonts.plusJakartaSans(
            fontSize: 12,
            fontWeight: FontWeight.w700,
            color: AppColors.textDim,
            letterSpacing: 0.5,
          ),
        ),
      ),
    );
  }

  Widget _buildMenuItem({
    required IconData icon,
    required Color color,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: AppColors.glassCard,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.glassBorder),
      ),
      child: ListTile(
        onTap: onTap,
        leading: Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.15),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, color: color, size: 22),
        ),
        title: Text(
          title,
          style: GoogleFonts.plusJakartaSans(
            fontWeight: FontWeight.w700,
            fontSize: 14,
            color: AppColors.textPrimary,
          ),
        ),
        subtitle: Text(
          subtitle,
          style: GoogleFonts.plusJakartaSans(
            fontSize: 11,
            color: AppColors.textMuted,
          ),
        ),
        trailing: const Icon(Icons.chevron_right_rounded, color: AppColors.textDim),
      ),
    );
  }
}

