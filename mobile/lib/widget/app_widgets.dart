import 'package:flutter/material.dart';

import '../helper/constant.dart';

class AppCard extends StatelessWidget {
  const AppCard({
    required this.child,
    this.padding = const EdgeInsets.all(space12),
    this.margin,
    this.color = Colors.white,
    this.border,
    this.radius = cardRadius,
    super.key,
  });

  final Widget child;
  final EdgeInsetsGeometry padding;
  final EdgeInsetsGeometry? margin;
  final Color color;
  final Border? border;
  final double radius;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: margin,
      padding: padding,
      decoration: BoxDecoration(
        color: color,
        border: border ?? Border.all(color: borderColor),
        borderRadius: BorderRadius.circular(radius),
      ),
      child: child,
    );
  }
}

class AppBadge extends StatelessWidget {
  const AppBadge({
    required this.label,
    this.danger = false,
    this.icon,
    super.key,
  });

  final String label;
  final bool danger;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    final foreground = danger ? dangerMuted : darkGreen;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: danger ? const Color(0xFFFFE4E6) : lightGreen,
        borderRadius: BorderRadius.circular(smallRadius),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 13, color: foreground),
            const SizedBox(width: 4),
          ],
          Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              color: foreground,
              fontSize: 11,
              fontWeight: FontWeight.w800,
              height: 1.3,
            ),
          ),
        ],
      ),
    );
  }
}

class AppButton extends StatelessWidget {
  const AppButton({
    required this.label,
    required this.onPressed,
    this.icon,
    this.trailingIcon,
    this.secondary = false,
    this.danger = false,
    this.height = 50,
    super.key,
  });

  final String label;
  final VoidCallback? onPressed;
  final IconData? icon;
  final IconData? trailingIcon;
  final bool secondary;
  final bool danger;
  final double height;

  @override
  Widget build(BuildContext context) {
    final background = secondary
        ? Colors.white
        : danger
        ? dangerColor
        : primaryColor;
    final foreground = secondary ? inkColor : Colors.white;
    return SizedBox(
      width: double.infinity,
      height: height,
      child: OutlinedButton(
        onPressed: onPressed,
        style: OutlinedButton.styleFrom(
          foregroundColor: foreground,
          backgroundColor: background,
          side: BorderSide(color: secondary ? borderColor : background),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(controlRadius),
          ),
          elevation: 0,
          padding: const EdgeInsets.symmetric(horizontal: space16),
        ),
        child: Row(
          children: [
            if (icon != null) ...[
              Icon(icon, size: 18),
              const SizedBox(width: 8),
            ],
            Expanded(
              child: Text(
                label,
                textAlign: secondary ? TextAlign.center : TextAlign.left,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
            if (trailingIcon != null) Icon(trailingIcon, size: 19),
          ],
        ),
      ),
    );
  }
}

class ScreenHeader extends StatelessWidget {
  const ScreenHeader({
    required this.title,
    this.eyebrow,
    this.trailing,
    super.key,
  });

  final String title;
  final String? eyebrow;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: space20),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (eyebrow != null) ...[
                  Text(
                    eyebrow!,
                    style: const TextStyle(
                      color: primaryColor,
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 1.3,
                    ),
                  ),
                  const SizedBox(height: space4),
                ],
                Text(
                  title,
                  style: const TextStyle(
                    color: inkColor,
                    fontSize: 23,
                    height: 1.2,
                    fontWeight: FontWeight.w800,
                    letterSpacing: -0.8,
                  ),
                ),
              ],
            ),
          ),
          ?trailing,
        ],
      ),
    );
  }
}

class AppIconButton extends StatelessWidget {
  const AppIconButton({required this.icon, required this.onPressed, super.key});

  final IconData icon;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      shape: RoundedRectangleBorder(
        side: const BorderSide(color: borderColor),
        borderRadius: BorderRadius.circular(controlRadius),
      ),
      child: InkWell(
        onTap: onPressed,
        borderRadius: BorderRadius.circular(controlRadius),
        child: SizedBox(
          width: 44,
          height: 44,
          child: Center(child: Icon(icon, size: 21, color: inkColor)),
        ),
      ),
    );
  }
}

class SectionHeading extends StatelessWidget {
  const SectionHeading({
    required this.title,
    required this.subtitle,
    this.trailing,
    super.key,
  });

  final String title;
  final String subtitle;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: space8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: primaryColor,
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 1.2,
                  ),
                ),
                const SizedBox(height: space4),
                Text(
                  subtitle,
                  style: const TextStyle(
                    color: inkColor,
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
          ?trailing,
        ],
      ),
    );
  }
}

Future<T?> showAppBottomSheet<T>({
  required BuildContext context,
  required WidgetBuilder builder,
  bool fullHeight = false,
}) {
  return showModalBottomSheet<T>(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    barrierColor: const Color(0x8C020617),
    builder: (context) => FractionallySizedBox(
      heightFactor: fullHeight ? 0.96 : 0.88,
      child: DecoratedBox(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(cardRadius)),
        ),
        child: SafeArea(
          top: false,
          child: Column(
            children: [
              const SizedBox(height: space8),
              Container(
                width: 38,
                height: 4,
                decoration: BoxDecoration(
                  color: const Color(0xFFCBD5E1),
                  borderRadius: BorderRadius.circular(smallRadius),
                ),
              ),
              const SizedBox(height: space8),
              Expanded(child: builder(context)),
            ],
          ),
        ),
      ),
    ),
  );
}

class EmptyPhoto extends StatelessWidget {
  const EmptyPhoto({
    this.message,
    this.loading = false,
    this.iconSize = 34,
    super.key,
  });

  final String? message;
  final bool loading;
  final double iconSize;

  @override
  Widget build(BuildContext context) {
    return ColoredBox(
      color: const Color(0xFFE2E8F0),
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (loading)
              const SizedBox(
                width: 24,
                height: 24,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: mutedColor,
                ),
              )
            else
              Icon(Icons.eco_outlined, size: iconSize, color: mutedColor),
            if (message != null) ...[
              const SizedBox(height: space8),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: space12),
                child: Text(
                  message!,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: mutedColor,
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

Widget samplePhoto({
  required String url,
  BoxFit fit = BoxFit.cover,
  double? width,
  double? height,
  BorderRadius? borderRadius,
}) {
  return ClipRRect(
    borderRadius: borderRadius ?? BorderRadius.zero,
    child: Image.network(
      url,
      width: width,
      height: height,
      fit: fit,
      loadingBuilder: (context, child, progress) {
        if (progress == null) return child;
        return const EmptyPhoto(
          loading: true,
          message: 'Memuat foto contoh...',
        );
      },
      errorBuilder: (context, error, stackTrace) => const EmptyPhoto(
        message: 'Foto contoh tidak tersedia. Periksa koneksi internet.',
      ),
    ),
  );
}

class AppChip extends StatelessWidget {
  const AppChip({
    required this.label,
    required this.selected,
    required this.onSelected,
    this.selectedBackground = primaryColor,
    this.selectedForeground = Colors.white,
    this.unselectedBackground = Colors.white,
    this.unselectedForeground = mutedColor,
    this.unselectedBorder = borderColor,
    super.key,
  });

  final String label;
  final bool selected;
  final ValueChanged<bool> onSelected;
  final Color selectedBackground;
  final Color selectedForeground;
  final Color unselectedBackground;
  final Color unselectedForeground;
  final Color unselectedBorder;

  @override
  Widget build(BuildContext context) {
    final foreground = selected ? selectedForeground : unselectedForeground;
    return ConstrainedBox(
      constraints: const BoxConstraints(minHeight: 44, minWidth: 44),
      child: Material(
        color: selected ? selectedBackground : unselectedBackground,
        shape: RoundedRectangleBorder(
          side: BorderSide(
            color: selected ? selectedBackground : unselectedBorder,
          ),
          borderRadius: BorderRadius.circular(smallRadius),
        ),
        child: InkWell(
          onTap: () => onSelected(!selected),
          borderRadius: BorderRadius.circular(smallRadius),
          child: Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: space12,
              vertical: space8,
            ),
            child: Center(
              widthFactor: 1,
              heightFactor: 1,
              child: Text(
                label,
                style: TextStyle(
                  color: foreground,
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class AppSampleLabel extends StatelessWidget {
  const AppSampleLabel({super.key});

  @override
  Widget build(BuildContext context) {
    return const Text(
      'Data contoh',
      style: TextStyle(
        color: mutedColor,
        fontSize: 11,
        fontWeight: FontWeight.w600,
      ),
    );
  }
}
