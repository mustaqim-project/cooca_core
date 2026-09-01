class ApiEndpoints {
  // Base URL (Production Hostinger default, configurable in App Settings)
  static String defaultBaseUrl = 'https://app.cooca.id/api/v1';

  // ── Auth & Profile ───────────────────────────────────────────
  static const String login           = '/auth/login';
  static const String register        = '/auth/register';
  static const String me              = '/auth/me';
  static const String logout          = '/auth/logout';
  static const String forgotPassword  = '/auth/forgot-password';
  static const String businesses      = '/me/businesses';
  static const String updateProfile   = '/profile';
  static const String contextCurrent  = '/context/current';

  // ── Team & Members Management ────────────────────────────────
  static const String members         = '/members';
  static String memberDetail(String id) => '/members/$id';

  // ── Business ─────────────────────────────────────────────────
  static const String createBusiness  = '/businesses';
  static const String businessTypes   = '/business-type-templates';

  // ── POS ──────────────────────────────────────────────────────
  static const String posBootstrap       = '/pos/terminal/bootstrap';
  static const String posSearchProducts  = '/pos/terminal/search-products';
  static const String posCheckout        = '/pos/terminal/checkout';
  static const String posOrders          = '/pos/orders';
  static String posHoldOrder(String id)    => '/pos/orders/$id/hold';
  static String posResumeOrder(String id)  => '/pos/orders/$id/resume';
  static String posVoidOrder(String id)    => '/pos/orders/$id/void';
  static String posRefundOrder(String id)  => '/pos/orders/$id/refund';
  static String posOrderDetail(String id)  => '/pos/orders/$id';

  // ── POS Shifts ───────────────────────────────────────────────
  static const String posActiveShift      = '/pos/shifts/active';
  static const String posOpenShift        = '/pos/shifts/open';
  static String posCloseShift(String id)      => '/pos/shifts/$id/close';
  static String posShiftSummary(String id)    => '/pos/shifts/$id/summary';
  static String posShiftCashMovement(String id) => '/pos/shifts/$id/cash-movement';

  // ── POS Reports ──────────────────────────────────────────────
  static const String posSalesSummary     = '/pos/reports/sales-summary';
  static const String posPaymentBreakdown = '/pos/reports/payment-breakdown';
  static const String posTopProducts      = '/pos/reports/top-products';
  static const String posDailyReport      = '/pos/reports/sales-summary';
  static const String posHourlyHeatmap    = '/pos/reports/hourly-heatmap';

  // ── Purchasing & Goods Receipts ──────────────────────────────
  static const String purchaseOrders      = '/purchasing/purchase-orders';
  static String purchaseOrderDetail(String id) => '/purchasing/purchase-orders/$id';
  static const String goodsReceipts       = '/purchasing/goods-receipts';
  static const String instantStockIn      = '/purchasing/stock-in';

  // ── Sales Pipeline ───────────────────────────────────────────
  static const String quotations          = '/sales/quotations';
  static String quotationDetail(String id) => '/sales/quotations/$id';
  static const String salesOrders         = '/sales/orders';
  static String salesOrderDetail(String id) => '/sales/orders/$id';
  static const String invoices            = '/sales/invoices';
  static String invoiceDetail(String id)  => '/sales/invoices/$id';

  // ── Inventory ────────────────────────────────────────────────
  static const String inventoryStocks       = '/inventory/stocks';
  static const String stocks                = '/inventory/stocks';
  static const String stockMovements        = '/inventory/movements';
  static const String inventoryAdjustments  = '/inventory/adjustments';
  static const String stockAdjust           = '/inventory/adjustments';
  static const String inventoryOpnames      = '/inventory/opnames';
  static const String inventoryTransfers    = '/inventory/transfers';
  static String inventoryProductMovements(String productId) => '/inventory/stocks/$productId/movements';

  // ── CRM ──────────────────────────────────────────────────────
  static const String crmCustomers        = '/crm/customers';
  static const String customers           = '/crm/customers';
  static const String crmVouchers         = '/crm/vouchers';
  static const String crmLoyaltyProgram   = '/crm/loyalty/program';
  static String crmCustomerDetail(String id)  => '/crm/customers/$id';
  static String crmCustomerLoyalty(String id) => '/crm/loyalty/customers/$id';

  // ── Subscription & Billing ───────────────────────────────────
  static const String subscriptionLimits  = '/billing/limits';
  static const String subscriptionUpgrade = '/billing/upgrade';

  // ── Finance & Accounting ─────────────────────────────────────
  static const String financeDashboard    = '/finance/dashboard';
  static const String financeExpenses     = '/finance/expenses';
  static const String financeCoa          = '/finance/chart-of-accounts';
  static const String financeJournals     = '/finance/journal-entries';

  // ── AI Assistant ─────────────────────────────────────────────
  static const String aiChat              = '/ai/chat';
  static const String aiExecuteAction     = '/ai/execute-action';
  static const String aiForecasting       = '/ai/forecasting';
  static const String aiStockPrediction   = '/ai/stock-prediction';
  static const String aiFraudDetection    = '/ai/fraud-detection';

  // ── Dashboard ────────────────────────────────────────────────
  static const String dashboard           = '/dashboard';

  // ── Master Data Products & Materials ─────────────────────────
  static const String products            = '/products';
  static const String productCategories   = '/product-categories';
  static const String materials           = '/materials';
  static const String suppliers           = '/suppliers';
  static const String units               = '/units';
}



