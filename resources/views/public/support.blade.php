@extends('public.partials.subpage_layout', [
    'title' => 'Pusat Bantuan & Support',
    'category' => 'Support',
    'badge' => 'Bantuan 24/7',
    'icon' => 'headphones',
    'headline' => 'Kami Siap Mendampingi Kelancaran Operasional Bisnis Anda',
    'subtitle' => 'Punya pertanyaan teknis, kendala saat transaksi kasir, atau ingin panduan setup cabang baru? Tim support COOCA siap membantu Anda kapan saja.',
    'features' => [
        ['icon' => 'message-circle', 'title' => 'Live Chat WhatsApp Langsung', 'desc' => 'Terhubung langsung dengan tim technical support kami lewat WhatsApp tanpa antrean tiket robot.'],
        ['icon' => 'book-open', 'title' => 'Basis Pengetahuan (Knowledge Base)', 'desc' => 'Ratusan artikel panduan dan solusi cepat untuk pertanyaan seputar kasir, printer, dan akun.'],
        ['icon' => 'video', 'title' => 'Sesi Onboarding Video Call Privat', 'desc' => 'Jadwalkan pendampingan langsung via Zoom/Google Meet bersama tim spesialis kami untuk tim gerai Anda.'],
        ['icon' => 'wrench', 'title' => 'Bantuan Setup Perangkat Keras', 'desc' => 'Panduan teknis konfigurasi printer struk Bluetooth, laci kasir (cash drawer), dan scanner barcode.'],
        ['icon' => 'activity', 'title' => 'Pusat Status Server (System Status)', 'desc' => 'Pantau kesehatan server cloud, API WhatsApp, dan payment gateway secara transparan 24 jam.'],
        ['icon' => 'mail', 'title' => 'Email Support Resmi', 'desc' => 'Kirimkan pertanyaan kemitraan atau eskalasi khusus ke alamat email resmi tim kami.'],
    ]
])
