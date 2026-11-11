import 'package:flutter/material.dart';

import '../data/dummy_data.dart';
import '../helper/constant.dart';
import '../widget/app_widgets.dart';

class HistoryDetail extends StatelessWidget {
  const HistoryDetail({required this.diagnosis, super.key});

  final DiagnosisSample diagnosis;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'Detail Diagnosis',
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
        ),
        actions: [
          IconButton(
            onPressed: () => ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Contoh tampilan: belum tersambung ke server'),
              ),
            ),
            icon: const Icon(Icons.ios_share_outlined),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(
          pagePadding,
          space4,
          pagePadding,
          space24,
        ),
        children: [
          InkWell(
            borderRadius: BorderRadius.circular(cardRadius),
            onTap: () => showDialog<void>(
              context: context,
              builder: (_) => Dialog(
                insetPadding: const EdgeInsets.all(12),
                child: InteractiveViewer(
                  child: samplePhoto(
                    url: diagnosis.photo,
                    borderRadius: BorderRadius.circular(controlRadius),
                  ),
                ),
              ),
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(cardRadius),
              child: SizedBox(
                height: 250,
                child: samplePhoto(url: diagnosis.photo),
              ),
            ),
          ),
          const SizedBox(height: space16),
          const AppSampleLabel(),
          const SizedBox(height: space8),
          Row(
            children: [
              Expanded(
                child: Text(
                  diagnosis.title,
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                    letterSpacing: -0.5,
                  ),
                ),
              ),
              AppBadge(label: diagnosis.type.toUpperCase()),
            ],
          ),
          const SizedBox(height: space4),
          Text(
            '${diagnosis.pot} • ${diagnosis.date}',
            style: const TextStyle(color: mutedColor, fontSize: 11),
          ),
          const SizedBox(height: space16),
          AppCard(
            padding: const EdgeInsets.all(space16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Expanded(
                      child: Text(
                        'Akurasi diagnosis',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                    Text(
                      '${diagnosis.accuracy}%',
                      style: const TextStyle(
                        color: primaryColor,
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: space8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(smallRadius),
                  child: LinearProgressIndicator(
                    value: diagnosis.accuracy / 100,
                    minHeight: 7,
                    backgroundColor: lightGreen,
                    color: primaryColor,
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Akurasi pada hasil diagnosis contoh',
                  style: TextStyle(color: mutedColor, fontSize: 11),
                ),
              ],
            ),
          ),
          const SizedBox(height: space12),
          AppCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(
                      Icons.assignment_outlined,
                      color: primaryColor,
                      size: 17,
                    ),
                    SizedBox(width: space8),
                    Text(
                      'CATATAN AGRONOMIS',
                      style: TextStyle(
                        color: primaryColor,
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        letterSpacing: 0.7,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: space8),
                Text(
                  diagnosis.note,
                  style: const TextStyle(fontSize: 12, height: 1.6),
                ),
              ],
            ),
          ),
          const SizedBox(height: space12),
          AppCard(
            padding: const EdgeInsets.symmetric(
              horizontal: space12,
              vertical: space12,
            ),
            child: Row(
              children: [
                Expanded(
                  child: Text(
                    diagnosis.sync,
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
