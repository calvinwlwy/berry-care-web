import 'package:flutter/material.dart';

import 'helper/constant.dart';
import 'helper/session.dart';
import 'helper/theme.dart';
import 'screens/auth/login.dart';
import 'screens/home.dart';
import 'widget/app_widgets.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Berry Care Field App',
      debugShowCheckedModeBanner: false,
      theme: buildAppTheme(),
      home: const AppGate(),
    );
  }
}

class AppGate extends StatefulWidget {
  const AppGate({super.key});

  @override
  State<AppGate> createState() => _AppGateState();
}

class _AppGateState extends State<AppGate> {
  late Future<SessionUser?> _session = Session.restore();

  void _retryRestore() {
    setState(() => _session = Session.restore());
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<SessionUser?>(
      future: _session,
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Scaffold(
            body: Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: primaryColor),
                  SizedBox(height: space12),
                  Text(
                    'Memuat sesi...',
                    style: TextStyle(color: mutedColor, fontSize: 12),
                  ),
                ],
              ),
            ),
          );
        }

        if (snapshot.hasError) {
          return Scaffold(
            body: SafeArea(
              child: Center(
                child: Padding(
                  padding: const EdgeInsets.all(space24),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Text(
                        'Sesi tidak dapat dimuat. Periksa penyimpanan perangkat '
                        'dan coba lagi.',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: inkColor, fontSize: 12),
                      ),
                      const SizedBox(height: space16),
                      AppButton(label: 'Coba lagi', onPressed: _retryRestore),
                    ],
                  ),
                ),
              ),
            ),
          );
        }

        return snapshot.data == null ? const Login() : const Home();
      },
    );
  }
}
