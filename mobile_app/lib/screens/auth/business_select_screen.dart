import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../models/business_model.dart';
import '../../providers/auth_provider.dart';

class BusinessSelectScreen extends StatefulWidget {
  const BusinessSelectScreen({super.key});

  @override
  State<BusinessSelectScreen> createState() => _BusinessSelectScreenState();
}

class _BusinessSelectScreenState extends State<BusinessSelectScreen> {
  final _newBizController = TextEditingController();

  @override
  void dispose() {
    _newBizController.dispose();
    super.dispose();
  }

  void _selectBusiness(BusinessModel biz) async {
    final auth = context.read<AuthProvider>();
    await auth.setActiveBusiness(biz);
    if (!mounted) return;
    Navigator.of(context).pushReplacementNamed('/home');
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final businesses = auth.businesses;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(
          'Pilih Bisnis / Outlet',
          style: GoogleFonts.plusJakartaSans(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout_rounded, color: AppColors.rose),
            onPressed: () async {
              await auth.logout();
              if (!mounted) return;
              Navigator.of(context).pushReplacementNamed('/login');
            },
          ),
        ],
      ),
      body: SafeArea(
        child: businesses.isEmpty
            ? Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.storefront_rounded, size: 64, color: AppColors.textDim),
                    const SizedBox(height: 16),
                    Text(
                      'Belum ada data bisnis.',
                      style: GoogleFonts.plusJakartaSans(color: AppColors.textMuted, fontSize: 16),
                    ),
                  ],
                ),
              )
            : ListView.separated(
                padding: const EdgeInsets.all(20),
                itemCount: businesses.length,
                separatorBuilder: (_, __) => const SizedBox(height: 12),
                itemBuilder: (context, index) {
                  final biz = businesses[index];
                  final isCurrent = auth.activeBusiness?.id == biz.id;

                  return InkWell(
                    onTap: () => _selectBusiness(biz),
                    borderRadius: BorderRadius.circular(16),
                    child: Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: isCurrent ? AppColors.primaryGlow : AppColors.glassCard,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isCurrent ? AppColors.primary : AppColors.glassBorder,
                          width: isCurrent ? 2 : 1,
                        ),
                      ),
                      child: Row(
                        children: [
                          Container(
                            width: 48,
                            height: 48,
                            decoration: BoxDecoration(
                              gradient: AppColors.logoGradient,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(Icons.store_rounded, color: Colors.white, size: 24),
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  biz.name,
                                  style: GoogleFonts.plusJakartaSans(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w700,
                                    color: AppColors.textPrimary,
                                  ),
                                ),
                                if (biz.slug != null)
                                  Text(
                                    '@${biz.slug}',
                                    style: GoogleFonts.plusJakartaSans(
                                      fontSize: 13,
                                      color: AppColors.textMuted,
                                    ),
                                  ),
                              ],
                            ),
                          ),
                          if (isCurrent)
                            const Icon(Icons.check_circle_rounded, color: AppColors.primary, size: 24)
                          else
                            const Icon(Icons.chevron_right_rounded, color: AppColors.textDim),
                        ],
                      ),
                    ),
                  );
                },
              ),
      ),
    );
  }
}

