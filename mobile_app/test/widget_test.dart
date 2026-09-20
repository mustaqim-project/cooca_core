import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';
import 'package:cooca_pos_mobile/app.dart';
import 'package:cooca_pos_mobile/providers/auth_provider.dart';
import 'package:cooca_pos_mobile/providers/crm_provider.dart';
import 'package:cooca_pos_mobile/providers/inventory_provider.dart';
import 'package:cooca_pos_mobile/providers/pos_provider.dart';
import 'package:cooca_pos_mobile/providers/product_provider.dart';
import 'package:cooca_pos_mobile/providers/report_provider.dart';
import 'package:cooca_pos_mobile/providers/settings_provider.dart';
import 'package:cooca_pos_mobile/providers/shift_provider.dart';
import 'package:cooca_pos_mobile/providers/subscription_provider.dart';

void main() {
  testWidgets('App initialization smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(
      MultiProvider(
        providers: [
          ChangeNotifierProvider(create: (_) => SettingsProvider()),
          ChangeNotifierProvider(create: (_) => AuthProvider()),
          ChangeNotifierProvider(create: (_) => ProductProvider()),
          ChangeNotifierProvider(create: (_) => PosProvider()),
          ChangeNotifierProvider(create: (_) => ShiftProvider()),
          ChangeNotifierProvider(create: (_) => InventoryProvider()),
          ChangeNotifierProvider(create: (_) => ReportProvider()),
          ChangeNotifierProvider(create: (_) => CrmProvider()),
          ChangeNotifierProvider(create: (_) => SubscriptionProvider()),
        ],
        child: const CoocaPosApp(),
      ),
    );

    expect(find.byType(CoocaPosApp), findsOneWidget);
  });
}
