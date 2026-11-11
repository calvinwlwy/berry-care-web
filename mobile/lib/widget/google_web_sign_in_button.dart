import 'package:flutter/widgets.dart';

import 'google_web_sign_in_button_stub.dart'
    if (dart.library.js_interop) 'google_web_sign_in_button_web.dart'
    as platform_button;

class GoogleWebSignInButton extends StatelessWidget {
  const GoogleWebSignInButton({super.key});

  @override
  Widget build(BuildContext context) =>
      platform_button.buildGoogleSignInButton();
}
