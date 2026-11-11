import 'package:flutter/material.dart';

import '../../helper/constant.dart';
import '../../helper/session.dart';
import '../../widget/app_widgets.dart';

class ProfileTab extends StatefulWidget {
  const ProfileTab({required this.onLogout, super.key});

  final Future<void> Function() onLogout;

  @override
  State<ProfileTab> createState() => _ProfileTabState();
}

class _ProfileTabState extends State<ProfileTab> {
  bool _offlineMode = false;

  @override
  Widget build(BuildContext context) {
    final user = Session.current;
    final name = user?.name ?? 'Operator Kebun';
    final role = user?.role.toLowerCase() == 'petani'
        ? 'Operator Kebun'
        : (user?.role ?? 'Operator Kebun');
    final initials = name
        .trim()
        .split(RegExp(r'\s+'))
        .where((part) => part.isNotEmpty)
        .take(2)
        .map((part) => part[0].toUpperCase())
        .join();

    return ListView(
      padding: const EdgeInsets.fromLTRB(
        pagePadding,
        space16,
        pagePadding,
        space24,
      ),
      children: [
        const ScreenHeader(title: 'Profil Operator', eyebrow: 'AKUN LAPANGAN'),
        AppCard(
          padding: const EdgeInsets.all(space16),
          child: Row(
            children: [
              Container(
                width: 52,
                height: 52,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: lightGreen,
                  borderRadius: BorderRadius.circular(cardRadius),
                ),
                child: Text(
                  initials.isEmpty ? 'O' : initials,
                  style: const TextStyle(
                    color: darkGreen,
                    fontSize: 17,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
              const SizedBox(width: space12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: space4),
                    Text(
                      user?.email ?? '',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(color: mutedColor, fontSize: 11),
                    ),
                    const SizedBox(height: space8),
                    AppBadge(
                      label: role.toUpperCase(),
                      icon: Icons.verified_user_outlined,
                    ),
                  ],
                ),
              ),
              const Icon(Icons.chevron_right, color: subtleColor, size: 19),
            ],
          ),
        ),
        const SizedBox(height: space16),
        const AppSampleLabel(),
        const SizedBox(height: space8),
        const _ProfileSectionLabel(title: 'Informasi Penugasan'),
        AppCard(
          padding: EdgeInsets.zero,
          child: Column(
            children: [
              _InfoRow(
                icon: Icons.location_on_outlined,
                title: 'Lokasi Kebun',
                subtitle: 'Haifresh Lembang • Blok A',
                onTap: () =>
                    _notify('Informasi penugasan masih berupa data contoh.'),
              ),
              const Divider(height: 1, indent: 54),
              _InfoRow(
                icon: Icons.badge_outlined,
                title: 'ID Operator',
                subtitle: user == null
                    ? '-'
                    : 'BC-${user.id.toString().padLeft(4, '0')}',
                onTap: () =>
                    _notify('Informasi penugasan masih berupa data contoh.'),
              ),
            ],
          ),
        ),
        const SizedBox(height: space16),
        const _ProfileSectionLabel(title: 'Perangkat Lapangan'),
        AppCard(
          padding: EdgeInsets.zero,
          child: Column(
            children: [
              _InfoRow(
                icon: Icons.sensors_outlined,
                title: 'Gateway Sensor',
                subtitle: 'GW-LEMBANG-01 • Data contoh',
                onTap: () =>
                    _notify('Informasi perangkat masih berupa data contoh.'),
              ),
              const Divider(height: 1, indent: 54),
              _InfoRow(
                icon: Icons.phone_android_outlined,
                title: 'Perangkat ini',
                subtitle: 'Field Device • Versi 1.0.0',
                onTap: () =>
                    _notify('Informasi perangkat masih berupa data contoh.'),
              ),
            ],
          ),
        ),
        const SizedBox(height: space16),
        const _ProfileSectionLabel(title: 'Pengaturan Aplikasi'),
        AppCard(
          padding: const EdgeInsets.symmetric(
            horizontal: space12,
            vertical: space4,
          ),
          child: Row(
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(controlRadius),
                ),
                child: const Icon(
                  Icons.cloud_off_outlined,
                  color: mutedColor,
                  size: 18,
                ),
              ),
              const SizedBox(width: space8),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Mode Offline',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    SizedBox(height: space4),
                    Text(
                      'Tidak mengubah koneksi API',
                      style: TextStyle(color: mutedColor, fontSize: 11),
                    ),
                  ],
                ),
              ),
              Switch(
                value: _offlineMode,
                activeThumbColor: primaryColor,
                onChanged: (value) => setState(() => _offlineMode = value),
              ),
            ],
          ),
        ),
        const SizedBox(height: space20),
        AppButton(
          label: 'Keluar / Logout',
          icon: Icons.logout,
          danger: true,
          onPressed: widget.onLogout,
        ),
        const SizedBox(height: space12),
        const Center(
          child: Text(
            'Berry Care Field App • Versi 1.0.0',
            style: TextStyle(color: mutedColor, fontSize: 11),
          ),
        ),
      ],
    );
  }

  void _notify(String message) {
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(message)));
  }
}

class _ProfileSectionLabel extends StatelessWidget {
  const _ProfileSectionLabel({required this.title});
  final String title;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: space8),
      child: Text(
        title.toUpperCase(),
        style: const TextStyle(
          color: mutedColor,
          fontSize: 11,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.9,
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(
          horizontal: space12,
          vertical: space12,
        ),
        child: Row(
          children: [
            Icon(icon, size: 19, color: primaryColor),
            const SizedBox(width: space8),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: space4),
                  Text(
                    subtitle,
                    style: const TextStyle(color: mutedColor, fontSize: 11),
                  ),
                ],
              ),
            ),
            const Icon(Icons.chevron_right, color: subtleColor, size: 17),
          ],
        ),
      ),
    );
  }
}
