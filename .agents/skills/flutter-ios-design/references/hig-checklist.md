# HIG Audit Checklist for Flutter

Contents: 1 Layout · 2 Typography · 3 Color · 4 Navigation · 5 Components · 6 Motion & feedback · 7 Accessibility · 8 Final gate · 9 Responsive (phone + tablet)

Values are standard iOS metrics in points (pt = Flutter logical pixels). Treat them as defaults, not laws; deviate only with a reason.

## 1. Layout & spacing
- [ ] Content respects safe areas (notch/Dynamic Island, home indicator)
- [ ] Horizontal screen margin ≈ 16 pt (20 pt on larger screens/iPad)
- [ ] Spacing uses a 4-pt grid (4, 8, 12, 16, 20, 24, 32)
- [ ] Touch targets ≥ 44×44 pt, with ≥ 8 pt between adjacent targets
- [ ] Related items grouped; unrelated separated by whitespace rather than lines/boxes
- [ ] Nav bar ≈ 44 pt (large-title state ≈ 96 pt); tab bar ≈ 49 pt (+ home indicator inset)
- [ ] Keyboard doesn't cover the focused field; content scrolls or insets with `viewInsets`
- [ ] Landscape / small devices (iPhone SE 320–375 pt wide) don't clip

## 2. Typography
iOS text styles (default size, pt) — use these, not arbitrary sizes:

| Style | Size | Weight | Typical use |
|---|---|---|---|
| Large Title | 34 | Bold | Top-level screen titles |
| Title 1 | 28 | Regular | Section/page titles |
| Title 2 | 22 | Regular | Subsections |
| Title 3 | 20 | Regular | Card titles |
| Headline | 17 | Semibold | Row titles, emphasis |
| Body | 17 | Regular | Default reading text |
| Callout | 16 | Regular | Secondary content |
| Subheadline | 15 | Regular | Row subtitles |
| Footnote | 13 | Regular | Section footers, metadata |
| Caption 1 | 12 | Regular | Labels |
| Caption 2 | 11 | Regular | Smallest; avoid for essentials |

- [ ] No more than ~3 distinct text styles on one screen
- [ ] Hierarchy by size/weight, not by many colors
- [ ] No text below 11 pt; essential info ≥ 13 pt
- [ ] Text scales with Dynamic Type; no fixed-height text boxes
- [ ] System font on iOS (SF Pro auto-applied by Cupertino theme); no bundled SF on Android

## 3. Color & materials
- [ ] Semantic colors only (label, secondaryLabel, systemBackground, systemGroupedBackground, separator, system tints)
- [ ] One accent/tint color for interactive elements; consistent meaning (blue = tappable)
- [ ] Destructive actions use systemRed; success systemGreen; warnings systemOrange
- [ ] Dark mode tested: no pure-black-on-black text, elevated surfaces lighter than base
- [ ] Contrast ≥ 4.5:1 body text, ≥ 3:1 large text and UI components
- [ ] Color is never the only carrier of meaning (add icon/text)
- [ ] Shadows minimal; separation via grouped backgrounds, hairline separators (0.33–0.5 pt), or blur materials

Key system colors (light / dark):
- systemBlue `#007AFF` / `#0A84FF`
- systemGreen `#34C759` / `#30D158`
- systemRed `#FF3B30` / `#FF453A`
- systemOrange `#FF9500` / `#FF9F0A`
- systemBackground `#FFFFFF` / `#000000`
- secondarySystemBackground `#F2F2F7` / `#1C1C1E`
- systemGroupedBackground `#F2F2F7` / `#000000`
- secondarySystemGroupedBackground `#FFFFFF` / `#1C1C1E`

## 4. Navigation
- [ ] 3–5 top-level destinations → bottom tab bar; labels present; filled icon for selected
- [ ] No hamburger drawer for primary navigation
- [ ] Hierarchy uses push with back chevron + previous title (or "Back"); swipe-back gesture works
- [ ] Large title on top-level screens, standard title when deeper
- [ ] Modal sheets for self-contained tasks: Cancel (left) / Done or Save (right); grabber for resizable sheets
- [ ] Don't stack more than 2 modals; never modal-in-modal for navigation
- [ ] State preserved per tab when switching

## 5. Components
- [ ] Primary button: full width in forms, ~50 pt tall, 12–14 pt radius, filled with tint; one primary per screen
- [ ] Secondary: tinted/gray fill or plain text button
- [ ] Lists: inset grouped for settings/forms; plain for long feeds; disclosure chevron for pushes; swipe actions for row operations
- [ ] Switches (not checkboxes) for on/off; segmented control for 2–4 mutually exclusive views
- [ ] Pickers: wheel/inline date pickers, menus for short choices
- [ ] Search: `CupertinoSearchTextField` in nav bar/scroll header
- [ ] Alerts: concise title, 1–2 sentence message, ≤ 2 buttons ideally; destructive button styled red; cancel bold on left
- [ ] Empty states: icon + short title + one action
- [ ] Loading: skeletons or `CupertinoActivityIndicator`; pull-to-refresh via `CupertinoSliverRefreshControl`
- [ ] Icons: consistent family (Cupertino icons / SF-style), consistent weight

## 6. Motion & feedback
- [ ] Transitions standard (push slide, sheet rise); no custom page transitions that break swipe-back
- [ ] Durations ≈ 200–350 ms; interactive, interruptible where possible
- [ ] Haptics: selection tick for pickers/segments, light impact for confirmations; not on every tap
- [ ] Immediate visual feedback on press (opacity ~0.4 on `CupertinoButton`)
- [ ] Reduce Motion respected

## 7. Accessibility
- [ ] `Semantics` labels on icon buttons/images; decorative images excluded
- [ ] Logical focus/reading order
- [ ] Works at 200% text scale and with Bold Text
- [ ] Not reliant on gestures alone; provide visible alternative
- [ ] Error messages explain what happened and how to fix

## 8. Final gate (run before delivering any redesign)
1. Every screen uses tokens (no raw hex, no magic font sizes/paddings)
2. Dark mode: all colors semantic or dynamic
3. Dynamic Type: no clipped text at large scale
4. Touch targets ≥ 44 pt
5. Native pattern chosen for navigation, lists, modals (no drawer/FAB/snackbar)
6. One clear primary action per screen
7. Code compiles conceptually: imports present, no undefined tokens, no deprecated APIs knowingly used
8. Responsive section (9) passes at compact, regular, and wide
9. Reported honestly what was and wasn't verified (device test, screen reader, contrast measured, sizes tested)

## 9. Responsive (phone + tablet)
Details and code in `adaptive-layout.md`.
- [ ] Layout decisions use available width (`LayoutBuilder` / `MediaQuery.sizeOf`), never `Platform`/device-type checks or one-time detection
- [ ] Size classes defined once (compact < 600, regular 600–1023, wide ≥ 1024) and reused everywhere
- [ ] Compact: tab bar + stack navigation. Regular/wide: sidebar, and split view for list → detail
- [ ] Selection, scroll position, and form input survive rotation and Split View resize
- [ ] Text, forms, and settings capped at ≈ 672 pt and centered on tablet; no edge-to-edge phone rows
- [ ] Primary buttons not stretched across 800+ pt; grids use max-extent delegates, not fixed column counts
- [ ] Font sizes, icon sizes, and touch targets unchanged between size classes (structure and whitespace scale instead)
- [ ] Modals are centered form sheets on tablet; action sheets become popovers or are width-constrained
- [ ] Dense operational screens (POS/ERP/dashboards) use multi-pane layouts on tablet
- [ ] Tablet input: hover states, keyboard shortcuts for primary actions, focus traversal, context menus
- [ ] All orientations supported on iPad; Android activity resizable; no global orientation lock
- [ ] No overflow at 393×852, 744×1133, 820×1180, 1180×820, and a ≈ 320 pt narrow split; 200% text scale still scrolls
