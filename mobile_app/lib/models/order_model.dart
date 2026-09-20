class PosOrderItemModel {
  final String id;
  final String? productId;
  final String productName;
  final double quantity;
  final double unitPrice;
  final double discountAmount;
  final double subtotal;
  final String? notes;

  PosOrderItemModel({
    required this.id,
    this.productId,
    required this.productName,
    required this.quantity,
    required this.unitPrice,
    this.discountAmount = 0.0,
    required this.subtotal,
    this.notes,
  });

  factory PosOrderItemModel.fromJson(Map<String, dynamic> json) {
    return PosOrderItemModel(
      id: json['id']?.toString() ?? '',
      productId: json['product_id']?.toString(),
      productName: json['product_name']?.toString() ?? 'Item',
      quantity: (json['quantity'] as num?)?.toDouble() ?? 1.0,
      unitPrice: (json['unit_price'] as num?)?.toDouble() ?? 0.0,
      discountAmount: (json['discount_amount'] as num?)?.toDouble() ?? 0.0,
      subtotal: (json['subtotal'] as num?)?.toDouble() ?? 0.0,
      notes: json['notes']?.toString(),
    );
  }
}

class PosOrderPaymentModel {
  final String id;
  final String paymentMethod;
  final double amount;
  final double? tenderedAmount;
  final double? changeAmount;
  final String? referenceNumber;

  PosOrderPaymentModel({
    required this.id,
    required this.paymentMethod,
    required this.amount,
    this.tenderedAmount,
    this.changeAmount,
    this.referenceNumber,
  });

  factory PosOrderPaymentModel.fromJson(Map<String, dynamic> json) {
    return PosOrderPaymentModel(
      id: json['id']?.toString() ?? '',
      paymentMethod: json['payment_method']?.toString() ?? 'cash',
      amount: (json['amount'] as num?)?.toDouble() ?? 0.0,
      tenderedAmount: (json['tendered_amount'] ?? json['amount_tendered'] as num?)?.toDouble(),
      changeAmount: (json['change_amount'] as num?)?.toDouble(),
      referenceNumber: json['reference_number']?.toString(),
    );
  }
}

class PosOrderModel {
  final String id;
  final String orderNumber;
  final String status;
  final DateTime createdAt;
  final double subtotal;
  final double discountAmount;
  final double taxAmount;
  final double serviceChargeAmount;
  final double grandTotal;
  final String? customerName;
  final String? customerId;
  final String? notes;
  final List<PosOrderItemModel> items;
  final List<PosOrderPaymentModel> payments;

  PosOrderModel({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.createdAt,
    required this.subtotal,
    this.discountAmount = 0.0,
    this.taxAmount = 0.0,
    this.serviceChargeAmount = 0.0,
    required this.grandTotal,
    this.customerName,
    this.customerId,
    this.notes,
    this.items = const [],
    this.payments = const [],
  });

  factory PosOrderModel.fromJson(Map<String, dynamic> json) {
    return PosOrderModel(
      id: json['id']?.toString() ?? '',
      orderNumber: json['order_number']?.toString() ?? '',
      status: json['status']?.toString() ?? 'completed',
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now() : DateTime.now(),
      subtotal: (json['subtotal'] ?? json['total_amount'] as num?)?.toDouble() ?? 0.0,
      discountAmount: (json['discount_amount'] as num?)?.toDouble() ?? 0.0,
      taxAmount: (json['tax_amount'] as num?)?.toDouble() ?? 0.0,
      serviceChargeAmount: (json['service_charge_amount'] as num?)?.toDouble() ?? 0.0,
      grandTotal: (json['grand_total'] ?? json['total_amount'] as num?)?.toDouble() ?? 0.0,
      customerName: json['customer'] is Map ? json['customer']['name'] : json['customer_name']?.toString(),
      customerId: json['customer_id']?.toString(),
      notes: json['notes']?.toString(),
      items: json['items'] is List
          ? (json['items'] as List).map((i) => PosOrderItemModel.fromJson(i)).toList()
          : [],
      payments: json['payments'] is List
          ? (json['payments'] as List).map((p) => PosOrderPaymentModel.fromJson(p)).toList()
          : [],
    );
  }
}

