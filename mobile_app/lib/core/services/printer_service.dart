import 'package:flutter/foundation.dart';
import 'package:intl/intl.dart';

class PrinterService {
  static final PrinterService _instance = PrinterService._internal();
  factory PrinterService() => _instance;
  PrinterService._internal();

  final currencyFormatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  bool _isConnected = false;
  String? _connectedDeviceName;

  bool get isConnected => _isConnected;
  String? get connectedDeviceName => _connectedDeviceName;

  Future<void> connectToPrinter(String deviceName) async {
    _connectedDeviceName = deviceName;
    _isConnected = true;
  }

  Future<void> disconnect() async {
    _connectedDeviceName = null;
    _isConnected = false;
  }

  /// Generate ESC/POS receipt text format for 58mm / 80mm thermal printers
  String generateReceiptText({
    required String businessName,
    required String invoiceNo,
    required List<Map<String, dynamic>> items,
    required double subtotal,
    required double discount,
    required double tax,
    required double total,
    required String paymentMethod,
    double cashReceived = 0,
    double changeAmount = 0,
  }) {
    final now = DateFormat('dd/MM/yyyy HH:mm').format(DateTime.now());
    final buffer = StringBuffer();

    // Center header
    buffer.writeln('================================');
    buffer.writeln(businessName.toUpperCase().padLeft((32 + businessName.length) ~/ 2));
    buffer.writeln('STRUK PEMBAYARAN KASIR'.padLeft(26));
    buffer.writeln('================================');
    buffer.writeln('No. Faktur: #$invoiceNo');
    buffer.writeln('Waktu     : $now');
    buffer.writeln('Kasir     : POS Terminal');
    buffer.writeln('--------------------------------');

    // Items
    for (final item in items) {
      final name = (item['name'] ?? 'Item').toString();
      final qty = (item['qty'] ?? 1).toString();
      final price = (item['price'] ?? 0).toDouble();
      final itemSubtotal = (item['subtotal'] ?? (price * (item['qty'] ?? 1))).toDouble();

      buffer.writeln(name);
      final line = '  ${qty}x @${currencyFormatter.format(price)}'.padRight(16) +
          currencyFormatter.format(itemSubtotal).padLeft(14);
      buffer.writeln(line);
    }

    buffer.writeln('--------------------------------');
    buffer.writeln('Subtotal  :'.padRight(16) + currencyFormatter.format(subtotal).padLeft(16));
    if (discount > 0) {
      buffer.writeln('Diskon    :'.padRight(16) + ('-' + currencyFormatter.format(discount)).padLeft(16));
    }
    if (tax > 0) {
      buffer.writeln('Pajak     :'.padRight(16) + currencyFormatter.format(tax).padLeft(16));
    }
    buffer.writeln('================================');
    buffer.writeln('TOTAL     :'.padRight(16) + currencyFormatter.format(total).padLeft(16));
    buffer.writeln('Metode    :'.padRight(16) + paymentMethod.toUpperCase().padLeft(16));
    if (paymentMethod.toLowerCase() == 'cash' && cashReceived > 0) {
      buffer.writeln('Bayar Tunai:'.padRight(16) + currencyFormatter.format(cashReceived).padLeft(16));
      buffer.writeln('Kembalian :'.padRight(16) + currencyFormatter.format(changeAmount).padLeft(16));
    }
    buffer.writeln('================================');
    buffer.writeln('Terima kasih atas kunjungan Anda');
    buffer.writeln('cooca.id - Business OS\n\n\n');

    return buffer.toString();
  }

  Future<bool> printReceipt(String receiptText) async {
    if (kDebugMode) {
      print('=== THERMAL PRINT DISPATCH ===\n$receiptText');
    }
    return true;
  }
}

