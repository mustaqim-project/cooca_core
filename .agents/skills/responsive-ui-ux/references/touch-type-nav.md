# Touch, typography, color, and navigation

## Contents
1. Touch targets and interaction
2. Thumb zone
3. Typography and readability
4. Color and contrast
5. Navigation patterns
6. Feedback, states, and motion

## 1. Touch targets and interaction

- Minimum tap target **44×44 CSS px** (Apple HIG); Material recommends 48×48dp. Use 48px for primary actions and list rows.
- The visible icon can be 24px if its hit area is padded to 44px:
  ```css
  .icon-btn { min-width: 44px; min-height: 44px; display:inline-grid; place-items:center; }
  ```
- Spacing between adjacent targets ≥ 8px. Two 44px buttons touching each other still produce mis-taps.
- Inline text links in paragraphs are the classic failure: give them padding or make the whole row tappable; never place two links on one line without spacing.
- Add `touch-action: manipulation` on buttons/controls to remove the 300ms tap delay and double-tap zoom.
- Pressed state (`:active`) must be immediate and visible (scale .98, darken). Do not rely on `:hover`.
- Focus-visible ring on everything interactive (keyboard, switch access, external keyboards on tablets): `:focus-visible { outline: 3px solid var(--focus); outline-offset: 2px; }`.
- Swipe gestures (delete, reveal) are shortcuts, never the only path. Provide a visible button alternative.
- Avoid tiny controls: checkbox/radio hit area ≥ 44px (wrap in a label with padding), sliders with a big thumb, steppers with 44px +/- buttons.
- Prevent accidental destructive taps: confirm or offer undo (snackbar with "Urungkan").
- Long-press and drag-and-drop need discoverability plus a fallback.

## 2. Thumb zone

On a ~6" phone held in one hand, the easy reach is the bottom-center and bottom-side arcs; the top corners (especially opposite the thumb) are hard.

Placement rules:
- **Easy (bottom third)**: primary CTA, bottom navigation, main filters/segmented control, compose/add FAB, search entry for frequently used search.
- **Okay (middle)**: content, list rows, secondary actions.
- **Hard (top)**: page title, back arrow (acceptable; it's standard), rare actions (settings, help), account/profile.
- Right-handed bias is common, but left-handed users exist: center the primary CTA or make it full-width rather than tucking it in one corner.
- Put "Simpan/Kirim/Bayar" in a sticky bottom bar on long forms; put "Batal" as a secondary (text or outline) style beside/above it, never the same visual weight.
- Tablets are held with two hands at the edges: nav rails and action buttons at left/right edges are reachable; center-bottom is not. Do not port the phone's bottom bar to tablet by default; use the side rail.
- Do not stack a sticky top header, a sticky sub-header, and a bottom bar all at once on small screens; keep at most two persistent bars and make the header collapse on scroll.

## 3. Typography and readability

- Body 16px minimum on phones (`1rem`), secondary text 14px, captions 12px only for non-essential info. Never use `px` sizes below 12.
- Line height 1.5 for body, 1.2-1.3 for headings. Paragraph max width 45-75ch (`max-width: 65ch`).
- Scale with `clamp()` (see layout reference) so headings shrink gracefully; avoid viewport-only units (`vw` alone) because they ignore user zoom.
- Use `rem`, not `px`, so user font-size preferences are respected. Test at 200% text zoom without clipped text or overlapping controls.
- Limit to 2 typefaces (display + text) and 3-4 weights. Tabular figures for numbers in tables/dashboards: `font-variant-numeric: tabular-nums;`.
- Left-align body text. Avoid all-caps for long strings; letter-space small caps labels slightly.
- Truncate deliberately: `text-overflow: ellipsis` on single-line titles in lists, `line-clamp` for descriptions, and always allow the detail view to show the full text.
- Fonts: `font-display: swap`, subset if self-hosting, and give a considered fallback stack so layout doesn't jump. For offline-first or poor networks prefer 1 variable font file.
- Indonesian text runs ~20-30% longer than English in UI labels; leave room in buttons, tabs, and table headers rather than fixing widths.

## 4. Color and contrast

- WCAG AA: 4.5:1 body text, 3:1 large text (≥ 24px or ≥ 19px bold) and UI components/borders/icons. Aim higher for primary reading text (7:1) since screens are used outdoors.
- Never convey status by color alone; pair with icon + text ("Lunas", "Belum bayar").
- Avoid thin light-gray text (`#999` on white ≈ 2.8:1 fails). Placeholder text is not a label.
- Define semantic tokens: `--bg`, `--surface`, `--text`, `--text-muted`, `--border`, `--accent`, `--accent-contrast`, `--danger`, `--success`, `--focus`. Build a dark theme by redefining tokens, not by inverting.
- Dark mode: don't use pure `#000` on `#fff` extremes; use slightly lifted surfaces (elevation via lighter surface, not shadows), desaturate accents a bit.
- One dominant neutral family, one accent used sparingly (CTA, active state, key numbers). More than two saturated hues in a UI reads as noise.
- Check contrast on hover/pressed/disabled/selected states as well, not just default.
- Bright sunlight test: increase weight and contrast of essential text; avoid low-contrast glass/blur surfaces over busy backgrounds.

## 5. Navigation patterns

Choose by number and type of destinations:

| Destinations | Phone | Tablet | Desktop |
|---|---|---|---|
| 2-5 top-level, equal importance | **Bottom navigation** (icon + label, active indicator) | Navigation rail | Sidebar or top nav |
| 6+ or hierarchical (ERP modules) | Bottom bar with 4 key items + **"Lainnya"** opening a sheet/list; or drawer | Rail + expandable drawer | Persistent sidebar with groups |
| Content site, few pages | Top bar with hamburger → full-screen menu | Top bar inline links | Top bar inline links |
| Deep task flow (checkout, wizard) | No bottom nav; step header + back + sticky CTA | Same | Stepper visible |

Rules:
- Hamburger menus hide navigation and cut discoverability; prefer visible bottom nav for primary destinations, and use hamburger/drawer only for secondary or many-item navigation.
- Bottom nav: 3-5 items, labels always visible, active state uses shape/weight + color, 56-64px tall, safe-area padding, badge counts small but legible. Don't animate items shifting.
- Back: always a visible, 44px back control in nested screens (top-left), plus the system back must work (use real routes/history, not JS state that breaks the back button). Breadcrumbs only on desktop.
- Search: if search is a core behavior (catalog, ERP records), show a search field or prominent search icon at the top of the list and put "recent searches" first; keep results reachable from the keyboard's position. Cmd/Ctrl+K on desktop.
- Show current location: highlight the active item, update the page title, and keep scroll position when returning.
- Keep depth ≤ 3 taps to any core task; offer shortcuts (FAB, quick actions row) for the top 1-2 tasks.
- Tabs (segmented) for peer views inside a screen; do not nest tabs inside tabs; scrollable tabs need a visible cut-off hint.
- Sticky headers collapse on scroll down and reappear on scroll up to reclaim space; keep the bottom bar persistent.

## 6. Feedback, states, and motion

- Every action gets feedback within 100ms: pressed state, spinner/skeleton, toast/snackbar. Optimistic UI where safe.
- Design all states: empty (with a next step), loading (skeleton matching layout), error (what happened + retry), offline (cached data + banner), success, disabled (explain why).
- Skeletons over spinners for content; spinners only for short unknown actions.
- Motion: 150-250ms for micro-interactions, 250-400ms for sheets/page transitions, ease-out for entering, ease-in for leaving. Animate `transform` and `opacity` only.
- Wrap decorative motion in `@media (prefers-reduced-motion: no-preference)`; provide instant alternatives for reduced motion.
- Haptics/sound are not available in plain web; don't promise them.
- Toasts appear above the bottom bar and outside the thumb-blocking zone of the primary CTA; auto-dismiss ≥ 5s with an "Urungkan" action when relevant.
