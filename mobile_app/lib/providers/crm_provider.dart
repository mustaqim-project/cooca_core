import 'package:flutter/foundation.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';

class CrmProvider with ChangeNotifier {
  List<Map<String, dynamic>> _customers = [];
  List<Map<String, dynamic>> _vouchers = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<Map<String, dynamic>> get customers => _customers;
  List<Map<String, dynamic>> get vouchers => _vouchers;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> fetchCustomers({String? query}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final url = query != null && query.isNotEmpty
          ? '${ApiEndpoints.customers}?search=$query'
          : ApiEndpoints.customers;
      final res = await ApiClient.get(url);
      if (res is Map && res.containsKey('customers')) {
        _customers = List<Map<String, dynamic>>.from(res['customers'] as List);
      } else if (res is List) {
        _customers = List<Map<String, dynamic>>.from(res);
      }
    } catch (e) {
      _errorMessage = 'Gagal memuat pelanggan: $e';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> createCustomer(Map<String, dynamic> data) async {
    try {
      await ApiClient.post(ApiEndpoints.customers, body: data);
      await fetchCustomers();
      return true;
    } catch (e) {
      _errorMessage = 'Gagal menambah pelanggan: $e';
      notifyListeners();
      return false;
    }
  }

  Future<void> fetchVouchers() async {
    try {
      final res = await ApiClient.get(ApiEndpoints.crmVouchers);
      if (res is Map && res.containsKey('vouchers')) {
        _vouchers = List<Map<String, dynamic>>.from(res['vouchers'] as List);
      }
      notifyListeners();
    } catch (_) {}
  }
}

