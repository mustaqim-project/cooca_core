---
name: flutter-ios-design
description: Design and redesign Flutter mobile app UI following Apple Human Interface Guidelines (HIG), fully responsive across phone and tablet (iPhone, iPad, Android tablets, foldables) — Cupertino widgets, SF typography scale, iOS system colors, spacing, adaptive navigation (tab bar ↔ sidebar), split view, dark mode, accessibility, and motion. Use this skill whenever the user mentions Flutter UI/UX, redesign aplikasi mobile, desain mobile app, tampilan iOS, Apple design, HIG, Cupertino, tablet, iPad, responsive, adaptive layout, "bikin UI lebih rapi/modern/premium", "standar Apple", or pastes Flutter screens/widgets/screenshots and wants them improved — even if they never say "Apple" or "HIG" explicitly. Also use for auditing an existing Flutter app against iOS design standards, making a phone-only app work well on tablets, building a Flutter design system/theme, or converting Material-style screens to an iOS-native look.
---

# Flutter iOS Design (Apple HIG) — Phone + Tablet

Redesign and design Flutter screens so they feel native to iOS: clear hierarchy, generous whitespace, system-consistent components, full support for dark mode and Dynamic Type, **and layouts that adapt properly from a 320 pt phone to a 1366 pt iPad**. The goal is not "make it look like iOS screenshots" but to apply the HIG's underlying principles — **clarity, deference, depth** — consistently at every screen size.

## First decide the mode

| User wants | Mode | Start at |
|---|---|---|
| Improve/redesign existing screens or whole app | **Redesign** | Step 1 (Audit) |
| Design new screen(s) from a description | **New design** | Step 2 (Tokens) |
| "Review my UI" / "what's wrong with this" | **Audit only** | Step 1, stop after report |
| Make a phone app work on tablet | **Adaptive pass** | Step 1, focus on responsiveness, then Step 3b |
| Build theme / design system only | **Design system** | Step 2, then Step 4 |

If the input is ambiguous, ask at most one question; the most valuable is the platform target (below). Otherwise proceed with stated assumptions.

## Decide the targets (they affect every later choice)

**Platform**
- **iOS-only** → `CupertinoApp` and Cupertino widgets directly.
- **Cross-platform, iOS-style everywhere** (default assumption) → `CupertinoApp` with Cupertino-styled components. Do not bundle SF fonts (Apple's license restricts SF to Apple platforms); on Android let the system font apply, or use Inter as a close substitute.
- **Adaptive per platform** → `.adaptive` constructors (`Switch.adaptive`, etc.) and branch dialogs/navigation by `defaultTargetPlatform`.

**Form factor** — default is **phone + tablet**, always. Even if the user only mentions mobile, design every screen for both `compact` and `regular/wide` size classes; apps ship on iPad whether or not the developer planned for it, and a stretched phone UI there looks broken. State this assumption in one line.

## Core responsive rules (apply to every screen)

1. **Branch on available width, never device type.** Use the three size classes: `compact` < 600, `regular` 600–1023, `wide` ≥ 1024 (`assets/ios_adaptive_layout.dart`). iPad Split View, Slide Over, Stage Manager, and rotation change the width at runtime; a device check made at startup will be wrong.
2. **One layout per size class, same state underneath.** Compact and regular layouts are different views of the same state, so rotating or resizing must never lose selection, scroll position, or form input.
3. **Structure grows; type doesn't.** On tablets use more columns, split panes, a sidebar, and more whitespace. Do not enlarge fonts, icons, or buttons.
4. **Cap content width.** Forms, settings, and text stay within ≈ 672 pt, centered. Dense data (POS, ERP, dashboards) gets true multi-pane layouts on tablet.
5. **Never lock orientation globally**; iPad apps that do cannot take part in multitasking.

Read `references/adaptive-layout.md` for the pattern table, reference device sizes, tablet behaviors (pointer, keyboard shortcuts, context menus), and the test matrix.

## Step 1 — Audit (Redesign / Audit / Adaptive modes)

Read the code or screenshots first; never redesign blind. For each screen, check against `references/hig-checklist.md` (including its section 9, Responsive) and produce a short report:

```
## Audit: <ScreenName>
Score: x/10 against HIG
Critical (breaks usability/accessibility/overflows at some size): - ...
Important (feels non-native / inconsistent / stretched on tablet): - ...
Polish: - ...
```

Be specific and cite the widget/line: "`ElevatedButton` with 8 px radius at `home_page.dart` → iOS uses a 50 pt filled button, 12–14 radius". For responsiveness, name the concrete failure: "`crossAxisCount: 2` hardcoded → 3 cards 700 pt wide on iPad", "`Platform.isIOS` used to pick tablet layout → breaks in Split View". Vague findings ("improve spacing") are not useful.

Then summarize the **top 3–5 systemic issues** (e.g., "no shared spacing scale", "hardcoded colors break dark mode", "no breakpoint system"), because fixing these at the token/component level beats fixing screens one by one.

## Step 2 — Define design tokens

Before touching screens, establish tokens. Copy and adapt `assets/ios_design_system.dart` (spacing, radii, text styles, semantic colors, theme, base components) and `assets/ios_adaptive_layout.dart` (size classes, readable width, adaptive scaffold, master-detail, adaptive sheet, grid delegate). Tokens first because a redesign applied screen by screen drifts; tokens make every later screen consistent and dark mode nearly free.

Rules that matter most:
- **Colors**: semantic system colors (`CupertinoColors.systemBlue`, `.label`, `.secondaryLabel`, `.systemGroupedBackground`) auto-resolve for dark mode. Hardcoded hex is the #1 cause of broken dark mode. Brand color = accent/tint only, defined as a `CupertinoDynamicColor` with light and dark variants.
- **Typography**: iOS text style scale (Large Title 34 → Caption2 11). Never invent sizes like 13.5. Hierarchy comes from size and weight, not color alone.
- **Spacing**: 4-pt grid; screen margin 16 (compact) / 20 (regular) / 24 (wide) via `context.screenMargin`.
- **Radius**: 10 for grouped list cells, 12–14 for cards/buttons, 20 for sheets.
- **Touch targets**: ≥ 44×44 pt at every size class.

## Step 3 — Pick the right iOS pattern per screen, for each size class

Map each screen to the native pattern instead of styling a Material one (`references/cupertino-widget-map.md`), then decide how it changes across size classes.

**3a. Compact (phone)**
- Settings / forms / detail lists → inset grouped list (`CupertinoListSection.insetGrouped`)
- Primary navigation (3–5 destinations) → tab bar; never a drawer/hamburger
- Hierarchical screens → large-title nav bar that collapses on scroll; swipe-back preserved
- Self-contained create/edit → bottom modal sheet, Cancel left / Done right
- Choosing among actions → action sheet; confirm/destructive → alert dialog (≤ 2–3 buttons)
- FAB → trailing nav-bar button or in-content filled button; snackbar → inline banner or undo row

**3b. Regular / wide (tablet)**
- Primary navigation → **sidebar** (`IOSAdaptiveScaffold` switches tab bar ↔ sidebar automatically)
- List → detail → **split view** (`IOSMasterDetail`): list left, detail right, empty-state placeholder before selection
- Forms/settings → centered at readable width (`IOSReadableWidth` / `IOSSliverReadable`), or two-pane settings
- Card/product grids → `iosAdaptiveGridDelegate` (column count follows width)
- Modals → centered form sheet (`showIOSAdaptiveSheet`), not a full-width bottom sheet
- Action sheets → popover anchored to the trigger, or constrained width
- Operational screens (POS, ERP, dashboards) → true multi-pane, e.g. menu grid + live order panel, instead of a centered phone UI

## Step 4 — Write the code

- Compose from small reusable widgets built on the tokens; screens should read like layout, not styling.
- Decide layout with `LayoutBuilder`/`context.sizeClass`; keep state above the builder so resizing doesn't reset it.
- Use `MediaQuery.sizeOf/paddingOf/viewInsetsOf/textScalerOf` rather than `MediaQuery.of` (avoids needless rebuilds).
- Wrap content in `SafeArea`; use `CupertinoScrollbar` and `BouncingScrollPhysics`.
- Haptics where iOS uses them (`selectionClick` on picker/segment changes, `lightImpact` on primary actions), sparingly.
- Motion: spring/ease-out, ≈ 250–350 ms, interruptible; respect `MediaQuery.disableAnimationsOf`.
- Dynamic Type: no fixed-height text containers; verify at 200% scale; reflow or scroll, never clip.
- `Semantics` labels on icon-only buttons and images.
- Tablet extras where relevant: hover states (`MouseRegion`), keyboard shortcuts (`CallbackShortcuts`), `CupertinoContextMenu` on rows.
- Deliver full compilable files for changed screens plus a short migration note (what was removed/replaced, packages to add).
- Flag version-dependent APIs (`withValues`, `showCupertinoSheet`, `CupertinoButton.minimumSize`) and ask for or note the Flutter version.

## Step 5 — Verify across sizes

Run the final gate in `references/hig-checklist.md` and the test matrix in `references/adaptive-layout.md` (393×852, 744×1133, 820×1180, 1180×820, narrow split ≈ 320 wide; light/dark; 200% text). Include a widget test that pumps the app at several sizes and asserts no exception (template in the adaptive reference). In your reply, state what you verified and what you could not (e.g., "not run on a device; verified by constraints reasoning only"). Don't claim pixel-perfect fidelity you haven't checked.

## Output format

For a redesign, respond with:
1. **Assumptions** (1–3 lines: platform target, phone + tablet, iOS baseline, Flutter version if known)
2. **Audit summary** (systemic issues + per-screen critical items)
3. **Tokens / shared components** (code)
4. **Redesigned screens** (code); for each: 2–3 lines of rationale tied to a HIG principle, and one line each on how it behaves at compact vs regular/wide
5. **Migration notes, test matrix & next steps**

For large apps, work in phases (tokens + adaptive scaffold → shared components → highest-traffic screens → rest) and confirm direction after phase 1 instead of rewriting everything at once.

## Common mistakes to avoid

- Styling Material widgets to *look* like iOS when a Cupertino/native pattern exists.
- Heavy shadows, gradients, and borders; iOS relies on spacing, grouping backgrounds, and hairline separators (0.33–0.5 pt).
- Low contrast (< 4.5:1 for body text); color as the only signal.
- Hamburger menus, FABs, and tab bars with > 5 items; tab bar on a 1200 pt-wide window (use a sidebar).
- Custom back buttons that disable swipe-back.
- `Colors.*` constants for text/backgrounds (not dark-mode aware).
- Phone UI stretched across an iPad; hardcoded widths or `crossAxisCount`; tablet detection via platform or at startup; scaling fonts up on tablet.
- Over-claiming "Liquid Glass" (iOS 26 material): approximate deliberately with `BackdropFilter` blur on bars/sheets and check current Flutter release notes for native support before promising it. Correct hierarchy and adaptivity matter more.

## Reference files

- `references/hig-checklist.md` — audit checklist (incl. Responsive section) and final gate (Steps 1, 5)
- `references/adaptive-layout.md` — size classes, patterns per class, tablet behaviors, orientation/multitasking, test matrix (Steps 3, 5)
- `references/cupertino-widget-map.md` — Material → Cupertino mapping with snippets (Steps 3, 4)
- `assets/ios_design_system.dart` — tokens, text styles, theme, base components (Step 2)
- `assets/ios_adaptive_layout.dart` — size classes, readable width, adaptive scaffold, master-detail, adaptive sheet, grid delegate (Steps 2–4)
