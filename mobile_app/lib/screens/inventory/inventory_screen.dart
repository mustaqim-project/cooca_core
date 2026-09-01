import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/inventory_provider.dart';

class InventoryScreen extends StatefulWidget {
  const InventoryScreen({super.key});

  @override
  State<InventoryScreen> createState() => _InventoryScreenState();
}

class _InventoryScreenState extends State<InventoryScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final _searchController = TextEditingController();
  String _filterQuery = '';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<InventoryProvider>().fetchStocks();
      context.read<InventoryProvider>().fetchMovements();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _showAdjustDialog(Map<String, dynamic> item) {
    final qtyController = TextEditingController();
    final reasonController = TextEditingController();
    String adjustType = 'in'; // 'in' or 'out'

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          backgroundColor: AppColors.surface,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Text(
            'Penyesuaian Stok',
            style: GoogleFonts.plusJakartaSans(
              fontSize: 16,
              fontWeight: FontWeight.w700,
              color: AppColors.textPrimary,
            ),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                item['name'] ?? 'Item',
                style: GoogleFonts.plusJakartaSans(
                  fontWeight: FontWeight.w600,
                  color: AppColors.cyan,
                  fontSize: 14,
                ),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: ChoiceChip(
                      label: const Center(child: Text('Tambah (+)')),
                      selected: adjustType == 'in',
                      selectedColor: AppColors.primaryGlow,
                      onSelected: (_) => setDialogState(() => adjustType = 'in'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: ChoiceChip(
                      label: const Center(child: Text('Kurang (-)')),
                      selected: adjustType == 'out',
                      selectedColor: AppColors.roseGlow,
                      onSelected: (_) => setDialogState(() => adjustType = 'out'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              TextField(
                controller: qtyController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  labelText: 'Jumlah',
                  hintText: 'Contoh: 10',
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: reasonController,
                decoration: const InputDecoration(
                  labelText: 'Alasan Penyesuaian',
                  hintText: 'Contoh: Rusak / Koreksi Fisik',
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              onPressed: () async {
                final qty = double.tryParse(qtyController.text);
                if (qty == null || qty <= 0) return;

                final prov = context.read<InventoryProvider>();
                final ok = await prov.quickAdjust(
                  productId: item['id'].toString(),
                  quantity: qty,
                  type: adjustType,
                  reason: reasonController.text,
                );

                if (ctx.mounted) Navigator.pop(ctx);
                if (mounted && ok) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Stok berhasil diperbarui!')),
                  );
                }
              },
              child: const Text('Simpan'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final inventory = context.watch<InventoryProvider>();
    final stocks = inventory.stocks.where((s) {
      final name = (s['name'] ?? '').toString().toLowerCase();
      return name.contains(_filterQuery.toLowerCase());
    }).toList();

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: AppColors.glassNav,
        title: Text(
          'Inventori & Gudang',
          style: GoogleFonts.plusJakartaSans(
            fontSize: 18,
            fontWeight: FontWeight.w800,
            color: AppColors.textPrimary,
          ),
        ),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: AppColors.cyan,
          labelColor: AppColors.cyan,
          tabs: const [
            Tab(text: 'Daftar Stok'),
            Tab(text: 'Mutasi & Riwayat'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // Tab 1: Stock List
          RefreshIndicator(
            color: AppColors.cyan,
            onRefresh: () async => inventory.fetchStocks(),
            child: Column(
              children: [
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: TextField(
                    controller: _searchController,
                    onChanged: (val) => setState(() => _filterQuery = val),
                    decoration: InputDecoration(
                      hintText: 'Cari nama produk / bahan...',
                      prefixIcon: const Icon(Icons.search_rounded, color: AppColors.cyan),
                      suffixIcon: _filterQuery.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear_rounded),
                              onPressed: () {
                                _searchController.clear();
                                setState(() => _filterQuery = '');
                              },
                            )
                          : null,
                    ),
                  ),
                ),
                Expanded(
                  child: inventory.isLoading
                      ? const Center(child: CircularProgressIndicator(color: AppColors.cyan))
                      : stocks.isEmpty
                          ? Center(
                              child: Text(
                                'Tidak ada data stok.',
                                style: GoogleFonts.plusJakartaSans(color: AppColors.textMuted),
                              ),
                            )
                          : ListView.separated(
                              padding: const EdgeInsets.symmetric(horizontal: 16),
                              itemCount: stocks.length,
                              separatorBuilder: (_, __) => const SizedBox(height: 8),
                              itemBuilder: (context, index) {
                                final item = stocks[index];
                                final qty = (item['quantity'] ?? 0).toDouble();
                                final minQty = (item['minimum_stock'] ?? 0).toDouble();
                                final isLow = qty <= minQty;

                                return Container(
                                  padding: const EdgeInsets.all(14),
                                  decoration: BoxDecoration(
                                    color: AppColors.glassCard,
                                    borderRadius: BorderRadius.circular(14),
                                    border: Border.all(color: AppColors.glassBorder),
                                  ),
                                  child: Row(
                                    children: [
                                      Container(
                                        width: 44,
                                        height: 44,
                                        decoration: BoxDecoration(
                                          color: isLow ? AppColors.roseGlow : AppColors.cyanGlow,
                                          borderRadius: BorderRadius.circular(12),
                                        ),
                                        child: Icon(
                                          Icons.inventory_2_rounded,
                                          color: isLow ? AppColors.rose : AppColors.cyan,
                                          size: 22,
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              item['name'] ?? 'Nama Produk',
                                              style: GoogleFonts.plusJakartaSans(
                                                fontWeight: FontWeight.w700,
                                                color: AppColors.textPrimary,
                                                fontSize: 14,
                                              ),
                                            ),
                                            Text(
                                              'Min. Stok: $minQty ${item['unit'] ?? 'pcs'}',
                                              style: GoogleFonts.plusJakartaSans(
                                                color: AppColors.textMuted,
                                                fontSize: 12,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Column(
                                        crossAxisAlignment: CrossAxisAlignment.end,
                                        children: [
                                          Text(
                                            '$qty ${item['unit'] ?? 'pcs'}',
                                            style: GoogleFonts.plusJakartaSans(
                                              fontWeight: FontWeight.w800,
                                              color: isLow ? AppColors.rose : AppColors.textPrimary,
                                              fontSize: 16,
                                            ),
                                          ),
                                          const SizedBox(height: 4),
                                          InkWell(
                                            onTap: () => _showAdjustDialog(item),
                                            child: Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                              decoration: BoxDecoration(
                                                color: AppColors.surfaceElevated,
                                                borderRadius: BorderRadius.circular(6),
                                              ),
                                              child: Text(
                                                'Sesuaikan',
                                                style: GoogleFonts.plusJakartaSans(
                                                  fontSize: 10,
                                                  fontWeight: FontWeight.w600,
                                                  color: AppColors.cyan,
                                                ),
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                );
                              },
                            ),
                ),
              ],
            ),
          ),

          // Tab 2: Stock Movements
          RefreshIndicator(
            color: AppColors.cyan,
            onRefresh: () async => inventory.fetchMovements(),
            child: inventory.movements.isEmpty
                ? Center(
                    child: Text(
                      'Belum ada riwayat mutasi stok.',
                      style: GoogleFonts.plusJakartaSans(color: AppColors.textMuted),
                    ),
                  )
                : ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: inventory.movements.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (context, index) {
                      final mov = inventory.movements[index];
                      final isPlus = (mov['type'] ?? 'in') == 'in';
                      return Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppColors.glassCard,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: AppColors.glassBorder),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              isPlus ? Icons.arrow_downward_rounded : Icons.arrow_upward_rounded,
                              color: isPlus ? AppColors.primary : AppColors.rose,
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    mov['product_name'] ?? 'Mutasi',
                                    style: GoogleFonts.plusJakartaSans(
                                      fontWeight: FontWeight.w700,
                                      color: AppColors.textPrimary,
                                    ),
                                  ),
                                  Text(
                                    mov['created_at'] ?? '-',
                                    style: GoogleFonts.plusJakartaSans(
                                      color: AppColors.textMuted,
                                      fontSize: 11,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            Text(
                              '${isPlus ? "+" : "-"}${mov['quantity'] ?? 0}',
                              style: GoogleFonts.plusJakartaSans(
                                fontWeight: FontWeight.w800,
                                color: isPlus ? AppColors.primary : AppColors.rose,
                                fontSize: 15,
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

