import 'dart:async';
import 'dart:convert';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/foundation.dart';
import '../constants/api_endpoints.dart';
import '../network/api_client.dart';
import '../storage/sqlite_db.dart';

class SyncManager {
  static final SyncManager _instance = SyncManager._internal();
  factory SyncManager() => _instance;
  SyncManager._internal();

  bool _isSyncing = false;
  StreamSubscription<List<ConnectivityResult>>? _connectivitySubscription;

  void initialize() {
    _connectivitySubscription = Connectivity().onConnectivityChanged.listen((results) {
      if (results.any((r) => r != ConnectivityResult.none)) {
        syncPendingOrders();
      }
    });
  }

  void dispose() {
    _connectivitySubscription?.cancel();
  }

  Future<void> syncPendingOrders() async {
    if (_isSyncing) return;
    _isSyncing = true;

    try {
      final pendingOrders = await SqliteDb.getPendingOrders();
      if (pendingOrders.isEmpty) {
        _isSyncing = false;
        return;
      }

      if (kDebugMode) print('SyncManager: Mengunggah ${pendingOrders.length} pesanan offline...');

      for (final order in pendingOrders) {
        final id = order['id'].toString();
        final payload = jsonDecode(order['payload'] as String);

        try {
          await SqliteDb.updateOrderStatus(id, 'syncing');
          await ApiClient.post(ApiEndpoints.posCheckout, body: payload);
          await SqliteDb.updateOrderStatus(id, 'synced');
          if (kDebugMode) print('SyncManager: Berhasil sinkronisasi pesanan #$id');
        } catch (e) {
          await SqliteDb.updateOrderStatus(id, 'failed', error: e.toString());
          if (kDebugMode) print('SyncManager: Gagal sinkronisasi #$id: $e');
        }
      }
    } catch (e) {
      if (kDebugMode) print('SyncManager error: $e');
    } finally {
      _isSyncing = false;
    }
  }
}

