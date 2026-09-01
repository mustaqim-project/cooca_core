import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import '../../core/constants/app_colors.dart';

class MobileCalculatorScreen extends StatefulWidget {
  const MobileCalculatorScreen({super.key});

  @override
  State<MobileCalculatorScreen> createState() => _MobileCalculatorScreenState();
}

class _MobileCalculatorScreenState extends State<MobileCalculatorScreen> {
  final currencyFormatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  // 3-Pilar Cost Controllers
  final _materialCostController = TextEditingController(text: '15000');
  final _laborCostController = TextEditingController(text: '3500');
  final _overheadCostController = TextEditingController(text: '2500');
  final _targetMarginController = TextEditingController(text: '35'); // 35%

  double _hppTotal = 21000.0;
  double _recommendedPrice = 32308.0;

  @override
  void initState() {
    super.initState();
    _calculateHpp();
  }

  void _calculateHpp() {
    final material = double.tryParse(_materialCostController.text) ?? 0;
    final labor = double.tryParse(_laborCostController.text) ?? 0;
    final overhead = double.tryParse(_overheadCostController.text) ?? 0;
    final margin = double.tryParse(_targetMarginController.text) ?? 30;

    final totalCost = material + labor + overhead;
    final marginFraction = (100 - margin) / 100;
    final price = marginFraction > 0 ? (totalCost / marginFraction) : totalCost * 1.5;

    setState(() {
      _hppTotal = totalCost;
      _recommendedPrice = price;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.glassNav,
        title: Text(
          'Kalkulator HPP 3-Pilar',
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
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Result Highlight Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF166534), Color(0xFF14532D)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: AppColors.primary.withValues(alpha: 0.2),
                    blurRadius: 20,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Total HPP / Unit',
                            style: GoogleFonts.plusJakartaSans(
                              color: AppColors.textSecondary,
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            currencyFormatter.format(_hppTotal),
                            style: GoogleFonts.plusJakartaSans(
                              color: Colors.white,
                              fontSize: 22,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ],
                      ),
                      Container(
                        height: 40,
                        width: 1,
                        color: Colors.white.withValues(alpha: 0.2),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(
                            'Rekomendasi Harga Jual',
                            style: GoogleFonts.plusJakartaSans(
                              color: AppColors.primaryLight,
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            currencyFormatter.format(_recommendedPrice),
                            style: GoogleFonts.plusJakartaSans(
                              color: AppColors.teal,
                              fontSize: 22,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // 3-Pilar Input Form
            Text(
              'Rincian Biaya Produksi',
              style: GoogleFonts.plusJakartaSans(
                fontSize: 15,
                fontWeight: FontWeight.w700,
                color: AppColors.textPrimary,
              ),
            ),
            const SizedBox(height: 12),

            _buildCostInput(
              controller: _materialCostController,
              label: '1. Biaya Bahan Baku & Kemasan',
              hint: 'Rp 15.000',
              icon: Icons.inventory_2_rounded,
              color: AppColors.cyan,
              onChanged: (_) => _calculateHpp(),
            ),
            const SizedBox(height: 12),

            _buildCostInput(
              controller: _laborCostController,
              label: '2. Biaya Tenaga Kerja (Labor)',
              hint: 'Rp 3.500',
              icon: Icons.person_rounded,
              color: AppColors.amber,
              onChanged: (_) => _calculateHpp(),
            ),
            const SizedBox(height: 12),

            _buildCostInput(
              controller: _overheadCostController,
              label: '3. Biaya Overhead / Operasional',
              hint: 'Rp 2.500',
              icon: Icons.bolt_rounded,
              color: AppColors.purple,
              onChanged: (_) => _calculateHpp(),
            ),
            const SizedBox(height: 16),

            // Target Margin Slider
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.glassCard,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.glassBorder),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Target Margin Keuntungan',
                        style: GoogleFonts.plusJakartaSans(
                          fontWeight: FontWeight.w600,
                          color: AppColors.textPrimary,
                        ),
                      ),
                      Text(
                        '${_targetMarginController.text}%',
                        style: GoogleFonts.plusJakartaSans(
                          fontWeight: FontWeight.w800,
                          color: AppColors.primaryLight,
                          fontSize: 16,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Slider(
                    value: double.tryParse(_targetMarginController.text) ?? 30,
                    min: 5,
                    max: 80,
                    divisions: 15,
                    activeColor: AppColors.primary,
                    onChanged: (val) {
                      _targetMarginController.text = val.toInt().toString();
                      _calculateHpp();
                    },
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            ElevatedButton.icon(
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Kalkulasi HPP siap diterapkan ke katalog produk.')),
                );
              },
              icon: const Icon(Icons.check_circle_rounded),
              label: const Text('Terapkan ke Produk'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCostInput({
    required TextEditingController controller,
    required String label,
    required String hint,
    required IconData icon,
    required Color color,
    required ValueChanged<String> onChanged,
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
          Row(
            children: [
              Icon(icon, color: color, size: 20),
              const SizedBox(width: 8),
              Text(
                label,
                style: GoogleFonts.plusJakartaSans(
                  fontWeight: FontWeight.w600,
                  color: AppColors.textPrimary,
                  fontSize: 13,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          TextField(
            controller: controller,
            keyboardType: TextInputType.number,
            onChanged: onChanged,
            style: GoogleFonts.plusJakartaSans(
              color: AppColors.textPrimary,
              fontWeight: FontWeight.w700,
              fontSize: 16,
            ),
            decoration: InputDecoration(
              prefixText: 'Rp ',
              prefixStyle: GoogleFonts.plusJakartaSans(color: AppColors.primaryLight, fontWeight: FontWeight.w700),
              hintText: hint,
            ),
          ),
        ],
      ),
    );
  }
}

