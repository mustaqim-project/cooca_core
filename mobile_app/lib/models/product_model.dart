class ProductModel {
  final String id;
  final String name;
  final String? sku;
  final String? barcode;
  final double sellingPrice;
  final double baseCost;
  final String? categoryId;
  final String? categoryName;
  final String? unitName;
  final String? imageUrl;
  final double stockQuantity;
  final bool trackInventory;

  ProductModel({
    required this.id,
    required this.name,
    this.sku,
    this.barcode,
    required this.sellingPrice,
    required this.baseCost,
    this.categoryId,
    this.categoryName,
    this.unitName,
    this.imageUrl,
    this.stockQuantity = 0.0,
    this.trackInventory = false,
  });

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    return ProductModel(
      id: json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      sku: json['sku']?.toString(),
      barcode: json['barcode']?.toString(),
      sellingPrice: (json['selling_price'] as num?)?.toDouble() ?? 0.0,
      baseCost: (json['base_cost'] as num?)?.toDouble() ?? 0.0,
      categoryId: json['category_id']?.toString(),
      categoryName: json['category'] is Map ? json['category']['name'] : json['category_name']?.toString(),
      unitName: json['output_unit'] is Map ? json['output_unit']['symbol'] ?? json['output_unit']['name'] : json['unit_name']?.toString() ?? 'pcs',
      imageUrl: json['image_url']?.toString(),
      stockQuantity: (json['stock_quantity'] ?? json['current_stock'] as num?)?.toDouble() ?? 0.0,
      trackInventory: json['track_inventory'] == true || json['track_inventory'] == 1,
    );
  }
}

