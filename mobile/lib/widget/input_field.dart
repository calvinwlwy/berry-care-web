import 'package:flutter/material.dart';

// ignore: non_constant_identifier_names
Widget InputField({
  required double width,
  required TextEditingController controller,
  required String hintText,
  bool isObscure = false,
}) {
  return Container(
    width: width * 0.8,
    padding: spacingForInput,
    child: TextFormField(
      controller: controller,
      obscureText: isObscure,
      style: const TextStyle(color: Colors.white),
      decoration: InputDecoration(
        hintText: hintText,
        hintStyle: const TextStyle(color: Colors.white, fontSize: 22),
        border: InputBorder.none,
      ),
    ),
  );
}

const EdgeInsets spacingForInput = EdgeInsets.symmetric(horizontal: 20);
