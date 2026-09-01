import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../providers/shift_provider.dart';
import '../shift/open_shift_screen.dart';

class ActiveShiftScreen extends StatefulWidget {
  const ActiveShiftScreen({super.key});

  @override
  State<ActiveShiftScreen> createState() => _ActiveShiftScreenState();
}

class _ActiveShiftScreenState extends State<ActiveShiftScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ShiftProvider>().fetchActiveShift();
    });
  }

  @override
  Widget build(BuildContext context) {
    final shift = context.watch<ShiftProvider>();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textPrimary, size: 20),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text('Manajemen Shift', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w700)),
        actions: [
          IconButton(icon: const Icon(Icons.refresh_rounded, color: AppColors.textSecondary), onPressed: shift.fetchActiveShift),
        ],
      ),
      body: shift.isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : !shift.hasActiveShift
              ? _buildNoShift(context, shift)
              : _buildActiveShift(context, shift),
    );
  }

  Widget _buildNoShift(BuildContext context, ShiftProvider shift) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: AppColors.danger.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.lock_clock_rounded, size: 64, color: AppColors.danger),
            ),
            const SizedBox(height: 24),
            const Text(
              'Shift Belum Dibuka',
              style: TextStyle(color: AppColors.textPrimary, fontSize: 22, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 10),
            const Text(
              'Buka shift terlebih dahulu untuk mulai menerima transaksi POS.',
              textAlign: TextAlign.center,
              style: TextStyle(color: AppColors.textSecondary, fontSize: 14),
            ),
            const SizedBox(height: 32),
            SizedBox(
              width: double.infinity,
              height: 56,
              child: ElevatedButton.icon(
                onPressed: () async {
                  final result = await Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const OpenShiftScreen()),
                  );
                  if (result == true && context.mounted) {
                    context.read<ShiftProvider>().fetchActiveShift();
                  }
                },
                icon: const Icon(Icons.play_circle_outline_rounded, color: Colors.white),
                label: const Text('Buka Shift Sekarang', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 15)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.success,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActiveShift(BuildContext context, ShiftProvider shift) {
    final s = shift.activeShift!;
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Status Banner
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(colors: [AppColors.success, Color(0xFF059669)]),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [BoxShadow(color: AppColors.success.withValues(alpha: 0.3), blurRadius: 16, offset: const Offset(0, 8))],
          ),
          child: Row(
            children: [
              const Icon(Icons.play_circle_rounded, color: Colors.white, size: 36),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('SHIFT AKTIF', style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 2),
                    Text(s.cashierName ?? 'Kasir', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 16)),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text('${s.totalOrders} transaksi', style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 16)),
                  const Text('Total Pesanan', style: TextStyle(color: Colors.white70, fontSize: 11)),
                ],
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Cash Breakdown
        _sectionCard(
          title: 'Kas & Laci',
          children: [
            _row('Saldo Awal', CurrencyFormatter.format(s.openingBalance)),
            _row('Penjualan Tunai', CurrencyFormatter.format(s.totalSalesCash), valueColor: AppColors.success),
            _row('Non-Tunai', CurrencyFormatter.format(s.totalSalesNonCash)),
            _row('Uang Masuk (Petty Cash)', CurrencyFormatter.format(s.totalCashIn), valueColor: AppColors.success),
            _row('Uang Keluar', CurrencyFormatter.format(s.totalCashOut), valueColor: AppColors.danger),
            const Divider(color: AppColors.border),
            _row('Ekspektasi Kas', CurrencyFormatter.format(s.expectedCash), isBold: true, valueColor: AppColors.accent),
          ],
        ),
        const SizedBox(height: 12),

        // Action buttons
        Row(
          children: [
            Expanded(
              child: _actionButton(
                label: 'Kas Masuk',
                icon: Icons.arrow_circle_down_rounded,
                color: AppColors.success,
                onTap: () => _showCashMovementDialog(context, shift, 'cash_in'),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _actionButton(
                label: 'Kas Keluar',
                icon: Icons.arrow_circle_up_rounded,
                color: AppColors.danger,
                onTap: () => _showCashMovementDialog(context, shift, 'cash_out'),
              ),
            ),
          ],
        ),
        const SizedBox(height: 24),

        // Close Shift
        SizedBox(
          width: double.infinity,
          height: 56,
          child: OutlinedButton.icon(
            onPressed: () => _showCloseShiftDialog(context, shift),
            icon: const Icon(Icons.stop_circle_outlined, color: AppColors.danger),
            label: const Text('Tutup Shift & Hitung Kas', style: TextStyle(color: AppColors.danger, fontWeight: FontWeight.w700)),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: AppColors.danger),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
          ),
        ),
      ],
    );
  }

  Widget _sectionCard({required String title, required List<Widget> children}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: AppColors.surface, borderRadius: BorderRadius.circular(16), border: Border.all(color: AppColors.border)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: const TextStyle(color: AppColors.textSecondary, fontWeight: FontWeight.w600, fontSize: 12)),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }

  Widget _row(String label, String value, {bool isBold = false, Color? valueColor}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 13)),
          Text(
            value,
            style: TextStyle(
              color: valueColor ?? AppColors.textPrimary,
              fontWeight: isBold ? FontWeight.w700 : FontWeight.w500,
              fontSize: 13,
            ),
          ),
        ],
      ),
    );
  }

  Widget _actionButton({required String label, required IconData icon, required Color color, required VoidCallback onTap}) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: color.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: color.withValues(alpha: 0.4)),
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 28),
            const SizedBox(height: 6),
            Text(label, style: TextStyle(color: color, fontWeight: FontWeight.w600, fontSize: 13)),
          ],
        ),
      ),
    );
  }

  void _showCashMovementDialog(BuildContext context, ShiftProvider shift, String type) {
    final amountCtrl = TextEditingController();
    final reasonCtrl = TextEditingController();
    final isIn = type == 'cash_in';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (context) => Padding(
        padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              isIn ? '💰 Kas Masuk' : '💸 Kas Keluar',
              style: TextStyle(color: isIn ? AppColors.success : AppColors.danger, fontWeight: FontWeight.w700, fontSize: 18),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: amountCtrl,
              keyboardType: TextInputType.number,
              style: const TextStyle(color: AppColors.textPrimary),
              decoration: const InputDecoration(labelText: 'Jumlah (Rp)', prefixIcon: Icon(Icons.attach_money), border: OutlineInputBorder()),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: reasonCtrl,
              style: const TextStyle(color: AppColors.textPrimary),
              decoration: const InputDecoration(labelText: 'Keterangan', prefixIcon: Icon(Icons.notes_rounded), border: OutlineInputBorder()),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () async {
                  final amount = double.tryParse(amountCtrl.text) ?? 0;
                  if (amount <= 0 || reasonCtrl.text.trim().isEmpty) return;
                  Navigator.of(context).pop();
                  await shift.recordCashMovement(type: type, amount: amount, reason: reasonCtrl.text.trim());
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: isIn ? AppColors.success : AppColors.danger,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
                child: const Text('Simpan', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showCloseShiftDialog(BuildContext context, ShiftProvider shift) {
    final amountCtrl = TextEditingController();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(24))),
      builder: (context) => Padding(
        padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('🔒 Tutup Shift', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w700, fontSize: 18)),
            const SizedBox(height: 8),
            Text('Ekspektasi kas: ${CurrencyFormatter.format(shift.activeShift?.expectedCash ?? 0)}', style: const TextStyle(color: AppColors.textSecondary)),
            const SizedBox(height: 16),
            TextField(
              controller: amountCtrl,
              keyboardType: TextInputType.number,
              autofocus: true,
              style: const TextStyle(color: AppColors.textPrimary),
              decoration: const InputDecoration(labelText: 'Kas Aktual di Laci (Rp)', prefixIcon: Icon(Icons.payment), border: OutlineInputBorder()),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () async {
                  final amount = double.tryParse(amountCtrl.text) ?? 0;
                  Navigator.of(context).pop();
                  await shift.closeShift(amount);
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Shift berhasil ditutup.'), backgroundColor: AppColors.success),
                    );
                  }
                },
                style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger, padding: const EdgeInsets.symmetric(vertical: 14)),
                child: const Text('Tutup Shift', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

