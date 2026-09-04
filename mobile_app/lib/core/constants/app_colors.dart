import 'package:flutter/material.dart';

/// Cooca UMKM Mobile — Design Tokens
/// Identik dengan web: bg-slate-950, emerald brand, Plus Jakarta Sans
class AppColors {
  AppColors._();

  // ── Backgrounds (Tailwind Slate) ──────────────────────────────
  static const Color background      = Color(0xFF020617); // slate-950
  static const Color surfaceDeep     = Color(0xFF0F172A); // slate-900
  static const Color surface         = Color(0xFF1E293B); // slate-800
  static const Color surfaceElevated = Color(0xFF334155); // slate-700
  static const Color surfaceHigh     = Color(0xFF475569); // slate-600

  // ── Brand: Emerald (sama seperti web) ─────────────────────────
  static const Color primary         = Color(0xFF22C55E); // emerald-500
  static const Color primaryLight    = Color(0xFF4ADE80); // emerald-400
  static const Color primaryDark     = Color(0xFF16A34A); // emerald-600
  static const Color primaryDeep     = Color(0xFF15803D); // emerald-700
  static const Color primaryGlow     = Color(0x3322C55E); // emerald-500/20
  static const Color badgeGreen      = Color(0xFF22C55E);

  // ── Teal (POS/Kasir accent) ───────────────────────────────────
  static const Color teal            = Color(0xFF2DD4BF); // teal-400
  static const Color tealDark        = Color(0xFF0D9488); // teal-600
  static const Color tealGlow        = Color(0x332DD4BF);
  static const Color accent          = Color(0xFF2DD4BF); // alias for teal/accent

  // ── Purple (AI / Billing) ─────────────────────────────────────
  static const Color purple          = Color(0xFFA855F7); // purple-500
  static const Color purpleLight     = Color(0xFFC084FC); // purple-400
  static const Color purpleGlow      = Color(0x33A855F7);
  static const Color badgePurple     = Color(0xFFA855F7);

  // ── Cyan (Inventory) ─────────────────────────────────────────
  static const Color cyan            = Color(0xFF22D3EE); // cyan-400
  static const Color cyanGlow        = Color(0x3322D3EE);

  // ── Amber (Finance/Reports) ───────────────────────────────────
  static const Color amber           = Color(0xFFFBBF24); // amber-400
  static const Color amberDark       = Color(0xFFF59E0B); // amber-500
  static const Color amberGlow       = Color(0x33F59E0B);

  // ── Indigo (CRM) ─────────────────────────────────────────────
  static const Color indigo          = Color(0xFF818CF8); // indigo-400
  static const Color indigoDark      = Color(0xFF6366F1); // indigo-500
  static const Color indigoGlow      = Color(0x336366F1);

  // ── Rose (Expense/Danger) ─────────────────────────────────────
  static const Color rose            = Color(0xFFFB7185); // rose-400
  static const Color roseDark        = Color(0xFFF43F5E); // rose-500
  static const Color roseGlow        = Color(0x33F43F5E);

  // ── Semantic ─────────────────────────────────────────────────
  static const Color success         = Color(0xFF22C55E); // emerald = success
  static const Color successBg       = Color(0x2222C55E);
  static const Color warning         = Color(0xFFFBBF24); // amber
  static const Color warningBg       = Color(0x22F59E0B);
  static const Color danger          = Color(0xFFF43F5E); // rose
  static const Color dangerBg        = Color(0x22F43F5E);
  static const Color info            = Color(0xFF38BDF8); // sky-400
  static const Color infoBg          = Color(0x2238BDF8);

  // ── Text (Tailwind Slate) ─────────────────────────────────────
  static const Color textPrimary     = Color(0xFFF1F5F9); // slate-100
  static const Color textSecondary   = Color(0xFFCBD5E1); // slate-300
  static const Color textMuted       = Color(0xFF94A3B8); // slate-400
  static const Color textDim         = Color(0xFF64748B); // slate-500
  static const Color textOnDark      = Color(0xFFFFFFFF);

  // ── Border ───────────────────────────────────────────────────
  static const Color border          = Color(0xFF1E293B); // slate-800
  static const Color borderLight     = Color(0xFF334155); // slate-700
  static const Color borderGlass     = Color(0x14FFFFFF); // white/8

  // ── Glass Morphism (sama seperti web) ─────────────────────────
  static const Color glassNav        = Color(0xD90F172A); // rgba(15,23,42,0.85)
  static const Color glassCard       = Color(0xB31E293B); // rgba(30,41,59,0.7)
  static const Color glassBorder     = Color(0x0FFFFFFF); // white/6

  // ── Gradients ────────────────────────────────────────────────
  static const LinearGradient primaryGradient = LinearGradient(
    colors: [Color(0xFF22C55E), Color(0xFF2DD4BF)], // emerald → teal (sama logo web)
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient logoGradient = LinearGradient(
    colors: [Color(0xFF16A34A), Color(0xFF2DD4BF)], // from-emerald-600 to-teal-400
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient purpleGradient = LinearGradient(
    colors: [Color(0xFFA855F7), Color(0xFF6366F1)], // purple → indigo
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient amberGradient = LinearGradient(
    colors: [Color(0xFFF59E0B), Color(0xFFFB7185)], // amber → rose
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient backgroundGradient = LinearGradient(
    colors: [Color(0xFF020617), Color(0xFF0F172A)], // slate-950 → slate-900
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );

  // ── Chart Colors ─────────────────────────────────────────────
  static const List<Color> chartPalette = [
    Color(0xFF22C55E), // emerald
    Color(0xFF2DD4BF), // teal
    Color(0xFFFBBF24), // amber
    Color(0xFF818CF8), // indigo
    Color(0xFFFB7185), // rose
    Color(0xFF22D3EE), // cyan
    Color(0xFFA855F7), // purple
  ];
}

