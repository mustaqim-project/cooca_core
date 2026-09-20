class UserModel {
  final String id;
  final String name;
  final String email;
  final String? phone;
  final String? avatarUrl;
  final String role;
  final bool isOwner;
  final List<String> permissions;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.avatarUrl,
    this.role = 'owner',
    this.isOwner = true,
    this.permissions = const [],
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    final permsList = json['permissions'];
    List<String> parsedPermissions = [];
    if (permsList is List) {
      parsedPermissions = permsList.map((e) => e.toString()).toList();
    }

    final roleStr = json['role']?.toString() ?? 'owner';
    final isOwnerVal = json['is_owner'] == true || roleStr == 'owner';

    return UserModel(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString(),
      avatarUrl: json['avatar_url']?.toString(),
      role: roleStr,
      isOwner: isOwnerVal,
      permissions: parsedPermissions,
    );
  }

  bool hasPermission(String permission) {
    if (isOwner || role == 'owner') return true;
    return permissions.contains(permission);
  }

  bool get canViewMargin => hasPermission('costing.view_margin');
  bool get canManageSettings => hasPermission('business.settings') || isOwner;
  bool get canManageTeam => hasPermission('users.manage') || isOwner;
  bool get canAccessBilling => hasPermission('billing.manage') || isOwner;
  bool get canAccessFinance => hasPermission('expenses.manage') || hasPermission('accounting.view') || hasPermission('reports.financial') || isOwner;
  bool get canAccessInventory => hasPermission('inventory.manage') || hasPermission('receiving.manage') || isOwner;
  bool get canAccessPurchasing => hasPermission('purchasing.manage') || hasPermission('receiving.manage') || isOwner;
  bool get canAccessSales => hasPermission('sales.pipeline') || hasPermission('invoices.manage') || isOwner;
  bool get canAccessPos => hasPermission('pos.terminal') || isOwner;
  bool get canAccessAi => hasPermission('ai.access') || isOwner;

  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'email': email,
    'phone': phone,
    'avatar_url': avatarUrl,
    'role': role,
    'is_owner': isOwner,
    'permissions': permissions,
  };
}


