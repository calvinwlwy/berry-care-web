import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:hexcolor/hexcolor.dart';

double deviceWidth(BuildContext context) => MediaQuery.sizeOf(context).width;

double deviceHeight(BuildContext context) => MediaQuery.sizeOf(context).height;

final Color primaryColor = HexColor('#023B47');
final Color secondaryColor = HexColor('#1F7879');
final Color secondaryTextColor = HexColor('#658E92');

String get apiUrl {
  if (!kIsWeb && defaultTargetPlatform == TargetPlatform.android) {
    return 'http://10.0.2.2:8000/api';
  }

  return 'http://127.0.0.1:8000/api';
}

Widget spancer({double w = 0, double h = 0}) {
  return SizedBox(height: h, width: w);
}

EdgeInsets spacing({double h = 0, double v = 0}) {
  return EdgeInsets.symmetric(horizontal: h, vertical: v);
}
