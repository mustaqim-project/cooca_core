class PosCashMovementModel {
  final String id;
  final String type; // cash_in, cash_out
  final double amount;
  final String? reason;
  final DateTime createdAt;

  PosCashMovementModel({
    required this.id,
    required this.type,
    required this.amount,
    this.reason,
    required this.createdAt,
  });

  factory PosCashMovementModel.fromJson(Map<String, dynamic> json) {
    return PosCashMovementModel(
      id: json['id']?.toString() ?? '',
      type: json['type']?.toString() ?? 'cash_in',
      amount: (json['amount'] as num?)?.toDouble() ?? 0.0,
      reason: json['reason']?.toString(),
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now() : DateTime.now(),
    );
  }
}

class PosShiftModel {
  final String id;
  final String status; // open, closed
  final double openingBalance;
  final double? closingBalance;
  final double? actualCash;
  final double? cashDifference;
  final double totalSalesCash;
  final double totalSalesNonCash;
  final double totalCashIn;
  final double totalCashOut;
  final int totalOrders;
  final DateTime openedAt;
  final DateTime? closedAt;
  final String? cashierName;

  PosShiftModel({
    required this.id,
    required this.status,
    required this.openingBalance,
    this.closingBalance,
    this.actualCash,
    this.cashDifference,
    this.totalSalesCash = 0.0,
    this.totalSalesNonCash = 0.0,
    this.totalCashIn = 0.0,
    this.totalCashOut = 0.0,
    this.totalOrders = 0,
    required this.openedAt,
    this.closedAt,
    this.cashierName,
  });

  double get expectedCash => openingBalance + totalSalesCash + totalCashIn - totalCashOut;
  bool get isOpen => status == 'open';

  factory PosShiftModel.fromJson(Map<String, dynamic> json) {
    return PosShiftModel(
      id: json['id']?.toString() ?? '',
      status: json['status']?.toString() ?? 'open',
      openingBalance: (json['opening_balance'] ?? json['opening_cash'] as num?)?.toDouble() ?? 0.0,
      closingBalance: (json['closing_balance'] as num?)?.toDouble(),
      actualCash: (json['actual_cash'] as num?)?.toDouble(),
      cashDifference: (json['cash_difference'] as num?)?.toDouble(),
      totalSalesCash: (json['total_cash_sales'] ?? json['total_sales_cash'] as num?)?.toDouble() ?? 0.0,
      totalSalesNonCash: (json['total_non_cash_sales'] ?? json['total_sales_non_cash'] as num?)?.toDouble() ?? 0.0,
      totalCashIn: (json['total_cash_in'] as num?)?.toDouble() ?? 0.0,
      totalCashOut: (json['total_cash_out'] as num?)?.toDouble() ?? 0.0,
      totalOrders: (json['orders_count'] ?? json['total_orders'] as num?)?.toInt() ?? 0,
      openedAt: json['opened_at'] != null ? DateTime.tryParse(json['opened_at'].toString()) ?? DateTime.now() : DateTime.now(),
      closedAt: json['closed_at'] != null ? DateTime.tryParse(json['closed_at'].toString()) : null,
      cashierName: json['user'] is Map ? json['user']['name'] : json['cashier_name']?.toString(),
    );
  }
}

