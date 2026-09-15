# AUDIT & BACKLOG REKONSULTASI - MOBILE APP UI/UX vs. APPLE HIG v2.0

> **Status:** Final (voor goedkeuring team) · **Versie:** 1.0 · **Datum:** 2026-09-13
> **Scope:** `mobile_app/` (Flutter `cooca_pos_mobile` v2.0.0) - 34 Dart-bestanden, 17 screens, 4 widgets
> **Standaard:** `docs/prompt.md` - "102 ATURAN STANDAR COOCA UI/UX DESIGN SYSTEM v2.0 - Apple HIG Edition (macOS Sonoma & iOS 18)"
> **Referenties:** Web-panel (sidebar/blade) - `#007AFF` System Blue, `#1C1C1E` dark surfaces, SF-schaal

---

## 1. VERDICT

⚠️ **De mobile-app voldoet NIET aan de COOCA Apple HIG v2.0-standaards.** De app is momenteel een
goed verzorgde **Material-3 (Google)-gestileerde dark-app met Tailwind Emerald-branding**, geënt op de
**OUDERE** web-branding (slate-950 + emerald). De geldende standaard (en het huidige web-panel) eisen
**System Blue `#007AFF`**, Apple system grays, SF Pro-typografie, dual theme, skeleton-loading en
tactiele/haptische respons.

**Globale compliance-schatting: ~35–45%** van de 102 regels.

> ⚠️ **Beslissing PENDING (product/branding):** Emerald (`#22C55E`) was het OUDE merk van de mobiele app;
> de huidige standaard (Regel 3.1, 100.2) eist **System Blue `#007AFF`** als primaire actie-kleur en reserveert
> groen uitsluitend voor **Success/Profit**. Zonder expliciet besluit BLIJFT de backlog op dit punt GEBLOKKEERD
> en kan niet worden geïmplementeerd volgens de afgesproken standaard.

---

## 2. SCORECARD PER DOMEIN

| Domein | Status | Opmerking |
|---|---|---|
| Color System (Apple tokens/System Blue) | ❌ | Emerald primary + slate-950 background |
| Typografie (SF Pro / Dynamic Type) | ⚠️ | Correcte schaal & tabular-token aanwezig, verkeerd lettertype + geen fallback |
| Dual Theme (Licht/Donker) | ❌ | Donker-only; licht thema ontbreekt volledig |
| Materialen / Vibrancy | ⚠️ | Glass aanwezig (nav/appbar); border+schaduw op kaarten |
| Radius-schaal (squircle 8/10/14/16–20/999) | ⚠️ | Grotendeels goed; off-scale 12 en 6 |
| Navigatie | ⚠️ | Apple Sheet/Toast goed; tab-bar regenboog-accents |
| States (loading/empty/error/success) | ⚠️ | Spinner-heavy; sheets/toasts goed; skeletontwerp ontbreekt |
| Haptiek / Motion | ❌ | Geen press-scale/haptics; InkWell-ripples (M3) |
| Toegankelijkheid / Contrast (WCAG 2.1 AA) | ⚠️ | Borderline bij kleine muted-labels |

---

## 3. BACKLOG VAN BEVINDINGEN (MB = Mobile Backlog)

| ID | Prioriteit | Domein | Bevinding & Bewijs | Regel-ref |
|---|---|---|---|---|
| MB-01 | 🔴 P0 | Color tokens | Primary = `#22C55E` emerald → moet **System Blue `#007AFF`** (licht) / `#0A84FF` (donker). Bewijs: `lib/core/constants/app_colors.dart:16` | 3.1, 100.2 |
| MB-02 | 🔴 P0 | Color tokens | Achtergrond `#020617`/`#1E293B` (slate) → Apple grays: base `#000000`/`#1E1E1E`, secondary `#1C1C1E`, tertiary `#2C2C2E`. Bewijs: `app_colors.dart:9-13` | 3.3, 3.4 |
| MB-03 | 🔴 P0 | Color tokens | Dubbele kleurbetekenis: `primary` == `badgeGreen` == cart-badge fill == in-stock badge → **één kleur = één betekenis**. Bewijs: `app_colors.dart:16,21`, `widgets/product_card.dart:122`, `dashboard_screen.dart:409` | 4, 100.4 |
| MB-04 | 🔴 P0 | Dual theme | Alleen `AppTheme.dark` bestaat; geen licht thema, geen `ThemeMode.system`. Bewijs: `app_theme.dart:8-13,18` (enige `ThemeData`); `Brightness.light` alleen in `main.dart:28-30`/`main_shell.dart:44-46` (systeem-chrome) | 61 |
| MB-05 | 🟠 P1 | Typografie | Plus Jakarta Sans overal (`google_fonts`, 14 files) → **SF Pro / Inter + `-apple-system` fallback**; GoogleFonts als downgrade. Bewijs: `app_theme.dart:29-90` | 7, 8, 65 |
| MB-06 | 🟠 P1 | Material 3 | `useMaterial3: true` + M3-thema-API → **`useMaterial3: false` + eigen Apple-button/sheet/alert-stijlen**; vervang `InkWell`-ripples (≥7 files) door korte iOS press (opacity 0.5/scale 0.97) | 25, 77, 100.7 |
| MB-07 | 🟠 P1 | Haptiek | `physicalFeedback` **0 hits** → voeg haptiek toe op primaire acties (checkout, void, betaling) | 77 |
| MB-08 | 🟠 P1 | Loading | 14 files met `CircularProgressIndicator` (+ `LinearProgressIndicator`, register:172) → **skeleton shimmer** voor lijsten/kaarten; spinner alleen inline in submit | 8.1, 37, 38 |
| MB-09 | 🟠 P2 | Navigatie | Tab-bar regenboog-accents per tab (teal/emerald/cyan/amber) → **één system accent** bij actieve tab, neutrale grijze instaten. Bewijs: `main_shell.dart:88-95` | 1, 89, 100.2 |
| MB-10 | 🟡 P2 | Surfaces | Kaarten met dubbele chrome (border + schaduw): `product_card.dart:31-41`; KPI glass+border: `dashboard_screen.dart:344-347` → hairline óf materiaalblur | 3.5, 25 |
| MB-11 | 🟡 P2 | Radius-schaal | `12` voor velden/nav (login:318-337, shell:107) en `6` (product_card:73) buiten de schaal → normaliseer naar **8/10/14/16–20/999** | 74, 100.5 |
| MB-12 | 🟡 P2 | Cijfers | Dashboard-KPI en prijzen zonder `tabularFigures` (dashboard:369-380, product_card:104-109) → **tabular overal voor bedragen**; `AppTextStyles.numeric*` is al correct (app_text_styles.dart:88-99) | 47, 100.6 |
| MB-13 | 🟡 P3 | Contrast | `labelSmall` 11px/w500 + muted-captions op grens WCAG-AA → verhoog contrast/lettergrootte voor kritieke data | 59, 60 |
| MB-14 | 🟢 P3 | Huishouding | Commentaar "Identik met web: slate-950 emerald" (`app_colors.dart:4`) verouderd → actualiseer; verwijder dubbele token-alias (`accent`==teal vs. system teal) | 136 (terminologie) |

**Positief (behouden):** Apple Sheet-patroon (`showModalBottomSheet` ≥10 flows) · floating SnackBar (16×) ·
glass-materialen op nav/appbar · tabular-token · squircle 14/16/20 · portrait-only · correcte systeem-UI-overlay.

---

## 4. FASEERPLAN (uitvoerings-volgorde)

### Fase P0 - Design tokens & dual theme (basis: MB-01…MB-04)
1. **MB-01/02**: Herschrijf `lib/core/constants/app_colors.dart` naar Apple-tokens:
   - `primary = #007AFF` (licht) / `#0A84FF` (donker) - niet langer emerald
   - Emerald → alleen `success` (#34C759 licht / #30D158 donker)
   - Gray-ladder `gray..gray6` (systemGray-palette), achtergrond `#1E1E1E`/`#1C1C1E`-familie
2. **MB-03**: Verwijder `badgeGreen`/dubbelgebruik; aparte `success`, `inStock`, `cartBadge` tokens met eigen semantiek.
3. **MB-04**: Voeg `AppTheme.light` toe (spiegel-schema in pale-tokens) en koppel in `MaterialApp` aan `ThemeMode.system`; zorg dat `main_shell`/screens geen hard-coded dark-only const meer gebruiken.
4. **Acceptatie P0**: `flutter analyze` groen; app start in licht én donker op iOS-simulator; alle screens hebben geldige kleur-paren.

### Fase P1 - Typografie & interactie-ervaring (MB-05…MB-08)
5. **MB-05**: Font-stack: probeer SF Pro/Inter (bundel/GoogleFonts), fallback `-apple-system`; pas `app_theme.dart` + screens aan naar gecentraliseerde `AppTextStyles`.
6. **MB-06**: `useMaterial3: false`; eigen styling voor `TextFormField`, buttons, dialog/sheet; vervang InkWell-press door `GestureDetector` + korte `AnimatedContainer` press-state (scale 0.97, opacity 0.7).
7. **MB-07**: Haptics op primaire acties; kleine bewegingen op checkout/void/betaling.
8. **MB-08**: Skelet-builder voor product/order/inventory-lijsten; submit-spinner alleen inline (wit, `strokeWidth 2.5` op primaire knop).

### Fase P2 - Navigatie, oppervlakken & afwerking (MB-09…MB-12)
9. **MB-09**: Eén System Blue actief-accent in tab-bar; inactieve pictogrammen neutral gray.
10. **MB-10**: Kaarten: haal schaduw weg óf border weg (hairline + vlakke tint), houd materials voor chrome.
11. **MB-11/12**: Radius normaliseren (10/14/16/20/999); tabular figures voor alle bedragen/cijfers.

### Fase P3 - Toegankelijkheid & huishouding (MB-13…MB-14)
12. Contrast-labels verhogen, stale commentaren/alts opruimen, token-documentatie synchroniseren met web (één bron).

---

## 5. VERIFICATIE
- `flutter analyze` (geen errors/warnings) - repository-analyzer van de app
- `flutter test` (bestaande tests in `mobile_app/test/`)
- Handmatige controle op iOS-simulator/device: licht+donker thema, tab-bar, sheets/toasts, skelet-on-laadtijd, tik-feedback
- Optioneel screenshot-vergelijking per screen tegen de web-panel (zelfde Apple-tokens)

## 6. BESLISSINGEN DIE NODIG ZIJN
1. **Brandingsbesluit (blokkeert P0):** Emarald → **System Blue #007AFF** akkoord? (Impact: logo-gradients, keten-gloei, tab/badges.)
2. **Dual theme**: automatisch via OS-appearance of handmatige toggle in Instellingen?
3. **Font**: SF Pro (gelicentieerd) vs. Inter (open) met `-apple-system` fallback - welk als primair?

## 7. WIJZIGINGLOG
- v1.0 (2026-09-13): Initiële audit + backlog uit `docs/prompt.md` regel-refs, evidence bestand:nr.