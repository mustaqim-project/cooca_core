import 'package:flutter/material.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/order_model.dart';

class OrderHistoryScreen extends StatefulWidget {
  const OrderHistoryScreen({super.key});

  @override
  State<OrderHistoryScreen> createState() => _OrderHistoryScreenState();
}

class _OrderHistoryScreenState extends State<OrderHistoryScreen> {
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
      final res = await ApiClient.get('/pos/orders', queryParameters: {'status': 'completed'});
      if (res is Map && res.containsKey('orders')) {
        setState(() {
          _orders = (res['orders'] as List).map((o) => PosOrderModel.fromJson(o)).toList();
        });
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memuat riwayat: $e'), backgroundColor: AppColors.danger),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _voidOrder(PosOrderModel order) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        title: const Text('Batalkan Transaksi?', style: TextStyle(color: AppColors.textPrimary)),
        content: Text('Yakin ingin membatalkan transaksi ${order.orderNumber}?\nTindakan ini tidak dapat dibatalkan.', style: const TextStyle(color: AppColors.textSecondary)),
        actions: [
          TextButton(onPressed: () => Navigator.of(context).pop(false), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(true),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            child: const Text('Ya, Batalkan', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      try {
        await ApiClient.post('/pos/orders/${order.id}/void');
        await _loadOrders();
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Transaksi berhasil dibatalkan.'), backgroundColor: AppColors.success),
          );
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Gagal membatalkan: $e'), backgroundColor: AppColors.danger),
          );
        }
      }
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
        title: const Text('Riwayat Transaksi', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w700)),
        actions: [
          IconButton(icon: const Icon(Icons.refresh_rounded, color: AppColors.textSecondary), onPressed: _loadOrders),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _orders.isEmpty
              ? const Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.receipt_long_outlined, size: 64, color: AppColors.textMuted),
                      SizedBox(height: 16),
                      Text('Belum ada transaksi hari ini.', style: TextStyle(color: AppColors.textSecondary)),
                    ],
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _orders.length,
                  itemBuilder: (context, index) {
                    final order = _orders[index];
                    return _OrderCard(order: order, onVoid: () => _voidOrder(order));
                  },
                ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  final PosOrderModel order;
  final VoidCallback onVoid;

  const _OrderCard({required this.order, required this.onVoid});

  @override
  Widget build(BuildContext context) {
    final isVoided = order.status == 'voided';
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isVoided ? AppColors.surface.withValues(alpha: 0.5) : AppColors.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: isVoided ? AppColors.border.withValues(alpha: 0.4) : AppColors.border),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: isVoided ? AppColors.danger.withValues(alpha: 0.1) : AppColors.success.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              isVoided ? Icons.block_rounded : Icons.receipt_rounded,
              color: isVoided ? AppColors.danger : AppColors.success,
              size: 22,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(order.orderNumber, style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w600, fontSize: 13)),
                Text(
                  '${order.items.length} item  •  ${order.payments.isNotEmpty ? order.payments.first.paymentMethod.toUpperCase() : ''}',
                  style: const TextStyle(color: AppColors.textSecondary, fontSize: 11),
                ),
              ],
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                CurrencyFormatter.format(order.grandTotal),
                style: TextStyle(
                  color: isVoided ? AppColors.textMuted : AppColors.textPrimary,
                  fontWeight: FontWeight.w700,
                  fontSize: 14,
                  decoration: isVoided ? TextDecoration.lineThrough : null,
                ),
              ),
              if (!isVoided)
                GestureDetector(
                  onTap: onVoid,
                  child: const Text('Batalkan', style: TextStyle(color: AppColors.danger, fontSize: 11, fontWeight: FontWeight.w500)),
                ),
            ],
          ),
        ],
      ),
    );
  }
}

