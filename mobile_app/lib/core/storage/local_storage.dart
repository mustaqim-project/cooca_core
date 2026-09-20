import 'package:shared_preferences/shared_preferences.dart';

class LocalStorage {
  static const String _keyToken = 'auth_token';
  static const String _keyBusinessId = 'active_business_id';
  static const String _keyBaseUrl = 'api_base_url';
  static const String _keyPrinterIp = 'printer_ip';
  static const String _keyPrinterPaperSize = 'printer_paper_size';

  static SharedPreferences? _prefs;

  static Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
  }

  static String? getToken() => _prefs?.getString(_keyToken);
  static Future<bool> setToken(String? token) async {
    if (token == null) return _prefs?.remove(_keyToken) ?? Future.value(false);
    return _prefs?.setString(_keyToken, token) ?? Future.value(false);
  }

  static String? getBusinessId() => _prefs?.getString(_keyBusinessId);
  static Future<bool> setBusinessId(String? id) async {
    if (id == null) return _prefs?.remove(_keyBusinessId) ?? Future.value(false);
    return _prefs?.setString(_keyBusinessId, id) ?? Future.value(false);
  }

  static String? getBaseUrl() => _prefs?.getString(_keyBaseUrl);
  static Future<bool> setBaseUrl(String url) async =>
      _prefs?.setString(_keyBaseUrl, url) ?? Future.value(false);

  static String? getPrinterIp() => _prefs?.getString(_keyPrinterIp);
  static Future<bool> setPrinterIp(String ip) async =>
      _prefs?.setString(_keyPrinterIp, ip) ?? Future.value(false);

  static int getPrinterPaperSize() => _prefs?.getInt(_keyPrinterPaperSize) ?? 58;
  static Future<bool> setPrinterPaperSize(int size) async =>
      _prefs?.setInt(_keyPrinterPaperSize, size) ?? Future.value(false);

  static const String _keyOnboardingDone = 'onboarding_done';

  static bool isOnboardingDone() => _prefs?.getBool(_keyOnboardingDone) ?? false;
  static Future<bool> setOnboardingDone(bool done) async =>
      _prefs?.setBool(_keyOnboardingDone, done) ?? Future.value(false);

  static Future<void> clearAll() async {
    await _prefs?.remove(_keyToken);
    await _prefs?.remove(_keyBusinessId);
  }
}

