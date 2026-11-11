import 'package:flutter/material.dart';

import '../../data/dummy_data.dart';
import '../../helper/constant.dart';
import '../../screens/history_detail.dart';
import '../../widget/app_widgets.dart';

class HistoryTab extends StatefulWidget {
  const HistoryTab({super.key});

  @override
  State<HistoryTab> createState() => _HistoryTabState();
}

class _HistoryTabState extends State<HistoryTab> {
  String _filter = 'Semua';
  static const _filters = ['Semua', 'Daun', 'Buah'];

  List<DiagnosisSample> get _results => diagnosisSamples
      .where((item) => _filter == 'Semua' || item.type == _filter)
      .toList();

  @override
  Widget build(BuildContext context) {
    final results = _results;
    return ListView(
      padding: const EdgeInsets.fromLTRB(
        pagePadding,
        space16,
        pagePadding,
        space24,
      ),
      children: [
        ScreenHeader(
          title: 'Riwayat Diagnosis',
          eyebrow: 'CATATAN PEMINDAIAN',
          trailing: AppIconButton(
            icon: Icons.calendar_month_outlined,
            onPressed: () => ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Riwayat ditampilkan untuk 18 Okt 2026'),
              ),
            ),
          ),
        ),
        const AppSampleLabel(),
        const SizedBox(height: space8),
        Row(
          children: _filters.map((filter) {
            final count = filter == 'Semua'
                ? diagnosisSamples.length
                : diagnosisSamples.where((item) => item.type == filter).length;
            final selected = filter == _filter;
            return Padding(
              padding: const EdgeInsets.only(right: space8),
              child: AppChip(
                label: '$filter ($count)',
                selected: selected,
                onSelected: (_) => setState(() => _filter = filter),
              ),
            );
          }).toList(),
        ),
        const SizedBox(height: space16),
        const Row(
          children: [
            Text(
              'HARI INI',
              style: TextStyle(
                color: mutedColor,
                fontSize: 11,
                fontWeight: FontWeight.w800,
                letterSpacing: 0.9,
              ),
            ),
            SizedBox(width: space8),
            Expanded(child: Divider(height: 1)),
            SizedBox(width: space8),
            Text(
              '18 Okt 2026',
              style: TextStyle(color: mutedColor, fontSize: 11),
            ),
          ],
        ),
        const SizedBox(height: space12),
        ...results.map(
          (item) => Padding(
            padding: const EdgeInsets.only(bottom: space12),
            child: InkWell(
              borderRadius: BorderRadius.circular(cardRadius),
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute<void>(
                  builder: (_) => HistoryDetail(diagnosis: item),
                ),
              ),
              child: AppCard(
                padding: EdgeInsets.zero,
                child: Column(
                  children: [
                    ClipRRect(
                      borderRadius: const BorderRadius.vertical(
                        top: Radius.circular(cardRadius),
                      ),
                      child: Stack(
                        children: [
                          SizedBox(
                            width: double.infinity,
                            height: 145,
                            child: samplePhoto(url: item.photo),
                          ),
                          Positioned(
                            top: 10,
                            left: 10,
                            child: AppBadge(
                              label: item.type.toUpperCase(),
                              icon: item.type == 'Daun'
                                  ? Icons.eco
                                  : Icons.local_florist,
                            ),
                          ),
                          Positioned(
                            top: 10,
                            right: 10,
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 5,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.95),
                                borderRadius: BorderRadius.circular(
                                  smallRadius,
                                ),
                              ),
                              child: Text(
                                '${item.accuracy}%',
                                style: const TextStyle(
                                  color: darkGreen,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.all(space12),
                      child: Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  item.title,
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  '${item.pot} • ${item.date}',
                                  style: const TextStyle(
                                    color: mutedColor,
                                    fontSize: 11,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    Text(
                                      item.sync,
                                      style: const TextStyle(
                                        color: mutedColor,
                                        fontSize: 11,
                                        fontWeight: FontWeight.w700,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          const Icon(
                            Icons.chevron_right,
                            color: subtleColor,
                            size: 19,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
        if (results.isEmpty)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: space24),
            child: Center(
              child: Text(
                'Belum ada riwayat untuk filter ini. Pilih kategori lain.',
                style: TextStyle(color: mutedColor, fontSize: 11),
              ),
            ),
          ),
        const SizedBox(height: space8),
        const Center(
          child: Text(
            'Status sinkronisasi pada daftar ini adalah contoh.',
            style: TextStyle(color: mutedColor, fontSize: 11),
          ),
        ),
      ],
    );
  }
}
