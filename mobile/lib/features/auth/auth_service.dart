import 'package:dio/dio.dart';
import '../../core/api_client.dart';

class AuthService {
  AuthService(this._api);
  final ApiClient _api;

  Future<void> login({required String email, required String password}) async {
    final res = await _api.dio.post('/login', data: {
      'email': email,
      'password': password,
    });
    final token = res.data['access_token'] as String?;
    if (token == null) throw Exception('No token');
    await _api.saveToken(token);
  }

  Future<void> logout() async {
    try {
      await _api.dio.post('/logout');
    } catch (_) {}
    await _api.clearToken();
  }
}
