import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/utils/currency_formatter.dart';
import '../../models/cart_item_model.dart';
import '../../models/product_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/pos_provider.dart';
import '../../providers/product_provider.dart';
import '../../providers/shift_provider.dart';
import '../../widgets/cart_item_tile.dart';
import '../../widgets/product_card.dart';

class PosCashierScreen extends StatefulWidget {
  const PosCashierScreen({super.key});

  @override
  State<PosCashierScreen> createState() => _PosCashierScreenState();
}

class _PosCashierScreenState extends State<PosCashierScreen> {
  final _searchCtrl = TextEditingController();
  bool _showCart = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ProductProvider>().fetchProducts();
      context.read<ShiftProvider>().fetchActiveShift();
    });
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  void _addToCart(ProductModel product) {
    context.read<PosProvider>().addItem(product);
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          content: Text('${product.name} ditambahkan ke keranjang'),
          backgroundColor: AppColors.success,
          behavior: SnackBarBehavior.floating,
          duration: const Duration(seconds: 1),
        ),
      );
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final pos = context.watch<PosProvider>();
    final products = context.watch<ProductProvider>();
    final shift = context.watch<ShiftProvider>();
    final business = auth.activeBusiness;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Column(
          children: [
            _buildTopBar(auth, shift, pos),
            _buildCategoryTabs(products),
            Expanded(
              child: _showCart
                  ? _buildCartPanel(pos, business)
                  : _buildProductGrid(products, pos),
            ),
            _buildBottomBar(pos, business),
          ],
        ),
      ),
    );
  }

  Widget _buildTopBar(AuthProvider auth, ShiftProvider shift, PosProvider pos) {
    final business = auth.activeBusiness;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(bottom: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        children: [
          // Business name
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  business?.name ?? 'Cooca POS',
                  style: const TextStyle(
                    color: AppColors.textPrimary,
                    fontWeight: FontWeight.w700,
                    fontSize: 15,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                Row(
                  children: [
                    Container(
                      width: 7,
                      height: 7,
                      margin: const EdgeInsets.only(right: 4),
                      decoration: BoxDecoration(
                        color: shift.hasActiveShift ? AppColors.success : AppColors.danger,
                        shape: BoxShape.circle,
                      ),
                    ),
                    Text(
                      shift.hasActiveShift ? 'Shift Aktif' : 'Shift Belum Dibuka',
                      style: TextStyle(
                        color: shift.hasActiveShift ? AppColors.success : AppColors.danger,
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          // Action Icons
          IconButton(
            icon: const Icon(Icons.search_rounded, color: AppColors.textSecondary),
            onPressed: () => _showSearchDialog(),
          ),
          IconButton(
            icon: const Icon(Icons.receipt_long_rounded, color: AppColors.textSecondary),
            tooltip: 'Riwayat Order',
            onPressed: () => Navigator.of(context).pushNamed('/pos/orders'),
          ),
          IconButton(
            icon: Stack(
              children: [
                const Icon(Icons.access_time_rounded, color: AppColors.textSecondary),
                if (!shift.hasActiveShift)
                  Positioned(
                    right: 0, top: 0,
                    child: Container(
                      width: 8, height: 8,
                      decoration: const BoxDecoration(color: AppColors.danger, shape: BoxShape.circle),
                    ),
                  ),
              ],
            ),
            tooltip: 'Manajemen Shift',
            onPressed: () => Navigator.of(context).pushNamed('/shift'),
          ),
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert_rounded, color: AppColors.textSecondary),
            color: AppColors.surface,
            onSelected: (value) async {
              switch (value) {
                case 'hold_orders':
                  Navigator.of(context).pushNamed('/pos/held');
                  break;
                case 'change_outlet':
                  Navigator.of(context).pushNamed('/business-select');
                  break;
                case 'settings':
                  Navigator.of(context).pushNamed('/settings');
                  break;
                case 'logout':
                  await context.read<AuthProvider>().logout();
                  if (context.mounted) {
                    Navigator.of(context).pushReplacementNamed('/login');
                  }
                  break;
              }
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'hold_orders',
                child: ListTile(
                  dense: true,
                  leading: Icon(Icons.pause_circle_outline_rounded, color: AppColors.warning),
                  title: Text('Pesanan Diparkir', style: TextStyle(color: AppColors.textPrimary, fontSize: 13)),
                ),
              ),
              const PopupMenuItem(
                value: 'change_outlet',
                child: ListTile(
                  dense: true,
                  leading: Icon(Icons.store_mall_directory_outlined, color: AppColors.accent),
                  title: Text('Ganti Outlet', style: TextStyle(color: AppColors.textPrimary, fontSize: 13)),
                ),
              ),
              const PopupMenuItem(
                value: 'settings',
                child: ListTile(
                  dense: true,
                  leading: Icon(Icons.settings_outlined, color: AppColors.textSecondary),
                  title: Text('Pengaturan', style: TextStyle(color: AppColors.textPrimary, fontSize: 13)),
                ),
              ),
              const PopupMenuItem(
                value: 'logout',
                child: ListTile(
                  dense: true,
                  leading: Icon(Icons.logout_rounded, color: AppColors.danger),
                  title: Text('Keluar', style: TextStyle(color: AppColors.danger, fontSize: 13)),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildCategoryTabs(ProductProvider products) {
    return Container(
      height: 46,
      color: AppColors.surface,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        children: [
          _categoryChip('Semua', null, products),
          ...products.categories.map((cat) => _categoryChip(cat.name, cat.id, products)),
        ],
      ),
    );
  }

  Widget _categoryChip(String label, String? id, ProductProvider products) {
    final isSelected = products.selectedCategoryId == id;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: GestureDetector(
        onTap: () => products.selectCategory(id),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
          decoration: BoxDecoration(
            color: isSelected ? AppColors.primary : AppColors.border,
            borderRadius: BorderRadius.circular(20),
          ),
          child: Text(
            label,
            style: TextStyle(
              color: isSelected ? Colors.white : AppColors.textSecondary,
              fontSize: 12,
              fontWeight: isSelected ? FontWeight.w700 : FontWeight.normal,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildProductGrid(ProductProvider products, PosProvider pos) {
    if (products.isLoading) {
      return const Center(child: CircularProgressIndicator(color: AppColors.primary));
    }
    if (products.errorMessage != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.wifi_off_rounded, size: 48, color: AppColors.textMuted),
            const SizedBox(height: 12),
            Text(products.errorMessage!, style: const TextStyle(color: AppColors.textSecondary)),
            const SizedBox(height: 16),
            ElevatedButton(onPressed: products.fetchProducts, child: const Text('Coba Lagi')),
          ],
        ),
      );
    }
    if (products.products.isEmpty) {
      return const Center(
        child: Text('Tidak ada produk ditemukan.', style: TextStyle(color: AppColors.textSecondary)),
      );
    }

    return GridView.builder(
      padding: const EdgeInsets.all(12),
      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: 3,
        crossAxisSpacing: 10,
        mainAxisSpacing: 10,
        childAspectRatio: 0.78,
      ),
      itemCount: products.products.length,
      itemBuilder: (context, index) {
        final product = products.products[index];
        final cartQty = pos.items
            .where((i) => i.product.id == product.id)
            .fold(0, (sum, i) => sum + i.quantity);
        return ProductCard(
          product: product,
          onTap: () => _addToCart(product),
          cartQuantity: cartQty,
        );
      },
    );
  }

  Widget _buildCartPanel(PosProvider pos, dynamic business) {
    if (pos.items.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.shopping_cart_outlined, size: 64, color: AppColors.textMuted),
            const SizedBox(height: 16),
            const Text('Keranjang kosong', style: TextStyle(color: AppColors.textSecondary, fontSize: 16)),
            const SizedBox(height: 8),
            TextButton(
              onPressed: () => setState(() => _showCart = false),
              child: const Text('← Kembali ke Produk'),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(12),
      itemCount: pos.items.length,
      itemBuilder: (context, index) {
        final item = pos.items[index];
        return CartItemTile(
          item: item,
          onIncrement: () => pos.updateQuantity(item.id, item.quantity + 1),
          onDecrement: () => pos.updateQuantity(item.id, item.quantity - 1),
          onRemove: () => pos.removeItem(item.id),
          onEditNotes: () => _showNotesDialog(item),
        );
      },
    );
  }

  Widget _buildBottomBar(PosProvider pos, dynamic business) {
    final grandTotal = pos.calculateGrandTotal(business);
    final itemCount = pos.totalItemCount;

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        children: [
          // Cart toggle
          GestureDetector(
            onTap: () => setState(() => _showCart = !_showCart),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              decoration: BoxDecoration(
                color: AppColors.surfaceElevated,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.border),
              ),
              child: Row(
                children: [
                  Icon(
                    _showCart ? Icons.grid_view_rounded : Icons.shopping_cart_outlined,
                    color: AppColors.textPrimary,
                    size: 22,
                  ),
                  if (itemCount > 0) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.primary,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        '$itemCount',
                        style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(width: 12),
          // Checkout button
          Expanded(
            child: ElevatedButton(
              onPressed: itemCount == 0
                  ? null
                  : () => Navigator.of(context).pushNamed('/pos/checkout'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.success,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                elevation: 0,
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.payment_rounded, color: Colors.white, size: 20),
                  const SizedBox(width: 8),
                  Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Bayar Sekarang', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 14)),
                      Text(
                        CurrencyFormatter.format(grandTotal),
                        style: const TextStyle(color: Colors.white70, fontSize: 12),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
          // Hold button
          if (itemCount > 0) ...[
            const SizedBox(width: 8),
            IconButton(
              onPressed: () => _showHoldOrderDialog(),
              icon: const Icon(Icons.pause_circle_outline_rounded, color: AppColors.warning, size: 28),
              tooltip: 'Parkir Pesanan',
            ),
          ],
        ],
      ),
    );
  }

  void _showSearchDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Cari Produk', style: TextStyle(color: AppColors.textPrimary)),
        content: TextField(
          controller: _searchCtrl,
          autofocus: true,
          style: const TextStyle(color: AppColors.textPrimary),
          decoration: const InputDecoration(
            hintText: 'Nama produk, SKU, atau barcode...',
            hintStyle: TextStyle(color: AppColors.textMuted),
            prefixIcon: Icon(Icons.search, color: AppColors.textSecondary),
            border: OutlineInputBorder(),
          ),
          onChanged: (v) => context.read<ProductProvider>().search(v),
        ),
        actions: [
          TextButton(
            onPressed: () {
              _searchCtrl.clear();
              context.read<ProductProvider>().search('');
              setState(() => _showCart = false);
              Navigator.of(context).pop();
            },
            child: const Text('Selesai'),
          ),
        ],
      ),
    );
  }

  void _showNotesDialog(CartItemModel item) {
    final ctrl = TextEditingController(text: item.notes);
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text('Catatan: ${item.product.name}', style: const TextStyle(color: AppColors.textPrimary, fontSize: 15)),
        content: TextField(
          controller: ctrl,
          autofocus: true,
          style: const TextStyle(color: AppColors.textPrimary),
          maxLines: 3,
          decoration: const InputDecoration(
            hintText: 'Contoh: tanpa es, gula sedikit...',
            hintStyle: TextStyle(color: AppColors.textMuted),
            border: OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('Batal')),
          ElevatedButton(
            onPressed: () {
              context.read<PosProvider>().updateItemNotes(item.id, ctrl.text.trim());
              Navigator.of(context).pop();
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  void _showHoldOrderDialog() {
    final ctrl = TextEditingController();
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Padding(
        padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('🅿️ Parkir Pesanan', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.w700, fontSize: 18)),
            const SizedBox(height: 8),
            const Text('Masukkan nama atau keterangan untuk pesanan ini:', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
            const SizedBox(height: 12),
            TextField(
              controller: ctrl,
              autofocus: true,
              style: const TextStyle(color: AppColors.textPrimary),
              decoration: const InputDecoration(
                hintText: 'Contoh: Meja 4, Bapak Budi, Takeaway 1',
                hintStyle: TextStyle(color: AppColors.textMuted),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () async {
                  Navigator.of(context).pop();
                  final ok = await context.read<PosProvider>().holdOrder(note: ctrl.text.trim());
                  if (context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(ok ? 'Pesanan diparkir.' : 'Gagal menyimpan pesanan.'),
                        backgroundColor: ok ? AppColors.success : AppColors.danger,
                      ),
                    );
                    if (ok) setState(() => _showCart = false);
                  }
                },
                icon: const Icon(Icons.pause_circle_outline_rounded),
                label: const Text('Parkir Pesanan'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.warning,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

