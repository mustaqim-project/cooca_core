---
name: responsive-ui-ux
description: Design and build mobile-first, touch-friendly, responsive UI that looks intentional rather than AI-generated ("anti-slop") across phone, tablet, and desktop. Use this skill whenever the user asks for a website, landing page, dashboard, admin panel, web app screen, component, form, or any HTML/CSS/Tailwind/Bootstrap/React/Blade UI, or asks to make a layout responsive, mobile-friendly, "rapi di HP", "tampilan mobile", "responsif", "UI/UX", "redesign", "bottom navigation", "tap target", "thumb zone", or to fix a UI that looks generic, cramped, or broken on small screens. Also use it when reviewing or auditing an existing interface for mobile usability, even if the user never says the word "responsive".
---

# Responsive UI/UX — mobile-first, touch-friendly, anti-slop

Goal: every interface Claude produces should be comfortable with one thumb on a 360px phone, use space well on a tablet, feel purposeful on a desktop, and never look like a default AI template.

Respond in the user's language (often Indonesian); code, class names, and comments stay in English unless the user asks otherwise.

## Workflow

1. **Frame the job (30 seconds, no interview unless truly ambiguous).** Who uses it, on what device, doing what most often? For Indonesian SMB/ERP-style products assume phone-first, one-handed, bright outdoor light, mid-range Android, patchy connection. State assumptions in one line and proceed.
2. **Rank the content.** List the 1 primary action, 2-3 secondary actions, and everything else. On a phone only primary and secondary get first-screen real estate. Everything else moves behind a tab, sheet, accordion, or second screen. Read `references/mobile-first-layout.md`.
3. **Pick an aesthetic direction before writing code.** Name it in a sentence (e.g. "warm editorial, ink-on-paper, one terracotta accent" or "dense operational, monochrome with signal colors"). Read `references/anti-slop.md` to choose fonts, palette, and composition that are not defaults.
4. **Build mobile base first, then enhance upward** with `min-width` breakpoints only. Start from `assets/responsive-starter.html` when a full page or app shell is needed; it already contains the adaptive navigation (bottom bar → rail → sidebar), fluid type, safe-area handling, and tokens.
5. **Apply touch, type, and navigation rules** from `references/touch-type-nav.md`.
6. **Self-review with `references/checklist.md`** before presenting. Fix failures; do not just list them.
7. **Deliver.** For a page or app, write a single self-contained file (or the user's stack: Blade/Tailwind/Bootstrap/React) and present it. Add 3-5 lines on the decisions that matter (what moved where per breakpoint, thumb-zone choices), not a lecture.

## Non-negotiables (and why)

- **Viewport meta present**: `width=device-width, initial-scale=1, viewport-fit=cover`. Without it phones render a shrunken desktop page and every other rule is moot.
- **Mobile-first CSS**: base styles = phone. Use `@media (min-width: …)` to add. Desktop-first `max-width` overrides accumulate bugs and ship heavier CSS to the weakest devices.
- **Tap targets ≥ 44×44px** with ≥ 8px gap between neighbors (48px is better for primary actions). Fingers are ~10mm wide; smaller targets cause mis-taps and rage-taps.
- **Primary action in the thumb zone** (bottom third, reachable arc) on phones. Put destructive or rare actions out of easy reach and behind confirmation.
- **Body text ≥ 16px** (never below 14px for secondary text), line-height 1.5, line length 45-75 characters. Form inputs must be ≥ 16px or iOS Safari zooms on focus.
- **Contrast ≥ 4.5:1** for body text, ≥ 3:1 for large text and UI boundaries. Test against a bright-sunlight mental model: thin gray-on-white fails outdoors.
- **No hover-only functionality.** Touch has no hover. Gate hover styling with `@media (hover: hover) and (pointer: fine)`.
- **No horizontal page scroll** at 320px. Wide content (tables, code) scrolls inside its own container or transforms into cards.
- **Real content, real states.** Use plausible data, and design empty, loading, error, and offline states. Lorem ipsum hides layout problems that real strings expose.

## Breakpoint strategy (content-driven, not device-driven)

Base 0-599 phone · `600px` large phone/small tablet · `768px` tablet portrait · `1024px` tablet landscape/small laptop · `1280px` desktop · `1536px` wide. Treat these as starting points and add a breakpoint where the *content* breaks (a line gets too long, a card gets too narrow), not per device model.

What typically changes per step:

| Element | Phone | Tablet (≥768) | Desktop (≥1024/1280) |
|---|---|---|---|
| Navigation | Bottom bar (3-5 items) + "More" | Left rail (icons + small labels) | Full sidebar with labels, or top nav for content sites |
| Layout | Single column, stacked | 2 columns, master-detail where useful | 12-col grid, max content width, side panels |
| Data tables | Cards / key-value rows, or tables in scroll container with sticky first column | Compact table | Full table with sorting, filters inline |
| Forms | One field per row, sticky submit | Two-column groups | Two-column + inline help/preview |
| Modals | Full-height or bottom sheet | Centered dialog | Centered dialog or side drawer |
| Density | Generous spacing | Medium | Can be denser; still respect 44px on touch-capable laptops |

Tablets are touch devices even at desktop widths. Use `pointer: coarse` (not width) to decide touch sizing, and never assume "wide = mouse".

## Where to go deeper

- `references/mobile-first-layout.md` — content prioritization, fluid grid, flex/grid/container-query patterns, safe areas, dvh, responsive images and tables.
- `references/touch-type-nav.md` — tap targets, thumb zone map, gestures, typography scale, color/contrast, navigation patterns (bottom nav, hamburger, back, search), forms on mobile.
- `references/anti-slop.md` — the tells of generic AI UI and what to do instead; font/palette/composition/motion guidance.
- `references/checklist.md` — pre-delivery QA list and quick audit procedure for existing UIs.
- `assets/responsive-starter.html` — working adaptive app shell to copy and adapt.

## When the user's stack is specified

Follow the stack's idioms but keep the rules above. Tailwind: mobile-first utilities are the default (`md:`/`lg:` add upward); define tokens in `tailwind.config` rather than sprinkling arbitrary hex values. Bootstrap 5: use `col-12 col-md-6` ordering, `d-none d-md-block`, and override CSS variables for a non-default look. Laravel Blade: keep layout in a component/partial so navigation logic (bottom bar vs sidebar) lives in one place. React: keep breakpoint logic in CSS where possible; use JS media queries only for behavior changes.

## Auditing an existing UI

If the user shares screenshots, code, or a URL: (1) test at 320, 375, 768, 1024, 1440 widths mentally or by rendering; (2) run `references/checklist.md`; (3) report the top issues ranked by user impact, then fix them. Prefer concrete diffs over general advice.
