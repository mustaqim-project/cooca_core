// iOS design system for Flutter — tokens, theme, and base components.
// Adapt names/brand accent to the project. Verify against the project's Flutter version.
//
// Contents: 1 Spacing · 2 Radius · 3 Colors · 4 Text styles · 5 Theme · 6 Components

import 'package:flutter/cupertino.dart';
import 'package:flutter/services.dart';

// ───────────────────────── 1. Spacing (4-pt grid) ─────────────────────────
abstract final class IOSSpacing {
  static const double xxs = 4;
  static const double xs = 8;
  static const double sm = 12;
  static const double md = 16; // standard screen margin
  static const double lg = 20;
  static const double xl = 24;
  static const double xxl = 32;

  static const EdgeInsets screen = EdgeInsets.symmetric(horizontal: md);
  static const double minTouchTarget = 44;
}

// ───────────────────────── 2. Radius ─────────────────────────
abstract final class IOSRadius {
  static const double cell = 10; // inset grouped list cells
  static const double card = 12;
  static const double button = 14;
  static const double sheet = 20;

  static BorderRadius get cardRadius => BorderRadius.circular(card);
  static BorderRadius get buttonRadius => BorderRadius.circular(button);
}

// ───────────────────────── 3. Colors ─────────────────────────
// Prefer CupertinoColors.* semantic colors; they resolve for light/dark automatically.
// Only the brand accent is custom. Resolve with: IOSColors.accent.resolveFrom(context)
abstract final class IOSColors {
  // Replace with the brand color; keep a lighter/brighter variant for dark mode.
  static const CupertinoDynamicColor accent = CupertinoDynamicColor.withBrightness(
    color: Color(0xFF007AFF), // default iOS blue (light)
    darkColor: Color(0xFF0A84FF), // default iOS blue (dark)
  );

  static const CupertinoDynamicColor groupedBackground =
      CupertinoColors.systemGroupedBackground;
  static const CupertinoDynamicColor groupedCell =
      CupertinoColors.secondarySystemGroupedBackground;
  static const CupertinoDynamicColor label = CupertinoColors.label;
  static const CupertinoDynamicColor secondaryLabel = CupertinoColors.secondaryLabel;
  static const CupertinoDynamicColor tertiaryLabel = CupertinoColors.tertiaryLabel;
  static const CupertinoDynamicColor separator = CupertinoColors.separator;
  static const CupertinoDynamicColor destructive = CupertinoColors.systemRed;
  static const CupertinoDynamicColor success = CupertinoColors.systemGreen;
  static const CupertinoDynamicColor warning = CupertinoColors.systemOrange;
}

// ───────────────────────── 4. Text styles ─────────────────────────
// Sizes follow the iOS text style scale. Letter-spacing values approximate SF Pro tracking.
// On iOS the system font (SF) is applied automatically by CupertinoTheme; do not set fontFamily.
// Colors are applied at theme level so text is dark-mode aware.
abstract final class IOSText {
  static const TextStyle largeTitle = TextStyle(fontSize: 34, fontWeight: FontWeight.w700, letterSpacing: 0.4);
  static const TextStyle title1 = TextStyle(fontSize: 28, fontWeight: FontWeight.w400, letterSpacing: 0.38);
  static const TextStyle title2 = TextStyle(fontSize: 22, fontWeight: FontWeight.w400, letterSpacing: -0.26);
  static const TextStyle title3 = TextStyle(fontSize: 20, fontWeight: FontWeight.w400, letterSpacing: -0.45);
  static const TextStyle headline = TextStyle(fontSize: 17, fontWeight: FontWeight.w600, letterSpacing: -0.43);
  static const TextStyle body = TextStyle(fontSize: 17, fontWeight: FontWeight.w400, letterSpacing: -0.43);
  static const TextStyle callout = TextStyle(fontSize: 16, fontWeight: FontWeight.w400, letterSpacing: -0.31);
  static const TextStyle subhead = TextStyle(fontSize: 15, fontWeight: FontWeight.w400, letterSpacing: -0.23);
  static const TextStyle footnote = TextStyle(fontSize: 13, fontWeight: FontWeight.w400, letterSpacing: -0.08);
  static const TextStyle caption1 = TextStyle(fontSize: 12, fontWeight: FontWeight.w400);
  static const TextStyle caption2 = TextStyle(fontSize: 11, fontWeight: FontWeight.w400, letterSpacing: 0.06);

  /// Secondary text helper: apply the dynamic secondary label color.
  static TextStyle secondary(BuildContext context, TextStyle base) =>
      base.copyWith(color: IOSColors.secondaryLabel.resolveFrom(context));
}

// ───────────────────────── 5. Theme ─────────────────────────
CupertinoThemeData buildIOSTheme({Brightness? brightness}) {
  return CupertinoThemeData(
    brightness: brightness, // null = follow system
    primaryColor: IOSColors.accent,
    scaffoldBackgroundColor: IOSColors.groupedBackground,
    barBackgroundColor: const CupertinoDynamicColor.withBrightness(
      color: Color(0xCCF9F9F9),
      darkColor: Color(0xCC1D1D1D),
    ), // translucent bars; pair with blur (CupertinoNavigationBar already blurs)
    textTheme: CupertinoTextThemeData(
      primaryColor: IOSColors.accent,
      textStyle: IOSText.body.copyWith(color: IOSColors.label),
    ),
  );
}

// ───────────────────────── 6. Components ─────────────────────────

/// Full-width primary action (one per screen).
class IOSPrimaryButton extends StatelessWidget {
  const IOSPrimaryButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.destructive = false,
    this.haptic = true,
  });

  final String label;
  final VoidCallback? onPressed;
  final bool destructive;
  final bool haptic;

  @override
  Widget build(BuildContext context) {
    final color = destructive
        ? IOSColors.destructive.resolveFrom(context)
        : IOSColors.accent.resolveFrom(context);
    return SizedBox(
      width: double.infinity,
      height: 50,
      child: CupertinoButton(
        padding: EdgeInsets.zero,
        color: color,
        borderRadius: IOSRadius.buttonRadius,
        onPressed: onPressed == null
            ? null
            : () {
                if (haptic) HapticFeedback.lightImpact();
                onPressed!();
              },
        child: Text(
          label,
          style: IOSText.headline.copyWith(color: CupertinoColors.white),
        ),
      ),
    );
  }
}

/// Grouped content surface (replaces Material Card). No elevation; separation via background.
class IOSCard extends StatelessWidget {
  const IOSCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(IOSSpacing.md),
    this.onTap,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final content = DecoratedBox(
      decoration: BoxDecoration(
        color: IOSColors.groupedCell.resolveFrom(context),
        borderRadius: IOSRadius.cardRadius,
      ),
      child: Padding(padding: padding, child: child),
    );
    if (onTap == null) return content;
    return CupertinoButton(
      padding: EdgeInsets.zero,
      pressedOpacity: 0.6,
      onPressed: onTap,
      child: SizedBox(width: double.infinity, child: content),
    );
  }
}

/// Small uppercase section header, used above grouped content outside CupertinoListSection.
class IOSSectionHeader extends StatelessWidget {
  const IOSSectionHeader(this.title, {super.key});
  final String title;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(IOSSpacing.lg, IOSSpacing.lg, IOSSpacing.lg, IOSSpacing.xs),
      child: Text(
        title.toUpperCase(),
        style: IOSText.secondary(context, IOSText.footnote),
      ),
    );
  }
}

/// Hairline separator; inset to align with text start in lists.
class IOSSeparator extends StatelessWidget {
  const IOSSeparator({super.key, this.indent = 0});
  final double indent;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsets.only(left: indent),
      height: 0.5,
      color: IOSColors.separator.resolveFrom(context),
    );
  }
}

/// Empty state: icon + title + optional action. Keep copy short.
class IOSEmptyState extends StatelessWidget {
  const IOSEmptyState({
    super.key,
    required this.icon,
    required this.title,
    this.message,
    this.actionLabel,
    this.onAction,
  });

  final IconData icon;
  final String title;
  final String? message;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(IOSSpacing.xxl),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 48, color: IOSColors.tertiaryLabel.resolveFrom(context)),
            const SizedBox(height: IOSSpacing.md),
            Text(title, style: IOSText.title3, textAlign: TextAlign.center),
            if (message != null) ...[
              const SizedBox(height: IOSSpacing.xs),
              Text(
                message!,
                style: IOSText.secondary(context, IOSText.subhead),
                textAlign: TextAlign.center,
              ),
            ],
            if (actionLabel != null) ...[
              const SizedBox(height: IOSSpacing.lg),
              CupertinoButton(onPressed: onAction, child: Text(actionLabel!)),
            ],
          ],
        ),
      ),
    );
  }
}
