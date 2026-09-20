import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import 'dart:convert';

class SqliteDb {
  static Database? _database;

  static Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDb();
    return _database!;
  }

  static Future<Database> _initDb() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'cooca_offline_pos.db');

    return await openDatabase(
      path,
      version: 1,
      onCreate: (db, version) async {
        // Cached products
        await db.execute('''
          CREATE TABLE cached_products (
            id TEXT PRIMARY KEY,
            name TEXT,
            sku TEXT,
            selling_price REAL,
            category TEXT,
            stock REAL,
            json_data TEXT
          )
        ''');

        // Offline transactions queue
        await db.execute('''
          CREATE TABLE offline_orders_queue (
            id TEXT PRIMARY KEY,
            business_id TEXT,
            invoice_no TEXT,
            total_amount REAL,
            payment_method TEXT,
            payload TEXT,
            created_at TEXT,
            sync_status TEXT, -- 'pending', 'syncing', 'synced', 'failed'
            error_message TEXT
          )
        ''');

        // Offline stock movements queue
        await db.execute('''
          CREATE TABLE offline_stock_adjustments (
            id TEXT PRIMARY KEY,
            product_id TEXT,
            quantity REAL,
            type TEXT,
            reason TEXT,
            created_at TEXT,
            sync_status TEXT
          )
        ''');
      },
    );
  }

  // --- Offline Orders Operations ---
  static Future<void> queueOfflineOrder({
    required String orderId,
    required String businessId,
    required String invoiceNo,
    required double totalAmount,
    required String paymentMethod,
    required Map<String, dynamic> payload,
  }) async {
    final db = await database;
    await db.insert(
      'offline_orders_queue',
      {
        'id': orderId,
        'business_id': businessId,
        'invoice_no': invoiceNo,
        'total_amount': totalAmount,
        'payment_method': paymentMethod,
        'payload': jsonEncode(payload),
        'created_at': DateTime.now().toIso8601String(),
        'sync_status': 'pending',
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  static Future<List<Map<String, dynamic>>> getPendingOrders() async {
    final db = await database;
    return await db.query(
      'offline_orders_queue',
      where: 'sync_status = ? OR sync_status = ?',
      whereArgs: ['pending', 'failed'],
      orderBy: 'created_at ASC',
    );
  }

  static Future<void> updateOrderStatus(String id, String status, {String? error}) async {
    final db = await database;
    await db.update(
      'offline_orders_queue',
      {
        'sync_status': status,
        if (error != null) 'error_message': error,
      },
      where: 'id = ?',
      whereArgs: [id],
    );
  }

  static Future<int> getPendingCount() async {
    final db = await database;
    final res = await db.rawQuery("SELECT COUNT(*) as count FROM offline_orders_queue WHERE sync_status != 'synced'");
    return Sqflite.firstIntValue(res) ?? 0;
  }
}

