# Adaptive Layout: Phone + Tablet (iPhone, iPad, Android tablets, foldables)

Contents: 1 Core principle · 2 Reference sizes · 3 Size classes · 4 Pattern per size class · 5 Layout rules · 6 Tablet-specific behaviors · 7 Orientation & multitasking · 8 Testing · 9 Anti-patterns

## 1. Core principle
Design for the **width actually available to the app window**, not for a device type. On iPad, Split View, Slide Over, and Stage Manager can shrink the app to phone-like widths at any moment, and the user can rotate at any time. So:

- Branch on `MediaQuery.sizeOf(context).width` (app-level decisions) or `LayoutBuilder` constraints (local decisions).
- Never branch on "is iPad", `Platform`, or `shortestSide` alone, and never cache the result in `initState`.
- Do not use `MediaQuery.of(context)` for size (rebuilds on every metric change); use `sizeOf`, `paddingOf`, `viewInsetsOf`, `textScalerOf`.
- Design **one layout per size class**, not one layout that stretches.

## 2. Reference sizes (logical points, portrait)
| Device | Width × Height |
|---|---|
| iPhone SE | 375 × 667 |
| iPhone 15/16 | 393 × 852 |
| iPhone Pro Max | 430 × 932 |
| iPad mini | 744 × 1133 |
| iPad / iPad Air 10.9–11" | 820 × 1180 |
| iPad Pro 11" | 834 × 1194 |
| iPad Pro 13" | 1032 × 1376 |

Android (dp = Flutter logical px): small phone 360×640, common 393×852 / 412×915, large 432×960; tablets ≈ 800×1280 (portrait) and 1280×800 (landscape); foldables ≈ 360–400 wide folded, ≈ 670–840 wide unfolded. Android's own window size classes (Compact < 600, Medium 600–839, Expanded ≥ 840) line up with the classes below; keep one set of thresholds for both platforms.

Landscape swaps width/height (iPad landscape ≈ 1024–1376 wide). Split View can leave the app anywhere from roughly 320 pt to most of the screen. Treat these as test targets, not constants in code.

## 3. Size classes (use these everywhere)
| Class | Width | Typical |
|---|---|---|
| `compact` | < 600 | iPhone, iPad Slide Over / narrow split |
| `regular` | 600 – 1023 | iPad portrait, iPad 1/2 split, large phones landscape, foldables |
| `wide` | ≥ 1024 | iPad landscape, desktop windows |

Implemented in `assets/ios_adaptive_layout.dart` (`IOSSizeClass`, `context.sizeClass`). Keep the thresholds in one place so the whole app changes together.

## 4. Pattern per size class
| Concern | compact | regular | wide |
|---|---|---|---|
| Primary navigation | Bottom tab bar | Sidebar (collapsible) or tab bar | Persistent sidebar |
| List → detail | Push (nav stack) | Split view: list + detail | Split view; optional 3rd column (inspector) |
| Screen margin | 16 | 20 | 24, content capped at readable width |
| Settings / forms | Full-width inset grouped | Centered, max ≈ 672 | Centered, max ≈ 672 (or two-pane settings) |
| Card grids | 1–2 columns | 2–3 columns | 3–5 columns |
| Modals | Bottom page sheet | Centered form sheet (≈ 540 × 620) | Centered form sheet |
| Action sheets | Bottom action sheet | Popover anchored to trigger (or constrained sheet) | Popover |
| Text styles | Same iOS scale | Same | Same (do not scale fonts up) |

Typography and touch-target sizes do **not** grow on tablets; whitespace, columns, and structure do. Large stretched text and giant buttons are the hallmark of a phone layout naively scaled up.

## 5. Layout rules
- **Readable width**: long-form text, forms, and settings should not exceed ≈ 672 pt wide. Center them (`IOSReadableWidth` / `IOSSliverReadable`). Full-bleed backgrounds can still extend to the edges.
- **Primary buttons**: full width on compact; on regular/wide cap width (≈ 320–400) and center, or keep inside a readable-width container.
- **Grids**: use `SliverGridDelegateWithMaxCrossAxisExtent` so column count follows width automatically (e.g., `maxCrossAxisExtent: 280`), not hardcoded `crossAxisCount` per device.
- **Images/media**: constrain with `AspectRatio` and max sizes; avoid upscaling low-res assets.
- **Dense data (tables, POS item grids, dashboards)**: tablets are where these shine. Give regular/wide a true multi-pane layout (e.g., POS: menu grid left, current order right; ERP: list left, detail right) instead of centered phone UI.
- **Keep one source of truth for state**; the compact and regular layouts are different *views* of the same state, so rotating or resizing never loses selection, scroll position, or form input. Use `IndexedStack`, `PageStorageKey`, or state held above the `LayoutBuilder`.
- **Safe areas**: still apply on iPad (home indicator, rounded corners, status bar in Stage Manager).
- **Sidebar**: ≈ 280–320 pt wide, `secondarySystemBackground`, selected row with rounded tint highlight, section headers in footnote caps. Items = same destinations as the tab bar.

## 6. Tablet-specific behaviors worth adding
- **Pointer/hover**: iPad with trackpad/mouse. Wrap custom tappables in `MouseRegion` with a subtle hover background; use `SystemMouseCursors.click`.
- **Keyboard**: add shortcuts for primary actions with `CallbackShortcuts` (e.g., ⌘N new, ⌘F search, ⌘W close). Tab/arrow focus traversal must work in forms and lists.
- **Context menus**: long-press/right-click via `CupertinoContextMenu` on rows and cards.
- **Drag & drop** (optional): reorder lists, drag items between panes.
- **Scrolling**: show `CupertinoScrollbar` in long lists when a pointer is present.

```dart
CallbackShortcuts(
  bindings: {
    const SingleActivator(LogicalKeyboardKey.keyN, meta: true): onNew,
    const SingleActivator(LogicalKeyboardKey.keyF, meta: true): focusSearch,
  },
  child: Focus(autofocus: true, child: content),
)
```

## 6b. Android specifics for adaptive layout
- **Foldables**: read `MediaQuery.displayFeaturesOf(context)` and wrap hinge-aware screens in `DisplayFeatureSubScreen` so panes never straddle a hinge; keep state when the device folds/unfolds (the width change is just another resize).
- **Multi-window / freeform / desktop mode**: Android windows resize freely, so width-based layout is mandatory; never assume a fixed tablet size.
- **Navigation modes**: bottom inset differs between 3-button and gesture navigation; the sidebar and tab bar must clear it.
- **Back**: in split view, system back first clears the detail selection on `regular`/`wide` if the detail was opened inline, then pops; implement with `PopScope`.
- **Input**: tablets and Chromebooks bring mouse, trackpad, and keyboard; the hover/shortcut/focus guidance applies equally.

## 7. Orientation & multitasking
- iPad apps should support **all four orientations**; locking to portrait disables multitasking participation. Only lock orientation for truly orientation-specific screens (camera, video).
- iOS: ensure `UISupportedInterfaceOrientations~ipad` in `ios/Runner/Info.plist` lists all orientations and do not set `UIRequiresFullScreen` unless required.
- Android: keep the activity resizable (`android:resizeableActivity="true"`), and do not lock `screenOrientation` for the whole app.
- Phones in landscape are `compact` height; keep content scrollable rather than designing a bespoke landscape layout, unless the app is landscape-first (e.g., POS on a tablet).

## 8. Testing (do this before calling the redesign done)
Test at least: **393×852** and **360×640** (phones), **412×915** (Android phone), **744×1133** and **820×1180** (iPad portrait), **1180×820** (iPad landscape), **320×568-ish** narrow split. Verify no `RenderFlex overflow`, selection survives resize, and text at 200% scale still scrolls rather than clips.

```dart
testWidgets('layout adapts without overflow', (tester) async {
  for (final size in const [Size(393, 852), Size(820, 1180), Size(1180, 820)]) {
    tester.view.physicalSize = size * 2;
    tester.view.devicePixelRatio = 2;
    addTearDown(tester.view.reset);
    await tester.pumpWidget(const MyApp());
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull, reason: 'overflow at $size');
  }
});
```
Manual: run on iPad simulator, drag the divider in Split View, rotate, toggle Dark Mode, set Larger Text. For quick iteration, `flutter run -d macos`/`chrome` and resize the window across the 600 and 1024 thresholds.

## 9. Anti-patterns
- Phone UI stretched edge to edge on iPad (full-width list rows 800 pt wide, giant buttons).
- Hardcoded widths/heights, or `crossAxisCount: 2` for every device.
- Detecting tablet once at startup (breaks in Split View/rotation).
- Locking orientation globally.
- Different features/screens only on tablet; content parity matters, only presentation changes.
- Bottom tab bar on a 1200 pt-wide window with three tiny icons: use a sidebar.
- Scaling font sizes up on tablets instead of using whitespace and columns.
