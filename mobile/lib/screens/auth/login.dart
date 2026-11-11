import 'dart:async';
import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:google_sign_in/google_sign_in.dart';

import '../../helper/constant.dart';
import '../../helper/google_auth.dart';
import '../../helper/session.dart';
import '../../methods/api.dart';
import '../../widget/app_widgets.dart';
import '../../widget/google_web_sign_in_button.dart';
import '../../widget/input_field.dart';
import '../home.dart';
import 'register.dart';

class Login extends StatefulWidget {
  const Login({this.initialMessage, super.key});

  final String? initialMessage;

  @override
  State<Login> createState() => _LoginState();
}

class _LoginState extends State<Login> {
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _rememberDevice = true;
  bool _showPassword = false;
  bool _isLoading = false;
  bool _isGoogleLoading = false;
  bool _webGoogleReady = false;
  StreamSubscription<GoogleSignInAuthenticationEvent>? _googleEvents;

  @override
  void initState() {
    super.initState();
    if (kIsWeb && GoogleAuth.isConfigured) {
      _initializeWebGoogleSignIn();
    }
    if (widget.initialMessage != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) _showMessage(widget.initialMessage!);
      });
    }
  }

  @override
  void dispose() {
    _googleEvents?.cancel();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (_isLoading || _isGoogleLoading) return;
    setState(() => _isLoading = true);

    try {
      final result = await API().postRequest(
        route: '/login',
        data: {
          'email': _emailController.text.trim(),
          'password': _passwordController.text,
        },
      );

      if (result.statusCode == 200) {
        await _startSession(result.body);
      } else {
        _showMessage(responseMessage(result));
      }
    } catch (error) {
      debugPrint('Login request failed: $error');
      _showMessage(
        'Tidak dapat menghubungi server. Periksa koneksi dan pastikan server '
        'berjalan, lalu coba lagi.',
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _loginWithGoogle() async {
    if (_isLoading || _isGoogleLoading) return;
    if (!GoogleAuth.isConfigured) {
      _showMessage('SSO belum dikonfigurasi.');
      return;
    }

    setState(() => _isGoogleLoading = true);
    try {
      final account = await GoogleAuth.signIn();
      await _submitGoogleAccount(account, alreadyLoading: true);
    } on GoogleSignInException catch (error) {
      if (error.code != GoogleSignInExceptionCode.canceled) {
        debugPrint('Google sign-in failed: ${error.code}');
        _showMessage(
          'Tidak dapat masuk dengan Google. Periksa konfigurasi akun lalu coba lagi.',
        );
      }
    } catch (error) {
      debugPrint('Google login request failed: $error');
      _showMessage(
        'Tidak dapat menghubungi server. Periksa koneksi dan coba lagi.',
      );
    } finally {
      if (mounted) setState(() => _isGoogleLoading = false);
    }
  }

  Future<void> _initializeWebGoogleSignIn() async {
    try {
      await GoogleAuth.initialize();
      if (!mounted) return;
      _googleEvents = GoogleSignIn.instance.authenticationEvents.listen(
        (event) {
          if (event is GoogleSignInAuthenticationEventSignIn) {
            _submitGoogleAccount(event.user);
          }
        },
        onError: (Object error) {
          if (error is GoogleSignInException &&
              error.code == GoogleSignInExceptionCode.canceled) {
            return;
          }
          debugPrint('Google web sign-in failed: $error');
          _showMessage(
            'Tidak dapat masuk dengan Google. Periksa konfigurasi dan coba lagi.',
          );
        },
      );
      setState(() => _webGoogleReady = true);
    } catch (error) {
      debugPrint('Google web initialization failed: $error');
      _showMessage(
        'Tombol Google tidak dapat dimuat. Periksa Client ID dan origin aplikasi.',
      );
    }
  }

  Future<void> _submitGoogleAccount(
    GoogleSignInAccount account, {
    bool alreadyLoading = false,
  }) async {
    if (_isLoading || (_isGoogleLoading && !alreadyLoading)) return;
    if (!alreadyLoading) setState(() => _isGoogleLoading = true);
    try {
      final idToken = account.authentication.idToken;
      if (idToken == null || idToken.isEmpty) {
        _showMessage('Token Google tidak tersedia. Coba pilih akun lagi.');
        return;
      }

      final response = await API().postRequest(
        route: '/auth/google',
        data: {'id_token': idToken},
      );
      if (response.statusCode == 200) {
        await _startSession(response.body);
      } else {
        _showMessage(responseMessage(response));
      }
    } catch (error) {
      debugPrint('Google login request failed: $error');
      _showMessage(
        'Tidak dapat menghubungi server. Periksa koneksi dan coba lagi.',
      );
    } finally {
      if (mounted) setState(() => _isGoogleLoading = false);
    }
  }

  Future<void> _startSession(String responseBody) async {
    final response = jsonDecode(responseBody) as Map<String, dynamic>;
    final user = SessionUser.fromApi(
      response['user'] as Map<String, dynamic>,
      response['token'] as String,
    );
    await Session.start(user, remember: _rememberDevice);
    if (!mounted) return;
    Navigator.of(context)
        .pushReplacement(MaterialPageRoute<void>(builder: (_) => const Home()));
  }

  void _showMessage(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(SnackBar(content: Text(message)));
  }

  void _openRegister() {
    Navigator.of(context)
        .push(MaterialPageRoute<void>(builder: (_) => const Register()));
  }

  Widget _googleSetupButton() {
    return SizedBox(
      height: 48,
      child: OutlinedButton(
        onPressed: () => _showMessage('SSO belum dikonfigurasi.'),
        style: OutlinedButton.styleFrom(
          backgroundColor: Colors.white,
          foregroundColor: inkColor,
          side: const BorderSide(color: borderColor),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(controlRadius),
          ),
        ),
        child: const Text(
          'Masuk dengan Google',
          style: TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: screenBackground,
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            return SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(
                space24,
                space20,
                space24,
                space20,
              ),
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  minHeight: constraints.maxHeight - 42,
                ),
                child: IntrinsicHeight(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      SizedBox(
                        height: 185,
                        child: Stack(
                          clipBehavior: Clip.none,
                          children: [
                            Positioned(
                              top: -22,
                              left: -24,
                              right: -24,
                              child: ClipPath(
                                clipper: _LoginHeaderClipper(),
                                child: Container(
                                  height: 225,
                                  color: loginHeaderColor,
                                ),
                              ),
                            ),
                            const Padding(
                              padding: EdgeInsets.only(top: space4),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    'HAIFRESH • LEMBANG',
                                    style: TextStyle(
                                      color: Colors.white70,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w800,
                                      letterSpacing: 1.6,
                                    ),
                                  ),
                                  Text(
                                    'Field operations',
                                    style: TextStyle(
                                      color: Colors.white70,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      AppCard(
                        padding: const EdgeInsets.fromLTRB(
                          space24,
                          space24,
                          space24,
                          space20,
                        ),
                        radius: cardRadius,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Row(
                              children: [
                                const _BerryLogo(),
                                const SizedBox(width: 12),
                                Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Berry Care',
                                      style: Theme.of(context)
                                          .textTheme
                                          .titleLarge
                                          ?.copyWith(
                                            fontSize: 22,
                                            fontWeight: FontWeight.w800,
                                            letterSpacing: -0.8,
                                          ),
                                    ),
                                    const Text(
                                      'FIELD APP',
                                      style: TextStyle(
                                        color: primaryColor,
                                        fontSize: 11,
                                        fontWeight: FontWeight.w800,
                                        letterSpacing: 1.5,
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                            const SizedBox(height: space24),
                            const Divider(height: 1, color: borderColor),
                            const SizedBox(height: 20),
                            const Text(
                              'Selamat datang kembali',
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.w800,
                                letterSpacing: -0.5,
                              ),
                            ),
                            const SizedBox(height: space4),
                            const Text(
                              'Sistem Operasional Lapangan\nHaifresh Lembang',
                              style: TextStyle(
                                color: mutedColor,
                                fontSize: 12,
                                height: 1.55,
                              ),
                            ),
                            const SizedBox(height: space20),
                            AppInputField(
                              controller: _emailController,
                              label: 'Email',
                              icon: Icons.mail_outline,
                              keyboardType: TextInputType.emailAddress,
                              textInputAction: TextInputAction.next,
                            ),
                            const SizedBox(height: space12),
                            AppInputField(
                              controller: _passwordController,
                              label: 'Password',
                              icon: Icons.lock_outline,
                              obscureText: !_showPassword,
                              textInputAction: TextInputAction.done,
                              onSubmitted: (_) => _login(),
                              suffix: IconButton(
                                tooltip: _showPassword
                                    ? 'Sembunyikan password'
                                    : 'Tampilkan password',
                                onPressed: () => setState(
                                  () => _showPassword = !_showPassword,
                                ),
                                icon: Icon(
                                  _showPassword
                                      ? Icons.visibility_off_outlined
                                      : Icons.visibility_outlined,
                                  size: 18,
                                  color: mutedColor,
                                ),
                              ),
                            ),
                            const SizedBox(height: space12),
                            InkWell(
                              onTap: () => setState(
                                () => _rememberDevice = !_rememberDevice,
                              ),
                              borderRadius: BorderRadius.circular(smallRadius),
                              child: ConstrainedBox(
                                constraints: const BoxConstraints(
                                  minHeight: 44,
                                ),
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(
                                    vertical: space8,
                                  ),
                                  child: Row(
                                    children: [
                                      SizedBox(
                                        width: 20,
                                        height: 20,
                                        child: Checkbox(
                                          value: _rememberDevice,
                                          activeColor: primaryColor,
                                          side: const BorderSide(
                                            color: borderColor,
                                          ),
                                          shape: RoundedRectangleBorder(
                                            borderRadius: BorderRadius.circular(
                                              smallRadius,
                                            ),
                                          ),
                                          onChanged: (value) => setState(
                                            () => _rememberDevice =
                                                value ?? false,
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: space8),
                                      const Flexible(
                                        child: Text(
                                          'Ingat perangkat ini di kebun',
                                          style: TextStyle(
                                            color: Color(0xFF475569),
                                            fontSize: 11,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(height: space16),
                            AppButton(
                              label: _isLoading || _isGoogleLoading
                                  ? 'Memeriksa akses...'
                                  : 'Masuk Tugas Kebun',
                              onPressed: _isLoading || _isGoogleLoading
                                  ? null
                                  : _login,
                              trailingIcon: _isLoading || _isGoogleLoading
                                  ? null
                                  : Icons.chevron_right,
                            ),
                            if (kIsWeb) ...[
                              const SizedBox(height: space12),
                              const Row(
                                children: [
                                  Expanded(child: Divider(color: borderColor)),
                                  Padding(
                                    padding: EdgeInsets.symmetric(
                                      horizontal: space12,
                                    ),
                                    child: Text(
                                      'atau',
                                      style: TextStyle(
                                        color: mutedColor,
                                        fontSize: 12,
                                      ),
                                    ),
                                  ),
                                  Expanded(child: Divider(color: borderColor)),
                                ],
                              ),
                              const SizedBox(height: space12),
                              if (!GoogleAuth.isConfigured)
                                _googleSetupButton()
                              else if (_isGoogleLoading)
                                const SizedBox(
                                  height: 48,
                                  child: Center(
                                    child: CircularProgressIndicator(
                                      color: primaryColor,
                                    ),
                                  ),
                                )
                              else if (_webGoogleReady)
                                const SizedBox(
                                  height: 48,
                                  child: GoogleWebSignInButton(),
                                )
                              else
                                const SizedBox(
                                  height: 48,
                                  child: Center(
                                    child: CircularProgressIndicator(
                                      color: primaryColor,
                                    ),
                                  ),
                                ),
                            ] else if (defaultTargetPlatform ==
                                TargetPlatform.android) ...[
                              const SizedBox(height: space12),
                              const Row(
                                children: [
                                  Expanded(child: Divider(color: borderColor)),
                                  Padding(
                                    padding: EdgeInsets.symmetric(
                                      horizontal: space12,
                                    ),
                                    child: Text(
                                      'atau',
                                      style: TextStyle(
                                        color: mutedColor,
                                        fontSize: 12,
                                      ),
                                    ),
                                  ),
                                  Expanded(child: Divider(color: borderColor)),
                                ],
                              ),
                              const SizedBox(height: space12),
                              SizedBox(
                                height: 48,
                                child: OutlinedButton(
                                  onPressed: _isLoading || _isGoogleLoading
                                      ? null
                                      : _loginWithGoogle,
                                  style: OutlinedButton.styleFrom(
                                    backgroundColor: Colors.white,
                                    foregroundColor: inkColor,
                                    side: const BorderSide(color: borderColor),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(
                                        controlRadius,
                                      ),
                                    ),
                                  ),
                                  child: _isGoogleLoading
                                      ? const SizedBox(
                                          width: 20,
                                          height: 20,
                                          child: CircularProgressIndicator(
                                            strokeWidth: 2,
                                            color: primaryColor,
                                          ),
                                        )
                                      : const Row(
                                          mainAxisAlignment:
                                              MainAxisAlignment.center,
                                          children: [
                                            Text(
                                              'G',
                                              style: TextStyle(
                                                color: Color(0xFF4285F4),
                                                fontSize: 19,
                                                fontWeight: FontWeight.w800,
                                              ),
                                            ),
                                            SizedBox(width: space8),
                                            Text(
                                              'Masuk dengan Google',
                                              style: TextStyle(
                                                fontSize: 13,
                                                fontWeight: FontWeight.w800,
                                              ),
                                            ),
                                          ],
                                        ),
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                      const Spacer(),
                      Padding(
                        padding: const EdgeInsets.only(top: space20),
                        child: Column(
                          children: [
                            TextButton(
                              onPressed: _openRegister,
                              child: const Text(
                                'Belum punya akun? Daftar',
                                style: TextStyle(
                                  color: primaryColor,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ),
                            const Text(
                              'Aplikasi Khusus Karyawan Kebun Haifresh Lembang © 2026',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                color: mutedColor,
                                fontSize: 11,
                                height: 1.5,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}

class _LoginHeaderClipper extends CustomClipper<Path> {
  @override
  Path getClip(Size size) {
    return Path()
      ..lineTo(size.width, 0)
      ..lineTo(size.width, size.height * 0.69)
      ..lineTo(0, size.height)
      ..close();
  }

  @override
  bool shouldReclip(covariant CustomClipper<Path> oldClipper) => false;
}

class _BerryLogo extends StatelessWidget {
  const _BerryLogo();

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 53,
      height: 53,
      decoration: BoxDecoration(
        color: const Color(0xFFFFF1F2),
        border: Border.all(color: const Color(0xFFFEE2E2)),
        borderRadius: BorderRadius.circular(cardRadius),
      ),
      child: CustomPaint(painter: _BerryLogoPainter()),
    );
  }
}

class _BerryLogoPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final scale = size.width / 48;
    canvas.save();
    canvas.scale(scale);

    final berry = Path()
      ..moveTo(24, 17)
      ..cubicTo(16.2, 9.9, 5.5, 15.4, 7.8, 25.1)
      ..cubicTo(10.2, 35.5, 24, 42, 24, 42)
      ..cubicTo(24, 42, 37.8, 35.5, 40.2, 25.1)
      ..cubicTo(42.5, 15.4, 31.8, 9.9, 24, 17)
      ..close();
    canvas.drawPath(berry, Paint()..color = dangerColor);

    final leaf = Path()
      ..moveTo(24, 17)
      ..cubicTo(22.2, 9.6, 26.8, 5.9, 33.4, 5.5)
      ..cubicTo(33.2, 11.5, 30.1, 16.1, 24, 17)
      ..close();
    canvas.drawPath(leaf, Paint()..color = primaryColor);

    final secondLeaf = Path()
      ..moveTo(23.5, 16.5)
      ..cubicTo(18.4, 11, 13.3, 12.4, 9.9, 15.7)
      ..cubicTo(14.9, 18.5, 19.4, 19.5, 23.5, 16.5)
      ..close();
    canvas.drawPath(secondLeaf, Paint()..color = accentGreen);

    final seedPaint = Paint()..color = const Color(0xFFFFF1F2);
    for (final seed in const [
      Offset(17, 25),
      Offset(26, 23),
      Offset(32, 28),
      Offset(21, 33),
      Offset(28, 35),
    ]) {
      canvas.drawCircle(seed, 1.2, seedPaint);
    }
    canvas.restore();
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
