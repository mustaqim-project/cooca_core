import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/report_provider.dart';

class ReportsScreen extends StatefulWidget {
  const ReportsScreen({super.key});

  @override
  State<ReportsScreen> createState() => _ReportsScreenState();
}

class _ReportsScreenState extends State<ReportsScreen> {
  final currencyFormatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ReportProvider>().fetchReports();
    });
  }

  @override
  Widget build(BuildContext context) {
    final report = context.watch<ReportProvider>();
    final summary = report.salesSummary;
    final pl = report.plSummary;

    final double grossRevenue = (summary['total_sales'] ?? 0).toDouble();
    final double totalExpense = (summary['total_expenses'] ?? 0).toDouble();
    final double netProfit = (summary['net_profit'] ?? (grossRevenue - totalExpense)).toDouble();
    final double hppCost = (pl['total_cogs'] ?? 0).toDouble();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.glassNav,
        title: Text(
          'Laporan & Analitik Keuangan',
          style: GoogleFonts.plusJakartaSans(
            fontSize: 18,
            fontWeight: FontWeight.w800,
            color: AppColors.textPrimary,
          ),
        ),
      ),
      body: RefreshIndicator(
        color: AppColors.amber,
        onRefresh: () async => report.fetchReports(period: report.period),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Period Segmented Filter
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _buildFilterChip('Hari Ini', 'today', report),
                    const SizedBox(width: 8),
                    _buildFilterChip('Minggu Ini', 'week', report),
                    const SizedBox(width: 8),
                    _buildFilterChip('Bulan Ini', 'month', report),
                    const SizedBox(width: 8),
                    _buildFilterChip('Tahun Ini', 'year', report),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // P&L Executive Card
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF1E293B), Color(0xFF0F172A)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: AppColors.amber.withValues(alpha: 0.3)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Ringkasan Laba Rugi (P&L)',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 15,
                            fontWeight: FontWeight.w700,
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const Icon(Icons.bar_chart_rounded, color: AppColors.amber),
                      ],
                    ),
                    const Divider(height: 24, color: AppColors.border),
                    _buildPlRow('Pendapatan Kotor (Omzet)', currencyFormatter.format(grossRevenue), isPositive: true),
                    const SizedBox(height: 10),
                    _buildPlRow('Beban Pokok Penjualan (HPP)', currencyFormatter.format(hppCost), isNegative: true),
                    const SizedBox(height: 10),
                    _buildPlRow('Biaya Operasional (OPEX)', currencyFormatter.format(totalExpense), isNegative: true),
                    const Divider(height: 24, color: AppColors.border),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Laba Bersih (Net Profit)',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                            color: AppColors.textPrimary,
                          ),
                        ),
                        Text(
                          currencyFormatter.format(netProfit),
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 18,
                            fontWeight: FontWeight.w900,
                            color: netProfit >= 0 ? AppColors.primaryLight : AppColors.rose,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Transaction Stats
              Text(
                'Statistik Transaksi Kasir',
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: AppColors.textPrimary,
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _buildMetricTile(
                      label: 'Total Transaksi',
                      val: '${summary['transaction_count'] ?? 0}',
                      icon: Icons.receipt_rounded,
                      color: AppColors.teal,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _buildMetricTile(
                      label: 'Rata-rata / Order',
                      val: currencyFormatter.format(
                        (summary['transaction_count'] ?? 0) > 0
                            ? grossRevenue / (summary['transaction_count'] ?? 1)
                            : 0,
                      ),
                      icon: Icons.shopping_bag_rounded,
                      color: AppColors.purple,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFilterChip(String label, String value, ReportProvider report) {
    final isSelected = report.period == value;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: AppColors.amberGlow,
      labelStyle: GoogleFonts.plusJakartaSans(
        color: isSelected ? AppColors.amber : AppColors.textMuted,
        fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
      ),
      onSelected: (_) => report.setPeriod(value),
    );
  }

  Widget _buildPlRow(String label, String value, {bool isPositive = false, bool isNegative = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: GoogleFonts.plusJakartaSans(
            fontSize: 13,
            color: AppColors.textMuted,
          ),
        ),
        Text(
          value,
          style: GoogleFonts.plusJakartaSans(
            fontSize: 14,
            fontWeight: FontWeight.w700,
            color: isNegative ? AppColors.rose : AppColors.textPrimary,
          ),
        ),
      ],
    );
  }

  Widget _buildMetricTile({
    required String label,
    required String val,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.glassCard,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.glassBorder),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 24),
          const SizedBox(height: 12),
          FittedBox(
            fit: BoxFit.scaleDown,
            child: Text(
              val,
              style: GoogleFonts.plusJakartaSans(
                fontSize: 18,
                fontWeight: FontWeight.w800,
                color: AppColors.textPrimary,
              ),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: GoogleFonts.plusJakartaSans(
              fontSize: 11,
              color: AppColors.textMuted,
            ),
          ),
        ],
      ),
    );
  }
}

