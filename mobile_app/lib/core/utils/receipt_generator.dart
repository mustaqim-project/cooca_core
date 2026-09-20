import 'package:intl/intl.dart';
import '../../models/order_model.dart';
import '../../models/business_model.dart';
import 'currency_formatter.dart';

class ReceiptGenerator {
  static String generateTextReceipt({
    required PosOrderModel order,
    required BusinessModel business,
    String? cashierName,
    int width = 32, // 32 characters for 58mm printer, 42 for 80mm
  }) {
    final buffer = StringBuffer();
    final divider = '-' * width;
    final doubleDivider = '=' * width;

    // Header
    buffer.writeln(_center(business.name.toUpperCase(), width));
    if (business.address != null && business.address!.isNotEmpty) {
      buffer.writeln(_center(business.address!, width));
    }
    if (business.phone != null && business.phone!.isNotEmpty) {
      buffer.writeln(_center('Telp: ${business.phone!}', width));
    }
    buffer.writeln(doubleDivider);

    // Order Info
    buffer.writeln('No. Struk : ${order.orderNumber}');
    buffer.writeln('Tanggal   : ${DateFormat('dd/MM/yyyy HH:mm').format(order.createdAt)}');
    if (cashierName != null) {
      buffer.writeln('Kasir     : $cashierName');
    }
    if (order.customerName != null) {
      buffer.writeln('Pelanggan : ${order.customerName}');
    }
    buffer.writeln(divider);

    // Items
    for (final item in order.items) {
      buffer.writeln(item.productName);
      final qtyStr = '${item.quantity} x ${CurrencyFormatter.format(item.unitPrice)}';
      final subtotalStr = CurrencyFormatter.format(item.subtotal);
      buffer.writeln(_alignLeftRight(qtyStr, subtotalStr, width));

      if (item.discountAmount > 0) {
        buffer.writeln(_alignLeftRight('  Diskon Item', '-${CurrencyFormatter.format(item.discountAmount)}', width));
      }
      if (item.notes != null && item.notes!.isNotEmpty) {
        buffer.writeln('  (${item.notes})');
      }
    }
    buffer.writeln(divider);

    // Totals
    buffer.writeln(_alignLeftRight('Subtotal', CurrencyFormatter.format(order.subtotal), width));
    if (order.discountAmount > 0) {
      buffer.writeln(_alignLeftRight('Diskon Pesanan', '-${CurrencyFormatter.format(order.discountAmount)}', width));
    }
    if (order.taxAmount > 0) {
      buffer.writeln(_alignLeftRight('Pajak (PB1)', CurrencyFormatter.format(order.taxAmount), width));
    }
    if (order.serviceChargeAmount > 0) {
      buffer.writeln(_alignLeftRight('Service Charge', CurrencyFormatter.format(order.serviceChargeAmount), width));
    }
    buffer.writeln(doubleDivider);
    buffer.writeln(_alignLeftRight('TOTAL', CurrencyFormatter.format(order.grandTotal), width));

    // Payments
    for (final payment in order.payments) {
      final method = payment.paymentMethod.toUpperCase();
      buffer.writeln(_alignLeftRight(method, CurrencyFormatter.format(payment.amount), width));
      if (payment.tenderedAmount != null && payment.tenderedAmount! > 0) {
        buffer.writeln(_alignLeftRight('Diterima', CurrencyFormatter.format(payment.tenderedAmount), width));
      }
      if (payment.changeAmount != null && payment.changeAmount! > 0) {
        buffer.writeln(_alignLeftRight('Kembali', CurrencyFormatter.format(payment.changeAmount), width));
      }
    }

    buffer.writeln(divider);
    buffer.writeln(_center(business.posReceiptFooterNote ?? 'Terima Kasih Atas Kunjungan Anda!', width));
    buffer.writeln(_center('Powered by Cooca POS', width));
    buffer.writeln('\n\n');

    return buffer.toString();
  }

  static String _center(String text, int width) {
    if (text.length >= width) return text.substring(0, width);
    final leftPadding = (width - text.length) ~/ 2;
    return ' ' * leftPadding + text;
  }

  static String _alignLeftRight(String left, String right, int width) {
    final spacesCount = width - left.length - right.length;
    if (spacesCount <= 0) {
      return '$left $right';
    }
    return left + (' ' * spacesCount) + right;
  }
}

