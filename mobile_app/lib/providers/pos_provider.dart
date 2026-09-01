import 'package:flutter/foundation.dart';
import 'package:uuid/uuid.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/business_model.dart';
import '../models/cart_item_model.dart';
import '../models/customer_model.dart';
import '../models/order_model.dart';
import '../models/product_model.dart';

class PosProvider with ChangeNotifier {
  final List<CartItemModel> _items = [];
  CustomerModel? _selectedCustomer;
  double _orderDiscountPercent = 0.0;
  double _orderDiscountFixed = 0.0;
  String? _orderNotes;
  bool _isCheckingOut = false;
  String? _errorMessage;

  List<CartItemModel> get items => _items;
  List<CartItemModel> get cartItems => _items;
  CustomerModel? get selectedCustomer => _selectedCustomer;
  double get orderDiscountPercent => _orderDiscountPercent;
  double get orderDiscountFixed => _orderDiscountFixed;
  String? get orderNotes => _orderNotes;
  bool get isCheckingOut => _isCheckingOut;
  String? get errorMessage => _errorMessage;

  int get totalItemCount => _items.fold(0, (sum, item) => sum + item.quantity);

  double get subtotal => _items.fold(0.0, (sum, item) => sum + item.subtotal);

  double calculateDiscountAmount(BusinessModel? business) {
    if (_orderDiscountFixed > 0) {
      return _orderDiscountFixed;
    }
    if (_orderDiscountPercent > 0) {
      final maxPercent = business?.posMaxCashierDiscountPercent ?? 100.0;
      final effectivePercent = _orderDiscountPercent > maxPercent ? maxPercent : _orderDiscountPercent;
      return (subtotal * effectivePercent) / 100.0;
    }
    return 0.0;
  }

  double calculateTaxAmount(BusinessModel? business) {
    if (business == null || !business.posEnableTax) return 0.0;
    final taxable = subtotal - calculateDiscountAmount(business);
    return (taxable * business.posTaxPercent) / 100.0;
  }

  double calculateServiceChargeAmount(BusinessModel? business) {
    if (business == null || !business.posEnableServiceCharge) return 0.0;
    final base = subtotal - calculateDiscountAmount(business);
    return (base * business.posServiceChargePercent) / 100.0;
  }

  double calculateGrandTotal(BusinessModel? business) {
    final sub = subtotal;
    final disc = calculateDiscountAmount(business);
    final tax = calculateTaxAmount(business);
    final svc = calculateServiceChargeAmount(business);
    final total = sub - disc + tax + svc;
    return total < 0 ? 0.0 : total;
  }

  void addItem(ProductModel product, {int qty = 1}) {
    final index = _items.indexWhere((i) => i.product.id == product.id && i.notes == null);
    if (index >= 0) {
      _items[index].quantity += qty;
    } else {
      _items.add(CartItemModel(
        id: const Uuid().v4(),
        product: product,
        quantity: qty,
      ));
    }
    notifyListeners();
  }

  void updateQuantity(String cartItemId, int newQty) {
    if (newQty <= 0) {
      _items.removeWhere((i) => i.id == cartItemId);
    } else {
      final index = _items.indexWhere((i) => i.id == cartItemId);
      if (index >= 0) {
        _items[index].quantity = newQty;
      }
    }
    notifyListeners();
  }

  void updateItemNotes(String cartItemId, String? notes) {
    final index = _items.indexWhere((i) => i.id == cartItemId);
    if (index >= 0) {
      _items[index].notes = notes;
      notifyListeners();
    }
  }

  void updateItemDiscount(String cartItemId, double discount) {
    final index = _items.indexWhere((i) => i.id == cartItemId);
    if (index >= 0) {
      _items[index].discountAmount = discount;
      notifyListeners();
    }
  }

  void removeItem(String cartItemId) {
    _items.removeWhere((i) => i.id == cartItemId);
    notifyListeners();
  }

  void clearCart() {
    _items.clear();
    _selectedCustomer = null;
    _orderDiscountPercent = 0.0;
    _orderDiscountFixed = 0.0;
    _orderNotes = null;
    notifyListeners();
  }

  void setCustomer(CustomerModel? customer) {
    _selectedCustomer = customer;
    notifyListeners();
  }

  void setOrderDiscountPercent(double percent) {
    _orderDiscountPercent = percent;
    _orderDiscountFixed = 0.0;
    notifyListeners();
  }

  void setOrderDiscountFixed(double amount) {
    _orderDiscountFixed = amount;
    _orderDiscountPercent = 0.0;
    notifyListeners();
  }

  void setOrderNotes(String? notes) {
    _orderNotes = notes;
    notifyListeners();
  }

  Future<PosOrderModel?> checkout({
    required BusinessModel? business,
    required List<Map<String, dynamic>> payments,
  }) async {
    if (_items.isEmpty) {
      _errorMessage = 'Keranjang belanja masih kosong.';
      notifyListeners();
      return null;
    }

    _isCheckingOut = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final body = {
        'items': _items.map((i) => i.toCheckoutJson()).toList(),
        'payments': payments,
        'customer_id': _selectedCustomer?.id,
        'discount_amount': calculateDiscountAmount(business),
        'tax_amount': calculateTaxAmount(business),
        'service_charge_amount': calculateServiceChargeAmount(business),
        'notes': _orderNotes,
      };

      final res = await ApiClient.post(ApiEndpoints.posCheckout, body: body);
      if (res is Map<String, dynamic> && res.containsKey('order')) {
        final order = PosOrderModel.fromJson(res['order']);
        clearCart();
        return order;
      }
      throw ApiException(statusCode: 500, message: 'Respon order tidak valid dari server.');
    } catch (e) {
      // Offline fallback: create local order model
      final offlineOrderNumber = 'OFFLINE-${DateTime.now().millisecondsSinceEpoch}';
      final offlineOrder = PosOrderModel(
        id: const Uuid().v4(),
        orderNumber: offlineOrderNumber,
        status: 'pending_sync',
        createdAt: DateTime.now(),
        subtotal: subtotal,
        discountAmount: calculateDiscountAmount(business),
        taxAmount: calculateTaxAmount(business),
        serviceChargeAmount: calculateServiceChargeAmount(business),
        grandTotal: calculateGrandTotal(business),
        notes: '[Offline] ${_orderNotes ?? ""}',
        items: _items.map((i) => PosOrderItemModel(
          id: const Uuid().v4(),
          productId: i.product.id,
          productName: i.product.name,
          quantity: i.quantity.toDouble(),
          unitPrice: i.unitPrice,
          subtotal: i.subtotal,
        )).toList(),
        payments: payments.map((p) => PosOrderPaymentModel(
          id: const Uuid().v4(),
          paymentMethod: p['payment_method']?.toString() ?? 'cash',
          amount: (p['amount'] as num?)?.toDouble() ?? 0.0,
          tenderedAmount: (p['tendered'] as num?)?.toDouble(),
        )).toList(),
      );

      clearCart();
      return offlineOrder;
    } finally {
      _isCheckingOut = false;
      notifyListeners();
    }
  }

  Future<bool> holdOrder({String? note}) async {
    if (_items.isEmpty) return false;
    _isCheckingOut = true;
    notifyListeners();

    try {
      final body = {
        'items': _items.map((i) => i.toCheckoutJson()).toList(),
        'customer_id': _selectedCustomer?.id,
        'notes': note ?? _orderNotes ?? 'Pesanan Disimpan (Parkir)',
      };

      await ApiClient.post(ApiEndpoints.posCheckout, body: {
        ...body,
        'status': 'held',
        'payments': [],
      });

      clearCart();
      return true;
    } catch (e) {
      _errorMessage = 'Gagal menyimpan pesanan: $e';
      return false;
    } finally {
      _isCheckingOut = false;
      notifyListeners();
    }
  }

  void loadHeldOrder(PosOrderModel order) {
    clearCart();
    for (final item in order.items) {
      final dummyProduct = ProductModel(
        id: item.productId ?? const Uuid().v4(),
        name: item.productName,
        sellingPrice: item.unitPrice,
        baseCost: 0,
      );
      _items.add(CartItemModel(
        id: const Uuid().v4(),
        product: dummyProduct,
        quantity: item.quantity.toInt(),
        customPrice: item.unitPrice,
        discountAmount: item.discountAmount,
        notes: item.notes,
      ));
    }
    _orderNotes = order.notes;
    notifyListeners();
  }
}

