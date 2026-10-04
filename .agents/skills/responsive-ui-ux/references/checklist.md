# Pre-delivery checklist and audit procedure

Run this before presenting any UI. Fix failures instead of listing them. For audits of existing UIs, report failures ranked by user impact (blocks a task > causes errors > annoys > polish).

## A. Structure and viewport
- [ ] `<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">` present; zoom not disabled
- [ ] `lang` attribute set (`lang="id"` for Indonesian UIs)
- [ ] CSS is mobile-first (`min-width` queries, base = phone)
- [ ] No horizontal page scroll at 320px; wide content scrolls inside its own container or becomes cards
- [ ] Full-height layouts use `dvh` (with `vh` fallback); fixed bars respect safe-area insets
- [ ] Content is not hidden behind fixed headers/bottom bars (padding reserved)

## B. Responsive behavior (check 320 · 375 · 768 · 1024 · 1440, portrait and landscape)
- [ ] Layout changes where content breaks, not only at device widths
- [ ] Navigation adapts: bottom bar → rail → sidebar/top nav
- [ ] Tables/cards/forms/modals transform as specified per breakpoint
- [ ] Line length stays 45-75ch at wide sizes; a max content width exists
- [ ] Images/media scale, keep aspect ratio, and don't cause layout shift
- [ ] Short landscape phones (height < 500px) remain usable
- [ ] 200% text zoom: no clipped or overlapping text

## C. Touch
- [ ] All interactive elements ≥ 44×44px, ≥ 8px apart (48px for primary actions)
- [ ] Primary action reachable in the bottom third on phones
- [ ] No hover-only interactions; hover styles gated by `(hover: hover)`
- [ ] Visible pressed and `:focus-visible` states
- [ ] Swipe/long-press features have visible alternatives
- [ ] Destructive actions confirmed or undoable
- [ ] Inputs ≥ 16px font (no iOS zoom), correct `type`/`inputmode`/`autocomplete`

## D. Typography and color
- [ ] Body ≥ 16px, secondary ≥ 14px, line-height ≈ 1.5
- [ ] Contrast: text ≥ 4.5:1, large text/UI ≥ 3:1 (including muted text, disabled, placeholders, button labels on accent color)
- [ ] Status never conveyed by color alone
- [ ] Type is `rem`-based; fluid via `clamp()`; numbers use tabular figures where aligned
- [ ] Font fallback stack defined; `font-display: swap`

## E. Navigation
- [ ] Primary destinations visible (bottom nav/rail/sidebar), 3-5 items on bottom bar with labels
- [ ] Clear back path in nested screens; browser back works
- [ ] Search easy to find where it is a core behavior
- [ ] Active location indicated; page titles update
- [ ] Core tasks ≤ 3 taps deep

## F. States and accessibility
- [ ] Empty, loading, error, offline, success, disabled states designed
- [ ] Semantic HTML (`nav`, `main`, `header`, `button` vs `a`, labels for inputs), one `h1`
- [ ] Focus order matches visual order; modals trap and restore focus; Escape closes
- [ ] Alt text/aria-labels on icon-only buttons and images
- [ ] `prefers-reduced-motion` honored; animations only `transform`/`opacity`
- [ ] Dark theme (if provided) meets the same contrast rules

## G. Anti-slop
- [ ] A named aesthetic direction is visible in type, color, and layout (not just a color swap)
- [ ] Not using the tells in `anti-slop.md` (purple gradient, Inter-only, identical cards, emoji icons, glass everywhere)
- [ ] Focal point per screen is obvious in 2 seconds
- [ ] Real, localized copy and sample data (no lorem ipsum)
- [ ] Icon set is consistent; radius/spacing scales are consistent

## H. Performance on mid-range phones
- [ ] No large blurs/shadows/full-screen animated gradients; images sized and lazy-loaded
- [ ] Few font files (≤ 2 families, variable if possible)
- [ ] No layout shift from images/fonts/ads (`aspect-ratio`, size attributes)

## Quick audit procedure for an existing UI
1. Render or mentally simulate at 320 / 375 / 768 / 1024 / 1440.
2. Try the primary task one-handed on the phone layout: count taps and check reach.
3. Scan for tap targets < 44px, text < 14px, contrast failures, horizontal overflow.
4. Check navigation model against the table in `touch-type-nav.md`.
5. Note slop tells; propose concrete replacements.
6. Deliver: top 5-8 issues ranked with the exact fix (code diff or snippet), then apply them if the user wants.
