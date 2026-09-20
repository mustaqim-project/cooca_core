import 'package:flutter/foundation.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';

class ReportProvider with ChangeNotifier {
  Map<String, dynamic> _salesSummary = {};
  List<Map<String, dynamic>> _topProducts = [];
  Map<String, dynamic> _plSummary = {};
  bool _isLoading = false;
  String? _errorMessage;
  String _period = 'today'; // today, week, month, year

  Map<String, dynamic> get salesSummary => _salesSummary;
  List<Map<String, dynamic>> get topProducts => _topProducts;
  Map<String, dynamic> get plSummary => _plSummary;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String get period => _period;

  Future<void> fetchReports({String period = 'today'}) async {
    _period = period;
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      final res = await ApiClient.get(
        '${ApiEndpoints.dashboard}?period=$period',
      );
      if (res is Map<String, dynamic>) {
        _salesSummary = Map<String, dynamic>.from(res['summary'] ?? {});
        _topProducts = List<Map<String, dynamic>>.from(
          res['top_products'] ?? [],
        );
        _plSummary = Map<String, dynamic>.from(res['pl'] ?? {});
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (e) {
      _errorMessage = 'Gagal memuat laporan';
      if (kDebugMode) print('ReportProvider error: $e');
    }
    _isLoading = false;
    notifyListeners();
  }

  void setPeriod(String period) {
    _period = period;
    fetchReports(period: period);
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}

