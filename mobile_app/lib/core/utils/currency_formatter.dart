import 'package:intl/intl.dart';

class CurrencyFormatter {
  static final NumberFormat _idrFormat = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  static final NumberFormat _compactFormat = NumberFormat.compactCurrency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 1,
  );

  static String format(num? amount, {String symbol = 'Rp '}) {
    if (amount == null) return '${symbol}0';
    if (symbol == 'Rp ') {
      return _idrFormat.format(amount);
    }
    return '$symbol ${NumberFormat('#,##0', 'en_US').format(amount)}';
  }

  static String formatCompact(num? amount) {
    if (amount == null) return 'Rp 0';
    return _compactFormat.format(amount);
  }

  static String formatNumber(num? amount) {
    if (amount == null) return '0';
    return NumberFormat('#,##0', 'id_ID').format(amount);
  }
}

