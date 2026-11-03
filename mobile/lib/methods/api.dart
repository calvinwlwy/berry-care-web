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
    } catch (e) {
      debugPrint('POST Error: $e');
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
    } catch (e) {
      debugPrint('GET Error: $e');
      rethrow;
    }
  }

  Future<http.Response> putRequest({
    required String route,
    required Map<String, dynamic> data,
    String? token,
  }) async {
    try {
      return await http.put(
        Uri.parse(baseUrl + route),
        body: jsonEncode(data),
        headers: _header(token: token),
      );
    } catch (e) {
      debugPrint('PUT Error: $e');
      rethrow;
    }
  }

  Future<http.Response> deleteRequest({
    required String route,
    Map<String, dynamic>? data,
    String? token,
  }) async {
    try {
      return await http.delete(
        Uri.parse(baseUrl + route),
        body: data == null ? null : jsonEncode(data),
        headers: _header(token: token),
      );
    } catch (e) {
      debugPrint('DELETE Error: $e');
      rethrow;
    }
  }

  Map<String, String> _header({String? token}) {
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }
}
