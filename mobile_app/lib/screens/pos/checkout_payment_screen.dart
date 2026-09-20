import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../providers/auth_provider.dart';
import '../../providers/pos_provider.dart';
import '../../widgets/numeric_keypad.dart';
import '../../widgets/quick_cash_buttons.dart';

class CheckoutPaymentScreen extends StatefulWidget {
  const CheckoutPaymentScreen({super.key});

  @override
  State<CheckoutPaymentScreen> createState() => _CheckoutPaymentScreenState();
}

class _CheckoutPaymentScreenState extends State<CheckoutPaymentScreen> {
  String _selectedMethod = 'cash';
  String _cashInput = '';
  bool _isProcessing = false;

  static const List<Map<String, dynamic>> _methods = [
    {'key': 'cash', 'label': 'Tunai', 'icon': Icons.payments_outlined},
    {'key': 'qris', 'label': 'QRIS', 'icon': Icons.qr_code_2_rounded},
    {'key': 'debit', 'label': 'Debit', 'icon': Icons.credit_card_outlined},
    {'key': 'transfer', 'label': 'Transfer', 'icon': Icons.account_balance_outlined},
    {'key': 'points', 'label': 'Poin', 'icon': Icons.star_outline_rounded},
  ];

  double get _tendered => double.tryParse(_cashInput.replaceAll(',', '')) ?? 0;

  void _onKey(String key) {
    if (key == '000') {
      setState(() => _cashInput += '000');
    } else {
      setState(() => _cashInput += key);
    }
  }

  void _onBackspace() {
    if (_cashInput.isNotEmpty) {
      setState(() => _cashInput = _cashInput.substring(0, _cashInput.length - 1));
    }
  }

  Future<void> _processPayment(PosProvider pos, dynamic business) async {
    final grandTotal = pos.calculateGrandTotal(business);
    final tendered = _selectedMethod == 'cash' ? _tendered : grandTotal;

    if (tendered < grandTotal) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Jumlah pembayaran kurang!'), backgroundColor: AppColors.danger),
      );
      return;
    }

    setState(() => _isProcessing = true);

    final payments = [
      {
        'payment_method': _selectedMethod,
        'amount': grandTotal,
        'tendered': tendered,
      }
    ];

    final order = await pos.checkout(business: business, payments: payments);
    if (!mounted) return;

    setState(() => _isProcessing = false);

    if (order != null) {
      Navigator.of(context).pushReplacementNamed('/pos/success', arguments: order);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(pos.errorMessage ?? 'Transaksi gagal diproses.'),
          backgroundColor: AppColors.danger,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final pos = context.watch<PosProvider>();
    final auth = context.watch<AuthProvider>();
    final business = auth.activeBusiness;
    final grandTotal = pos.calculateGrandTotal(business);
    final change = _selectedMethod == 'cash' && _tendered >= grandTotal ? _tendered - grandTotal : 0.0;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textPrimary, size: 20),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text('Pembayaran', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w700)),
        actions: [
          // Customer search button
          IconButton(
            icon: const Icon(Icons.person_search_rounded, color: AppColors.accent),
            tooltip: 'Pilih Pelanggan',
            onPressed: () => Navigator.of(context).pushNamed('/crm/customer-picker'),
          ),
        ],
      ),
      body: Column(
        children: [
          // Total banner
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 24),
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [AppColors.primary, AppColors.accent],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Column(
              children: [
                const Text('Total Pembayaran', style: TextStyle(color: Colors.white70, fontSize: 13)),
                const SizedBox(height: 4),
                Text(
                  CurrencyFormatter.format(grandTotal),
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 32,
                    fontWeight: FontWeight.w800,
                    letterSpacing: -0.5,
                  ),
                ),
                if (pos.selectedCustomer != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        '👤 ${pos.selectedCustomer!.name}  •  ${pos.selectedCustomer!.pointsBalance} poin',
                        style: const TextStyle(color: Colors.white, fontSize: 12),
                      ),
                    ),
                  ),
              ],
            ),
          ),

          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Payment method selector
                  const Text('Metode Pembayaran', style: TextStyle(color: AppColors.textSecondary, fontSize: 12, fontWeight: FontWeight.w600)),
                  const SizedBox(height: 10),
                  SizedBox(
                    height: 72,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      children: _methods.map((m) => _methodCard(m)).toList(),
                    ),
                  ),

                  const SizedBox(height: 20),

                  if (_selectedMethod == 'cash') ...[
                    // Cash input display
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: AppColors.border),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Uang Diterima', style: TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                          const SizedBox(height: 4),
                          Text(
                            _cashInput.isEmpty ? 'Rp 0' : CurrencyFormatter.format(double.tryParse(_cashInput) ?? 0),
                            style: const TextStyle(
                              color: AppColors.textPrimary,
                              fontSize: 28,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          if (_tendered >= grandTotal && _tendered > 0)
                            Padding(
                              padding: const EdgeInsets.only(top: 8),
                              child: Row(
                                children: [
                                  const Text('Kembalian: ', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                                  Text(
                                    CurrencyFormatter.format(change),
                                    style: const TextStyle(
                                      color: AppColors.success,
                                      fontSize: 16,
                                      fontWeight: FontWeight.w700,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    QuickCashButtons(grandTotal: grandTotal, onSelected: (v) => setState(() => _cashInput = v.toInt().toString())),
                    const SizedBox(height: 16),
                    NumericKeypad(onKey: _onKey, onBackspace: _onBackspace),
                  ] else if (_selectedMethod == 'qris') ...[
                    Center(
                      child: Column(
                        children: [
                          const SizedBox(height: 16),
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.1), blurRadius: 12)],
                            ),
                            child: const Icon(Icons.qr_code_2_rounded, size: 160, color: Colors.black),
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'Scan QRIS untuk membayar\n${CurrencyFormatter.format(grandTotal)}',
                            textAlign: TextAlign.center,
                            style: const TextStyle(color: AppColors.textSecondary),
                          ),
                        ],
                      ),
                    ),
                  ] else ...[
                    Center(
                      child: Column(
                        children: [
                          const SizedBox(height: 32),
                          Icon(_methods.firstWhere((m) => m['key'] == _selectedMethod)['icon'],
                              size: 64, color: AppColors.primary.withValues(alpha: 0.5)),
                          const SizedBox(height: 16),
                          Text(
                            'Total: ${CurrencyFormatter.format(grandTotal)}',
                            style: const TextStyle(color: AppColors.textPrimary, fontSize: 18, fontWeight: FontWeight.w600),
                          ),
                          const SizedBox(height: 8),
                          const Text('Tekan konfirmasi setelah pembayaran diterima.',
                              style: TextStyle(color: AppColors.textSecondary)),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),

          // Confirm Button
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
            child: SizedBox(
              width: double.infinity,
              height: 56,
              child: ElevatedButton(
                onPressed: _isProcessing
                    ? null
                    : () {
                        if (_selectedMethod == 'cash' && _tendered < grandTotal) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Jumlah uang yang diterima kurang.'),
                              backgroundColor: AppColors.danger,
                            ),
                          );
                          return;
                        }
                        _processPayment(pos, business);
                      },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.success,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
                child: _isProcessing
                    ? const CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5)
                    : Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.check_circle_outline_rounded, color: Colors.white),
                          const SizedBox(width: 8),
                          Text(
                            'Konfirmasi Pembayaran  •  ${CurrencyFormatter.format(grandTotal)}',
                            style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 14),
                          ),
                        ],
                      ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _methodCard(Map<String, dynamic> method) {
    final isSelected = _selectedMethod == method['key'];
    return GestureDetector(
      onTap: () => setState(() {
        _selectedMethod = method['key'];
        _cashInput = '';
      }),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        margin: const EdgeInsets.only(right: 10),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : AppColors.surface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: isSelected ? AppColors.primary : AppColors.border, width: isSelected ? 2 : 1),
          boxShadow: isSelected
              ? [BoxShadow(color: AppColors.primary.withValues(alpha: 0.3), blurRadius: 8, offset: const Offset(0, 4))]
              : null,
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(method['icon'] as IconData, size: 22, color: isSelected ? Colors.white : AppColors.textSecondary),
            const SizedBox(height: 4),
            Text(
              method['label'] as String,
              style: TextStyle(
                color: isSelected ? Colors.white : AppColors.textSecondary,
                fontSize: 11,
                fontWeight: isSelected ? FontWeight.w700 : FontWeight.normal,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

