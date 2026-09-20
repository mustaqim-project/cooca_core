import 'product_model.dart';

class CartItemModel {
  final String id;
  final ProductModel product;
  int quantity;
  double customPrice;
  double discountAmount;
  String? notes;

  CartItemModel({
    required this.id,
    required this.product,
    this.quantity = 1,
    double? customPrice,
    this.discountAmount = 0.0,
    this.notes,
  }) : customPrice = customPrice ?? product.sellingPrice;

  double get unitPrice => customPrice;
  double get subtotal => (unitPrice * quantity) - discountAmount;
  double get totalHpp => product.baseCost * quantity;
  double get grossProfit => subtotal - totalHpp;

  Map<String, dynamic> toCheckoutJson() => {
    'product_id': product.id,
    'product_name': product.name,
    'quantity': quantity,
    'unit_price': unitPrice,
    'discount_amount': discountAmount,
    'notes': notes,
  };
}

