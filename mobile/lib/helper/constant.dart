import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

const Color primaryColor = Color(0xFF15803D);
const Color darkGreen = Color(0xFF166534);
const Color loginHeaderColor = Color(0xFF0D4928);
const Color scanTabColor = Color(0xFF174F2E);
const Color accentGreen = Color(0xFF22C55E);
const Color screenBackground = Color(0xFFF8FAFC);
const Color inkColor = Color(0xFF0F172A);
const Color mutedColor = Color(0xFF64748B);
const Color subtleColor = Color(0xFF94A3B8);
const Color borderColor = Color(0xFFE2E8F0);
const Color paleGreen = Color(0xFFF0FDF4);
const Color lightGreen = Color(0xFFDCFCE7);
const Color dangerColor = Color(0xFFBE123C);
const Color dangerBackground = Color(0xFFFFF1F2);
const Color dangerText = Color(0xFF881337);
const Color dangerMuted = Color(0xFF9F1239);
const Color dangerBorder = Color(0xFFFECDD3);

const double pagePadding = 16;
const double sectionSpacing = 20;
const double cardRadius = 16;
const double controlRadius = 12;
const double smallRadius = 8;

const double space4 = 4;
const double space8 = 8;
const double space12 = 12;
const double space16 = 16;
const double space20 = 20;
const double space24 = 24;

String get apiUrl {
  const configuredApiUrl = String.fromEnvironment('API_URL');
  if (configuredApiUrl.trim().isNotEmpty) {
    return configuredApiUrl.trim();
  }

  if (!kIsWeb && defaultTargetPlatform == TargetPlatform.android) {
    return 'http://10.0.2.2:8000/api';
  }

  return 'http://127.0.0.1:8000/api';
}

double deviceWidth(BuildContext context) => MediaQuery.sizeOf(context).width;

double deviceHeight(BuildContext context) => MediaQuery.sizeOf(context).height;

EdgeInsets spacing({double h = 0, double v = 0}) {
  return EdgeInsets.symmetric(horizontal: h, vertical: v);
}
