class CustomerModel {
  final String id;
  final String name;
  final String? code;
  final String? phone;
  final String? email;
  final int pointsBalance;
  final double currentCreditBalance;
  final double creditLimit;
  final String? membershipTier;

  CustomerModel({
    required this.id,
    required this.name,
    this.code,
    this.phone,
    this.email,
    this.pointsBalance = 0,
    this.currentCreditBalance = 0.0,
    this.creditLimit = 0.0,
    this.membershipTier,
  });

  factory CustomerModel.fromJson(Map<String, dynamic> json) {
    return CustomerModel(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      code: json['code']?.toString(),
      phone: json['phone']?.toString(),
      email: json['email']?.toString(),
      pointsBalance: (json['points_balance'] as num?)?.toInt() ?? 0,
      currentCreditBalance: (json['current_credit_balance'] as num?)?.toDouble() ?? 0.0,
      creditLimit: (json['credit_limit'] as num?)?.toDouble() ?? 0.0,
      membershipTier: json['membership_tier']?.toString(),
    );
  }
}

