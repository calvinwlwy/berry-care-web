import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

import '../helper/constant.dart';

class API {
  final String baseUrl = apiUrl;

  Future<http.Response> postRequest({
    required String route,
    required Map<String, dynamic> data,
    String? token,
  }) async {
    try {
      return await http.post(
        Uri.parse(baseUrl + route),
        body: jsonEncode(data),
        headers: _header(token: token),
      );
    } catch (error) {
      debugPrint('POST Error: $error');
      rethrow;
    }
  }

  Future<http.Response> getRequest({
    required String route,
    String? token,
  }) async {
    try {
      return await http.get(
        Uri.parse(baseUrl + route),
        headers: _header(token: token),
      );
    } catch (error) {
      debugPrint('GET Error: $error');
      rethrow;
    }
  }

  Map<String, String> _header({String? token}) {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null && token.isNotEmpty) 'Authorization': 'Bearer $token',
    };
  }
}

String responseMessage(http.Response response) {
  try {
    final decoded = jsonDecode(response.body);
    if (response.statusCode == 422 &&
        decoded is Map &&
        decoded['errors'] is Map) {
      final errors = decoded['errors'] as Map;
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty) {
          return value.first.toString();
        }
      }
    }

    if (decoded is Map && decoded['message'] is String) {
      return decoded['message'] as String;
    }
  } on FormatException {
    return _fallbackMessage(response.statusCode);
  }

  return _fallbackMessage(response.statusCode);
}

String _fallbackMessage(int statusCode) {
  return switch (statusCode) {
    401 => 'Email atau password salah. Periksa kembali data login Anda.',
    422 => 'Data yang dikirim belum sesuai. Periksa kembali isian Anda.',
    429 => 'Terlalu banyak percobaan. Tunggu beberapa saat lalu coba lagi.',
    >= 500 =>
      'Server sedang bermasalah. Coba lagi beberapa saat atau hubungi admin.',
    _ =>
      'Permintaan gagal (kode $statusCode). Periksa koneksi atau hubungi admin.',
  };
}
