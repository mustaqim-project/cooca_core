import 'package:flutter/foundation.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';

class InventoryProvider with ChangeNotifier {
  List<Map<String, dynamic>> _stocks = [];
  List<Map<String, dynamic>> _movements = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<Map<String, dynamic>> get stocks => _stocks;
  List<Map<String, dynamic>> get movements => _movements;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  List<Map<String, dynamic>> get lowStockItems => _stocks
      .where((s) => (s['quantity'] ?? 0) <= (s['minimum_stock'] ?? 0))
      .toList();

  Future<void> fetchStocks() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      final res = await ApiClient.get(ApiEndpoints.stocks);
      if (res is Map && res.containsKey('stocks')) {
        _stocks = List<Map<String, dynamic>>.from(res['stocks'] as List);
      } else if (res is List) {
        _stocks = List<Map<String, dynamic>>.from(res);
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (e) {
      _errorMessage = 'Gagal memuat data stok';
      if (kDebugMode) print('InventoryProvider error: $e');
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchMovements() async {
    try {
      final res = await ApiClient.get(ApiEndpoints.stockMovements);
      if (res is Map && res.containsKey('movements')) {
        _movements = List<Map<String, dynamic>>.from(res['movements'] as List);
      }
    } catch (e) {
      if (kDebugMode) print('InventoryProvider.movements error: $e');
    }
    notifyListeners();
  }

  Future<bool> quickAdjust({
    required String productId,
    required double quantity,
    required String type,
    String? reason,
  }) async {
    try {
      await ApiClient.post(ApiEndpoints.stockAdjust, body: {
        'product_id': productId,
        'quantity': quantity,
        'type': type,
        'reason': reason ?? '',
      });
      await fetchStocks();
      return true;
    } catch (e) {
      _errorMessage = 'Gagal menyesuaikan stok';
      notifyListeners();
      return false;
    }
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}

