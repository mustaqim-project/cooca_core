import 'package:flutter/material.dart';
import 'core/theme/app_theme.dart';
import 'screens/ai/ai_assistant_chat_screen.dart';
import 'screens/auth/business_select_screen.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/billing/billing_upgrade_screen.dart';
import 'screens/calculator/mobile_calculator_screen.dart';
import 'screens/crm/customer_picker_screen.dart';
import 'screens/home/main_shell.dart';
import 'screens/onboarding/onboarding_screen.dart';
import 'screens/pos/checkout_payment_screen.dart';
import 'screens/pos/held_orders_screen.dart';
import 'screens/pos/order_history_screen.dart';
import 'screens/pos/payment_success_screen.dart';
import 'screens/settings/app_settings_screen.dart';
import 'screens/settings/printer_settings_screen.dart';
import 'screens/shift/active_shift_screen.dart';
import 'screens/shift/open_shift_screen.dart';
import 'screens/splash_screen.dart';

class CoocaPosApp extends StatelessWidget {
  const CoocaPosApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Cooca Core',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.dark,
      initialRoute: '/',
      routes: {
        '/': (context) => const SplashScreen(),
        '/onboarding': (context) => const OnboardingScreen(),
        '/login': (context) => const LoginScreen(),
        '/register': (context) => const RegisterScreen(),
        '/business-select': (context) => const BusinessSelectScreen(),
        '/home': (context) => const MainShell(),
        '/pos/checkout': (context) => const CheckoutPaymentScreen(),
        '/pos/success': (context) => const PaymentSuccessScreen(),
        '/pos/held': (context) => const HeldOrdersScreen(),
        '/pos/orders': (context) => const OrderHistoryScreen(),
        '/shift': (context) => const ActiveShiftScreen(),
        '/shift/open': (context) => const OpenShiftScreen(),
        '/crm/customer-picker': (context) => const CustomerPickerScreen(),
        '/calculator/mobile': (context) => const MobileCalculatorScreen(),
        '/ai/chat': (context) => const AiAssistantChatScreen(),
        '/billing/upgrade': (context) => const BillingUpgradeScreen(),
        '/settings': (context) => const AppSettingsScreen(),
        '/settings/printer': (context) => const PrinterSettingsScreen(),
      },
    );
  }
}

