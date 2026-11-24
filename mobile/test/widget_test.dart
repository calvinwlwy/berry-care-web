import 'package:berry_care_mobile/helper/session.dart';
import 'package:berry_care_mobile/main.dart';
import 'package:berry_care_mobile/screens/home.dart';
import 'package:berry_care_mobile/screens/auth/register.dart';
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
      expect(find.text('Ingat perangkat ini di kebun'), findsNothing);
      final hints = find
          .byType(TextField)
          .evaluate()
          .map((element) => (element.widget as TextField).decoration?.hintText)
          .toList();
      expect(hints, ['Masukkan email', 'Masukkan password']);
      expect(find.text('Masuk dengan Google'), findsOneWidget);
      final googleButtonText = tester.widget<Text>(
        find.text('Masuk dengan Google'),
      );
      expect(googleButtonText.style?.fontWeight, FontWeight.w800);
    } finally {
      debugDefaultTargetPlatformOverride = null;
    }
  });

  testWidgets('registration fields show helpful placeholders', (tester) async {
    await tester.pumpWidget(const MaterialApp(home: Register()));

    final hints = find
        .byType(TextField)
        .evaluate()
        .map((element) => (element.widget as TextField).decoration?.hintText)
        .toList();
    expect(hints, [
      'Masukkan nama lengkap',
      'Masukkan email',
      'Minimal 8 karakter',
      'Ulangi password',
    ]);
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

    expect(find.text('Cuaca lokal'), findsNothing);
    expect(find.text('Lembang 21°C'), findsNothing);
    await tester.tap(find.byKey(const ValueKey('tab-status-pot')));
    await tester.pump(const Duration(milliseconds: 300));

    expect(find.text('Status Polybag'), findsOneWidget);
    await Session.clear();
  });

  testWidgets('scan requires a polybag number before analysis', (tester) async {
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
    await tester.tap(find.byKey(const ValueKey('tab-scan')));
    await tester.pump(const Duration(milliseconds: 300));
    await tester.tap(find.byKey(const ValueKey('scan-shutter')));
    await tester.pump();

    expect(find.text('Isi nomor polybag.'), findsOneWidget);
    expect(find.text('ID Polybag'), findsOneWidget);
    await Session.clear();
  });

  testWidgets('scan normalizes the typed polybag number in diagnosis result', (
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
    await tester.tap(find.byKey(const ValueKey('tab-scan')));
    await tester.pump(const Duration(milliseconds: 300));
    final fieldFinder = find.byKey(const ValueKey('polybag-number'));
    await tester.enterText(fieldFinder, '5');
    await tester.pump();

    expect(find.text('5'), findsOneWidget);
    expect(find.text('POT-'), findsOneWidget);
    expect(tester.getSize(fieldFinder).height, 48);
    final editableText = tester.widget<EditableText>(
      find.descendant(of: fieldFinder, matching: find.byType(EditableText)),
    );
    final inputDecorator = tester.widget<InputDecorator>(
      find.descendant(of: fieldFinder, matching: find.byType(InputDecorator)),
    );
    expect(editableText.style.color, Colors.white);
    expect(
      editableText.style.color,
      isNot(inputDecorator.decoration.fillColor),
    );
    expect(
      tester.getTopLeft(find.text('PEMINDAIAN AI')).dx,
      tester
          .getTopLeft(
            find.text('Data contoh • kamera dan analisis disimulasikan'),
          )
          .dx,
    );
    await tester.testTextInput.receiveAction(TextInputAction.done);
    await tester.pump();
    expect(editableText.focusNode.hasFocus, isFalse);

    await tester.tap(find.byKey(const ValueKey('scan-shutter')));
    await tester.pump(const Duration(seconds: 1));
    await tester.pump(const Duration(milliseconds: 500));

    expect(find.textContaining('POT-05'), findsOneWidget);
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
    expect(find.text('Mode Offline'), findsNothing);
    expect(find.text('Pengaturan Aplikasi'), findsNothing);
    await tester.tap(find.byKey(const ValueKey('operator-profile-card')));
    await tester.pump(const Duration(milliseconds: 300));
    expect(find.text('Detail Profil Operator'), findsOneWidget);
    expect(find.text('Asep Petani'), findsNWidgets(2));
    expect(find.text('asep@example.test'), findsNWidgets(2));
    expect(find.text('ID Operator: BC-0001'), findsOneWidget);
    await tester.tap(find.text('Tutup'));
    await tester.pump(const Duration(milliseconds: 300));
    await tester.drag(find.byType(ListView).last, const Offset(0, -700));
    await tester.pump(const Duration(milliseconds: 300));
    await tester.tap(find.text('Keluar / Logout'));
    await tester.pump(const Duration(milliseconds: 300));

    expect(find.text('Keluar dari akun?'), findsOneWidget);
    expect(find.text('Ya, Keluar'), findsOneWidget);
    await Session.clear();
  });
}
