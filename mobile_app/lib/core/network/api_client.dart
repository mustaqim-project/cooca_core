import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../constants/api_endpoints.dart';
import '../storage/local_storage.dart';

class ApiException implements Exception {
  final int statusCode;
  final String message;
  final Map<String, dynamic>? errors;

  ApiException({
    required this.statusCode,
    required this.message,
    this.errors,
  });

  @override
  String toString() => 'ApiException [$statusCode]: $message';
}

class ApiClient {
  static String get baseUrl => LocalStorage.getBaseUrl() ?? ApiEndpoints.defaultBaseUrl;

  static Map<String, String> _buildHeaders() {
    final token = LocalStorage.getToken();
    final businessId = LocalStorage.getBusinessId();

    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }

    if (businessId != null && businessId.isNotEmpty) {
      headers['X-Business-Id'] = businessId;
    }

    return headers;
  }

  static Future<dynamic> get(String path, {Map<String, dynamic>? queryParameters}) async {
    try {
      final uri = Uri.parse('$baseUrl$path').replace(
        queryParameters: queryParameters?.map((k, v) => MapEntry(k, v.toString())),
      );
      final response = await http.get(uri, headers: _buildHeaders());
      return _processResponse(response);
    } on SocketException {
      throw ApiException(statusCode: 0, message: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(statusCode: 500, message: e.toString());
    }
  }

  static Future<dynamic> post(String path, {dynamic body}) async {
    try {
      final uri = Uri.parse('$baseUrl$path');
      final response = await http.post(
        uri,
        headers: _buildHeaders(),
        body: body != null ? jsonEncode(body) : null,
      );
      return _processResponse(response);
    } on SocketException {
      throw ApiException(statusCode: 0, message: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(statusCode: 500, message: e.toString());
    }
  }

  static Future<dynamic> put(String path, {dynamic body}) async {
    try {
      final uri = Uri.parse('$baseUrl$path');
      final response = await http.put(
        uri,
        headers: _buildHeaders(),
        body: body != null ? jsonEncode(body) : null,
      );
      return _processResponse(response);
    } on SocketException {
      throw ApiException(statusCode: 0, message: 'Tidak dapat terhubung ke server.');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(statusCode: 500, message: e.toString());
    }
  }

  static Future<dynamic> patch(String path, {dynamic body}) async {
    try {
      final uri = Uri.parse('$baseUrl$path');
      final response = await http.patch(
        uri,
        headers: _buildHeaders(),
        body: body != null ? jsonEncode(body) : null,
      );
      return _processResponse(response);
    } on SocketException {
      throw ApiException(statusCode: 0, message: 'Tidak dapat terhubung ke server.');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(statusCode: 500, message: e.toString());
    }
  }

  static Future<dynamic> delete(String path) async {
    try {
      final uri = Uri.parse('$baseUrl$path');
      final response = await http.delete(uri, headers: _buildHeaders());
      return _processResponse(response);
    } on SocketException {
      throw ApiException(statusCode: 0, message: 'Tidak dapat terhubung ke server.');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(statusCode: 500, message: e.toString());
    }
  }

  static dynamic _processResponse(http.Response response) {
    dynamic decoded;
    try {
      decoded = jsonDecode(response.body);
    } catch (_) {
      decoded = {'message': response.body};
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }

    String message = 'Terjadi kesalahan sistem (${response.statusCode})';
    Map<String, dynamic>? errors;

    if (decoded is Map<String, dynamic>) {
      if (decoded.containsKey('message')) {
        message = decoded['message'].toString();
      }
      if (decoded.containsKey('errors') && decoded['errors'] is Map<String, dynamic>) {
        errors = decoded['errors'] as Map<String, dynamic>;
      }
    }

    throw ApiException(
      statusCode: response.statusCode,
      message: message,
      errors: errors,
    );
  }
}

