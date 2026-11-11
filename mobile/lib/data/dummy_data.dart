class PotSample {
  const PotSample({
    required this.id,
    required this.block,
    required this.soil,
    required this.condition,
    required this.npk,
    required this.status,
    required this.attention,
    required this.time,
  });

  final String id;
  final String block;
  final int soil;
  final String condition;
  final String npk;
  final String status;
  final bool attention;
  final String time;
}

class ActivitySample {
  const ActivitySample({
    required this.pot,
    required this.text,
    required this.score,
    required this.time,
    required this.warning,
  });

  final String pot;
  final String text;
  final String score;
  final String time;
  final bool warning;
}

class DiagnosisSample {
  const DiagnosisSample({
    required this.id,
    required this.type,
    required this.title,
    required this.pot,
    required this.date,
    required this.accuracy,
    required this.photo,
    required this.note,
    required this.sync,
  });

  final int id;
  final String type;
  final String title;
  final String pot;
  final String date;
  final double accuracy;
  final String photo;
  final String note;
  final String sync;
}

const leafPhoto =
    'https://images.unsplash.com/photo-1588165171080-c89acfa5ee83?auto=format&fit=crop&w=900&q=85';
const ripePhoto =
    'https://images.unsplash.com/photo-1680180226263-fab5f01a01c2?auto=format&fit=crop&w=900&q=85';
const healthyPhoto =
    'https://images.unsplash.com/photo-1582757793483-e407049be006?auto=format&fit=crop&w=900&q=85';

// DEMO DATA: replace these field readings, alerts, histories, and trends with API data.
const weatherLocation = 'Lembang 21°C';
const weatherHumidity = 'RH 84%';
const currentAirTemperature = '21.4';
const currentAirHumidity = '84%';
const averageSoilMoisture = '62';
const activityHeading = 'Pemindaian hari ini';
const activeAlertTitle = 'Waspada Jamur Botrytis';
const activeAlertDescription =
    'Kelembapan udara 84% rentan memicu pembusukan buah. Periksa ventilasi naungan.';

const recentActivities = <ActivitySample>[
  ActivitySample(
    pot: 'Pot 05',
    text: 'Terdeteksi Bercak Daun',
    score: '94%',
    time: '10:42 WIB',
    warning: true,
  ),
  ActivitySample(
    pot: 'Pot 12',
    text: 'Buah Matang',
    score: '98%',
    time: '10:24 WIB',
    warning: false,
  ),
  ActivitySample(
    pot: 'Pot 01',
    text: 'Daun Sehat',
    score: '96%',
    time: '09:15 WIB',
    warning: false,
  ),
];

const potSamples = <PotSample>[
  PotSample(
    id: 'POT-01',
    block: 'Blok A',
    soil: 62,
    condition: 'Normal',
    npk: '45–18–52',
    status: 'Sehat',
    attention: false,
    time: 'Hari ini, 10:18 WIB',
  ),
  PotSample(
    id: 'POT-05',
    block: 'Blok A',
    soil: 78,
    condition: 'Terlalu Basah',
    npk: '18–08–20',
    status: 'Defisiensi Hara & Rawan Busuk Akar',
    attention: true,
    time: 'Hari ini, 10:36 WIB',
  ),
  PotSample(
    id: 'POT-12',
    block: 'Blok B',
    soil: 58,
    condition: 'Normal',
    npk: '50–22–60',
    status: 'Siap Panen',
    attention: false,
    time: 'Hari ini, 09:58 WIB',
  ),
];

const diagnosisSamples = <DiagnosisSample>[
  DiagnosisSample(
    id: 1,
    type: 'Daun',
    title: 'Daun Bercak',
    pot: 'POT-05',
    date: '18 Okt 2026, 10:42 WIB',
    accuracy: 94.8,
    photo: leafPhoto,
    note: 'Gejala bercak terlokalisasi. Pangkas daun terdampak dan pantau kelembapan selama 48 jam.',
    sync: 'Status contoh: tersinkron',
  ),
  DiagnosisSample(
    id: 2,
    type: 'Buah',
    title: 'Buah Stroberi Matang',
    pot: 'POT-12',
    date: '18 Okt 2026, 10:24 WIB',
    accuracy: 98.1,
    photo: ripePhoto,
    note: 'Contoh catatan: Buah tampak matang. Periksa kondisi langsung sebelum panen.',
    sync: 'Status contoh: tersinkron',
  ),
  DiagnosisSample(
    id: 3,
    type: 'Daun',
    title: 'Daun Stroberi Sehat',
    pot: 'POT-01',
    date: '18 Okt 2026, 09:15 WIB',
    accuracy: 96.5,
    photo: healthyPhoto,
    note: 'Warna dan tekstur daun normal. Lanjutkan jadwal nutrisi dan penyiraman rutin.',
    sync: 'Status contoh: lokal',
  ),
];

const trendSoilMoisture = <double>[48, 52, 50, 59, 56, 68, 78];
const trendWeekdays = <String>['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
