# Material → Cupertino / iOS Pattern Map

Contents: App shell · Lists & forms · Buttons · Inputs · Overlays · Feedback · Tablet equivalents · Snippets

Verify API availability against the user's Flutter version (`flutter --version`); a few newer widgets (e.g. `showCupertinoSheet`, `CupertinoSheetRoute`) require recent stable releases. When unsure, say so and offer a fallback.

## App shell
| Material | iOS replacement |
|---|---|
| `MaterialApp` | `CupertinoApp` (+ `CupertinoThemeData`) |
| `Scaffold` | `CupertinoPageScaffold` |
| `AppBar` | `CupertinoNavigationBar` / `CupertinoSliverNavigationBar` (large title) |
| `BottomNavigationBar` / `NavigationBar` | `CupertinoTabBar` inside `CupertinoTabScaffold` |
| `Drawer` | Tab bar + a "More"/Settings tab or pushed screen |
| `FloatingActionButton` | Trailing nav-bar button (`CupertinoButton` + `CupertinoIcons.add`) or in-content filled button |
| `TabBar` (top) | `CupertinoSlidingSegmentedControl` for 2–4 views |

## Lists & forms
| Material | iOS replacement |
|---|---|
| `ListView` + `ListTile` | `CupertinoListSection.insetGrouped` + `CupertinoListTile` |
| `Card` | Grouped section or container with 12 pt radius on `secondarySystemGroupedBackground`, no elevation |
| `Divider` | Hairline separator 0.33–0.5 pt, inset to text start |
| `Dismissible` | Swipe actions (e.g. `flutter_slidable` styled iOS-like) or `CupertinoContextMenu` |
| `RefreshIndicator` | `CupertinoSliverRefreshControl` |
| `Checkbox` / `Switch` | `CupertinoSwitch` (prefer switch for settings) |
| `DropdownButton` | `CupertinoContextMenu`/popup menu, `CupertinoPicker` in `showCupertinoModalPopup` |

## Buttons
| Material | iOS replacement |
|---|---|
| `ElevatedButton` | `CupertinoButton.filled` |
| `TextButton` | `CupertinoButton` (plain, tint text) |
| `OutlinedButton` | `CupertinoButton.tinted` (or gray fill) |
| `IconButton` | `CupertinoButton(padding: EdgeInsets.zero, minimumSize: Size(44,44))` |

Note: `CupertinoButton`'s sizing parameter has changed across versions (`minSize` → `minimumSize`). Check the project's Flutter version.

## Inputs
| Material | iOS replacement |
|---|---|
| `TextField` | `CupertinoTextField` / `CupertinoTextFormFieldRow` inside a `CupertinoFormSection` |
| `SearchBar` | `CupertinoSearchTextField` |
| `DatePicker` | `CupertinoDatePicker` in modal popup, or inline compact |
| `Slider` | `CupertinoSlider` |
| `SegmentedButton` | `CupertinoSlidingSegmentedControl` |

## Overlays
| Material | iOS replacement |
|---|---|
| `AlertDialog` | `CupertinoAlertDialog` via `showCupertinoDialog` |
| `showModalBottomSheet` | `showCupertinoSheet` (page-sheet style) or `showCupertinoModalPopup` |
| `BottomSheet` action list | `CupertinoActionSheet` |
| `PopupMenuButton` | `CupertinoContextMenu` or `CupertinoMenuAnchor` where available |
| `SnackBar` | Inline banner / undo row / toast-like overlay (custom, brief) |
| `Tooltip` | Not an iOS pattern; use labels or long-press context menu |

## Feedback
| Material | iOS replacement |
|---|---|
| `CircularProgressIndicator` | `CupertinoActivityIndicator` |
| `LinearProgressIndicator` | Thin custom progress bar (4 pt, rounded) |
| Ripple (`InkWell`) | Opacity press state (`CupertinoButton` handles it) or `GestureDetector` with highlight |

## Tablet equivalents (regular / wide widths)
| Phone pattern | Tablet pattern | Helper (`assets/ios_adaptive_layout.dart`) |
|---|---|---|
| Bottom tab bar | Sidebar | `IOSAdaptiveScaffold` |
| Push list → detail | Split view (list + detail) | `IOSMasterDetail` |
| Full-width form | Centered at ≈ 672 pt | `IOSReadableWidth`, `IOSSliverReadable` |
| Bottom modal sheet | Centered form sheet ≈ 540×620 | `showIOSAdaptiveSheet` |
| Bottom action sheet | Popover anchored to trigger | custom overlay / constrained `CupertinoActionSheet` |
| 2-column grid | Width-driven columns | `iosAdaptiveGridDelegate` |

---

## Snippets

### App shell with tabs
```dart
CupertinoApp(
  theme: buildIOSTheme(),
  home: CupertinoTabScaffold(
    tabBar: CupertinoTabBar(items: const [
      BottomNavigationBarItem(icon: Icon(CupertinoIcons.house), activeIcon: Icon(CupertinoIcons.house_fill), label: 'Home'),
      BottomNavigationBarItem(icon: Icon(CupertinoIcons.search), label: 'Search'),
      BottomNavigationBarItem(icon: Icon(CupertinoIcons.settings), activeIcon: Icon(CupertinoIcons.settings_solid), label: 'Settings'),
    ]),
    tabBuilder: (context, i) => CupertinoTabView(builder: (_) => pages[i]),
  ),
);
```
`CupertinoTabView` gives each tab its own navigator so state and back stack persist.

### Adaptive app shell (tab bar on phone, sidebar on tablet)
```dart
CupertinoApp(
  theme: buildIOSTheme(),
  home: IOSAdaptiveScaffold(destinations: [
    IOSDestination(label: 'Home', icon: CupertinoIcons.house, selectedIcon: CupertinoIcons.house_fill, builder: (_) => const HomePage()),
    IOSDestination(label: 'Orders', icon: CupertinoIcons.doc_text, selectedIcon: CupertinoIcons.doc_text_fill, builder: (_) => const OrdersPage()),
    IOSDestination(label: 'Settings', icon: CupertinoIcons.settings, selectedIcon: CupertinoIcons.settings_solid, builder: (_) => const SettingsPage()),
  ]),
);
```

### List → detail that adapts
```dart
IOSMasterDetail<Order>(
  masterBuilder: (context, onSelect, selected) => OrderList(onSelect: onSelect, selected: selected),
  detailBuilder: (context, order) => OrderDetailPage(order: order),
  emptyDetail: const IOSEmptyState(icon: CupertinoIcons.doc_text, title: 'Select an order'),
)
```

### Centered, readable form content inside a scroll view
```dart
CustomScrollView(slivers: [
  const CupertinoSliverNavigationBar(largeTitle: Text('Settings')),
  IOSSliverReadable(sliver: SliverToBoxAdapter(child: settingsSections)),
])
```

### Large-title screen with inset grouped list
```dart
CupertinoPageScaffold(
  child: CustomScrollView(slivers: [
    const CupertinoSliverNavigationBar(largeTitle: Text('Settings')),
    SliverToBoxAdapter(
      child: CupertinoListSection.insetGrouped(
        header: const Text('ACCOUNT'),
        children: [
          CupertinoListTile.notched(
            leading: const Icon(CupertinoIcons.person_crop_circle),
            title: const Text('Profile'),
            additionalInfo: const Text('Asdf'),
            trailing: const CupertinoListTileChevron(),
            onTap: () {},
          ),
        ],
      ),
    ),
  ]),
);
```
In a sliver nav bar with grouped content, set `backgroundColor: CupertinoColors.systemGroupedBackground` on the page scaffold so the list sits on the right background.

### Destructive confirmation
```dart
showCupertinoDialog<void>(
  context: context,
  builder: (_) => CupertinoAlertDialog(
    title: const Text('Delete item?'),
    content: const Text('This can’t be undone.'),
    actions: [
      CupertinoDialogAction(isDefaultAction: true, onPressed: () => Navigator.pop(context), child: const Text('Cancel')),
      CupertinoDialogAction(isDestructiveAction: true, onPressed: onDelete, child: const Text('Delete')),
    ],
  ),
);
```

### Press feedback on custom tappable
```dart
CupertinoButton(
  padding: EdgeInsets.zero,
  onPressed: onTap,
  child: child,
)
```
Prefer wrapping custom cards in `CupertinoButton` (with `pressedOpacity: 0.6`) rather than `InkWell`, so the press state matches iOS.

## Useful packages (verify current maintenance before recommending)
- `cupertino_icons` — iOS-style icon set (SF-like)
- `flutter_slidable` — swipe actions on rows
- `sf_symbols`-style packages — exist but check license/maintenance; SF Symbols are Apple-platform-only
- `adaptive_dialog` / `.adaptive` constructors — for cross-platform apps
