import 'package:flutter/foundation.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/shift_model.dart';

class ShiftProvider with ChangeNotifier {
  PosShiftModel? _activeShift;
  bool _isLoading = false;
  String? _errorMessage;

  PosShiftModel? get activeShift => _activeShift;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get hasActiveShift => _activeShift != null && _activeShift!.isOpen;

  Future<void> fetchActiveShift() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await ApiClient.get(ApiEndpoints.posActiveShift);
      if (res is Map<String, dynamic> && res.containsKey('active_shift') && res['active_shift'] != null) {
        _activeShift = PosShiftModel.fromJson(res['active_shift']);
      } else {
        _activeShift = null;
      }
    } on ApiException catch (e) {
      if (e.statusCode == 404) {
        _activeShift = null;
      } else {
        _errorMessage = e.message;
      }
    } catch (e) {
      _errorMessage = 'Gagal memuat status shift: $e';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> openShift(double initialCash, {String? notes}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await ApiClient.post(ApiEndpoints.posOpenShift, body: {
        'opening_cash': initialCash,
        'notes': notes,
      });

      if (res is Map<String, dynamic> && res.containsKey('shift')) {
        _activeShift = PosShiftModel.fromJson(res['shift']);
        notifyListeners();
        return true;
      }
      return false;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      return false;
    } catch (e) {
      _errorMessage = 'Gagal membuka shift: $e';
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> closeShift(double actualCash, {String? notes}) async {
    if (_activeShift == null) return false;

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await ApiClient.post(ApiEndpoints.posCloseShift(_activeShift!.id), body: {
        'actual_cash': actualCash,
        'notes': notes,
      });

      if (res is Map<String, dynamic> && res.containsKey('shift')) {
        _activeShift = PosShiftModel.fromJson(res['shift']);
        notifyListeners();
        return true;
      }
      return false;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      return false;
    } catch (e) {
      _errorMessage = 'Gagal menutup shift: $e';
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> recordCashMovement({
    required String type, // cash_in or cash_out
    required double amount,
    required String reason,
  }) async {
    if (_activeShift == null) return false;

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await ApiClient.post(ApiEndpoints.posShiftCashMovement(_activeShift!.id), body: {
        'type': type,
        'amount': amount,
        'reason': reason,
      });

      await fetchActiveShift();
      return true;
    } on ApiException catch (e) {
      _errorMessage = e.message;
      return false;
    } catch (e) {
      _errorMessage = 'Gagal mencatat mutasi kas: $e';
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}

