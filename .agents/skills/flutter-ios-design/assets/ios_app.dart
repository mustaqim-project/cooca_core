// App shells that deliver the iOS look on BOTH iOS and Android. Verify against the project's Flutter version.
//
//  IOSApp        — pure CupertinoApp. Use when the UI is Cupertino widgets + your own components only.
//  IOSHybridApp  — MaterialApp wearing Cupertino clothes (accent comes from IOSColors.accent). Use when any dependency needs a Material ancestor
//                  (TextField, Slider, Checkbox, showDatePicker, many third-party packages, file pickers, charts...).
//                  You'll see "No Material widget found" or "No MaterialLocalizations found" if you need this one.
//
// Both: iOS page transitions + swipe-back on every platform, rubber-band scrolling, edge-to-edge system bars,
// status/navigation bar icon contrast, and the localization delegates you pass in.
//
// Localization (important for Indonesian apps): add `flutter_localizations` (sdk: flutter) and pass
//   localizationsDelegates: const [GlobalMaterialLocalizations.delegate, GlobalWidgetsLocalizations.delegate, GlobalCupertinoLocalizations.delegate]
//   supportedLocales: const [Locale('id'), Locale('en')]
// Without them, Cupertino date pickers, dialogs' default labels, and text-selection toolbars fall back to English
// or throw on non-English locales.

import 'package:flutter/cupertino.dart';
import 'package:flutter/foundation.dart' show TargetPlatform;
import 'package:flutter/material.dart' show ColorScheme, MaterialApp, NoSplash, PageTransitionsTheme, ThemeData, ThemeMode;

import 'ios_design_system.dart';
import 'ios_platform.dart';

class IOSApp extends StatelessWidget {
  const IOSApp({
    super.key,
    required this.home,
    this.title = '',
    this.localizationsDelegates,
    this.supportedLocales = const [Locale('en', 'US')],
    this.locale,
    this.routes = const <String, WidgetBuilder>{},
    this.navigatorObservers = const <NavigatorObserver>[],
    this.theme,
  });

  final Widget home;
  final String title;
  final Iterable<LocalizationsDelegate<dynamic>>? localizationsDelegates;
  final Iterable<Locale> supportedLocales;
  final Locale? locale;
  final Map<String, WidgetBuilder> routes;
  final List<NavigatorObserver> navigatorObservers;
  final CupertinoThemeData? theme;

  @override
  Widget build(BuildContext context) {
    return CupertinoApp(
      title: title,
      theme: theme ?? buildIOSTheme(),
      home: home,
      routes: routes,
      navigatorObservers: navigatorObservers,
      locale: locale,
      supportedLocales: supportedLocales,
      localizationsDelegates: localizationsDelegates,
      scrollBehavior: const IOSScrollBehavior(),
      debugShowCheckedModeBanner: false,
      // CupertinoPageRoute (default here) gives iOS push/pop + swipe-back on Android too.
      builder: (context, child) => IOSSystemUiScope(child: child ?? const SizedBox.shrink()),
    );
  }
}

class IOSHybridApp extends StatelessWidget {
  const IOSHybridApp({
    super.key,
    required this.home,
    this.title = '',
    this.localizationsDelegates,
    this.supportedLocales = const [Locale('en', 'US')],
    this.locale,
    this.routes = const <String, WidgetBuilder>{},
    this.navigatorObservers = const <NavigatorObserver>[],
    this.themeMode = ThemeMode.system,
    this.forceIOSPlatform = false,
  });

  final Widget home;
  final String title;
  final Iterable<LocalizationsDelegate<dynamic>>? localizationsDelegates;
  final Iterable<Locale> supportedLocales;
  final Locale? locale;
  final Map<String, WidgetBuilder> routes;
  final List<NavigatorObserver> navigatorObservers;
  final ThemeMode themeMode;

  /// Sets ThemeData.platform to iOS so Material-based widgets that read Theme.of(context).platform
  /// (scroll physics, adaptive switches, text selection controls) behave like iOS on Android too.
  /// It does NOT affect widgets that read defaultTargetPlatform directly, so test on a real Android device.
  final bool forceIOSPlatform;

  ThemeData _material(Brightness b) {
    final dark = b == Brightness.dark;
    return ThemeData(
      useMaterial3: true,
      platform: forceIOSPlatform ? TargetPlatform.iOS : null,
      colorScheme: dark ? ColorScheme.dark(primary: IOSColors.accent.darkColor) : ColorScheme.light(primary: IOSColors.accent.color),
      fontFamily: IOSPlatform.fontFamily,
      fontFamilyFallback: IOSPlatform.fontFamilyFallback,
      splashFactory: NoSplash.splashFactory, // iOS has no ripple; press = opacity change
      highlightColor: const Color(0x00000000),
      scaffoldBackgroundColor: dark ? const Color(0xFF000000) : const Color(0xFFF2F2F7),
      pageTransitionsTheme: PageTransitionsTheme(builders: {
        for (final p in TargetPlatform.values) p: const CupertinoPageTransitionsBuilder(),
      }),
    );
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: title,
      theme: _material(Brightness.light),
      darkTheme: _material(Brightness.dark),
      themeMode: themeMode,
      home: home,
      routes: routes,
      navigatorObservers: navigatorObservers,
      locale: locale,
      supportedLocales: supportedLocales,
      localizationsDelegates: localizationsDelegates,
      scrollBehavior: const IOSScrollBehavior(),
      debugShowCheckedModeBanner: false,
      builder: (context, child) {
        // Re-apply our full Cupertino theme (fonts per slot, bar colors) over the Material-derived one.
        final brightness = MediaQuery.platformBrightnessOf(context);
        final b = switch (themeMode) {
          ThemeMode.light => Brightness.light,
          ThemeMode.dark => Brightness.dark,
          ThemeMode.system => brightness,
        };
        return CupertinoTheme(
          data: buildIOSTheme(brightness: b),
          child: IOSSystemUiScope(brightness: b, child: child ?? const SizedBox.shrink()),
        );
      },
    );
  }
}
