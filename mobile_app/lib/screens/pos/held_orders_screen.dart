import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/order_model.dart';

class HeldOrdersScreen extends StatefulWidget {
  const HeldOrdersScreen({super.key});

  @override
  State<HeldOrdersScreen> createState() => _HeldOrdersScreenState();
}

class _HeldOrdersScreenState extends State<HeldOrdersScreen> {
  List<PosOrderModel> _orders = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadOrders();
  }

  Future<void> _loadOrders() async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiClient.get('/pos/orders', queryParameters: {'status': 'held'});
      if (res is Map && res.containsKey('orders')) {
        setState(() {
          _orders = (res['orders'] as List).map((o) => PosOrderModel.fromJson(o)).toList();
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memuat pesanan: $e'), backgroundColor: AppColors.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: AppColors.textPrimary, size: 20),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text('Pesanan Diparkir 🅿️', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w700)),
        actions: [
          IconButton(icon: const Icon(Icons.refresh_rounded, color: AppColors.textSecondary), onPressed: _loadOrders),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _orders.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.inbox_outlined, size: 64, color: AppColors.textMuted),
                      const SizedBox(height: 16),
                      const Text('Tidak ada pesanan yang diparkir.', style: TextStyle(color: AppColors.textSecondary)),
                    ],
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _orders.length,
                  itemBuilder: (context, index) {
                    final order = _orders[index];
                    return _HeldOrderCard(
                      order: order,
                      onResume: () => Navigator.of(context).pop(order),
                    );
                  },
                ),
    );
  }
}

class _HeldOrderCard extends StatelessWidget {
  final PosOrderModel order;
  final VoidCallback onResume;

  const _HeldOrderCard({required this.order, required this.onResume});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: AppColors.warning.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Text('DIPARKIR', style: TextStyle(color: AppColors.warning, fontWeight: FontWeight.w700, fontSize: 10)),
              ),
              const Spacer(),
              Text(order.orderNumber, style: const TextStyle(color: AppColors.textMuted, fontSize: 11)),
            ],
          ),
          const SizedBox(height: 10),
          if (order.notes != null && order.notes!.isNotEmpty)
            Text(order.notes!, style: const TextStyle(color: AppColors.accent, fontWeight: FontWeight.w600, fontSize: 15)),
          const SizedBox(height: 4),
          Text(
            '${order.items.length} item  •  ${CurrencyFormatter.format(order.grandTotal)}',
            style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: onResume,
              icon: const Icon(Icons.play_circle_outline_rounded, size: 18),
              label: const Text('Lanjutkan Pesanan'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

