import 'package:berry_care_mobile/helper/session.dart';
import 'package:berry_care_mobile/main.dart';
import 'package:berry_care_mobile/screens/home.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  setUp(() {
    SharedPreferences.setMockInitialValues({});
  });

  testWidgets('login screen is shown when there is no remembered session', (
    tester,
  ) async {
    debugDefaultTargetPlatformOverride = TargetPlatform.android;
    try {
      await tester.pumpWidget(const MyApp());
      await tester.pumpAndSettle();

      expect(find.text('Selamat datang kembali'), findsOneWidget);
      expect(find.text('Masuk Tugas Kebun'), findsOneWidget);
      expect(find.text('Masuk dengan Google'), findsOneWidget);
      final googleButtonText = tester.widget<Text>(
        find.text('Masuk dengan Google'),
      );
      expect(googleButtonText.style?.fontWeight, FontWeight.w800);
    } finally {
      debugDefaultTargetPlatformOverride = null;
    }
  });

  testWidgets('bottom navigation switches to the pots tab', (tester) async {
    await Session.start(
      const SessionUser(
        id: 1,
        name: 'Asep Petani',
        email: 'asep@example.test',
        role: 'petani',
        token: 'test-token',
      ),
      remember: false,
    );
    await tester.pumpWidget(
      const MaterialApp(home: Home(skipSessionCheck: true)),
    );
    await tester.pump(const Duration(milliseconds: 100));

    await tester.tap(find.byKey(const ValueKey('tab-status-pot')));
    await tester.pump(const Duration(milliseconds: 300));

    expect(find.text('Status Polybag'), findsOneWidget);
    await Session.clear();
  });

  testWidgets('profile logout action shows confirmation dialog', (
    tester,
  ) async {
    await Session.start(
      const SessionUser(
        id: 1,
        name: 'Asep Petani',
        email: 'asep@example.test',
        role: 'petani',
        token: 'test-token',
      ),
      remember: false,
    );
    await tester.pumpWidget(
      const MaterialApp(home: Home(skipSessionCheck: true)),
    );
    await tester.pump(const Duration(milliseconds: 100));

    await tester.tap(find.byKey(const ValueKey('tab-profil')));
    await tester.pump(const Duration(milliseconds: 300));
    expect(find.text('Profil Operator'), findsOneWidget);
    await tester.drag(find.byType(ListView).last, const Offset(0, -700));
    await tester.pump(const Duration(milliseconds: 300));
    await tester.tap(find.text('Keluar / Logout'));
    await tester.pump(const Duration(milliseconds: 300));

    expect(find.text('Keluar dari akun?'), findsOneWidget);
    expect(find.text('Ya, Keluar'), findsOneWidget);
    await Session.clear();
  });
}
