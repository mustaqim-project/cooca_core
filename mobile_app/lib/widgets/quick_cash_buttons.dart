import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';

class QuickCashButtons extends StatelessWidget {
  final double grandTotal;
  final ValueChanged<double> onSelected;

  const QuickCashButtons({
    super.key,
    required this.grandTotal,
    required this.onSelected,
  });

  @override
  Widget build(BuildContext context) {
    final nominals = _buildNominals(grandTotal);
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: nominals.map((nominal) {
        final isExact = nominal == grandTotal;
        return GestureDetector(
          onTap: () => onSelected(nominal),
          child: AnimatedContainer(
            duration: const Duration(milliseconds: 150),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              gradient: isExact
                  ? const LinearGradient(
                      colors: [AppColors.accent, AppColors.primary],
                    )
                  : null,
              color: isExact ? null : AppColors.surfaceElevated,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: isExact ? Colors.transparent : AppColors.border,
              ),
              boxShadow: isExact
                  ? [
                      BoxShadow(
                        color: AppColors.accent.withValues(alpha: 0.4),
                        blurRadius: 8,
                        offset: const Offset(0, 3),
                      )
                    ]
                  : null,
            ),
            child: Text(
              isExact ? 'Uang Pas' : CurrencyFormatter.format(nominal),
              style: TextStyle(
                color: isExact ? Colors.white : AppColors.textPrimary,
                fontWeight: FontWeight.w600,
                fontSize: 13,
              ),
            ),
          ),
        );
      }).toList(),
    );
  }

  static List<double> _buildNominals(double total) {
    // Round up to nearest common denomination
    final rounded = _roundUpToNearest(total);
    final nominals = [
      total,
      if (rounded != total) rounded,
      5000.0,
      10000.0,
      20000.0,
      50000.0,
      100000.0,
      200000.0,
      500000.0,
      1000000.0,
    ];

    // Filter: only show nominals >= grandTotal and remove duplicates
    final filtered = nominals
        .toSet()
        .where((n) => n >= total)
        .toList()
      ..sort();

    // Show max 6 options
    return filtered.take(6).toList();
  }

  static double _roundUpToNearest(double value) {
    if (value <= 5000) return (value / 1000).ceil() * 1000;
    if (value <= 50000) return (value / 5000).ceil() * 5000;
    if (value <= 100000) return (value / 10000).ceil() * 10000;
    if (value <= 500000) return (value / 50000).ceil() * 50000;
    return (value / 100000).ceil() * 100000;
  }
}

