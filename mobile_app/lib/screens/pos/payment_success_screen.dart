import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/order_model.dart';

class PaymentSuccessScreen extends StatelessWidget {
  const PaymentSuccessScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final order = ModalRoute.of(context)?.settings.arguments as PosOrderModel?;

    if (order == null) {
      return const Scaffold(
        backgroundColor: AppColors.background,
        body: Center(child: Text('Order tidak ditemukan', style: TextStyle(color: AppColors.textPrimary))),
      );
    }

    final payment = order.payments.isNotEmpty ? order.payments.first : null;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: Column(
                  children: [
                    const SizedBox(height: 20),
                    // Success Animation
                    TweenAnimationBuilder<double>(
                      tween: Tween(begin: 0.0, end: 1.0),
                      duration: const Duration(milliseconds: 600),
                      curve: Curves.elasticOut,
                      builder: (context, value, child) => Transform.scale(
                        scale: value,
                        child: child,
                      ),
                      child: Container(
                        width: 100,
                        height: 100,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: AppColors.success.withValues(alpha: 0.15),
                          border: Border.all(color: AppColors.success, width: 3),
                        ),
                        child: const Icon(Icons.check_rounded, color: AppColors.success, size: 56),
                      ),
                    ),
                    const SizedBox(height: 20),
                    const Text(
                      'Pembayaran Berhasil!',
                      style: TextStyle(
                        color: AppColors.textPrimary,
                        fontSize: 24,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      order.orderNumber,
                      style: const TextStyle(color: AppColors.accent, fontSize: 14, fontWeight: FontWeight.w600),
                    ),
                    const SizedBox(height: 32),

                    // Receipt card
                    Container(
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Column(
                        children: [
                          Padding(
                            padding: const EdgeInsets.all(20),
                            child: Column(
                              children: [
                                // Items
                                ...order.items.map((item) => Padding(
                                  padding: const EdgeInsets.only(bottom: 10),
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(item.productName, style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w500, fontSize: 13)),
                                            Text('${item.quantity.toInt()} x ${CurrencyFormatter.format(item.unitPrice)}', style: const TextStyle(color: AppColors.textSecondary, fontSize: 11)),
                                          ],
                                        ),
                                      ),
                                      Text(CurrencyFormatter.format(item.subtotal), style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w600)),
                                    ],
                                  ),
                                )),

                                const Divider(color: AppColors.border),

                                if (order.discountAmount > 0)
                                  _summaryRow('Diskon', '-${CurrencyFormatter.format(order.discountAmount)}', valueColor: AppColors.warning),
                                if (order.taxAmount > 0)
                                  _summaryRow('Pajak', CurrencyFormatter.format(order.taxAmount)),
                                if (order.serviceChargeAmount > 0)
                                  _summaryRow('Service Charge', CurrencyFormatter.format(order.serviceChargeAmount)),

                                const SizedBox(height: 8),
                                _summaryRow(
                                  'TOTAL',
                                  CurrencyFormatter.format(order.grandTotal),
                                  isBold: true,
                                  valueColor: AppColors.accent,
                                  fontSize: 18,
                                ),

                                const Divider(color: AppColors.border),

                                if (payment != null) ...[
                                  _summaryRow('Metode', payment.paymentMethod.toUpperCase()),
                                  if (payment.tenderedAmount != null && payment.tenderedAmount! > 0)
                                    _summaryRow('Diterima', CurrencyFormatter.format(payment.tenderedAmount)),
                                  if (payment.changeAmount != null && payment.changeAmount! > 0)
                                    _summaryRow('Kembalian', CurrencyFormatter.format(payment.changeAmount), valueColor: AppColors.success),
                                ],
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Bottom Actions
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
              child: Column(
                children: [
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      onPressed: () {
                        // Show receipt preview modal
                        _showReceiptModal(context, order);
                      },
                      icon: const Icon(Icons.receipt_outlined, color: AppColors.textSecondary),
                      label: const Text('Lihat & Cetak Struk', style: TextStyle(color: AppColors.textSecondary)),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: AppColors.border),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    height: 56,
                    child: ElevatedButton.icon(
                      onPressed: () => Navigator.of(context).pushNamedAndRemoveUntil('/pos', (route) => false),
                      icon: const Icon(Icons.add_shopping_cart_rounded, color: Colors.white),
                      label: const Text('Transaksi Baru', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 15)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _summaryRow(String label, String value, {bool isBold = false, Color? valueColor, double fontSize = 14}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(color: AppColors.textSecondary, fontSize: fontSize)),
          Text(
            value,
            style: TextStyle(
              color: valueColor ?? AppColors.textPrimary,
              fontWeight: isBold ? FontWeight.w700 : FontWeight.w500,
              fontSize: fontSize,
            ),
          ),
        ],
      ),
    );
  }

  void _showReceiptModal(BuildContext context, PosOrderModel order) {
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surface,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (context) => DraggableScrollableSheet(
        expand: false,
        initialChildSize: 0.7,
        maxChildSize: 0.95,
        builder: (context, controller) => Column(
          children: [
            const SizedBox(height: 12),
            Container(width: 40, height: 4, decoration: BoxDecoration(color: AppColors.border, borderRadius: BorderRadius.circular(2))),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Preview Struk', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w700, fontSize: 16)),
                  IconButton(
                    icon: const Icon(Icons.print_rounded, color: AppColors.accent),
                    onPressed: () {},
                  ),
                ],
              ),
            ),
            Expanded(
              child: SingleChildScrollView(
                controller: controller,
                padding: const EdgeInsets.all(16),
                child: Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '====== STRUK PEMBELIAN ======\nNo: ${order.orderNumber}\n\n${order.items.map((i) => '${i.productName}\n${i.quantity.toInt()} x ${CurrencyFormatter.format(i.unitPrice)} = ${CurrencyFormatter.format(i.subtotal)}').join('\n')}\n\n----------------------------\nTOTAL: ${CurrencyFormatter.format(order.grandTotal)}\n============================\n\nTerima Kasih!\nPowered by Cooca POS',
                    style: const TextStyle(fontFamily: 'Courier', color: Colors.black, fontSize: 13),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

