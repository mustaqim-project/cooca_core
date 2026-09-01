import 'package:flutter/foundation.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../core/storage/local_storage.dart';
import '../models/business_model.dart';
import '../models/user_model.dart';

enum AuthStatus { initial, authenticating, authenticated, unauthenticated, error }

class AuthProvider with ChangeNotifier {
  AuthStatus _status = AuthStatus.initial;
  UserModel? _user;
  List<BusinessModel> _businesses = [];
  BusinessModel? _activeBusiness;
  String? _errorMessage;

  AuthStatus get status => _status;
  UserModel? get user => _user;
  List<BusinessModel> get businesses => _businesses;
  BusinessModel? get activeBusiness => _activeBusiness;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _status == AuthStatus.authenticated && _user != null;

  Future<void> checkAuthStatus() async {
    final token = LocalStorage.getToken();
    if (token == null || token.isEmpty) {
      _status = AuthStatus.unauthenticated;
      notifyListeners();
      return;
    }
    try {
      final res = await ApiClient.get(ApiEndpoints.me);
      if (res is Map<String, dynamic> && res.containsKey('user')) {
        _user = UserModel.fromJson(res['user']);
        await fetchBusinesses();
        await syncCurrentContext();
        _status = AuthStatus.authenticated;
      } else {
        _status = AuthStatus.unauthenticated;
      }
    } catch (e) {
      _status = AuthStatus.unauthenticated;
    }
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    _status = AuthStatus.authenticating;
    _errorMessage = null;
    notifyListeners();
    try {
      final res = await ApiClient.post(ApiEndpoints.login, body: {
        'email': email, 'password': password,
      });
      if (res is Map<String, dynamic> && res.containsKey('token')) {
        await LocalStorage.setToken(res['token'].toString());
        if (res.containsKey('user')) _user = UserModel.fromJson(res['user']);
        await fetchBusinesses();
        await syncCurrentContext();
        _status = AuthStatus.authenticated;
        notifyListeners();
        return true;
      }
      throw ApiException(statusCode: 400, message: 'Format respon tidak valid.');
    } on ApiException catch (e) {
      _errorMessage = e.message;
      _status = AuthStatus.error;
      notifyListeners();
      return false;
    } catch (e) {
      _errorMessage = 'Gagal login: $e';
      _status = AuthStatus.error;
      notifyListeners();
      return false;
    }
  }

  /// Register user + bisnis sekaligus dari mobile app
  Future<bool> register({
    required String name,
    required String email,
    required String phone,
    required String password,
    required String businessName,
    required String businessType,
    required String city,
  }) async {
    _status = AuthStatus.authenticating;
    _errorMessage = null;
    notifyListeners();
    try {
      final res = await ApiClient.post(ApiEndpoints.register, body: {
        'name': name,
        'email': email,
        'phone': phone,
        'password': password,
        'password_confirmation': password,
        'business_name': businessName,
        'business_type': businessType,
        'city': city,
      });
      if (res is Map<String, dynamic> && res.containsKey('token')) {
        await LocalStorage.setToken(res['token'].toString());
        if (res.containsKey('user')) _user = UserModel.fromJson(res['user']);
        await fetchBusinesses();
        await syncCurrentContext();
        _status = AuthStatus.authenticated;
        notifyListeners();
        return true;
      }
      throw ApiException(statusCode: 400, message: 'Registrasi gagal. Coba lagi.');
    } on ApiException catch (e) {
      _errorMessage = e.message;
      _status = AuthStatus.error;
      notifyListeners();
      return false;
    } catch (e) {
      _errorMessage = 'Error: $e';
      _status = AuthStatus.error;
      notifyListeners();
      return false;
    }
  }

  Future<void> syncCurrentContext() async {
    try {
      final res = await ApiClient.get(ApiEndpoints.contextCurrent);
      if (res is Map<String, dynamic>) {
        final role = res['role']?.toString() ?? 'owner';
        final isOwner = res['is_owner'] == true || role == 'owner';
        final perms = (res['permissions'] as List?)?.map((e) => e.toString()).toList() ?? [];

        if (_user != null) {
          _user = UserModel(
            id: _user!.id,
            name: _user!.name,
            email: _user!.email,
            phone: _user!.phone,
            avatarUrl: _user!.avatarUrl,
            role: role,
            isOwner: isOwner,
            permissions: perms,
          );
        }
      }
    } catch (e) {
      if (kDebugMode) print('Error syncCurrentContext: $e');
    }
    notifyListeners();
  }

  Future<void> fetchBusinesses() async {
    try {
      final res = await ApiClient.get(ApiEndpoints.businesses);
      if (res is Map<String, dynamic> && res.containsKey('businesses')) {
        final list = res['businesses'] as List;
        _businesses = list.map((b) => BusinessModel.fromJson(b)).toList();
        final savedId = LocalStorage.getBusinessId();
        if (savedId != null) {
          _activeBusiness = _businesses.firstWhere(
            (b) => b.id == savedId,
            orElse: () => _businesses.isNotEmpty ? _businesses.first
                : BusinessModel(id: '', name: 'Outlet'),
          );
        } else if (_businesses.isNotEmpty) {
          _activeBusiness = _businesses.first;
          await LocalStorage.setBusinessId(_activeBusiness!.id);
        }
      }
    } catch (e) {
      if (kDebugMode) print('Error fetchBusinesses: $e');
    }
    notifyListeners();
  }

  Future<void> setActiveBusiness(BusinessModel business) async {
    _activeBusiness = business;
    await LocalStorage.setBusinessId(business.id);
    await syncCurrentContext();
    notifyListeners();
  }

  Future<void> logout() async {
    try { await ApiClient.post(ApiEndpoints.logout); } catch (_) {}
    await LocalStorage.clearAll();
    _user = null; _activeBusiness = null; _businesses = [];
    _status = AuthStatus.unauthenticated;
    notifyListeners();
  }
}


