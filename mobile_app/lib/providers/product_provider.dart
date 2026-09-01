import 'package:flutter/foundation.dart';
import '../core/constants/api_endpoints.dart';
import '../core/network/api_client.dart';
import '../models/category_model.dart';
import '../models/product_model.dart';

class ProductProvider with ChangeNotifier {
  List<ProductModel> _products = [];
  List<CategoryModel> _categories = [];
  String? _selectedCategoryId;
  String _searchQuery = '';
  bool _isLoading = false;
  String? _errorMessage;

  List<ProductModel> get products => _filteredProducts();
  List<CategoryModel> get categories => _categories;
  String? get selectedCategoryId => _selectedCategoryId;
  String get searchQuery => _searchQuery;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  List<ProductModel> _filteredProducts() {
    return _products.where((p) {
      final matchesCategory = _selectedCategoryId == null || p.categoryId == _selectedCategoryId;
      final matchesSearch = _searchQuery.isEmpty ||
          p.name.toLowerCase().contains(_searchQuery.toLowerCase()) ||
          (p.sku != null && p.sku!.toLowerCase().contains(_searchQuery.toLowerCase())) ||
          (p.barcode != null && p.barcode!.contains(_searchQuery));
      return matchesCategory && matchesSearch;
    }).toList();
  }

  Future<void> fetchProducts() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await ApiClient.get(ApiEndpoints.posSearchProducts);
      if (res is Map<String, dynamic> && res.containsKey('products')) {
        final list = res['products'] as List;
        _products = list.map((p) => ProductModel.fromJson(p)).toList();

        // Extract distinct categories from products if available
        final catMap = <String, CategoryModel>{};
        for (final p in _products) {
          if (p.categoryId != null && p.categoryName != null) {
            catMap[p.categoryId!] = CategoryModel(id: p.categoryId!, name: p.categoryName!);
          }
        }
        _categories = catMap.values.toList();
      }
    } on ApiException catch (e) {
      _errorMessage = e.message;
    } catch (e) {
      _errorMessage = 'Gagal memuat katalog produk: $e';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void selectCategory(String? categoryId) {
    _selectedCategoryId = categoryId;
    notifyListeners();
  }

  void search(String query) {
    _searchQuery = query;
    notifyListeners();
  }

  ProductModel? findByBarcode(String barcode) {
    try {
      return _products.firstWhere(
        (p) => p.barcode == barcode || p.sku == barcode,
      );
    } catch (_) {
      return null;
    }
  }
}

