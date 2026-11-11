import 'package:flutter/material.dart';

import '../../helper/constant.dart';
import '../../methods/api.dart';
import '../../widget/app_widgets.dart';
import '../../widget/input_field.dart';
import 'login.dart';

class Register extends StatefulWidget {
  const Register({super.key});

  @override
  State<Register> createState() => _RegisterState();
}

class _RegisterState extends State<Register> {
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _confirmation = TextEditingController();
  bool _busy = false;

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _password.dispose();
    _confirmation.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    if (_busy) return;
    setState(() => _busy = true);
    try {
      final response = await API().postRequest(
        route: '/register',
        data: {
          'name': _name.text.trim(),
          'email': _email.text.trim(),
          'password': _password.text,
          'password_confirmation': _confirmation.text,
        },
      );
      if (response.statusCode >= 200 && response.statusCode < 300) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Pendaftaran berhasil. Silakan masuk.')),
        );
        Navigator.of(context).pushReplacement(
          MaterialPageRoute<void>(builder: (_) => const Login()),
        );
      } else {
        _message(responseMessage(response));
      }
    } catch (error) {
      debugPrint('Register request failed: $error');
      _message(
        'Tidak dapat menghubungi server. Periksa koneksi dan coba lagi.',
      );
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  void _message(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context)
        .showSnackBar(SnackBar(content: Text(message)));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            const SizedBox(height: space16),
            IconButton(
              alignment: Alignment.centerLeft,
              onPressed: () => Navigator.of(context).maybePop(),
              icon: const Icon(Icons.arrow_back),
            ),
            const SizedBox(height: space16),
            Text(
              'Daftar akun',
              style: Theme.of(context).textTheme.headlineSmall
                  ?.copyWith(fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: space4),
            const Text(
              'Buat akun operator kebun Berry Care.',
              style: TextStyle(color: mutedColor),
            ),
            const SizedBox(height: space20),
            AppCard(
              padding: const EdgeInsets.all(20),
              child: Column(
                children: [
                  AppInputField(
                    controller: _name,
                    label: 'Nama',
                    icon: Icons.person_outline,
                    textInputAction: TextInputAction.next,
                  ),
                  const SizedBox(height: space12),
                  AppInputField(
                    controller: _email,
                    label: 'Email',
                    icon: Icons.mail_outline,
                    keyboardType: TextInputType.emailAddress,
                    textInputAction: TextInputAction.next,
                  ),
                  const SizedBox(height: space12),
                  AppInputField(
                    controller: _password,
                    label: 'Password',
                    icon: Icons.lock_outline,
                    obscureText: true,
                    textInputAction: TextInputAction.next,
                  ),
                  const SizedBox(height: space12),
                  AppInputField(
                    controller: _confirmation,
                    label: 'Konfirmasi password',
                    icon: Icons.lock_outline,
                    obscureText: true,
                    textInputAction: TextInputAction.done,
                    onSubmitted: (_) => _register(),
                  ),
                  const SizedBox(height: 20),
                  AppButton(
                    label: _busy ? 'Mendaftarkan...' : 'Daftar',
                    onPressed: _busy ? null : _register,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
