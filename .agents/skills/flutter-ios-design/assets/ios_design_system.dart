// iOS-look design system for Flutter (iOS + Android) — tokens, theme, and base components.
// Adapt names/brand accent to the project. Verify against the project's Flutter version.
// Requires ios_platform.dart (fonts/tracking per platform). Bundle the Inter font for Android (see android-ios-look.md).
//
// Contents: 1 Spacing · 2 Radius · 3 Colors · 4 Text styles · 5 Theme · 6 Components

import 'package:flutter/cupertino.dart';
import 'package:flutter/services.dart';

import 'ios_platform.dart';

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
// Sizes follow the iOS text style scale (identical on iOS and Android).
// iOS/macOS: system font (SF Pro) applies automatically; fontFamily stays null and Apple tracking is used.
// Android/others: bundled 'Inter' with approximated tracking (IOSPlatform.trackingFor).
// These are getters (not const) because the font depends on the platform at runtime.
abstract final class IOSText {
  static TextStyle _s(double size, FontWeight weight, double appleTracking, {double? height}) => TextStyle(
        fontFamily: IOSPlatform.fontFamily,
        fontFamilyFallback: IOSPlatform.fontFamilyFallback,
        fontSize: size,
        fontWeight: weight,
        letterSpacing: IOSPlatform.trackingFor(size, appleTracking),
        height: height,
      );

  static TextStyle get largeTitle => _s(34, FontWeight.w700, 0.4);
  static TextStyle get title1 => _s(28, FontWeight.w400, 0.38);
  static TextStyle get title2 => _s(22, FontWeight.w400, -0.26);
  static TextStyle get title3 => _s(20, FontWeight.w400, -0.45);
  static TextStyle get headline => _s(17, FontWeight.w600, -0.43);
  static TextStyle get body => _s(17, FontWeight.w400, -0.43);
  static TextStyle get callout => _s(16, FontWeight.w400, -0.31);
  static TextStyle get subhead => _s(15, FontWeight.w400, -0.23);
  static TextStyle get footnote => _s(13, FontWeight.w400, -0.08);
  static TextStyle get caption1 => _s(12, FontWeight.w400, 0);
  static TextStyle get caption2 => _s(11, FontWeight.w400, 0.06);

  /// Secondary text helper: apply the dynamic secondary label color.
  static TextStyle secondary(BuildContext context, TextStyle base) =>
      base.copyWith(color: IOSColors.secondaryLabel.resolveFrom(context));

  /// Prices, quantities, timers: stops digits from jittering.
  static TextStyle tabular(TextStyle base) => base.copyWith(fontFeatures: IOSPlatform.tabular);
}

// ───────────────────────── 5. Theme ─────────────────────────
// On Android the Cupertino defaults reference '.SF Pro ...' families that don't exist there and silently fall
// back to Roboto, so EVERY Cupertino text slot is set explicitly (nav bar, tab bar, pickers, actions).
CupertinoThemeData buildIOSTheme({Brightness? brightness}) {
  const label = CupertinoColors.label;
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
      textStyle: IOSText.body.copyWith(color: label),
      actionTextStyle: IOSText.body.copyWith(color: IOSColors.accent),
      navTitleTextStyle: IOSText.headline.copyWith(color: label),
      navLargeTitleTextStyle: IOSText.largeTitle.copyWith(color: label),
      navActionTextStyle: IOSText.body.copyWith(color: IOSColors.accent),
      tabLabelTextStyle: IOSText.caption2.copyWith(
        fontWeight: FontWeight.w500,
        color: CupertinoColors.inactiveGray,
      ),
      pickerTextStyle: IOSText.title3.copyWith(color: label),
      dateTimePickerTextStyle: IOSText.title3.copyWith(color: label),
    ),
  );
}

// ───────────────────────── 6. Components ─────────────────────────

// ── Buttons and small controls: few variants, every state styled ──────────────
// Three kinds only (filled, tinted, plain) x two sizes (large, regular), plus destructive as a color modifier.
// No outlined, gradient, shadowed, or icon+badge variants on purpose: fewer choices = calmer screens.
// States covered: default, pressed (opacity), disabled, loading (width never jumps), dark mode (semantic colors),
// Dynamic Type / font scale (grows instead of clipping), Android 48 dp hit area, haptic on filled actions.

enum IOSButtonKind { filled, tinted, plain }

enum IOSButtonSize { large, regular }

class IOSButton extends StatelessWidget {
  const IOSButton(
    this.label, {
    super.key,
    required this.onPressed,
    this.kind = IOSButtonKind.filled,
    this.size = IOSButtonSize.large,
    this.destructive = false,
    this.loading = false,
    this.icon,
    this.expand,
  });

  final String label; // verb + object, max two words: "Simpan", "Tambah item"
  final VoidCallback? onPressed; // null = disabled
  final IOSButtonKind kind;
  final IOSButtonSize size;
  final bool destructive;
  final bool loading;
  final IconData? icon; // only when it adds meaning
  final bool? expand; // default: large = full width, regular = hugs content

  @override
  Widget build(BuildContext context) {
    final large = size == IOSButtonSize.large;
    final active = onPressed != null; // loading keeps the active look
    final tappable = active && !loading;
    final base = (destructive ? IOSColors.destructive : IOSColors.accent).resolveFrom(context);
    final disabledFg = IOSColors.tertiaryLabel.resolveFrom(context);
    final disabledBg = CupertinoColors.tertiarySystemFill.resolveFrom(context);

    final Color? bg = switch (kind) {
      IOSButtonKind.filled => active ? base : disabledBg,
      IOSButtonKind.tinted => active ? base.withValues(alpha: 0.14) : disabledBg,
      IOSButtonKind.plain => null,
    };
    final Color fg = switch (kind) {
      IOSButtonKind.filled => active ? CupertinoColors.white : disabledFg,
      _ => active ? base : disabledFg,
    };

    final style = (large ? IOSText.headline : IOSText.body.copyWith(fontWeight: FontWeight.w600)).copyWith(color: fg);
    final content = Row(
      mainAxisSize: MainAxisSize.min,
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (icon != null) ...[Icon(icon, size: 20, color: fg), const SizedBox(width: IOSSpacing.xxs + 2)],
        Flexible(child: Text(label, style: style, textAlign: TextAlign.center, maxLines: 2, overflow: TextOverflow.ellipsis)),
      ],
    );

    Widget button = ConstrainedBox(
      // minHeight (not a fixed height) so large text scales the button instead of clipping the label.
      constraints: BoxConstraints(minHeight: large ? 50 : IOSPlatform.minTarget),
      child: CupertinoButton(
        padding: EdgeInsets.symmetric(horizontal: large ? IOSSpacing.lg : IOSSpacing.md, vertical: IOSSpacing.sm),
        color: bg,
        disabledColor: bg ?? const Color(0x00000000),
        borderRadius: IOSRadius.buttonRadius,
        pressedOpacity: 0.6,
        onPressed: tappable
            ? () {
                if (kind == IOSButtonKind.filled) HapticFeedback.lightImpact();
                onPressed!();
              }
            : null,
        child: Stack(
          alignment: Alignment.center,
          children: [
            Opacity(opacity: loading ? 0 : 1, child: content), // keeps width stable while loading
            if (loading) CupertinoActivityIndicator(color: fg),
          ],
        ),
      ),
    );

    if (expand ?? large) button = SizedBox(width: double.infinity, child: button);
    return Semantics(
      container: true,
      label: loading ? '$label, memuat' : null, // adapt the wording to the app language
      child: button,
    );
  }
}

/// Backward-compatible alias: the full-width primary action (one per screen).
class IOSPrimaryButton extends StatelessWidget {
  const IOSPrimaryButton({super.key, required this.label, required this.onPressed, this.destructive = false});

  final String label;
  final VoidCallback? onPressed;
  final bool destructive;

  @override
  Widget build(BuildContext context) => IOSButton(label, onPressed: onPressed, destructive: destructive);
}

/// Icon-only action. The semantic label is REQUIRED (VoiceOver/TalkBack). Hit area is 44 pt / 48 dp;
/// `filled: true` draws the small gray circle used by close / more buttons.
class IOSIconButton extends StatelessWidget {
  const IOSIconButton(
    this.icon, {
    super.key,
    required this.onPressed,
    required this.semanticLabel,
    this.color,
    this.filled = false,
    this.iconSize = 22,
  });

  final IconData icon;
  final VoidCallback? onPressed;
  final String semanticLabel;
  final Color? color;
  final bool filled;
  final double iconSize;

  @override
  Widget build(BuildContext context) {
    final enabled = onPressed != null;
    final fg = enabled
        ? (color ?? IOSColors.accent.resolveFrom(context))
        : IOSColors.tertiaryLabel.resolveFrom(context);
    final glyph = Icon(icon, size: iconSize, color: fg);
    return Semantics(
      button: true,
      enabled: enabled,
      label: semanticLabel,
      excludeSemantics: true,
      child: CupertinoButton(
        padding: EdgeInsets.zero,
        pressedOpacity: 0.5,
        onPressed: onPressed,
        child: SizedBox(
          width: IOSPlatform.minTarget,
          height: IOSPlatform.minTarget,
          child: Center(
            child: filled
                ? DecoratedBox(
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: CupertinoColors.tertiarySystemFill.resolveFrom(context),
                    ),
                    child: SizedBox(width: 32, height: 32, child: Center(child: glyph)),
                  )
                : glyph,
          ),
        ),
      ),
    );
  }
}

/// Selectable pill for filters. Keep a row to <= 6 visible chips; beyond that use a "Filter" button + sheet.
class IOSChip extends StatelessWidget {
  const IOSChip(this.label, {super.key, required this.selected, required this.onTap});

  final String label;
  final bool selected;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final accent = IOSColors.accent.resolveFrom(context);
    return Semantics(
      button: true,
      selected: selected,
      child: CupertinoButton(
        padding: EdgeInsets.zero,
        pressedOpacity: 0.6,
        onPressed: onTap == null
            ? null
            : () {
                HapticFeedback.selectionClick();
                onTap!();
              },
        child: ConstrainedBox(
          constraints: BoxConstraints(minHeight: IOSPlatform.minTarget),
          child: Center(
            widthFactor: 1,
            child: DecoratedBox(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(999),
                color: selected ? accent : CupertinoColors.tertiarySystemFill.resolveFrom(context),
              ),
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                child: Text(
                  label,
                  style: IOSText.subhead.copyWith(
                    fontWeight: FontWeight.w500,
                    color: selected ? CupertinoColors.white : IOSColors.label.resolveFrom(context),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// One entry of an overflow menu. Secondary and rare actions live here instead of as extra visible buttons.
class IOSMenuAction {
  const IOSMenuAction(this.label, this.onTap, {this.destructive = false});

  final String label;
  final VoidCallback onTap;
  final bool destructive;
}

/// Overflow menu as an iOS action sheet (use a popover anchored to the trigger on regular/wide widths).
Future<void> showIOSActionMenu(
  BuildContext context, {
  String? title,
  required List<IOSMenuAction> actions,
  required String cancelLabel,
}) {
  return showCupertinoModalPopup<void>(
    context: context,
    builder: (ctx) => CupertinoActionSheet(
      title: title == null ? null : Text(title),
      actions: [
        for (final a in actions)
          CupertinoActionSheetAction(
            isDestructiveAction: a.destructive,
            onPressed: () {
              Navigator.pop(ctx);
              a.onTap();
            },
            child: Text(a.label),
          ),
      ],
      cancelButton: CupertinoActionSheetAction(onPressed: () => Navigator.pop(ctx), child: Text(cancelLabel)),
    ),
  );
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
