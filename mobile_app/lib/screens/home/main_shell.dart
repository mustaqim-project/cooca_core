import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/pos_provider.dart';
import '../../providers/shift_provider.dart';
import '../../screens/pos/pos_cashier_screen.dart';
import '../dashboard/dashboard_screen.dart';
import '../inventory/inventory_screen.dart';
import '../reports/reports_screen.dart';
import '../more/more_screen.dart';

class MainShell extends StatefulWidget {
  const MainShell({super.key});

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> with TickerProviderStateMixin {
  int _currentIndex = 0;

  final List<Widget> _pages = const [
    PosCashierScreen(),
    DashboardScreen(),
    InventoryScreen(),
    ReportsScreen(),
    MoreScreen(),
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ShiftProvider>().fetchActiveShift();
    });
  }

  @override
  Widget build(BuildContext context) {
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.light,
        systemNavigationBarColor: Color(0xFF020617),
        systemNavigationBarIconBrightness: Brightness.light,
      ),
      child: Scaffold(
        backgroundColor: AppColors.background,
        body: IndexedStack(index: _currentIndex, children: _pages),
        bottomNavigationBar: _buildBottomNav(),
      ),
    );
  }

  Widget _buildBottomNav() {
    final pos = context.watch<PosProvider>();
    final cartCount = pos.cartItems.length;

    return Container(
      decoration: const BoxDecoration(
        color: AppColors.glassNav,
        border: Border(top: BorderSide(color: AppColors.borderGlass)),
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _navItem(0, Icons.point_of_sale_rounded, 'Kasir', badge: cartCount > 0 ? '$cartCount' : null),
              _navItem(1, Icons.grid_view_rounded, 'Ringkasan'),
              _navItem(2, Icons.inventory_2_rounded, 'Inventori'),
              _navItem(3, Icons.bar_chart_rounded, 'Laporan'),
              _navItem(4, Icons.apps_rounded, 'Lainnya'),
            ],
          ),
        ),
      ),
    );
  }

  Widget _navItem(int index, IconData icon, String label, {String? badge}) {
    final isActive = _currentIndex == index;

    // Color accent per tab — sama seperti web nav
    final Color activeColor = switch (index) {
      0 => AppColors.teal,     // POS = teal (seperti web)
      1 => AppColors.primary,  // Dashboard = emerald
      2 => AppColors.cyan,     // Inventori = cyan
      3 => AppColors.amber,    // Laporan = amber
      4 => AppColors.primary,  // Lainnya = emerald
      _ => AppColors.primary,
    };

    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _currentIndex = index),
        behavior: HitTestBehavior.opaque,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          curve: Curves.easeInOut,
          padding: const EdgeInsets.symmetric(vertical: 6),
          decoration: BoxDecoration(
            color: isActive ? activeColor.withValues(alpha: 0.1) : Colors.transparent,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Stack(
                clipBehavior: Clip.none,
                children: [
                  Icon(
                    icon,
                    size: 22,
                    color: isActive ? activeColor : AppColors.textDim,
                  ),
                  if (badge != null)
                    Positioned(
                      top: -6, right: -8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                        decoration: BoxDecoration(
                          color: AppColors.danger,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          badge,
                          style: const TextStyle(
                            color: Colors.white, fontSize: 9,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 3),
              Text(
                label,
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: isActive ? FontWeight.w700 : FontWeight.w500,
                  color: isActive ? activeColor : AppColors.textDim,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

