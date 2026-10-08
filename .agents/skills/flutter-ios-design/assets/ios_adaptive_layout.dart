// Adaptive layout helpers: phone ↔ tablet. Companion to ios_design_system.dart.
// Verify against the project's Flutter version. Contents:
// 1 Size classes · 2 Readable width · 3 Adaptive scaffold (tab bar ↔ sidebar)
// 4 Master-detail · 5 Adaptive sheet · 6 Adaptive grid delegate

import 'package:flutter/cupertino.dart';

import 'ios_design_system.dart';

// ───────────────────────── 1. Size classes ─────────────────────────
enum IOSSizeClass { compact, regular, wide }

abstract final class IOSBreakpoints {
  static const double regular = 600;
  static const double wide = 1024;
  static const double readableWidth = 672;
  static const double sidebarWidth = 300;
  static const double formSheetWidth = 540;
  static const double formSheetHeight = 620;

  static IOSSizeClass of(double width) {
    if (width >= wide) return IOSSizeClass.wide;
    if (width >= regular) return IOSSizeClass.regular;
    return IOSSizeClass.compact;
  }
}

extension IOSAdaptiveContext on BuildContext {
  /// Size class of the app window (updates on rotation / Split View resize).
  IOSSizeClass get sizeClass => IOSBreakpoints.of(MediaQuery.sizeOf(this).width);
  bool get isCompact => sizeClass == IOSSizeClass.compact;

  /// Horizontal screen margin per size class.
  double get screenMargin => switch (sizeClass) {
        IOSSizeClass.compact => 16,
        IOSSizeClass.regular => 20,
        IOSSizeClass.wide => 24,
      };
}

// ───────────────────────── 2. Readable width ─────────────────────────
/// Centers box content and caps its width (forms, settings, long text).
class IOSReadableWidth extends StatelessWidget {
  const IOSReadableWidth({
    super.key,
    required this.child,
    this.maxWidth = IOSBreakpoints.readableWidth,
  });

  final Widget child;
  final double maxWidth;

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: Alignment.topCenter,
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: maxWidth),
        child: child,
      ),
    );
  }
}

/// Sliver version: centers a sliver and caps its cross-axis width.
class IOSSliverReadable extends StatelessWidget {
  const IOSSliverReadable({
    super.key,
    required this.sliver,
    this.maxWidth = IOSBreakpoints.readableWidth,
  });

  final Widget sliver;
  final double maxWidth;

  @override
  Widget build(BuildContext context) {
    return SliverLayoutBuilder(
      builder: (context, constraints) {
        final extra = (constraints.crossAxisExtent - maxWidth) / 2;
        final horizontal = extra > 0 ? extra : 0.0;
        return SliverPadding(
          padding: EdgeInsets.symmetric(horizontal: horizontal),
          sliver: sliver,
        );
      },
    );
  }
}

// ───────────────────────── 3. Adaptive scaffold ─────────────────────────
class IOSDestination {
  const IOSDestination({
    required this.label,
    required this.icon,
    required this.builder,
    this.selectedIcon,
  });

  final String label;
  final IconData icon;
  final IconData? selectedIcon;
  final WidgetBuilder builder;
}

/// Bottom tab bar on compact widths, persistent sidebar on regular/wide.
/// Each destination keeps its own navigator and state (IndexedStack).
class IOSAdaptiveScaffold extends StatefulWidget {
  const IOSAdaptiveScaffold({
    super.key,
    required this.destinations,
    this.initialIndex = 0,
    this.sidebarHeader,
  });

  final List<IOSDestination> destinations;
  final int initialIndex;
  final Widget? sidebarHeader;

  @override
  State<IOSAdaptiveScaffold> createState() => _IOSAdaptiveScaffoldState();
}

class _IOSAdaptiveScaffoldState extends State<IOSAdaptiveScaffold> {
  late int _index = widget.initialIndex;

  void _select(int i) => setState(() => _index = i);

  @override
  Widget build(BuildContext context) {
    final pages = IndexedStack(
      index: _index,
      children: [
        for (final d in widget.destinations) CupertinoTabView(builder: d.builder),
      ],
    );

    if (context.isCompact) {
      return CupertinoPageScaffold(
        resizeToAvoidBottomInset: false,
        child: Column(
          children: [
            Expanded(
              child: MediaQuery.removePadding(
                context: context,
                removeBottom: true, // tab bar below owns the bottom inset
                child: pages,
              ),
            ),
            CupertinoTabBar(
              currentIndex: _index,
              onTap: _select,
              items: [
                for (final d in widget.destinations)
                  BottomNavigationBarItem(
                    icon: Icon(d.icon),
                    activeIcon: Icon(d.selectedIcon ?? d.icon),
                    label: d.label,
                  ),
              ],
            ),
          ],
        ),
      );
    }

    return CupertinoPageScaffold(
      child: Row(
        children: [
          _IOSSidebar(
            destinations: widget.destinations,
            selectedIndex: _index,
            onSelected: _select,
            header: widget.sidebarHeader,
          ),
          Container(width: 0.5, color: IOSColors.separator.resolveFrom(context)),
          Expanded(child: pages),
        ],
      ),
    );
  }
}

class _IOSSidebar extends StatelessWidget {
  const _IOSSidebar({
    required this.destinations,
    required this.selectedIndex,
    required this.onSelected,
    this.header,
  });

  final List<IOSDestination> destinations;
  final int selectedIndex;
  final ValueChanged<int> onSelected;
  final Widget? header;

  @override
  Widget build(BuildContext context) {
    final accent = IOSColors.accent.resolveFrom(context);
    return SizedBox(
      width: IOSBreakpoints.sidebarWidth,
      child: ColoredBox(
        color: CupertinoColors.secondarySystemBackground.resolveFrom(context),
        child: SafeArea(
          right: false,
          child: ListView(
            padding: const EdgeInsets.all(IOSSpacing.sm),
            children: [
              if (header != null)
                Padding(
                  padding: const EdgeInsets.fromLTRB(IOSSpacing.xs, IOSSpacing.xs, IOSSpacing.xs, IOSSpacing.md),
                  child: header,
                ),
              for (var i = 0; i < destinations.length; i++)
                _SidebarRow(
                  destination: destinations[i],
                  selected: i == selectedIndex,
                  accent: accent,
                  onTap: () => onSelected(i),
                ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SidebarRow extends StatelessWidget {
  const _SidebarRow({
    required this.destination,
    required this.selected,
    required this.accent,
    required this.onTap,
  });

  final IOSDestination destination;
  final bool selected;
  final Color accent;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final fg = selected ? accent : IOSColors.label.resolveFrom(context);
    return Padding(
      padding: const EdgeInsets.only(bottom: 2),
      child: CupertinoButton(
        padding: EdgeInsets.zero,
        pressedOpacity: 0.6,
        onPressed: onTap,
        child: Container(
          constraints: const BoxConstraints(minHeight: IOSSpacing.minTouchTarget),
          padding: const EdgeInsets.symmetric(horizontal: IOSSpacing.sm),
          decoration: BoxDecoration(
            color: selected ? accent.withValues(alpha: 0.12) : null,
            borderRadius: BorderRadius.circular(IOSRadius.cell),
          ),
          child: Row(
            children: [
              Icon(selected ? (destination.selectedIcon ?? destination.icon) : destination.icon,
                  size: 22, color: fg),
              const SizedBox(width: IOSSpacing.sm),
              Expanded(
                child: Text(
                  destination.label,
                  style: IOSText.body.copyWith(
                    color: fg,
                    fontWeight: selected ? FontWeight.w600 : FontWeight.w400,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ───────────────────────── 4. Master–detail ─────────────────────────
/// Compact: master only; selecting pushes the detail route.
/// Regular/wide: master and detail side by side; selecting swaps the detail.
/// detailBuilder should return a CupertinoPageScaffold (with its own nav bar).
class IOSMasterDetail<T> extends StatefulWidget {
  const IOSMasterDetail({
    super.key,
    required this.masterBuilder,
    required this.detailBuilder,
    required this.emptyDetail,
    this.masterWidth = 360,
  });

  /// `selected` is non-null only in split mode, for highlighting the active row.
  final Widget Function(BuildContext context, ValueChanged<T> onSelect, T? selected) masterBuilder;
  final Widget Function(BuildContext context, T item) detailBuilder;
  final Widget emptyDetail;
  final double masterWidth;

  @override
  State<IOSMasterDetail<T>> createState() => _IOSMasterDetailState<T>();
}

class _IOSMasterDetailState<T> extends State<IOSMasterDetail<T>> {
  T? _selected; // lives above LayoutBuilder, so rotation/resizing keeps selection

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final split = constraints.maxWidth >= IOSBreakpoints.regular;

        void onSelect(T item) {
          if (split) {
            setState(() => _selected = item);
          } else {
            Navigator.of(context).push(
              CupertinoPageRoute<void>(builder: (routeContext) => widget.detailBuilder(routeContext, item)),
            );
          }
        }

        final master = widget.masterBuilder(context, onSelect, split ? _selected : null);
        if (!split) return master;

        final selected = _selected;
        return Row(
          children: [
            SizedBox(width: widget.masterWidth, child: master),
            Container(width: 0.5, color: IOSColors.separator.resolveFrom(context)),
            Expanded(
              child: selected == null
                  ? widget.emptyDetail
                  : KeyedSubtree(
                      key: ValueKey<T>(selected),
                      child: widget.detailBuilder(context, selected),
                    ),
            ),
          ],
        );
      },
    );
  }
}

// ───────────────────────── 5. Adaptive sheet ─────────────────────────
/// Bottom sheet on compact; centered form sheet on regular/wide.
Future<R?> showIOSAdaptiveSheet<R>({
  required BuildContext context,
  required WidgetBuilder builder,
}) {
  if (context.isCompact) {
    final height = MediaQuery.sizeOf(context).height * 0.92;
    return showCupertinoModalPopup<R>(
      context: context,
      builder: (ctx) => Align(
        alignment: Alignment.bottomCenter,
        child: SizedBox(
          height: height,
          child: ClipRRect(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(IOSRadius.sheet)),
            child: ColoredBox(
              color: CupertinoColors.systemGroupedBackground.resolveFrom(ctx),
              child: builder(ctx),
            ),
          ),
        ),
      ),
    );
  }

  return showCupertinoDialog<R>(
    context: context,
    barrierDismissible: true,
    builder: (ctx) => Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(
          maxWidth: IOSBreakpoints.formSheetWidth,
          maxHeight: IOSBreakpoints.formSheetHeight,
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(IOSRadius.sheet),
          child: ColoredBox(
            color: CupertinoColors.systemGroupedBackground.resolveFrom(ctx),
            child: builder(ctx),
          ),
        ),
      ),
    ),
  );
}

// ───────────────────────── 6. Adaptive grid ─────────────────────────
/// Column count follows available width. Use in SliverGrid / GridView.
SliverGridDelegateWithMaxCrossAxisExtent iosAdaptiveGridDelegate({
  double maxItemWidth = 280,
  double spacing = IOSSpacing.md,
  double childAspectRatio = 1,
}) {
  return SliverGridDelegateWithMaxCrossAxisExtent(
    maxCrossAxisExtent: maxItemWidth,
    mainAxisSpacing: spacing,
    crossAxisSpacing: spacing,
    childAspectRatio: childAspectRatio,
  );
}
