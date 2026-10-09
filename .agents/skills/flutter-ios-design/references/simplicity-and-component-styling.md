# Simple, Not Crowded — and Still Fully Styled

Contents: 1 Principle · 2 Density budget · 3 Buttons · 4 Where actions live · 5 Forms · 6 Chips, segments, toggles · 7 Cards and lists · 8 Dense → simple conversions · 9 What "fully styled" means (state matrix) · 10 The simplify pass · 11 Anti-patterns

## 1. Principle
**Simple means fewer choices on screen, not less polish.** Every control that remains is complete: sized, spaced, typed, colored, and stateful. A calm screen has one obvious next step, generous space, and secondary things tucked one tap away. A bare, default-looking screen is not simple, it is unfinished.

**Never delete capability to simplify.** Demote it instead: keep every action reachable (menu, sheet, detail screen, swipe action, settings row), just not all visible at once.

## 2. Density budget (per screen, guidelines to challenge, not laws)
| Element | Budget |
|---|---|
| Filled (primary) buttons | **1** |
| Tinted / secondary buttons | ≤ 2 |
| Plain text buttons or links | ≤ 3 |
| Nav bar trailing actions | ≤ 2 icons, or 1 text button ("Simpan") |
| Tab bar items | 3–5 |
| Visible filter chips | ≤ 6 (scroll), otherwise one "Filter" button + sheet |
| Fields per form step | ≤ 5–6; more → sections, steps, or progressive disclosure |
| Controls per list row | ≤ 1 trailing (chevron, switch, or value) |
| Card content | 1 title + 1 subtitle + 1 meta + at most 1 action |
| Text styles on one screen | ≤ 3–4 |
| Nested containers | 1 level (no card inside a card inside a section) |
| Accent-colored area | small: the accent marks what is tappable and the single primary action |
| Space between adjacent tap targets | ≥ 8 pt |

On tablets the budget is per **pane**, not per screen: a sidebar plus a detail pane each follow it.

## 3. Buttons
Three kinds, two sizes, one destructive modifier. That is the whole system (`IOSButton` in `assets/ios_design_system.dart`).

| Kind | Use | Look |
|---|---|---|
| **filled** | The single primary action of a screen or sheet | Accent fill, white label, 14 pt radius |
| **tinted** | A useful secondary action beside the primary | Accent at ~14% fill, accent label |
| **plain** | Tertiary, cancel, "Lewati", inline links | No fill, accent label |
| destructive | Modifier on any kind | systemRed instead of accent |

| Size | Use | Metrics |
|---|---|---|
| **large** | Page- or sheet-level action | Full width, min height 50, headline weight |
| **regular** | Inline, in rows and cards | Hugs content, min height 44 pt (48 dp on Android), semibold body |
| icon-only (`IOSIconButton`) | Nav bar and toolbars | 44 pt / 48 dp hit area, 22 pt glyph, required semantic label |

Rules:
- **One filled button per screen.** If two things feel primary, one of them is secondary.
- **At most two buttons side by side**, and only inside a sheet or dialog; on pages stack them (primary above, plain below) or put the second in the nav bar.
- **Labels**: verb or verb + object, one to two words, sentence case ("Simpan", "Tambah item", "Bayar"). Never "OK", "Submit", "Klik di sini". Same action = same word everywhere.
- **Icon + label only when the icon adds meaning** (a plus for add is fine; decorative icons on every button are not). No icon-only button without a semantic label.
- No outlined buttons, gradients, drop shadows, glows, ripples, or badges on buttons.
- Destructive actions are never big, red, and adjacent to the primary. Put them as a red plain row at the bottom of a grouped section ("Hapus akun") or inside an action sheet, then confirm with an alert.
- Disabled is styled (muted fill and label), not hidden and not just 50% opacity of the primary color. If the reason isn't obvious, show helper text near the button.
- Loading replaces the label with a spinner **without changing the button's width**, and disables further taps.

## 4. Where actions live
- **Modal sheet / editor**: Cancel at the left of the nav bar, Save/Done at the right. Then there is **no** bottom button (never both).
- **Page with a main action** (checkout, submit): one large filled button pinned above the home indicator (or at the end of the form), with the safe area respected.
- **Secondary and rare actions**: overflow menu via `showIOSActionMenu` (action sheet; popover on tablets), swipe actions on rows, or a detail screen.
- **Row actions**: tap the row to open details; one trailing control at most. Avoid per-row button clusters.
- **Search/filter/sort**: one nav-bar or in-content control that opens a sheet, instead of a toolbar of toggles.
- **Floating action buttons** do not exist: use the nav bar's add button or the single large button.

## 5. Forms
- Use `CupertinoFormSection.insetGrouped` + `CupertinoTextFormFieldRow(prefix: Text(label), placeholder: …)`: one input per row, label left, hairline separators, 10 pt cell radius. Group related fields into sections with short headers; hints go in section footers (footnote, secondary label).
- **Validation**: inline under the row in footnote red, shown after the field is left or after submit, never on every keystroke; the primary button stays enabled unless the form is truly unusable.
- **Reduce typing**: prefilled sensible defaults, pickers/segmented controls/switches for finite choices, correct `keyboardType`, `textInputAction`, and `autofillHints`.
- **Progressive disclosure**: optional fields behind "Tambah detail" (a plain button or disclosure row). Long forms become steps with a clear progress indication or separate screens.
- **Keyboard**: the focused field scrolls into view; the last field's action is "Selesai" or "Simpan".

## 6. Chips, segmented controls, toggles
- `CupertinoSlidingSegmentedControl` for 2–4 mutually exclusive views; `IOSChip` for filters (≤ 6 visible); `CupertinoSwitch` for on/off settings, aligned right in an inset grouped row with a short label (one line of footer text if an explanation is needed).
- Don't mix chips, segments, and tabs for the same job on one screen.
- Selected state = filled accent (chip) or raised white thumb (segment); never rely on color alone (also change weight or add a check where the context needs it).

## 7. Cards and lists
- Prefer **grouped list rows** to cards for text-heavy content; use cards for media-led items.
- A card is one tap target. Title (headline), one line of secondary text (subhead, secondary label), optional meta (footnote). Actions go to the detail screen, a swipe action, or the overflow menu.
- Separation by whitespace and grouped backgrounds, not by borders on everything: no box around every element, no shadow on cards in lists.
- Metrics/dashboards: 3–4 hero numbers with `IOSText.tabular`, then "Lihat semua". Not eight equal tiles.
- Empty states: icon, one-line title, one action (`IOSEmptyState`).

## 8. Dense → simple conversions
| Dense pattern | Simple pattern (nothing removed) |
|---|---|
| Row of 3–4 equal buttons (Edit, Share, Archive, Delete) | One filled/tinted action + "Lainnya" menu holding the rest; Delete red inside the menu |
| Cancel + Save big buttons side by side on a page | Save as the single large button; Cancel as nav bar left or a plain button below |
| Toolbar with 6 icons | 2 icons + overflow menu |
| 12 filter chips | Segmented control (≤ 4) or one "Filter" button opening a grouped-list sheet |
| Card with icon, title, subtitle, badge, 3 buttons | Title, subtitle, chevron; actions in detail screen or swipe |
| Settings screen with switches, descriptions, and buttons mixed | Inset grouped list, one control per row, descriptions in footers |
| 12-field form on one screen | Sections plus "Tambah detail", or a 2–3 step flow |
| Dashboard of 8 stat tiles with trends | 3–4 hero metrics, rest on a "Lihat semua" screen |
| 7 bottom tabs | 5 max (tab bar) with the rest under a "Lainnya" tab; sidebar on tablets can show all |
| Inline help text under every field | Placeholder + footer hint only where error-prone |

## 9. What "fully styled" means (state matrix)
Every interactive component ships with all of these. Missing any one is an unfinished component.

| State | Requirement |
|---|---|
| Default | Token color, token type style, token radius, token padding |
| Pressed | Opacity 0.4–0.6 (no ripple), optional haptic on primary actions |
| Disabled | Muted fill + muted label from semantic colors, not tappable, reason visible if non-obvious |
| Loading | Spinner in place of label, same size, taps blocked |
| Selected / on | Distinct fill or check, not color alone |
| Error | Inline message in red footnote + field outline/label tint |
| Focus (keyboard, tablet, switch access) | Visible focus indication; logical focus order |
| Dark mode | Only semantic/dynamic colors (no hardcoded hex) |
| Dynamic Type / font scale | Grows to fit, reflows to two lines, never clips; verified at 200% |
| Hit area | ≥ 44 pt iOS, ≥ 48 dp Android (visual size may be smaller) |
| Semantics | Label, role, state announced (icon-only controls require a label) |
| Motion | Short, interruptible, respects reduce-motion |
| Tablet | Hover/pointer cursor where relevant; honors width-based layout |

Tokens to use everywhere: radius 10/12–14/20, spacing 4-pt grid (margins 16/20/24), text styles from `IOSText`, colors from `IOSColors`/`CupertinoColors` semantic set.

## 10. The simplify pass (run after the audit, before coding)
1. **Count** interactive controls per screen (buttons, icon buttons, chips, switches, links, row actions, fields). Record "before".
2. **Classify** each as essential (needed to finish the screen's main job), frequent, or rare.
3. **Place**: essential stays visible (one primary). Frequent becomes tinted/plain or a row. Rare moves to the overflow menu, a sheet, a swipe action, or the detail screen. Duplicates are merged, not deleted if they differ in function.
4. **Verify nothing is lost**: every original action must map to a visible control or a named location ("Archive → ••• menu"). Keep a mapping table.
5. **Style** the survivors completely using the state matrix.
6. **Recount** and report before/after per screen. A 30–50% drop in visible controls on dense screens is typical; don't force it on screens that were already calm.

Report format:
```
Screen: OrderDetail   controls 14 → 5
Primary: "Bayar" (filled, large)
Kept visible: "Ubah" (plain), status chip
Moved to ••• menu: Duplikat, Cetak, Bagikan, Arsipkan, Hapus (red)
Moved to swipe action on list rows: Tandai selesai
Removed: none
```

## 11. Anti-patterns
- Making a screen "simple" by deleting features instead of demoting them.
- Calling it simple but leaving default, unstyled Cupertino widgets with inconsistent spacing.
- Two or more filled buttons competing; rainbow of accent colors for different actions.
- Button labels like "OK", "Submit", or full sentences; truncated labels at large font size.
- Fixed-height buttons that clip at 200% text scale.
- Icon-only buttons without semantic labels; 24 pt tap targets.
- Chips, tabs, and segmented controls all on one screen for the same filter.
- Cards nested in cards, borders around every element, shadows in lists.
- Toolbars with more than two nav-bar actions; hamburger menus; FABs.
- Disabled buttons with no explanation and no styling.
