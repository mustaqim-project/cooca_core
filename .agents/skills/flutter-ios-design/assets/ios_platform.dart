// Platform layer: makes the iOS look hold up on Android (and iOS itself).
// No dependency on the design system, so ios_design_system.dart can import this file.
// Verify against the project's Flutter version. Contents:
// 1 IOSPlatform (fonts, tracking) · 2 IOSScrollBehavior · 3 IOSSystemUi (edge-to-edge, bar icons)

import 'dart:ui' show FontFeature, PointerDeviceKind;

import 'package:flutter/cupertino.dart';
import 'package:flutter/foundation.dart' show TargetPlatform, defaultTargetPlatform, kIsWeb;
import 'package:flutter/services.dart';

// ───────────────────────── 1. Platform, fonts, tracking ─────────────────────────
abstract final class IOSPlatform {
  /// True on iOS/macOS, where the system font IS San Francisco.
  static bool get isApple =>
      !kIsWeb &&
      (defaultTargetPlatform == TargetPlatform.iOS || defaultTargetPlatform == TargetPlatform.macOS);

  static bool get isAndroid => !kIsWeb && defaultTargetPlatform == TargetPlatform.android;

  /// Minimum tappable size: 44 pt on iOS (HIG), 48 dp on Android. Visual size can stay smaller; the hit area may not.
  static double get minTarget => isAndroid ? 48 : 44;

  /// null on Apple platforms -> the system font (SF Pro) is used automatically.
  /// Elsewhere use a bundled SF-like font. Never bundle SF itself: Apple's license limits it to Apple platforms.
  /// Add Inter (OFL license) under `fonts:` in pubspec.yaml with family name 'Inter'.
  static String? get fontFamily => isApple ? null : 'Inter';

  /// Fallbacks if the bundled font is missing a glyph (e.g., rare symbols).
  static List<String>? get fontFamilyFallback => isApple ? null : const ['Roboto', 'sans-serif'];

  /// Letter-spacing in logical px for a given font size.
  /// Apple: SF Pro tracking table values are baked into IOSText. Elsewhere: Inter needs slight negative
  /// tracking as size grows (approximation, tune by eye).
  static double trackingFor(double size, double appleTracking) {
    if (isApple) return appleTracking;
    if (size <= 12) return 0;
    if (size <= 17) return size * -0.011;
    if (size <= 24) return size * -0.019;
    return size * -0.022;
  }

  /// Tabular figures keep prices, quantities, and timers from jittering. Inter supports it; SF does too.
  static const List<FontFeature> tabular = [FontFeature.tabularFigures()];
}

// ───────────────────────── 2. Scroll behavior ─────────────────────────
/// iOS-style rubber-band scrolling on every platform, with mouse/trackpad/stylus drag for tablets and desktop.
/// Needed so CupertinoSliverRefreshControl and large-title collapse feel right on Android: they depend on
/// overscroll, which Android's default clamping physics doesn't provide.
/// To keep native Android scrolling instead, remove this behavior and accept that pull-to-refresh needs
/// a different widget on Android.
class IOSScrollBehavior extends CupertinoScrollBehavior {
  const IOSScrollBehavior();

  @override
  ScrollPhysics getScrollPhysics(BuildContext context) =>
      const BouncingScrollPhysics(parent: AlwaysScrollableScrollPhysics());

  @override
  Set<PointerDeviceKind> get dragDevices => const {
        PointerDeviceKind.touch,
        PointerDeviceKind.mouse,
        PointerDeviceKind.trackpad,
        PointerDeviceKind.stylus,
        PointerDeviceKind.invertedStylus,
      };
}

// ───────────────────────── 3. System bars / edge-to-edge ─────────────────────────
abstract final class IOSSystemUi {
  /// Call once in main() after WidgetsFlutterBinding.ensureInitialized().
  /// Android 15 (target SDK 35) enforces edge-to-edge anyway; opting in everywhere keeps behavior consistent
  /// and lets translucent bars and large titles draw under the status bar like iOS.
  static Future<void> init() async {
    await SystemChrome.setEnabledSystemUIMode(SystemUiMode.edgeToEdge);
  }

  static SystemUiOverlayStyle styleFor(Brightness brightness) {
    final dark = brightness == Brightness.dark;
    return SystemUiOverlayStyle(
      statusBarColor: const Color(0x00000000),
      statusBarIconBrightness: dark ? Brightness.light : Brightness.dark, // Android
      statusBarBrightness: dark ? Brightness.dark : Brightness.light, // iOS (inverse meaning)
      systemNavigationBarColor: const Color(0x00000000),
      systemNavigationBarDividerColor: const Color(0x00000000),
      systemNavigationBarIconBrightness: dark ? Brightness.light : Brightness.dark,
      systemNavigationBarContrastEnforced: false, // removes Android's translucent 3-button scrim
    );
  }
}

/// Wrap the app (via CupertinoApp.builder) or any full-screen page so status/navigation bar icons
/// follow light/dark. Keeps iOS and Android visually consistent.
class IOSSystemUiScope extends StatelessWidget {
  const IOSSystemUiScope({super.key, required this.child, this.brightness});

  final Widget child;

  /// Override for pages with a dark hero/photo header. Null = follow the current theme.
  final Brightness? brightness;

  @override
  Widget build(BuildContext context) {
    final b = brightness ?? CupertinoTheme.brightnessOf(context);
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: IOSSystemUi.styleFor(b),
      child: child,
    );
  }
}
