# Mobile-first layout and responsive patterns

## Contents
1. Content prioritization
2. Fluid foundations (tokens, grid, container queries)
3. Viewport, safe areas, dvh
4. Responsive patterns (tables, cards, images, forms)
5. Media query rules

## 1. Content prioritization

Before any markup, fill this in (mentally or in a comment):

- **Primary action**: the one thing 80% of visits are for (e.g. "Buat transaksi", "Pesan sekarang").
- **Secondary**: 2-3 supporting actions.
- **Glanceable info**: the 3-5 numbers/facts the user checks without tapping anything.
- **Everything else**: deferred.

Rules of thumb:
- First phone screen (~360×640 visible) shows glanceable info + primary action. No hero image that pushes them below the fold.
- Progressive disclosure: summary row → tap → detail sheet. Accordions for FAQs and settings.
- Cut, don't shrink. If something only fits by making it tiny, it doesn't belong on the first screen.
- Order in the DOM = order on phone = reading/accessibility order. Use CSS `order` sparingly and only for larger breakpoints.

## 2. Fluid foundations

Tokens (spacing, type, radii) as CSS variables; scale with `clamp()` so there are fewer breakpoints to maintain:

```css
:root {
  --space-1: .25rem; --space-2: .5rem; --space-3: .75rem;
  --space-4: 1rem;   --space-6: 1.5rem; --space-8: 2rem; --space-12: 3rem;
  --gutter: clamp(1rem, 4vw, 2.5rem);
  --content-max: 72rem;
  --step-0: clamp(1rem, .96rem + .2vw, 1.125rem);      /* body */
  --step-1: clamp(1.25rem, 1.1rem + .7vw, 1.625rem);   /* h3 */
  --step-2: clamp(1.6rem, 1.3rem + 1.4vw, 2.4rem);     /* h2 */
  --step-3: clamp(2rem, 1.5rem + 2.6vw, 3.6rem);       /* h1 */
}
.container { width: min(100% - 2*var(--gutter), var(--content-max)); margin-inline: auto; }
```

Auto-responsive grid with no media query:

```css
.grid {
  display: grid;
  gap: var(--space-4);
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 16rem), 1fr));
}
```
`min(100%, 16rem)` prevents overflow on 320px screens (a bare `minmax(16rem,1fr)` overflows below 16rem + gutters).

Flexbox for one-dimensional rows that wrap: `display:flex; flex-wrap:wrap; gap:…`. Use `flex: 1 1 14rem` for "as many as fit".

Use logical properties (`margin-inline`, `padding-block`) so RTL and future locales don't break.

**Container queries** for components that live in different-width slots (a card in a sidebar vs main column):

```css
.card-wrap { container-type: inline-size; }
@container (min-width: 28rem) {
  .card { grid-template-columns: 8rem 1fr; }   /* image left on wide slot */
}
```
Prefer container queries for components; media queries for page-level layout.

## 3. Viewport, safe areas, dvh

```html
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
```
- Never `user-scalable=no` or `maximum-scale=1`; it blocks zoom for low-vision users.
- Use `100dvh` (dynamic viewport height) for full-height layouts; `100vh` on mobile includes the URL bar and clips the bottom. Provide a `100vh` fallback first.
- Keep fixed bars clear of notches and home indicators:
  ```css
  .bottom-nav { padding-bottom: env(safe-area-inset-bottom, 0); }
  header.top  { padding-top: env(safe-area-inset-top, 0); }
  ```
- Reserve space for a fixed bottom bar: `main { padding-bottom: calc(4rem + env(safe-area-inset-bottom, 0)); }` so the last item isn't hidden.
- Virtual keyboard: sticky bottom submit bars should not cover inputs; test focus behavior, use `scroll-margin-bottom` on inputs.

## 4. Responsive patterns

**Tables**
- Phone: convert rows to cards (label: value pairs) using `display:block` on cells with `data-label` + `::before`, or build a separate card list. Keep the 2-3 most important fields visible, rest in expand/detail.
- If a true table is required (accounting, comparisons): wrap in `overflow-x:auto`, make first column `position:sticky; left:0`, add a shadow hint on the scroll edge, and keep `min-width` on the table.
- Tablet+: real table. Desktop: add column sorting, filters, bulk actions.

**Cards and lists**
- Whole card is the tap target (not just a small "Lihat" link). Provide a clear pressed state.
- Use list rows with a 56-72px height for tappable items; put trailing actions in a `⋯` menu with a 44px target.

**Images / media**
- `img { max-width:100%; height:auto; display:block; }`, set `width`/`height` attributes or `aspect-ratio` to prevent layout shift.
- `srcset` + `sizes` for raster images; `loading="lazy"` below the fold; `fetchpriority="high"` for the hero only.
- Use `aspect-ratio` and `object-fit: cover` for consistent crops; art-direct with `<picture>` if the crop must change on phone.

**Forms**
- One column on phone, label above field, input height ≥ 48px, correct `type`, `inputmode`, and `autocomplete` (`inputmode="numeric"` for amounts, `type="tel"`, `type="email"`) so the right keyboard appears.
- Primary submit button full-width on phone, sticky at the bottom for long forms; inline validation with text (not color only).
- Tablet+: group related fields in 2 columns; keep labels above fields, not floating placeholders.

**Modals and overlays**
- Phone: bottom sheet (rounded top, drag handle, max-height 90dvh, scrollable body, sticky action row). Tablet/desktop: centered dialog or side drawer.
- Always provide a visible close control (44px) and support Escape and backdrop tap; trap focus; restore focus on close.

**Dashboards**
- Phone: KPI carousel or 2×2 KPI grid, then one chart at a time (tabs/segmented control), lists below. Avoid five charts stacked with tiny axes.
- Charts: fewer ticks, larger touch tooltips (tap to show, not hover), horizontally scrollable if time-series is dense.

## 5. Media query rules

- Only `min-width`, in `rem`/`em` so user font-size settings scale breakpoints (`@media (min-width: 48em)` = 768px at default).
- Keep queries near the component they change; avoid one giant block at the bottom.
- Feature queries beat width guesses:
  - `@media (hover: hover) and (pointer: fine)` — hover effects
  - `@media (pointer: coarse)` — bigger targets
  - `@media (prefers-reduced-motion: reduce)` — remove nonessential motion
  - `@media (prefers-color-scheme: dark)` — dark theme tokens
  - `@media (orientation: landscape) and (max-height: 500px)` — short landscape phones: shrink headers, hide bottom nav labels
- Test both orientations. Landscape phones have very little height; sticky headers + bottom bars can leave a 100px content window.
