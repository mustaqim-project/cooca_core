import 'package:flutter/foundation.dart';
import '../core/constants/api_endpoints.dart';
import '../core/storage/local_storage.dart';

class SettingsProvider with ChangeNotifier {
  String _baseUrl = ApiEndpoints.defaultBaseUrl;
  String? _printerIp;
  int _printerPaperSize = 58; // 58 or 80 mm

  String get baseUrl => _baseUrl;
  String? get printerIp => _printerIp;
  int get printerPaperSize => _printerPaperSize;

  Future<void> init() async {
    _baseUrl = LocalStorage.getBaseUrl() ?? ApiEndpoints.defaultBaseUrl;
    _printerIp = LocalStorage.getPrinterIp();
    _printerPaperSize = LocalStorage.getPrinterPaperSize();
    notifyListeners();
  }

  Future<void> setBaseUrl(String url) async {
    _baseUrl = url.trim();
    await LocalStorage.setBaseUrl(_baseUrl);
    notifyListeners();
  }

  Future<void> setPrinterIp(String? ip) async {
    _printerIp = ip?.trim();
    if (_printerIp != null) {
      await LocalStorage.setPrinterIp(_printerIp!);
    }
    notifyListeners();
  }

  Future<void> setPrinterPaperSize(int size) async {
    _printerPaperSize = size;
    await LocalStorage.setPrinterPaperSize(size);
    notifyListeners();
  }
}

