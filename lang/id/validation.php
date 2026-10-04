<?php

declare(strict_types=1);

return [
    'accepted' => ':attribute harus diterima.',
    'active_url' => ':attribute bukan URL yang valid.',
    'after' => ':attribute harus berupa tanggal setelah :date.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'array' => ':attribute harus berupa array.',
    'before' => ':attribute harus berupa tanggal sebelum :date.',
    'between' => [
        'numeric' => ':attribute harus antara :min dan :max.',
        'file' => ':attribute harus antara :min dan :max kilobita.',
        'string' => ':attribute harus antara :min dan :max karakter.',
        'array' => ':attribute harus memiliki antara :min dan :max item.',
    ],
    'boolean' => 'Kolom :attribute harus bernilai benar atau salah.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':attribute bukan tanggal yang valid.',
    'email' => ':attribute harus berupa alamat surel yang valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'in' => ':attribute yang dipilih tidak valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'max' => [
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'file' => ':attribute tidak boleh lebih dari :max kilobita.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
        'array' => ':attribute tidak boleh memiliki lebih dari :max item.',
    ],
    'min' => [
        'numeric' => ':attribute minimal bernilai :min.',
        'file' => ':attribute minimal bernilai :min kilobita.',
        'string' => ':attribute minimal berisi :min karakter.',
        'array' => ':attribute minimal memiliki :min item.',
    ],
    'numeric' => ':attribute harus berupa angka.',
    'required' => 'Kolom :attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah terdaftar di sistem.',
    'uuid' => ':attribute harus berupa UUID yang valid.',

    'custom' => [
        'payment_method' => [
            'required' => 'Silakan pilih metode pembayaran yang akan digunakan.',
            'in' => 'Metode pembayaran yang dipilih tidak didukung.',
        ],
        'cycle' => [
            'in' => 'Siklus tagihan harus berupa bulanan atau tahunan.',
        ],
        'tier' => [
            'in' => 'Tier paket langganan yang dipilih tidak valid.',
        ],
    ],

    'attributes' => [
        'payment_method' => 'Metode Pembayaran',
        'cycle' => 'Siklus Tagihan',
        'tier' => 'Tier Paket',
        'order_type' => 'Tipe Pesanan',
        'package_id' => 'Paket Langganan',
    ],
];
