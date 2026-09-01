import 'package:flutter/foundation.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';

class SubscriptionProvider with ChangeNotifier {
  Map<String, dynamic>? _subscription;
  Map<String, dynamic>? _usage;
  bool _isLoading = false;
  String? _errorMessage;

  Map<String, dynamic>? get subscription => _subscription;
  Map<String, dynamic>? get usage => _usage;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  bool get isCore => _subscription?['plan_code'] == 'core' || _usage?['is_core'] == true;

  Future<void> fetchSubscriptionInfo() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await ApiClient.get(ApiEndpoints.subscriptionLimits);
      if (res is Map<String, dynamic>) {
        _subscription = res['subscription'];
        _usage = res['usage'];
      }
    } catch (e) {
      _errorMessage = 'Gagal memuat status langganan: $e';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> upgradePlan(String planCode, String billingCycle) async {
    _isLoading = true;
    notifyListeners();

    try {
      await ApiClient.post(ApiEndpoints.subscriptionUpgrade, body: {
        'plan_code': planCode,
        'billing_cycle': billingCycle,
      });
      await fetchSubscriptionInfo();
      return true;
    } catch (e) {
      _errorMessage = 'Gagal melakukan upgrade: $e';
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}

