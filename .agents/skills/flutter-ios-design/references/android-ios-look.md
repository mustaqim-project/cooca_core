# iOS Look on Android (Flutter)

Contents: 1 Principle · 2 Decision table · 3 Fonts · 4 Scrolling · 5 Back navigation · 6 System bars & edge-to-edge · 7 Material ancestors (App shell choice) · 8 Text input & selection · 9 Press feedback & haptics · 10 Localization · 11 Accessibility on Android · 12 Android config · 13 Test matrix · 14 Pitfalls

## 1. Principle
**Look = iOS. Contracts = Android.** The visual language (type scale, spacing, grouped lists, large titles, tab bar, sheets, press states, translucency) follows Apple's HIG on both platforms. But Android users still rely on platform contracts the app must honor: the system back button/gesture, TalkBack, font and display-size settings, system share/permission/notification UI, the status/navigation bars, and window resizing. Copy the skin, never break the plumbing.

Never mimic iOS *system* UI. Permission prompts, notifications, share sheet, biometric prompt, and the keyboard stay native on each platform.

## 2. Decision table
| Concern | iOS | Android (iOS look) |
|---|---|---|
| Font | System SF (automatic) | Bundled **Inter** (OFL). Never bundle SF: Apple's license limits it to Apple platforms |
| Type scale / spacing / radii | HIG | Same values |
| Scroll physics | Bouncing | Bouncing via `IOSScrollBehavior` (needed by pull-to-refresh and large-title collapse) |
| Page transition | Cupertino push + swipe-back | Same (`CupertinoPageRoute` / Cupertino transitions builder) **plus** a visible back button and working system back |
| Press feedback | Opacity | Same; no Material ripple (`NoSplash`) |
| Switches, sliders, pickers, dialogs, sheets | Cupertino | Cupertino (they render the same on Android) |
| Haptics | Taptic engine | `HapticFeedback` works on Android; keep to selection/light impacts, devices vary |
| Status bar / nav bar | Translucent | Edge-to-edge, transparent, icon brightness set per theme |
| Keyboard, IME actions, autofill | System | System (don't restyle) |
| Share, permissions, notifications | System | System |

## 3. Fonts
Cupertino defaults reference `.SF Pro Text/Display` families that do not exist on Android; Flutter silently falls back to Roboto, so the result looks "almost iOS but off".

1. Bundle Inter (variable or Regular 400 / Medium 500 / SemiBold 600 / Bold 700) in `assets/fonts/` and declare it:
```yaml
flutter:
  fonts:
    - family: Inter
      fonts:
        - asset: assets/fonts/Inter-Regular.ttf
        - asset: assets/fonts/Inter-Medium.ttf
          weight: 500
        - asset: assets/fonts/Inter-SemiBold.ttf
          weight: 600
        - asset: assets/fonts/Inter-Bold.ttf
          weight: 700
```
2. Use `IOSText` (platform-aware) and `buildIOSTheme()`, which sets **every** `CupertinoTextThemeData` slot (nav title, large title, tab label, pickers, actions). Missing one slot is the usual reason a nav bar or picker looks off on Android.
3. Bundle the font; do not fetch at runtime (`google_fonts` downloads fail offline and flash a fallback). If `google_fonts` is already in the project, ship the files as assets and disable runtime fetching.
4. Tracking: SF uses size-dependent tracking; Inter needs slight negative tracking at larger sizes. `IOSPlatform.trackingFor` approximates this; tune by eye at 17 pt, 28 pt, 34 pt.
5. Numbers: use `IOSText.tabular(...)` for prices, quantities, timers.

## 4. Scrolling
Android's default is clamping with a glow/stretch. Cupertino widgets that depend on overscroll misbehave on it: `CupertinoSliverRefreshControl` never triggers and the large title feels stiff.
- Set `scrollBehavior: const IOSScrollBehavior()` on the app (both shells do it).
- Lists that may be shorter than the viewport still need pull-to-refresh: `AlwaysScrollableScrollPhysics` is included in the behavior's physics.
- Trade-off to state in your reply: rubber-banding on Android is a deliberate fidelity choice. If the product wants native Android scrolling, drop the behavior and replace pull-to-refresh with `RefreshIndicator` on Android only.

## 5. Back navigation (highest-risk area)
- Android has a system back button/gesture. The app must honor it everywhere. `Navigator.pop`-based routes do this automatically; custom stacks, tab shells, and sheets must use `PopScope`.
- **Gesture conflict**: with Android gesture navigation, the system consumes left/right edge swipes for back, so Cupertino's in-app edge swipe rarely fires. Always show an explicit back affordance (chevron + previous title, 44×44 hit area). Do not rely on the swipe.
- **Tab roots**: back on a non-first tab should return to the first tab, then exit. Implement with `PopScope(canPop: index == 0, onPopInvokedWithResult: ...)` (check the signature for the project's Flutter version; older versions use `onPopInvoked`).
- **Sheets/dialogs**: system back dismisses them (default for routes); custom overlays need `PopScope`.
- **Predictive back** (Android 13+, default for opted-in apps on 15): to opt in, set `android:enableOnBackInvokedCallback="true"` in `AndroidManifest.xml`. Flutter's predictive-back animation is a Material page transition; with iOS-style transitions you keep the Cupertino animation and the system still delivers the back event. Test back from every screen type on an Android 14/15 device and say plainly if the animation differs from stock.

```dart
PopScope(
  canPop: _tab == 0,
  onPopInvokedWithResult: (didPop, _) { if (!didPop) setState(() => _tab = 0); },
  child: tabs,
)
```

## 6. System bars & edge-to-edge
- Call `await IOSSystemUi.init()` in `main()` (after `WidgetsFlutterBinding.ensureInitialized()`).
- Apps targeting Android SDK 35 (Android 15) are edge-to-edge regardless; designing for it everywhere avoids content hidden behind bars.
- Wrap with `IOSSystemUiScope` (done by `IOSApp`/`IOSHybridApp`) so status-bar and navigation-bar icon brightness follow light/dark. For a page with a dark photo header, pass `brightness: Brightness.dark` on that page only.
- Use `MediaQuery.paddingOf(context)` / `SafeArea`. With 3-button navigation the bottom inset equals the nav-bar height, with gesture navigation it is a slim handle; both must clear the tab bar and primary buttons.
- `systemNavigationBarContrastEnforced: false` removes Android's translucent scrim behind 3-button navigation, which is what makes the bottom tab bar blend like iOS.

## 7. Material ancestors: choose the app shell
| Situation | Shell |
|---|---|
| Only Cupertino widgets and own components | `IOSApp` (CupertinoApp) |
| Any widget/package needing Material: `TextField`, `Slider`, `Checkbox`, `showDatePicker`, `ReorderableListView`, charts, file/image pickers, many third-party packages | `IOSHybridApp` (MaterialApp wearing Cupertino theme) |

Symptoms that mean you need the hybrid shell: `No Material widget found`, `No MaterialLocalizations found`, `Scaffold.of() ... `. Quick patch for a single widget is `Material(type: MaterialType.transparency, child: ...)`, but if it recurs, switch shells instead of sprinkling patches.

In the hybrid shell: ripples are disabled (`NoSplash`), page transitions are Cupertino for every platform, and `forceIOSPlatform: true` optionally sets `ThemeData.platform` to iOS for Material widgets that read it. That flag does not affect code reading `defaultTargetPlatform` (e.g., some text-field internals), so verify on a device.

## 8. Text input & selection
- Prefer `CupertinoTextField` / `CupertinoTextFormFieldRow` in `CupertinoFormSection` for the iOS look. On Android its selection handles/toolbar can still follow Material unless `selectionControls: cupertinoTextSelectionControls` is passed; check the project's Flutter version and test long-press selection on a device.
- Keep Android conventions that users depend on: correct `keyboardType`, `textInputAction` (next/done/search), `autofillHints`, and `inputFormatters`. Don't reimplement the IME.
- Scroll the focused field into view with the keyboard open (`resizeToAvoidBottomInset`, `viewInsets`); test with Gboard and a third-party keyboard.
- Min font size 16 isn't required on Android (it is on iOS web), but keep body 17 for consistency.

## 9. Press feedback & haptics
- Pressed state = opacity ~0.4–0.6 (`CupertinoButton` does this). Replace `InkWell`/`ElevatedButton` ripples.
- `HapticFeedback.selectionClick()` for pickers/segments, `lightImpact()` for primary actions. Android devices differ (some have weak motors, users can disable touch vibration), so haptics must never be the only feedback.

## 10. Localization
`CupertinoApp` only ships English strings. For Indonesian (or any non-English) users add `flutter_localizations` and pass `GlobalCupertinoLocalizations.delegate`, `GlobalWidgetsLocalizations.delegate`, `GlobalMaterialLocalizations.delegate` plus `supportedLocales`. Without them `CupertinoDatePicker`, selection toolbars (Cut/Copy/Paste), and dialogs show English or throw. Format dates/currency with `intl` using the locale, e.g. `NumberFormat.currency(locale: 'id_ID', symbol: 'Rp', decimalDigits: 0)`.

## 11. Accessibility on Android
- TalkBack: every icon-only button needs `Semantics(label: ..., button: true)`; custom controls need correct roles; focus order must follow reading order.
- Font scale: Android allows up to ~200% font size plus a separate "Display size". Layouts must reflow at 2.0 text scale and large display size; test both together.
- Touch target: 48 dp is Android's guideline, 44 pt is Apple's. Use **48** for tappable areas on Android (the design looks the same; add invisible padding via `minimumSize`).
- Contrast and non-color cues: same requirements as iOS.
- Respect "Remove animations" (`MediaQuery.disableAnimationsOf`).

## 12. Android project config
- `AndroidManifest.xml`: `android:enableOnBackInvokedCallback="true"`; activity `android:resizeableActivity="true"` (default true when targeting recent SDKs); don't set a global `screenOrientation`.
- `android/app/build.gradle`: `compileSdk`/`targetSdk` current; Flutter's Impeller is the renderer on supported devices in recent releases, so test visual effects (blur, backdrop filters) on mid-range hardware.
- Adaptive launcher icon + splash: Android 12+ uses the system splash; set the splash background to the scaffold color and keep the icon centered so the transition into the app doesn't flash a different color.
- `minSdk`: translucent blur (`BackdropFilter`) is expensive on low-end devices; budget it (nav bar, tab bar, sheet only), and offer a solid fallback color when `MediaQuery.disableAnimationsOf` or low-end detection applies.

## 13. Android test matrix
Phones (dp): 360×640 (small), 393×852 / 412×915 (common), 432×960 (large). Tablets: ~800×1280 portrait (Pixel Tablet class) and 1280×800 landscape. Foldables: folded ≈ 360–400 wide, unfolded ≈ 670–840 wide; for hinge/fold, use `MediaQuery.displayFeaturesOf` / `DisplayFeatureSubScreen` so nothing sits across the hinge.
Verify on emulator or device: gesture navigation **and** 3-button navigation, Android 13/14/15, light/dark, font scale 1.3 and 2.0, display size Large, TalkBack on, back from every screen type, keyboard open on each form, rotation.

Android window size classes (Compact < 600 dp, Medium 600–839, Expanded ≥ 840) roughly align with this skill's `compact` / `regular` / `wide` (< 600, 600–1023, ≥ 1024); keep the skill's thresholds so iOS and Android share one layout system.

## 14. Pitfalls
- Setting only `textStyle` in `CupertinoTextThemeData` and leaving nav/tab/picker slots on Roboto.
- Clamping scroll physics making pull-to-refresh dead on Android.
- Relying on swipe-back with no visible back button.
- Forgetting `PopScope` on tab shells so back exits the app from any tab.
- Hardcoded status bar style: black icons on dark screens.
- Content hidden behind the 3-button navigation bar or the gesture handle.
- Mixing Material ripples/FABs/snackbars into an otherwise iOS-styled screen.
- `google_fonts` fetching at runtime, or bundling SF.
- Testing only on iPhone simulators and shipping to Android unseen.
