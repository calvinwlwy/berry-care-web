import 'package:flutter/material.dart';

import '../../data/dummy_data.dart';
import '../../helper/constant.dart';
import '../../widget/app_widgets.dart';

class PotsTab extends StatefulWidget {
  const PotsTab({super.key});

  @override
  State<PotsTab> createState() => _PotsTabState();
}

class _PotsTabState extends State<PotsTab> {
  final _search = TextEditingController();
  String _filter = 'Semua Pot';
  String? _expandedPot;

  static const _filters = ['Semua Pot', 'Blok A', 'Blok B', 'Perlu Perhatian'];

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  List<PotSample> get _results {
    final query = _search.text.trim().toLowerCase();
    return potSamples.where((pot) {
      final matchesText =
          query.isEmpty ||
          '${pot.id} ${pot.block} ${pot.status}'.toLowerCase().contains(query);
      final matchesFilter = switch (_filter) {
        'Blok A' => pot.block == 'Blok A',
        'Blok B' => pot.block == 'Blok B',
        'Perlu Perhatian' => pot.attention,
        _ => true,
      };
      return matchesText && matchesFilter;
    }).toList();
  }

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
        const ScreenHeader(title: 'Status Polybag', eyebrow: 'TELEMETRI IOT'),
        const AppSampleLabel(),
        const SizedBox(height: space12),
        SizedBox(
          height: 48,
          child: TextField(
            controller: _search,
            onChanged: (_) => setState(() {}),
            decoration: InputDecoration(
              hintText: 'Cari ID polybag atau status...',
              hintStyle: const TextStyle(color: mutedColor, fontSize: 11),
              prefixIcon: const Icon(Icons.search, color: mutedColor, size: 19),
              suffixIcon: _search.text.isEmpty
                  ? null
                  : IconButton(
                      onPressed: () {
                        _search.clear();
                        setState(() {});
                      },
                      icon: const Icon(Icons.close, size: 17),
                    ),
              contentPadding: const EdgeInsets.symmetric(vertical: space8),
            ),
          ),
        ),
        const SizedBox(height: space8),
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: _filters.map((filter) {
              final selected = filter == _filter;
              return Padding(
                padding: const EdgeInsets.only(right: space8),
                child: AppChip(
                  label: filter,
                  selected: selected,
                  onSelected: (_) => setState(() => _filter = filter),
                ),
              );
            }).toList(),
          ),
        ),
        const SizedBox(height: space12),
        Row(
          children: [
            Text(
              '${results.length} HASIL DITEMUKAN',
              style: const TextStyle(
                color: mutedColor,
                fontSize: 11,
                fontWeight: FontWeight.w800,
                letterSpacing: 0.8,
              ),
            ),
            const Spacer(),
          ],
        ),
        const SizedBox(height: space8),
        if (results.isEmpty)
          AppCard(
            padding: const EdgeInsets.symmetric(
              vertical: space24,
              horizontal: space16,
            ),
            child: const Column(
              children: [
                Icon(Icons.search_off, color: subtleColor, size: 32),
                SizedBox(height: space8),
                Text(
                  'Polybag tidak ditemukan',
                  style: TextStyle(fontWeight: FontWeight.w700, fontSize: 12),
                ),
                SizedBox(height: space4),
                Text(
                  'Coba kata kunci atau filter lain.',
                  style: TextStyle(color: mutedColor, fontSize: 11),
                ),
              ],
            ),
          )
        else
          ...results.map(
            (pot) => Padding(
              padding: const EdgeInsets.only(bottom: space8),
              child: _PotCard(
                pot: pot,
                expanded: _expandedPot == pot.id,
                onTap: () => setState(() {
                  _expandedPot = _expandedPot == pot.id ? null : pot.id;
                }),
                onTrend: () => showAppBottomSheet<void>(
                  context: context,
                  builder: (_) => const _TrendSheet(),
                ),
              ),
            ),
          ),
      ],
    );
  }
}

class _PotCard extends StatelessWidget {
  const _PotCard({
    required this.pot,
    required this.expanded,
    required this.onTap,
    required this.onTrend,
  });

  final PotSample pot;
  final bool expanded;
  final VoidCallback onTap;
  final VoidCallback onTrend;

  @override
  Widget build(BuildContext context) {
    return AppCard(
      padding: EdgeInsets.zero,
      border: Border.all(color: pot.attention ? dangerBorder : borderColor),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(cardRadius),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(
                width: 4,
                color: pot.attention ? dangerColor : primaryColor,
              ),
              Expanded(
                child: Column(
                  children: [
                    InkWell(
                      onTap: onTap,
                      child: Padding(
                        padding: const EdgeInsets.fromLTRB(
                          space12,
                          space12,
                          space12,
                          space12,
                        ),
                        child: Row(
                          children: [
                            Container(
                              width: 38,
                              height: 38,
                              decoration: BoxDecoration(
                                color: pot.attention
                                    ? dangerBackground
                                    : paleGreen,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Icon(
                                Icons.spa_outlined,
                                color: pot.attention
                                    ? dangerColor
                                    : primaryColor,
                                size: 20,
                              ),
                            ),
                            const SizedBox(width: space8),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Text(
                                        pot.id,
                                        style: const TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w800,
                                        ),
                                      ),
                                      const SizedBox(width: space4),
                                      AppBadge(label: pot.block.toUpperCase()),
                                    ],
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    pot.status,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                    style: TextStyle(
                                      color: pot.attention
                                          ? dangerMuted
                                          : mutedColor,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            Icon(
                              expanded
                                  ? Icons.keyboard_arrow_up
                                  : Icons.keyboard_arrow_down,
                              color: mutedColor,
                            ),
                          ],
                        ),
                      ),
                    ),
                    AnimatedSize(
                      duration: const Duration(milliseconds: 220),
                      curve: Curves.easeInOut,
                      child: expanded
                          ? Padding(
                              padding: const EdgeInsets.fromLTRB(
                                space12,
                                0,
                                space12,
                                space12,
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  const Divider(height: 1),
                                  const SizedBox(height: 12),
                                  const Text(
                                    'PEMBACAAN PROBE TERAKHIR',
                                    style: TextStyle(
                                      color: mutedColor,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w800,
                                      letterSpacing: 0.8,
                                    ),
                                  ),
                                  const SizedBox(height: space8),
                                  Row(
                                    children: [
                                      _Probe(
                                        label: 'Kelembapan',
                                        value: '${pot.soil}%',
                                        icon: Icons.water_drop_outlined,
                                      ),
                                      _Probe(
                                        label: 'Suhu tanah',
                                        value: '18.6°C',
                                        icon: Icons.thermostat_outlined,
                                      ),
                                      _Probe(
                                        label: 'NPK',
                                        value: pot.npk,
                                        icon: Icons.grain,
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: space8),
                                  Text(
                                    'Terakhir diperbarui ${pot.time}',
                                    style: const TextStyle(
                                      color: mutedColor,
                                      fontSize: 11,
                                    ),
                                  ),
                                  const SizedBox(height: space12),
                                  OutlinedButton.icon(
                                    onPressed: onTrend,
                                    icon: const Icon(
                                      Icons.show_chart,
                                      size: 16,
                                    ),
                                    label: const Text('Lihat Tren 7 Hari'),
                                    style: OutlinedButton.styleFrom(
                                      foregroundColor: primaryColor,
                                      side: const BorderSide(
                                        color: Color(0xFFBBF7D0),
                                      ),
                                      textStyle: const TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w700,
                                      ),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(
                                          controlRadius,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            )
                          : const SizedBox(width: double.infinity),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Probe extends StatelessWidget {
  const _Probe({required this.label, required this.value, required this.icon});
  final String label;
  final String value;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 13, color: primaryColor),
          const SizedBox(height: 4),
          Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
          ),
          Text(
            label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(color: mutedColor, fontSize: 11),
          ),
        ],
      ),
    );
  }
}

class _TrendSheet extends StatelessWidget {
  const _TrendSheet();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, space8, 20, space20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Expanded(
                child: Text(
                  'Tren 7 Hari',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
                ),
              ),
              IconButton(
                onPressed: () => Navigator.pop(context),
                icon: const Icon(Icons.close),
              ),
            ],
          ),
          const Text(
            'Perubahan kelembapan media tanam • POT-05',
            style: TextStyle(color: mutedColor, fontSize: 11),
          ),
          const SizedBox(height: space8),
          const AppSampleLabel(),
          const SizedBox(height: space20),
          AppCard(
            padding: const EdgeInsets.fromLTRB(
              space8,
              space12,
              space12,
              space12,
            ),
            child: SizedBox(
              height: 225,
              child: CustomPaint(
                painter: _TrendPainter(values: trendSoilMoisture),
                child: const SizedBox.expand(),
              ),
            ),
          ),
          const SizedBox(height: space16),
          const Row(
            children: [
              Icon(Icons.circle, size: 8, color: primaryColor),
              SizedBox(width: space8),
              Text(
                'Kelembapan tanah',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700),
              ),
              Spacer(),
              Text(
                'Rata-rata 58%',
                style: TextStyle(color: mutedColor, fontSize: 11),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _TrendPainter extends CustomPainter {
  const _TrendPainter({required this.values});
  final List<double> values;

  @override
  void paint(Canvas canvas, Size size) {
    const left = 31.0;
    const top = 14.0;
    const bottom = 28.0;
    final graphWidth = size.width - left - 7;
    final graphHeight = size.height - top - bottom;
    final grid = Paint()
      ..color = borderColor
      ..strokeWidth = 1;
    for (var i = 0; i < 5; i++) {
      final y = top + graphHeight * i / 4;
      canvas.drawLine(Offset(left, y), Offset(size.width - 4, y), grid);
      final label = TextPainter(
        text: TextSpan(
          text: '${100 - i * 25}',
          style: const TextStyle(color: mutedColor, fontSize: 11),
        ),
        textDirection: TextDirection.ltr,
      )..layout();
      label.paint(canvas, Offset(0, y - 5));
    }

    final points = <Offset>[];
    for (var i = 0; i < values.length; i++) {
      points.add(
        Offset(
          left + graphWidth * i / (values.length - 1),
          top + graphHeight * (1 - values[i] / 100),
        ),
      );
    }
    final line = Path()..moveTo(points.first.dx, points.first.dy);
    for (final point in points.skip(1)) {
      line.lineTo(point.dx, point.dy);
    }
    final area = Path.from(line)
      ..lineTo(points.last.dx, top + graphHeight)
      ..lineTo(points.first.dx, top + graphHeight)
      ..close();
    canvas.drawPath(
      area,
      Paint()
        ..shader = const LinearGradient(
          colors: [Color(0x5534D399), Color(0x0834D399)],
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
        ).createShader(Rect.fromLTWH(left, top, graphWidth, graphHeight)),
    );
    canvas.drawPath(
      line,
      Paint()
        ..color = primaryColor
        ..strokeWidth = 2.5
        ..style = PaintingStyle.stroke
        ..strokeCap = StrokeCap.round
        ..strokeJoin = StrokeJoin.round,
    );
    for (var i = 0; i < points.length; i++) {
      canvas.drawCircle(points[i], 3.5, Paint()..color = Colors.white);
      canvas.drawCircle(points[i], 2.5, Paint()..color = primaryColor);
      final day = TextPainter(
        text: TextSpan(
          text: trendWeekdays[i],
          style: const TextStyle(color: mutedColor, fontSize: 11),
        ),
        textDirection: TextDirection.ltr,
      )..layout();
      day.paint(
        canvas,
        Offset(points[i].dx - day.width / 2, size.height - bottom + 8),
      );
    }
  }

  @override
  bool shouldRepaint(covariant _TrendPainter oldDelegate) =>
      oldDelegate.values != values;
}
