import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import '../../helper/constant.dart';
import '../../methods/api.dart';
import '../../widget/input_field.dart';
import '../home.dart';
import 'register.dart';

class Login extends StatefulWidget {
  const Login({super.key});

  @override
  State<Login> createState() => _LoginState();
}

class _LoginState extends State<Login> {
  final TextEditingController emailController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  bool isLoading = false;

  @override
  void dispose() {
    emailController.dispose();
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

  Future<void> loginUser() async {
    if (isLoading) return;
    setState(() => isLoading = true);

    try {
      final result = await API().postRequest(
        route: '/login',
        data: {
          'email': emailController.text.trim(),
          'password': passwordController.text,
        },
      );
      final response = jsonDecode(result.body);

      if (result.statusCode == 200) {
        final preferences = await SharedPreferences.getInstance();
        await preferences.setInt('user_id', response['user']['id'] as int);
        await preferences.setString('name', response['user']['name'] as String);
        await preferences.setString(
          'email',
          response['user']['email'] as String,
        );
        await preferences.setString('token', response['token'] as String);
        _showMessage(response['message'] as String);
        if (!mounted) return;
        Navigator.pushReplacement(
          context,
          MaterialPageRoute<void>(builder: (_) => const Home()),
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
                        'Sign In',
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
                        'A world of possibly in an App',
                        style: TextStyle(color: Colors.white, fontSize: 32),
                      ),
                    ),
                  ],
                ),
                spancer(h: height * 0.15),
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
                spancer(h: height * 0.03),
                Container(
                  width: double.maxFinite,
                  padding: spacing(h: 20),
                  child: const Text(
                    'Forgot Password',
                    textAlign: TextAlign.right,
                    style: TextStyle(color: Colors.white, fontSize: 18),
                  ),
                ),
                spancer(h: height * 0.1),
                InkWell(
                  onTap: isLoading ? null : loginUser,
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
                      'Your first time?',
                      style: TextStyle(color: secondaryTextColor, fontSize: 18),
                    ),
                    spancer(w: 4),
                    InkWell(
                      onTap: () => Navigator.pushReplacement(
                        context,
                        MaterialPageRoute<void>(
                          builder: (_) => const Register(),
                        ),
                      ),
                      child: const Text(
                        'Sign Up',
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
