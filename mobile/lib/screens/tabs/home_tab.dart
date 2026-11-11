import 'package:flutter/material.dart';

import '../../data/dummy_data.dart';
import '../../helper/constant.dart';
import '../../helper/session.dart';
import '../../widget/app_widgets.dart';

class HomeTab extends StatelessWidget {
  const HomeTab({required this.onSelectTab, super.key});

  final ValueChanged<int> onSelectTab;

  @override
  Widget build(BuildContext context) {
    final user = Session.current;
    final name = user?.name.trim().isNotEmpty == true
        ? user!.name.trim().split(' ').first
        : 'Operator';
    final role = user?.role.toLowerCase() == 'petani'
        ? 'Operator Kebun'
        : (user?.role ?? 'Operator Kebun');
    final initials = _initials(user?.name ?? 'Operator');

    return ListView(
      padding: const EdgeInsets.fromLTRB(
        pagePadding,
        space16,
        pagePadding,
        space24,
      ),
      children: [
        Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'HALO,',
                    style: TextStyle(
                      color: mutedColor,
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      letterSpacing: 1.4,
                    ),
                  ),
                  const SizedBox(height: space4),
                  Text(
                    name,
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.w800,
                      letterSpacing: -0.8,
                    ),
                  ),
                  const SizedBox(height: space4),
                  Text(
                    role,
                    style: const TextStyle(color: mutedColor, fontSize: 11),
                  ),
                ],
              ),
            ),
            Container(
              width: 44,
              height: 44,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: lightGreen,
                borderRadius: BorderRadius.circular(cardRadius),
              ),
              child: Text(
                initials,
                style: const TextStyle(
                  color: darkGreen,
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
            const SizedBox(width: 8),
            AppIconButton(
              icon: Icons.notifications_none,
              onPressed: () => _showAlerts(context),
            ),
          ],
        ),
        const SizedBox(height: space8),
        const AppSampleLabel(),
        const SizedBox(height: space16),
        Container(
          padding: const EdgeInsets.symmetric(
            horizontal: space12,
            vertical: space12,
          ),
          decoration: BoxDecoration(
            color: const Color(0xFFEAF5FF),
            borderRadius: BorderRadius.circular(controlRadius),
            border: Border.all(color: const Color(0xFFD8EAFE)),
          ),
          child: Row(
            children: [
              const Icon(
                Icons.cloud_outlined,
                color: Color(0xFF2563EB),
                size: 23,
              ),
              const SizedBox(width: space8),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      weatherLocation,
                      style: TextStyle(
                        fontWeight: FontWeight.w800,
                        fontSize: 12,
                      ),
                    ),
                    SizedBox(height: space4),
                    Text(
                      'Cuaca lokal',
                      style: TextStyle(color: mutedColor, fontSize: 11),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: space8),
              const Text(
                weatherHumidity,
                style: TextStyle(
                  color: mutedColor,
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: space12),
        InkWell(
          onTap: () => _showAlerts(context),
          borderRadius: BorderRadius.circular(cardRadius),
          child: Container(
            padding: const EdgeInsets.all(space12),
            decoration: BoxDecoration(
              color: dangerBackground,
              borderRadius: BorderRadius.circular(cardRadius),
              border: Border.all(color: dangerBorder),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 34,
                  height: 34,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(controlRadius),
                  ),
                  child: const Icon(
                    Icons.warning_amber_rounded,
                    color: dangerColor,
                    size: 20,
                  ),
                ),
                const SizedBox(width: space8),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        activeAlertTitle,
                        style: TextStyle(
                          color: dangerText,
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        activeAlertDescription,
                        style: TextStyle(
                          color: dangerMuted,
                          fontSize: 12,
                          height: 1.45,
                        ),
                      ),
                    ],
                  ),
                ),
                const Icon(Icons.chevron_right, color: dangerMuted, size: 18),
              ],
            ),
          ),
        ),
        const SizedBox(height: space16),
        AppButton(
          label: 'Mulai Pemindaian Tanaman',
          icon: Icons.center_focus_strong,
          trailingIcon: Icons.arrow_forward,
          onPressed: () => onSelectTab(1),
        ),
        const SizedBox(height: space20),
        const SectionHeading(
          title: 'RINGKASAN KEBUN',
          subtitle: 'Kondisi saat ini',
        ),
        Row(
          children: [
            Expanded(
              child: _MetricCard(
                icon: Icons.thermostat_outlined,
                label: 'Suhu Udara',
                value: '$currentAirTemperature°C',
                note: 'Stabil',
                color: const Color(0xFFFFF7ED),
                iconColor: const Color(0xFFEA580C),
              ),
            ),
            const SizedBox(width: space8),
            Expanded(
              child: _MetricCard(
                icon: Icons.water_drop_outlined,
                label: 'Kelembapan Tanah',
                value: '$averageSoilMoisture%',
                note: 'Rata-rata',
                color: paleGreen,
                iconColor: primaryColor,
              ),
            ),
          ],
        ),
        const SizedBox(height: space20),
        SectionHeading(
          title: 'AKTIVITAS TERBARU',
          subtitle: activityHeading,
          trailing: TextButton(
            onPressed: () => onSelectTab(3),
            child: const Text(
              'Lihat semua',
              style: TextStyle(
                color: primaryColor,
                fontSize: 11,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ),
        if (recentActivities.isEmpty)
          const AppCard(
            child: Text(
              'Belum ada aktivitas contoh.',
              style: TextStyle(color: mutedColor, fontSize: 12),
            ),
          ),
        ...recentActivities.map(
          (activity) => Padding(
            padding: const EdgeInsets.only(bottom: space8),
            child: InkWell(
              onTap: () => onSelectTab(3),
              borderRadius: BorderRadius.circular(controlRadius),
              child: AppCard(
                padding: const EdgeInsets.symmetric(
                  horizontal: space12,
                  vertical: space12,
                ),
                child: Row(
                  children: [
                    Container(
                      width: 35,
                      height: 35,
                      decoration: BoxDecoration(
                        color: activity.warning ? dangerBackground : paleGreen,
                        borderRadius: BorderRadius.circular(controlRadius),
                      ),
                      child: Icon(
                        activity.warning
                            ? Icons.coronavirus_outlined
                            : Icons.eco_outlined,
                        color: activity.warning ? dangerColor : primaryColor,
                        size: 18,
                      ),
                    ),
                    const SizedBox(width: space8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${activity.pot} • ${activity.text}',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          const SizedBox(height: space4),
                          Text(
                            activity.time,
                            style: const TextStyle(
                              color: mutedColor,
                              fontSize: 11,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Text(
                      activity.score,
                      style: const TextStyle(
                        color: primaryColor,
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(width: 3),
                    const Icon(
                      Icons.chevron_right,
                      color: subtleColor,
                      size: 17,
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }

  String _initials(String value) {
    final parts = value
        .trim()
        .split(RegExp(r'\s+'))
        .where((part) => part.isNotEmpty);
    return parts.take(2).map((part) => part[0].toUpperCase()).join();
  }
}

class _MetricCard extends StatelessWidget {
  const _MetricCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.note,
    required this.color,
    required this.iconColor,
  });

  final IconData icon;
  final String label;
  final String value;
  final String note;
  final Color color;
  final Color iconColor;

  @override
  Widget build(BuildContext context) {
    return AppCard(
      padding: const EdgeInsets.all(space12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 31,
            height: 31,
            decoration: BoxDecoration(
              color: color,
              borderRadius: BorderRadius.circular(controlRadius),
            ),
            child: Icon(icon, size: 18, color: iconColor),
          ),
          const SizedBox(height: space12),
          Text(
            label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(color: mutedColor, fontSize: 11),
          ),
          const SizedBox(height: space4),
          Text(
            value,
            style: const TextStyle(
              fontSize: 21,
              fontWeight: FontWeight.w800,
              letterSpacing: -0.6,
            ),
          ),
          Text(
            note,
            style: const TextStyle(
              color: primaryColor,
              fontSize: 11,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}

void _showAlerts(BuildContext context) {
  showAppBottomSheet<void>(
    context: context,
    builder: (context) => Padding(
      padding: const EdgeInsets.fromLTRB(20, space8, 20, space24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Peringatan Aktif',
            style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: space4),
          const Text(
            'Pantau kondisi yang membutuhkan perhatian.',
            style: TextStyle(color: mutedColor, fontSize: 11),
          ),
          const SizedBox(height: space8),
          const AppSampleLabel(),
          const SizedBox(height: space16),
          AppCard(
            color: dangerBackground,
            border: Border.all(color: dangerBorder),
            child: const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                AppBadge(label: 'PERINGATAN • BARU', danger: true),
                SizedBox(height: space8),
                Text(
                  activeAlertTitle,
                  style: TextStyle(
                    color: dangerText,
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                SizedBox(height: space4),
                Text(
                  activeAlertDescription,
                  style: TextStyle(
                    color: dangerMuted,
                    fontSize: 11,
                    height: 1.5,
                  ),
                ),
                SizedBox(height: space12),
                Text(
                  'Blok A • Sensor lingkungan • 10:40 WIB',
                  style: TextStyle(
                    color: dangerMuted,
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    ),
  );
}
