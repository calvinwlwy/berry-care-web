import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import '../../helper/constant.dart';
import '../../methods/api.dart';
import '../../widget/input_field.dart';
import 'login.dart';

class Register extends StatefulWidget {
  const Register({super.key});

  @override
  State<Register> createState() => _RegisterState();
}

class _RegisterState extends State<Register> {
  final TextEditingController emailController = TextEditingController();
  final TextEditingController usernameController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  bool isLoading = false;

  @override
  void dispose() {
    emailController.dispose();
    usernameController.dispose();
    passwordController.dispose();
    super.dispose();
  }

  String _responseMessage(http.Response response) {
    final decoded = jsonDecode(response.body);
    if (response.statusCode == 422 && decoded['errors'] is Map) {
      final errors = decoded['errors'] as Map<String, dynamic>;
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty) {
          return value.first.toString();
        }
      }
    }

    return decoded is Map && decoded['message'] is String
        ? decoded['message'] as String
        : 'Terjadi kesalahan. Silakan coba lagi.';
  }

  void _showMessage(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(SnackBar(content: Text(message)));
  }

  Future<void> registerUser() async {
    if (isLoading) return;
    setState(() => isLoading = true);

    try {
      final password = passwordController.text;
      final result = await API().postRequest(
        route: '/register',
        data: {
          'email': emailController.text.trim(),
          'name': usernameController.text.trim(),
          'password': password,
          'password_confirmation': password,
        },
      );
      final response = jsonDecode(result.body);

      if (result.statusCode == 201) {
        _showMessage(response['message'] as String);
        if (!mounted) return;
        Navigator.pushReplacement(
          context,
          MaterialPageRoute<void>(builder: (_) => const Login()),
        );
      } else {
        _showMessage(_responseMessage(result));
      }
    } catch (e) {
      _showMessage('Tidak dapat terhubung ke server: $e');
    } finally {
      if (mounted) setState(() => isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final width = deviceWidth(context);
    final height = deviceHeight(context);

    return Scaffold(
      body: Container(
        width: width,
        height: height,
        padding: spacing(h: 20),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topRight,
            end: Alignment.bottomLeft,
            colors: [primaryColor, secondaryColor],
          ),
        ),
        child: SafeArea(
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                spancer(h: height * 0.1),
                Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    const RotatedBox(
                      quarterTurns: 3,
                      child: Text(
                        'Sign Up',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 48,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    spancer(w: width * 0.05),
                    // Keep Container to match the supplied reference layout.
                    // ignore: sized_box_for_whitespace
                    Container(
                      width: width * 0.5,
                      child: const Text(
                        'We can start something new',
                        style: TextStyle(color: Colors.white, fontSize: 32),
                      ),
                    ),
                  ],
                ),
                spancer(h: height * 0.12),
                InputField(
                  width: width,
                  controller: usernameController,
                  hintText: 'Username',
                ),
                spancer(h: height * 0.01),
                InputField(
                  width: width,
                  controller: emailController,
                  hintText: 'Email',
                ),
                spancer(h: height * 0.01),
                InputField(
                  width: width,
                  controller: passwordController,
                  hintText: 'Password',
                  isObscure: true,
                ),
                spancer(h: height * 0.08),
                InkWell(
                  onTap: isLoading ? null : registerUser,
                  child: Container(
                    alignment: Alignment.centerRight,
                    padding: spacing(h: 20),
                    child: Container(
                      width: width * 0.3,
                      padding: spacing(h: 20, v: 10),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(width),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            isLoading ? '...' : 'OK',
                            style: TextStyle(color: primaryColor, fontSize: 18),
                          ),
                          spancer(w: 10),
                          Icon(
                            Icons.arrow_right_alt_rounded,
                            color: primaryColor,
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
                SizedBox(height: height * 0.1),
                Row(
                  children: [
                    Text(
                      'Have we met before?',
                      style: TextStyle(color: secondaryTextColor, fontSize: 18),
                    ),
                    spancer(w: 4),
                    InkWell(
                      onTap: () => Navigator.pushReplacement(
                        context,
                        MaterialPageRoute<void>(
                          builder: (_) => const Login(),
                        ),
                      ),
                      child: const Text(
                        'Sign In',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
