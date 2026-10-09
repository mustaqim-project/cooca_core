---
name: flutter-ios-design
description: Design and redesign Flutter mobile app UI with the Apple HIG look on BOTH iOS and Android, responsive across phone and tablet (iPhone, iPad, Android phones, tablets, foldables). Simple, uncluttered but fully styled controls (few button kinds, one primary action, every state styled), Cupertino widgets, SF/Inter typography, adaptive navigation, dark mode, accessibility, plus Android plumbing (back, edge-to-edge, fonts, scroll, TalkBack). Use whenever the user mentions Flutter UI/UX, redesign aplikasi mobile, tampilan iOS, iOS-style di Android, Apple design, HIG, Cupertino, tablet, iPad, responsive, tombol/btn terlalu padat, "sederhana tapi full styling", "bikin UI lebih rapi/modern/premium", or pastes Flutter screens to improve. Also for auditing against iOS standards, converting Material screens, making phone apps work on tablets, or building a Flutter theme/design system.
---

# Flutter iOS-Look Design (Apple HIG) — iOS + Android, Phone + Tablet

Redesign and design Flutter screens so they look and feel like a native iOS app: clear hierarchy, generous whitespace, system-consistent components, full dark mode and Dynamic Type/font-scale support, layouts that adapt from a 320 pt phone to a 1366 pt tablet, **and a delivery that holds up on Android**, where the iOS look is intentional but the Android platform contracts (back, system bars, TalkBack, fonts) still have to work.

The goal is not "make it look like iOS screenshots" but to apply the HIG's principles — **clarity, deference, depth** — consistently on every platform and size.

## Core stance: look = iOS, contracts = Android

- Copy the **skin** everywhere: type scale, spacing, radii, grouped lists, large titles, tab bar, sheets, press states, translucency.
- Respect the **plumbing** on Android: system back button/gesture and predictive back, edge-to-edge system bars, bundled font instead of SF, TalkBack, 200% font scale, resizable windows/foldables, native permission/share/notification UI.
- Never mimic iOS *system* UI (permission prompts, share sheet, keyboard). Those stay native per platform.

Details: `references/android-ios-look.md`.

## Simple, but fully styled (applies to every control)

Buttons, chips, rows, forms, and cards stay **few, calm, and uncluttered**, and every one that remains is **completely styled**. Simple means fewer choices on screen, not less polish; a default-looking, unstyled screen is unfinished.
- Buttons: 3 kinds (filled, tinted, plain) x 2 sizes + destructive modifier via `IOSButton`. **One filled button per screen.** Labels are 1–2 word verbs.
- Never delete capability to simplify. **Demote it**: overflow menu, sheet, swipe action, or detail screen, and name where each action went.
- Density budget, state matrix (default, pressed, disabled, loading, selected, error, focus, dark, large text), and conversions of dense patterns are in `references/simplicity-and-component-styling.md`.

## First decide the mode

| User wants | Mode | Start at |
|---|---|---|
| Improve/redesign existing screens or whole app | **Redesign** | Step 1 (Audit) |
| Design new screen(s) from a description | **New design** | Step 2 (Tokens) |
| "Review my UI" / "what's wrong with this" | **Audit only** | Step 1, stop after report |
| Make a phone app work on tablet | **Adaptive pass** | Step 1 (responsive items), then Step 3b |
| Make an iOS-styled app work properly on Android | **Android pass** | Step 1 (Android items), then Step 4 |
| Build theme / design system only | **Design system** | Step 2, then Step 4 |

If the input is ambiguous, ask at most one question; the most valuable is the platform target below. Otherwise proceed with stated assumptions.

## Decide the targets (they affect every later choice)

**Platform** — default assumption: **iOS look on iOS and Android** (one UI, shipped to both). State it in one line.
- **iOS-only app** → `IOSApp`/`CupertinoApp`; ignore Android sections.
- **iOS look on both (default)** → `IOSApp` (pure Cupertino) or `IOSHybridApp` (MaterialApp wearing Cupertino theme) from `assets/ios_app.dart`. Choose the hybrid when any dependency needs a Material ancestor (`TextField`, `Slider`, `showDatePicker`, many packages). Bundle Inter for non-Apple platforms.
- **Adaptive per platform** (iOS looks iOS, Android looks Material) → use `.adaptive` constructors and branch dialogs/navigation by `defaultTargetPlatform`. This is *not* the iOS-look-on-Android goal; use only if the user asks for native Material on Android.

**Form factor** — default is **phone + tablet**, always. Design every screen for `compact` and `regular/wide` size classes, on both platforms.

## Core responsive rules (apply to every screen)

1. **Branch on available width, never device type.** Size classes: `compact` < 600, `regular` 600–1023, `wide` ≥ 1024 (`assets/ios_adaptive_layout.dart`). Split View, Slide Over, Stage Manager, Android multi-window, foldables, and rotation change width at runtime.
2. **One layout per size class, same state underneath.** Resizing or rotating must never lose selection, scroll position, or form input.
3. **Structure grows; type doesn't.** Tablets get columns, split panes, sidebar, whitespace; not larger fonts, icons, or buttons.
4. **Cap content width.** Forms, settings, text ≈ 672 pt, centered. Dense data (POS, ERP, dashboards) gets true multi-pane layouts on tablet.
5. **Never lock orientation globally.**

See `references/adaptive-layout.md`.

## Step 1 — Audit (Redesign / Audit / Adaptive / Android modes)

Read the code or screenshots first; never redesign blind. Check each screen against `references/hig-checklist.md` (sections 1–9, plus section 10 if it ships on Android) and report:

```
## Audit: <ScreenName>
Score: x/10 against HIG
Critical (breaks usability/accessibility, overflows, back button, hidden behind system bars): - ...
Important (non-native, inconsistent, stretched on tablet, wrong font on Android): - ...
Polish: - ...
```

Be specific and cite the widget/line: "`ElevatedButton` with 8 px radius at `home_page.dart` → 50 pt filled button, 12–14 radius". For Android, name concrete failures: "nav bar title renders in Roboto (only `textStyle` set in theme)", "no `PopScope` on tab shell → back exits app from any tab", "`CupertinoSliverRefreshControl` never fires: clamping physics". Vague findings ("improve spacing") are not useful.

Then summarize the **top 3–5 systemic issues** (shared tokens, dark-mode colors, breakpoint system, app shell choice, Android font/back/system bars). Fixing these at the token/shell level beats fixing screens one by one.

## Step 2 — Define tokens and shell

Copy and adapt, in this order:
1. `assets/ios_platform.dart` — platform detection, font family (system SF on Apple, Inter elsewhere), tracking, `IOSScrollBehavior`, `IOSSystemUi`/`IOSSystemUiScope`.
2. `assets/ios_design_system.dart` — spacing, radii, semantic colors, platform-aware `IOSText`, full `buildIOSTheme()`, base components.
3. `assets/ios_adaptive_layout.dart` — size classes, readable width, adaptive scaffold, master-detail, adaptive sheet, grid delegate.
4. `assets/ios_app.dart` — `IOSApp` / `IOSHybridApp`.

Tokens first because a screen-by-screen redesign drifts; tokens make consistency, dark mode, and Android parity nearly free. Rules that matter most:
- **Colors**: semantic system colors (`CupertinoColors.label`, `.systemGroupedBackground`, …) auto-resolve for dark mode. Hardcoded hex is the #1 cause of broken dark mode. Brand color = accent only, a `CupertinoDynamicColor` with light and dark variants.
- **Typography**: the iOS text style scale (Large Title 34 → Caption2 11). Never invent sizes. On Android set **every** Cupertino text slot (done by `buildIOSTheme`).
- **Spacing**: 4-pt grid; margin 16 / 20 / 24 per size class via `context.screenMargin`.
- **Radius**: 10 cells, 12–14 cards/buttons, 20 sheets.
- **Touch targets**: ≥ 44 pt on iOS, **48 dp on Android** (same visual size, extra hit padding).

## Step 2b — Simplify pass (before coding)

For each screen: count interactive controls ("before"), classify essential / frequent / rare, keep one primary visible, demote the rest to a named place (overflow menu, sheet, swipe action, detail), merge true duplicates, then style the survivors with the full state matrix and recount ("after"). Every original action must still be reachable; supply the mapping. Procedure and report format: `references/simplicity-and-component-styling.md` §10.

## Step 3 — Pick the right pattern per screen, per size class

Map each screen to the native iOS pattern (`references/cupertino-widget-map.md`), then decide how it changes across size classes.

**3a. Compact (phone)**
- Settings / forms / detail lists → inset grouped list
- Primary navigation (3–5 destinations) → tab bar; never a drawer/hamburger
- Hierarchical screens → large-title nav bar that collapses on scroll; **visible back button** (swipe-back is a bonus, not a dependency, on Android)
- Self-contained create/edit → bottom modal sheet, Cancel left / Done right
- Choosing among actions → action sheet; confirm/destructive → alert dialog
- FAB → trailing nav-bar button or in-content filled button; snackbar → inline banner or undo row

**3b. Regular / wide (tablet, foldable, large window)**
- Primary navigation → **sidebar** (`IOSAdaptiveScaffold` switches automatically)
- List → detail → **split view** (`IOSMasterDetail`)
- Forms/settings → centered at readable width, or two-pane settings
- Grids → `iosAdaptiveGridDelegate`; modals → centered form sheet (`showIOSAdaptiveSheet`)
- Operational screens (POS, ERP, dashboards) → true multi-pane layout

## Step 4 — Write the code

- Compose from small reusable widgets on the tokens; screens read like layout, not styling. Use `IOSButton`, `IOSIconButton`, `IOSChip`, `showIOSActionMenu` instead of ad-hoc button styling; never add outlined/gradient/shadowed button variants.
- `main()`: `WidgetsFlutterBinding.ensureInitialized(); await IOSSystemUi.init();` then `runApp` with `IOSApp`/`IOSHybridApp` and localization delegates + `supportedLocales` (e.g., `id`, `en`).
- Decide layout with `LayoutBuilder`/`context.sizeClass`; keep state above the builder. Use `MediaQuery.sizeOf/paddingOf/viewInsetsOf/textScalerOf`, not `MediaQuery.of`.
- Wrap tab shells and custom overlays in `PopScope` so Android system back behaves (first tab before exit; sheets dismiss).
- Press feedback is opacity (`CupertinoButton`); no ripples, FABs, snackbars, or elevation shadows.
- Haptics (`selectionClick`, `lightImpact`) sparingly; never the only feedback.
- Motion: spring/ease-out, ≈ 250–350 ms, interruptible; respect `MediaQuery.disableAnimationsOf`. Budget `BackdropFilter` blur (bars/sheets only) for mid-range Android.
- Dynamic Type / Android font scale: no fixed-height text containers; verify at 200%.
- `Semantics` labels on icon-only buttons and images (TalkBack and VoiceOver).
- Tablet extras: hover (`MouseRegion`), keyboard shortcuts (`CallbackShortcuts`), `CupertinoContextMenu`.
- Deliver full compilable files for changed screens plus a migration note (removed/replaced, packages and pubspec/AndroidManifest changes such as the Inter font and `enableOnBackInvokedCallback`).
- Flag version-dependent APIs (`withValues`, `showCupertinoSheet`, `PopScope.onPopInvokedWithResult`, `CupertinoButton.minimumSize`) and note the assumed Flutter version.

## Step 5 — Verify across sizes and platforms

Run the final gate in `references/hig-checklist.md` and the test matrices in `references/adaptive-layout.md` and `references/android-ios-look.md`:
- iPhone 393×852, iPad 744×1133 / 820×1180 / 1180×820, Android 360×640 / 412×915, tablet ≈ 800×1280, narrow split ≈ 320 wide
- light/dark, 200% text, TalkBack/VoiceOver, 3-button and gesture navigation, system back from every screen type
- A widget test that pumps the app at several sizes and asserts no exception (template in the adaptive reference)

State what you verified and what you could not (e.g., "not run on a device; back behavior and Inter rendering unverified on Android"). Don't claim pixel-perfect parity you haven't checked.

## Output format

For a redesign, respond with:
1. **Assumptions** (platform target, phone + tablet, iOS/Android baseline, Flutter version if known)
2. **Audit summary** (systemic issues + per-screen critical items)
3. **Tokens / shell / shared components** (code + pubspec/Manifest changes)
4. **Redesigned screens** (code); each with 2–3 lines of rationale tied to a HIG principle, one line each on compact vs regular/wide behavior, any Android-specific handling, and the simplify report (`controls before → after`, primary action, where each demoted action now lives)
5. **Migration notes, test matrix & next steps**

For large apps, work in phases (tokens + shell + adaptive scaffold → shared components → highest-traffic screens → rest) and confirm direction after phase 1 instead of rewriting everything at once.

## Common mistakes to avoid

- Crowded screens: several filled buttons, button rows of 3–4, toolbars with 5+ icons, chips for every filter, cards with badges plus buttons.
- "Simplifying" by deleting features, or by leaving default unstyled widgets with no disabled/loading/error states.
- Fixed-height buttons that clip at 200% text; icon-only buttons without semantic labels; tap targets under 44 pt / 48 dp.
- Styling Material widgets to *look* like iOS when a Cupertino/native pattern exists.
- Heavy shadows, gradients, borders; iOS relies on spacing, grouping backgrounds, and hairline separators (0.33–0.5 pt).
- Low contrast (< 4.5:1 for body text); color as the only signal.
- Hamburger menus, FABs, tab bars with > 5 items; tab bar on a 1200 pt-wide window (use a sidebar).
- Custom back buttons that disable swipe-back, or relying on swipe-back with no visible back button.
- `Colors.*` constants for text/backgrounds (not dark-mode aware).
- Phone UI stretched across a tablet; hardcoded widths or `crossAxisCount`; tablet detection via platform or at startup; scaling fonts up on tablet.
- **Android**: only `textStyle` set in the Cupertino text theme (rest falls back to Roboto); clamping scroll physics (dead pull-to-refresh); no `PopScope` on tab shells; hardcoded status bar style; content under the 3-button nav bar; bundling SF or fetching fonts at runtime; missing localization delegates for non-English locales; testing only on iOS.
- Over-claiming "Liquid Glass" (iOS 26 material): approximate deliberately with `BackdropFilter` blur on bars/sheets, check current Flutter release notes for native support before promising it, and note the cost on low-end Android.

## Reference files

- `references/hig-checklist.md` — audit checklist (incl. Responsive §9, Android §10) and final gate (Steps 1, 5)
- `references/simplicity-and-component-styling.md` — density budget, button system, where actions live, forms, state matrix, simplify pass (Steps 1, 2b, 4)
- `references/android-ios-look.md` — fonts, scrolling, back/predictive back, edge-to-edge, shell choice, input, localization, accessibility, Android config and tests (Steps 1–5 on Android)
- `references/adaptive-layout.md` — size classes, patterns per class, tablet/foldable behaviors, test matrix (Steps 3, 5)
- `references/cupertino-widget-map.md` — Material → Cupertino mapping with snippets (Steps 3, 4)
- `assets/ios_platform.dart` — platform fonts/tracking, scroll behavior, system UI (Step 2)
- `assets/ios_design_system.dart` — tokens, platform-aware text styles, full Cupertino theme, `IOSButton`/`IOSIconButton`/`IOSChip`/`showIOSActionMenu`, base components (Step 2)
- `assets/ios_adaptive_layout.dart` — size classes and adaptive components (Steps 2–4)
- `assets/ios_app.dart` — `IOSApp` and `IOSHybridApp` shells (Steps 2, 4)
