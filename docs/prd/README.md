# COOCA Enterprise Upgrade: Daftar Dokumen PRD (Product Requirements Documents)

Repositori ini memuat 11 dokumen PRD resmi yang merinci spesifikasi audit sistem existing, kesenjangan (*gap analysis*), rancangan arsitektur baru, skema database, antarmuka Bento Apple HIG, dan alur kerja (*workflow*) end-to-end untuk evolusi COOCA dari platform UMKM menjadi ekosistem ERP & Omnichannel bertaraf korporasi.

---

## Indeks Dokumen PRD

| No | Kode PRD | Judul Fitur / Modul | File Dokumen | Status |
| :---: | :--- | :--- | :--- | :---: |
| 1 | `PRD-01` | **Pilihan Segmen UMKM vs. Korporasi saat Register** | [`PRD-01-SEGMEN-UMKM-KORPORASI-REGISTER.md`](PRD-01-SEGMEN-UMKM-KORPORASI-REGISTER.md) | `READY` |
| 2 | `PRD-02` | **Restrukturisasi Sidebar Bento Apple HIG & Topbar Ctrl + K** | [`PRD-02-BENTO-SIDEBAR-TOPBAR-CTRL-K.md`](PRD-02-BENTO-SIDEBAR-TOPBAR-CTRL-K.md) | `READY` |
| 3 | `PRD-03` | **Keuangan Korporasi (Multi-Ledger) Tanpa Kehilangan Data** | [`PRD-03-KEUANGAN-MULTI-LEDGER-SEAMLESS.md`](PRD-03-KEUANGAN-MULTI-LEDGER-SEAMLESS.md) | `READY` |
| 4 | `PRD-04` | **Tata Kelola Maker – Multi Approval – Release (MAR)** | [`PRD-04-WORKFLOW-MAKER-APPROVAL-RELEASE.md`](PRD-04-WORKFLOW-MAKER-APPROVAL-RELEASE.md) | `READY` |
| 5 | `PRD-05` | **Audit Log Explorer & Anti-Fraud Real-Time WhatsApp Alert** | [`PRD-05-AUDIT-LOG-ANTI-FRAUD-WHATSAPP.md`](PRD-05-AUDIT-LOG-ANTI-FRAUD-WHATSAPP.md) | `READY` |
| 6 | `PRD-06` | **HRM Presensi Geofencing, Mode Bebas & Tiket Koreksi Absensi** | [`PRD-06-HRM-ABSENSI-GEOFENCING-KOREKSI.md`](PRD-06-HRM-ABSENSI-GEOFENCING-KOREKSI.md) | `READY` |
| 7 | `PRD-07` | **Toko Online Multi-Page dengan 20 Tema Industri (Bebas Pop-up & Auto-Hide)** | [`PRD-07-STOREFRONT-MULTI-PAGE-20-TEMA.md`](PRD-07-STOREFRONT-MULTI-PAGE-20-TEMA.md) | `READY` |
| 8 | `PRD-08` | **Multi-Cabang & Multi-Gudang untuk Online Fulfillment** | [`PRD-08-MULTI-CABANG-GUDANG-ONLINE-FULFILLMENT.md`](PRD-08-MULTI-CABANG-GUDANG-ONLINE-FULFILLMENT.md) | `READY` |
| 9 | `PRD-09` | **Remediasi Sistem Navigasi Persisten, Hierarki Breadcrumb & Tab Sekunder** | [`PRD-09-PERSISTENT-TABS-BREADCRUMB-NAVIGATION-REMEDIATION.md`](PRD-09-PERSISTENT-TABS-BREADCRUMB-NAVIGATION-REMEDIATION.md) | `READY` |
| 10 | `PRD-10` | **Hardening Keamanan, Proteksi Fraud Stok & UI Sadar Konteks 20 Industri** | [`PRD-10-WAREHOUSE-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md`](PRD-10-WAREHOUSE-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md) | `COMPLETE` |
| 11 | `PRD-11` | **Hardening Keamanan Cyber, Proteksi Fraud Struk POS, & UI Sadar Konteks 20 Industri** | [`PRD-11-WHATSAPP-GATEWAY-AND-BROADCAST-MULTI-INDUSTRY.md`](PRD-11-WHATSAPP-GATEWAY-AND-BROADCAST-MULTI-INDUSTRY.md) | `COMPLETE` |
| 12 | `PRD-12` | **Social Media Hardening, Anti-Fraud & Multi-Industry Automation** | [`PRD-12-SOCIAL-MEDIA-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md`](PRD-12-SOCIAL-MEDIA-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md) | `COMPLETE` |
| 13 | `PRD-13` | **Marketplace Hardening, Anti-Fraud & Omnichannel Sync Multi-Industry** | [`PRD-13-MARKETPLACE-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md`](PRD-13-MARKETPLACE-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md) | `COMPLETE` |
| 14 | `PRD-14` | **Remediasi Menyeluruh Navigasi Sidebar, Sinkronisasi Flyout & Stabilitas Interaksi** | [`PRD-14-SIDEBAR-NAVIGATION-REMEDIATION-UX-STABILITY.md`](PRD-14-SIDEBAR-NAVIGATION-REMEDIATION-UX-STABILITY.md) | `COMPLETE` |
| 15 | `PRD-15` | **Remediasi Otorisasi Granular RBAC, Eliminasi 403 & Isolasi Status Aktif Deterministik** | [`PRD-15-SIDEBAR-GRANULAR-RBAC-AUTHORIZATION-AND-ACTIVE-STATE-ISOLATION.md`](PRD-15-SIDEBAR-GRANULAR-RBAC-AUTHORIZATION-AND-ACTIVE-STATE-ISOLATION.md) | `COMPLETE` |
| 16 | `PRD-16` | **Arsitektur Finansial Terpadu, Multi-Payment POS, EDC, Cooca Pay Payout Hub & Rekonsiliasi Multi-Cabang** | [`PRD-16-UNIFIED-FINANCIAL-ENGINE-MULTI-PAYMENT-SETTLEMENT-AND-PAYOUT-HUB.md`](PRD-16-UNIFIED-FINANCIAL-ENGINE-MULTI-PAYMENT-SETTLEMENT-AND-PAYOUT-HUB.md) | `READY` |
| 17 | `PRD-17` | **POS Terminal Hardening, Anti-Fraud & Multi-Industry Context** | [`PRD-17-POS-TERMINAL-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md`](PRD-17-POS-TERMINAL-HARDENING-ANTI-FRAUD-MULTI-INDUSTRY.md) | `COMPLETE` |
| 18 | `PRD-18` | **Comprehensive System Hardening & Multi-Industry Remediation (7 View Modules)** | [`PRD-18-COMPREHENSIVE-SYSTEM-HARDENING-7-MODULES-AND-MULTI-INDUSTRY-REMEDIATION.md`](PRD-18-COMPREHENSIVE-SYSTEM-HARDENING-7-MODULES-AND-MULTI-INDUSTRY-REMEDIATION.md) | `PROPOSED` |
| 19 | `PRD-19` | **Comprehensive Social Media Caption & Hashtag Limits Multi-Industry** | [`PRD-19-COMPREHENSIVE-SOCIAL-MEDIA-CAPTION-HASHTAG-LIMITS-AND-MULTI-INDUSTRY.md`](PRD-19-COMPREHENSIVE-SOCIAL-MEDIA-CAPTION-HASHTAG-LIMITS-AND-MULTI-INDUSTRY.md) | `COMPLETE` |
| 20 | `PRD-20` | **Comprehensive Storefront Security, Multi-Industry & i18n/l10n Hardening** | [`PRD-20-COMPREHENSIVE-STOREFRONT-SECURITY-MULTI-INDUSTRY-I18N.md`](PRD-20-COMPREHENSIVE-STOREFRONT-SECURITY-MULTI-INDUSTRY-I18N.md) | `READY` |
| 21 | `PRD-21` | **Storefront Themes: F&B & Culinary (6 Sektor: Cafe, Resto, Cloud Kitchen, Bakery, Catering, Diet)** | [`PRD-21-STOREFRONT-THEMES-FNB-CULINARY.md`](PRD-21-STOREFRONT-THEMES-FNB-CULINARY.md) | `READY` |
| 22 | `PRD-22` | **Storefront Themes: Retail, Trading & Farmasi (4 Sektor: Reseller, FMCG, Apotek, Craft)** | [`PRD-22-STOREFRONT-THEMES-RETAIL-COMMERCE.md`](PRD-22-STOREFRONT-THEMES-RETAIL-COMMERCE.md) | `READY` |
| 23 | `PRD-23` | **Storefront Themes: Jasa, Otomotif, Event & Kreatif (6 Sektor: Bengkel, Cuci Mobil, Salon, Laundry, WO, Agency)** | [`PRD-23-STOREFRONT-THEMES-SERVICES-HEALTH-BEAUTY.md`](PRD-23-STOREFRONT-THEMES-SERVICES-HEALTH-BEAUTY.md) | `READY` |
| 24 | `PRD-24` | **Storefront Themes: Manufaktur, Konstruksi & Agribisnis (9 Sektor: Garment, Tailor, Mebel, Print, Skincare, Plastik, Frozen, Kontraktor, Agri)** | [`PRD-24-STOREFRONT-THEMES-MANUFACTURING-CRAFT-AGRI.md`](PRD-24-STOREFRONT-THEMES-MANUFACTURING-CRAFT-AGRI.md) | `READY` |

---

## Dokumen Induk & Panduan Terkait
* [**Analisis Komprehensif Kebutuhan, Desain & Fitur 25 Tema Storefront**](file:///c:/laragon/www/cooca_core/docs/ANALISA_DESAIN_DAN_FITUR_20_SEKTOR_STOREFRONT.md)
* [**Rencana Implementasi Teknis Super Lengkap (22 Fase)**](file:///c:/laragon/www/cooca_core/docs/IMPLEMENTATION_PLAN_25_INDUSTRY_STOREFRONT_THEMES_AND_SECURITY.md)
* [**Master Blueprint Upgrade UMKM & Korporasi**](file:///C:/Users/Ulfah%20Ramadhani/.gemini/antigravity-ide/brain/79ca078a-db4d-42de-b56a-8e02ce60694d/blueprint_upgrade_umkm_korporasi.md)
* [**Direktif Operasional AI Agent COOCA**](file:///c:/laragon/www/cooca_core/.agents/skills/cooca-agent-directive/SKILL.md)
* [**Panduan Sistem COOCA (SYSTEM_GUIDE)**](file:///c:/laragon/www/cooca_core/docs/SYSTEM_GUIDE.md)
