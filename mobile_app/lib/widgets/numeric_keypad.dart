import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';

class NumericKeypad extends StatelessWidget {
  final ValueChanged<String> onKey;
  final VoidCallback onBackspace;
  final VoidCallback? onConfirm;
  final String? confirmLabel;
  final bool showDecimal;

  const NumericKeypad({
    super.key,
    required this.onKey,
    required this.onBackspace,
    this.onConfirm,
    this.confirmLabel,
    this.showDecimal = false,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        _buildRow(['7', '8', '9']),
        const SizedBox(height: 10),
        _buildRow(['4', '5', '6']),
        const SizedBox(height: 10),
        _buildRow(['1', '2', '3']),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: showDecimal
                  ? _keyButton(
                      label: '.',
                      onTap: () => onKey('.'),
                    )
                  : const SizedBox.shrink(),
            ),
            const SizedBox(width: 10),
            Expanded(
              flex: showDecimal ? 1 : 2,
              child: _keyButton(
                label: '0',
                onTap: () => onKey('0'),
              ),
            ),
            if (!showDecimal) ...[
              const SizedBox(width: 10),
              Expanded(
                child: _keyButton(
                  label: '000',
                  onTap: () => onKey('000'),
                  textStyle: const TextStyle(
                    color: AppColors.textSecondary,
                    fontWeight: FontWeight.w600,
                    fontSize: 16,
                  ),
                ),
              ),
            ],
            const SizedBox(width: 10),
            Expanded(
              child: _keyButton(
                label: '⌫',
                onTap: onBackspace,
                color: AppColors.danger.withValues(alpha: 0.12),
                textStyle: const TextStyle(
                  color: AppColors.danger,
                  fontWeight: FontWeight.bold,
                  fontSize: 20,
                ),
              ),
            ),
          ],
        ),
        if (onConfirm != null) ...[
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 56,
            child: ElevatedButton(
              onPressed: onConfirm,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.success,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
              ),
              child: Text(
                confirmLabel ?? 'Konfirmasi',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                ),
              ),
            ),
          ),
        ],
      ],
    );
  }

  Widget _buildRow(List<String> keys) {
    return Row(
      children: keys
          .map((k) => Expanded(
                child: Padding(
                  padding: EdgeInsets.only(right: k != keys.last ? 10 : 0),
                  child: _keyButton(label: k, onTap: () => onKey(k)),
                ),
              ))
          .toList(),
    );
  }

  Widget _keyButton({
    required String label,
    required VoidCallback onTap,
    Color? color,
    TextStyle? textStyle,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          height: 56,
          decoration: BoxDecoration(
            color: color ?? AppColors.surfaceElevated,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.border),
          ),
          alignment: Alignment.center,
          child: Text(
            label,
            style: textStyle ??
                const TextStyle(
                  color: AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                  fontSize: 20,
                ),
          ),
        ),
      ),
    );
  }
}

