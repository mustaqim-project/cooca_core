import 'package:flutter/material.dart';

/// Cooca UMKM Mobile - Apple HIG v2.0 Design Tokens
/// System Blue primary, Apple System Gray palette, Inter (SF Pro-compatible).
class AppColors {
  AppColors._();

  // ── Backgrounds (Apple System Gray ladder, dark) ───────────────
  static const Color background =
      Color(0xFF1E1E1E); // window background (Apple dark)
  static const Color surfaceDeep =
      Color(0xFF1C1C1E); // gray6 - secondary system background
  static const Color surface =
      Color(0xFF2C2C2E); // gray5 - tertiary / card surface
  static const Color surfaceElevated =
      Color(0xFF3A3A3C); // gray4 - elevated surface
  static const Color surfaceHigh =
      Color(0xFF48484A); // gray3 - high-contrast surface

  // ── Brand / Primary: System Blue (Apple HIG §3.1) ─────────────
  static const Color primary = Color(0xFF007AFF); // System Blue (light)
  static const Color primaryLight = Color(0xFF0A84FF); // System Blue (dark)
  static const Color primaryDark =
      Color(0xFF0062CC); // pressed/selected darker tone
  static const Color primaryDeep = Color(0xFF004C99);
  static const Color primaryGlow = Color(0x33007AFF); // System Blue /20
  static const Color badgeGreen =
      Color(0xFF34C759); // retro-compat: System Green (success only)

  // ── Teal (POS/Kasir accent - Apple System Teal) ────────────────
  static const Color teal = Color(0xFF30B0C7); // System Teal
  static const Color tealDark = Color(0xFF2A8E9E);
  static const Color tealGlow = Color(0x3330B0C7);
  static const Color accent = Color(0xFF30B0C7); // alias teal/accent

  // ── Purple (AI / Billing - Apple System Purple) ────────────────
  static const Color purple = Color(0xFFAF52DE);
  static const Color purpleLight = Color(0xFFBF5AF2);
  static const Color purpleGlow = Color(0x33AF52DE);
  static const Color badgePurple = Color(0xFFAF52DE);

  // ── Cyan / Teal-ALT (Inventory) ────────────────────────────────
  static const Color cyan = Color(0xFF40C8E0); // System Teal (dark variant)
  static const Color cyanGlow = Color(0x3340C8E0);

  // ── Amber (Finance/Reports - Apple System Orange) ──────────────
  static const Color amber = Color(0xFFFF9F0A); // System Orange (dark)
  static const Color amberDark = Color(0xFFFF9500); // System Orange (light)
  static const Color amberGlow = Color(0x33FF9F0A);

  // ── Indigo (CRM - Apple System Indigo) ─────────────────────────
  static const Color indigo = Color(0xFF5E5CE6); // System Indigo (dark)
  static const Color indigoDark = Color(0xFF5856D6); // System Indigo (light)
  static const Color indigoGlow = Color(0x335E5CE6);

  // ── Rose (Expense/Danger - Apple System Red) ───────────────────
  static const Color rose = Color(0xFFFF6961); // System Red (light)
  static const Color roseDark = Color(0xFFFF453A); // System Red (dark)
  static const Color roseGlow = Color(0x33FF453A);

  // ── Semantic (one color = one meaning, Apple HIG §3.2) ─────────
  static const Color success =
      Color(0xFF34C759); // System Green - Success/Profit only
  static const Color successBg = Color(0x2234C759);
  static const Color warning =
      Color(0xFFFF9F0A); // System Orange - Pending/Warning
  static const Color warningBg = Color(0x22FF9F0A);
  static const Color danger =
      Color(0xFFFF453A); // System Red - Danger/Delete/Loss
  static const Color dangerBg = Color(0x22FF453A);
  static const Color info =
      Color(0xFF5E5CE6); // System Indigo - Info/Processing
  static const Color infoBg = Color(0x225E5CE6);

  // ── Text (Apple label levels, dark) ────────────────────────────
  static const Color textPrimary = Color(0xFFFFFFFF); // primary label
  static const Color textSecondary =
      Color(0xFFAEAEB2); // gray2 - secondary label (~white/60)
  static const Color textMuted = Color(0xFF8E8E93); // gray - tertiary label
  static const Color textDim =
      Color(0xFF636366); // gray2 dark - disabled/hairline text
  static const Color textOnDark = Color(0xFFFFFFFF);

  // ── Border (separators) ───────────────────────────────────────
  static const Color border = Color(0xFF48484A); // gray3 hairline separator
  static const Color borderLight = Color(0xFF3A3A3C); // gray4
  static const Color borderGlass = Color(0x14FFFFFF); // white/8

  // ── Glass Morphism (Apple materials §3.5) ──────────────────────
  static const Color glassNav =
      Color(0xD91C1C1E); // rgba(28,28,30,0.85) - regularMaterial
  static const Color glassCard =
      Color(0xB32C2C2E); // rgba(44,44,46,0.7) - thinMaterial
  static const Color glassBorder = Color(0x0FFFFFFF); // white/6

  // ── Gradients ──────────────────────────────────────────────────
  static const LinearGradient primaryGradient = LinearGradient(
    colors: [Color(0xFF007AFF), Color(0xFF30B0C7)], // System Blue → System Teal
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient logoGradient = LinearGradient(
    colors: [
      Color(0xFF0A84FF),
      Color(0xFF30B0C7)
    ], // System Blue (dark) → System Teal
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient purpleGradient = LinearGradient(
    colors: [Color(0xFFAF52DE), Color(0xFF5E5CE6)], // purple → indigo
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient amberGradient = LinearGradient(
    colors: [Color(0xFFFF9F0A), Color(0xFFFF453A)], // orange → red
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient backgroundGradient = LinearGradient(
    colors: [Color(0xFF1E1E1E), Color(0xFF17171A)], // window → deep
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );

  // ── Chart Colors (Apple System palette) ────────────────────────
  static const List<Color> chartPalette = [
    Color(0xFF007AFF), // System Blue
    Color(0xFF30B0C7), // System Teal
    Color(0xFFFF9F0A), // System Orange
    Color(0xFF5E5CE6), // System Indigo
    Color(0xFFFF453A), // System Red
    Color(0xFF40C8E0), // System Teal (alt)
    Color(0xFFAF52DE), // System Purple
  ];
}
