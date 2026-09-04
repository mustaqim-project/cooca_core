import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/subscription_provider.dart';

class BillingUpgradeScreen extends StatefulWidget {
  const BillingUpgradeScreen({super.key});

  @override
  State<BillingUpgradeScreen> createState() => _BillingUpgradeScreenState();
}

class _BillingUpgradeScreenState extends State<BillingUpgradeScreen> {
  String _cycle = 'monthly'; // 'monthly' or 'annual'

  @override
  Widget build(BuildContext context) {
    final sub = context.watch<SubscriptionProvider>();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.glassNav,
        title: Text(
          'Paket & Langganan',
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
            // Billing Cycle Switcher
            Container(
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: InkWell(
                      onTap: () => setState(() => _cycle = 'monthly'),
                      borderRadius: BorderRadius.circular(10),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        decoration: BoxDecoration(
                          color: _cycle == 'monthly' ? AppColors.primary : Colors.transparent,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Center(
                          child: Text(
                            'Bulanan (Monthly)',
                            style: GoogleFonts.plusJakartaSans(
                              fontWeight: FontWeight.w700,
                              fontSize: 12,
                              color: _cycle == 'monthly' ? Colors.white : AppColors.textMuted,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                  Expanded(
                    child: InkWell(
                      onTap: () => setState(() => _cycle = 'annual'),
                      borderRadius: BorderRadius.circular(10),
                      child: Container(
                        padding: const EdgeInsets.symmetric(vertical: 10),
                        decoration: BoxDecoration(
                          color: _cycle == 'annual' ? AppColors.primary : Colors.transparent,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              'Tahunan (Annual)',
                              style: GoogleFonts.plusJakartaSans(
                                fontWeight: FontWeight.w700,
                                fontSize: 12,
                                color: _cycle == 'annual' ? Colors.white : AppColors.textMuted,
                              ),
                            ),
                            const SizedBox(width: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                              decoration: BoxDecoration(
                                color: AppColors.amber,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: const Text('HEMAT', style: TextStyle(color: Colors.black, fontSize: 8, fontWeight: FontWeight.w900)),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Pricing Cards
            // 1. FREE Card
            _buildPlanCard(
              title: 'Free Plan',
              price: 'Rp 0',
              subtitle: 'Cocok untuk awal mulai usaha',
              features: [
                '1 Bisnis / Outlet Aktif',
                'Maksimal 50 Produk Katalog',
                'Maksimal 20 Resep HPP',
                '10 Invoice / Bulan',
                'Storage 5 GB',
              ],
              isCurrent: !sub.isCore,
              color: AppColors.textSecondary,
              onTap: null,
            ),
            const SizedBox(height: 16),

            // 2. CORE Card (Hero)
            _buildPlanCard(
              title: 'Cooca UMKM Plan',
              price: _cycle == 'monthly' ? 'Rp 129.000 / bln' : 'Rp 1.290.000 / thn',
              subtitle: _cycle == 'annual' ? 'Setara 10 bulan (Gratis 2 bulan)' : 'Akses penuh tanpa batas',
              features: [
                'Multi-Bisnis Tanpa Batas',
                'Produk & Resep HPP Tanpa Batas',
                'Invoice & Transaksi Kasir Tanpa Batas',
                '10.000.000 Token AI / Bulan',
                'Integrasi WhatsApp 1 Nomor Bisnis',
                'Laporan Keuangan & Export Excel/PDF',
              ],
              isCurrent: sub.isCore,
              isHighlight: true,
              color: AppColors.purple,
              onTap: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Mengalihkan ke gateway pembayaran...')),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPlanCard({
    required String title,
    required String price,
    required String subtitle,
    required List<String> features,
    required Color color,
    bool isCurrent = false,
    bool isHighlight = false,
    VoidCallback? onTap,
  }) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.glassCard,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isHighlight ? AppColors.purple : AppColors.glassBorder,
          width: isHighlight ? 2 : 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                title,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 18,
                  fontWeight: FontWeight.w800,
                  color: AppColors.textPrimary,
                ),
              ),
              if (isCurrent)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: AppColors.primaryGlow,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    'PAKET AKTIF',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                      color: AppColors.primaryLight,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            price,
            style: GoogleFonts.plusJakartaSans(
              fontSize: 22,
              fontWeight: FontWeight.w900,
              color: isHighlight ? AppColors.purpleLight : AppColors.textPrimary,
            ),
          ),
          Text(
            subtitle,
            style: GoogleFonts.plusJakartaSans(
              fontSize: 12,
              color: AppColors.textMuted,
            ),
          ),
          const Divider(height: 24, color: AppColors.border),
          ...features.map((f) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  children: [
                    const Icon(Icons.check_circle_rounded, color: AppColors.primary, size: 16),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        f,
                        style: GoogleFonts.plusJakartaSans(
                          fontSize: 13,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ),
                  ],
                ),
              )),
          if (!isCurrent && onTap != null) ...[
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: onTap,
              style: ElevatedButton.styleFrom(backgroundColor: AppColors.purple),
              child: const Text('Pilih & Berlangganan'),
            ),
          ],
        ],
      ),
    );
  }
}

