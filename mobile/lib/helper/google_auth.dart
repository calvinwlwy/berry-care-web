import 'package:flutter/foundation.dart';
import 'package:google_sign_in/google_sign_in.dart';

const String googleServerClientId = String.fromEnvironment(
  'GOOGLE_SERVER_CLIENT_ID',
);

class GoogleAuth {
  GoogleAuth._();

  static Future<void>? _initialization;

  static bool get isConfigured => googleServerClientId.trim().isNotEmpty;

  static Future<void> initialize() {
    return _initialization ??= GoogleSignIn.instance.initialize(
      clientId: kIsWeb && isConfigured ? googleServerClientId.trim() : null,
      serverClientId: !kIsWeb && isConfigured
          ? googleServerClientId.trim()
          : null,
    );
  }

  static Future<GoogleSignInAccount> signIn() async {
    await initialize();
    return GoogleSignIn.instance.authenticate();
  }

  static Future<void> signOut() async {
    await initialize();
    await GoogleSignIn.instance.signOut();
  }
}
