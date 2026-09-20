class BusinessModel {
  final String id;
  final String name;
  final String? slug;
  final String? logoUrl;
  final String? address;
  final String? phone;
  final String currency;
  final String currencySymbol;
  final bool posEnableTax;
  final double posTaxPercent;
  final bool posEnableServiceCharge;
  final double posServiceChargePercent;
  final double posMaxCashierDiscountPercent;
  final bool posRequirePinForVoid;
  final bool posRequirePinForRefund;
  final String? posReceiptFooterNote;

  BusinessModel({
    required this.id,
    required this.name,
    this.slug,
    this.logoUrl,
    this.address,
    this.phone,
    this.currency = 'IDR',
    this.currencySymbol = 'Rp',
    this.posEnableTax = false,
    this.posTaxPercent = 0.0,
    this.posEnableServiceCharge = false,
    this.posServiceChargePercent = 0.0,
    this.posMaxCashierDiscountPercent = 100.0,
    this.posRequirePinForVoid = false,
    this.posRequirePinForRefund = false,
    this.posReceiptFooterNote,
  });

  factory BusinessModel.fromJson(Map<String, dynamic> json) {
    return BusinessModel(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? 'Outlet Bisnis',
      slug: json['slug']?.toString(),
      logoUrl: json['logo_url']?.toString(),
      address: json['address']?.toString(),
      phone: json['phone']?.toString(),
      currency: json['currency']?.toString() ?? 'IDR',
      currencySymbol: json['currency_symbol']?.toString() ?? 'Rp',
      posEnableTax: json['pos_enable_tax'] == true || json['pos_enable_tax'] == 1,
      posTaxPercent: (json['pos_tax_percent'] as num?)?.toDouble() ?? 0.0,
      posEnableServiceCharge: json['pos_enable_service_charge'] == true || json['pos_enable_service_charge'] == 1,
      posServiceChargePercent: (json['pos_service_charge_percent'] as num?)?.toDouble() ?? 0.0,
      posMaxCashierDiscountPercent: (json['pos_max_cashier_discount_percent'] as num?)?.toDouble() ?? 100.0,
      posRequirePinForVoid: json['pos_require_pin_for_void'] == true || json['pos_require_pin_for_void'] == 1,
      posRequirePinForRefund: json['pos_require_pin_for_refund'] == true || json['pos_require_pin_for_refund'] == 1,
      posReceiptFooterNote: json['pos_receipt_footer_note']?.toString(),
    );
  }
}

