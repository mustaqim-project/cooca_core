# Anti-slop design guide

"Slop" is UI that could belong to any product: the statistical average of every template. It is not ugly, it is forgettable, and it usually fails on mobile in specific ways (cramped cards, tiny text, decorative effects that eat performance). The fix is to make deliberate choices tied to the product's audience and job.

## Contents
1. The tells
2. Choosing a direction
3. Type
4. Color
5. Composition and shape
6. Imagery and icons
7. Motion and effects
8. Copy and content
9. Slop-to-fix quick table

## 1. The tells (avoid by default)

- Purple→blue/pink gradient hero, gradient text, glowing blobs, "aurora" backgrounds.
- Inter/Roboto/Arial/system-ui used everywhere with no typographic hierarchy beyond size.
- Centered hero + "three feature cards with an emoji/icon in a circle" + "testimonials" + "pricing" + CTA banner, in that order, for every product.
- Every surface is `rounded-2xl` with the same soft shadow and 1px border; everything is a card, including things that should be plain rows.
- Glassmorphism/backdrop-blur on everything (also costly on low-end phones and low contrast outdoors).
- Emoji as icons; mixed icon styles; icons with no labels in primary nav.
- Generic copy: "Welcome to the future of…", "Supercharge your workflow", "Seamless, powerful, intuitive". Lorem ipsum. Fake names like "John Doe".
- Identical spacing everywhere; no rhythm, no focal point; everything centered.
- Dashboard = 4 pastel KPI cards with up/down arrows + one line chart + one donut chart, regardless of what the user needs to decide.
- Decorative animation on everything (floating, pulsing, parallax) that hurts battery and reduced-motion users.
- Dark mode = black background with neon accents by default.

## 2. Choosing a direction

Before coding, write one line: **audience · feeling · one memorable move.** Examples:
- Bengkel/workshop ERP for mechanics on a phone: *operational, sturdy, high-contrast; big status chips; one orange signal color; numerals large and tabular.*
- Villa booking: *warm, editorial, photography-led; serif display, generous whitespace, one earthy accent.*
- Fintech/finance: *calm and precise; restrained palette; numbers as the hero; strong hierarchy, no playful gimmicks.*
- Kids/education: *rounded, saturated, high-legibility, large targets, playful motion.*

Commit fully: a bold maximal look and a quiet minimal look both work; timid mid-way defaults do not. The direction should also bend the layout, not just the colors (e.g. an editorial direction uses asymmetry and big type; an operational one uses dense lists and status color).

## 3. Type

- Pair a **distinctive display face** with a **quiet text face**. Load from Google Fonts (`https://fonts.googleapis.com`) or self-host; always include a metric-similar fallback stack and `font-display: swap`.
- Candidates by mood (mix, don't copy): editorial serif (Fraunces, Playfair Display, Newsreader, DM Serif Display), grotesk (Space Grotesk, Bricolage Grotesque, Familjen Grotesk, Schibsted Grotesk), humanist sans for reading (Source Sans 3, Figtree, Onest, Plus Jakarta Sans — a good Indonesian-market choice), mono for data (JetBrains Mono, IBM Plex Mono). Avoid defaulting to Inter/Roboto/Poppins unless the brief demands neutrality; if using one, express personality via weight, size contrast, and spacing instead.
- Build hierarchy with size contrast (ratio ≥ 1.25), weight, and case, not just color. One clear H1 per screen.
- Numbers matter in ERP/finance: `tabular-nums`, right-aligned in columns, currency symbol styled smaller (Rp), large key metrics.

## 4. Color

- Build a small system: 1 dominant neutral family (5-7 steps), 1 sharp accent, semantic success/warning/danger. The accent should occupy <10% of the surface.
- Prefer tinted neutrals (warm paper `#F6F1E9`, cool slate `#0F1720`) over pure white/black/gray.
- Pick a palette from the product's world (materials, industry, place) rather than "tech blue/purple". Verify contrast programmatically or with known ratios.
- Define tokens once (`:root`) and derive states from them. Provide dark theme through token overrides where appropriate.

## 5. Composition and shape

- Establish a **focal point per screen**: the one number, action, or image the eye lands on first. Everything else is quieter.
- Vary density and scale. A long list of same-height cards is a table wearing a costume; use rows with dividers when items are homogeneous, cards only when items are self-contained objects with media/actions.
- Use asymmetry, offsets, full-bleed sections, and overlap intentionally on tablet/desktop; on phone simplify to a strong single-column rhythm with alternating section backgrounds or dividers.
- Shape language: choose one radius scale (e.g. 4/8/16 or 0/2 for sharp brutalist) and apply consistently; borders vs shadows vs flat fills: pick one dominant depth strategy.
- Spacing on an 4/8px scale with intentional big gaps between sections (48-96px) and tight gaps inside groups (8-12px) — proximity should communicate grouping.
- Backgrounds with texture or atmosphere (subtle grain via inline SVG noise, geometric pattern, layered color blocks) instead of flat gradient blobs; keep them cheap to render.

## 6. Imagery and icons

- One icon set, one stroke weight (Lucide, Phosphor, Tabler, Material Symbols). Icons in primary navigation always carry text labels.
- Real product/photo content or purposeful illustration. When no assets exist, use CSS/SVG shapes, patterns, or typography as the visual, not stock-like emoji or placeholder gray boxes.
- Provide alt text; use `aspect-ratio` to avoid shifts; compress and lazy-load.

## 7. Motion and effects

- Spend the motion budget on 1-2 moments: page-load staggered reveal, sheet transitions, state changes. Not on constant ambient movement.
- `transform`/`opacity` only; 150-400ms; respect `prefers-reduced-motion`.
- Avoid heavy blur, large box-shadows, and full-screen animated gradients on mobile (jank, battery). Use `will-change` sparingly.
- Scroll-driven effects must degrade to static layout with no content hidden if JS fails.

## 8. Copy and content

- Write real labels and microcopy in the user's language (Indonesian labels: "Simpan", "Tambah pelanggan", "Belum ada transaksi — buat yang pertama"). Verbs on buttons, specific over generic.
- Realistic sample data with local formats: `Rp 1.250.000`, `12 Sep 2026`, Indonesian names, plate numbers, and addresses when relevant.
- Empty states teach the next step; errors say what to do.
- Headlines state the benefit or fact; no filler superlatives.

## 9. Slop-to-fix quick table

| Slop | Fix |
|---|---|
| Purple gradient hero | Solid or tinted background from the brand palette; type-led hero; one strong image or pattern |
| 3 identical feature cards | One feature large + supporting rows; or a numbered list with distinct visuals; or an interactive demo |
| Everything rounded-2xl + shadow | Pick one radius scale; use dividers and whitespace for grouping; reserve elevation for overlays |
| Inter everywhere | Display + text pairing; tune weights, tracking, and size contrast |
| KPI card grid + 2 charts | Lead with the decision: alert list, "needs attention" queue, today's number huge, trend as sparkline |
| Glass everywhere | Opaque surfaces with clear borders; glass only on a floating bar over content, with a solid fallback |
| Emoji icons | Consistent SVG icon set |
| Centered everything | Left-aligned text, asymmetry, a clear reading axis |
| Ambient animation | Load-in stagger + purposeful transitions only |
