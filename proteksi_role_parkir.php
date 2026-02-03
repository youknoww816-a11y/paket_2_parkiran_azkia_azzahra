<?php

// Masih Placeholder, aku agak cape

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi_parkir.php';
$active_page = 'proteksi_role_parkir';

if (!isset($_SESSION['role'])) {
    header("Location: login_parkir.php");
    exit();
}

// Proteksi akses role
$halaman = basename($_SERVER['PHP_SELF']);

if ($_SESSION['role'] === 'admin') {
    // Admin boleh akses semua halaman (lebih fleksibel)
    // -> kalau mau batasi ke Master Data saja, aktifkan array whitelist
    
    $allowed_admin_pages = [
        'dashboard_bumdes.php',
        'produk_bumdes.php',
        'jasa_bumdes.php',
        'suplier_bumdes.php',
        'customer_bumdes.php',
        'karyawan_bumdes.php',
        'user_bumdes.php',
        'keuangan_bumdes.php',
        'omset_bumdes.php'
    ];
    if (!in_array($halaman, $allowed_admin_pages)) {
        header("Location: dashboard_bumdes.php");
        exit();
    }
    
} elseif ($_SESSION['pengguna'] === 'penjual') {
    // Penjual boleh akses halaman tertentu
    $allowed_penjual_pages = [
        'dashboard_bumdes.php',
        'manejemen_penjualan_bumdes.php',
        'order_layanan_bumdes.php',
        'pengambilan_order_bumdes.php',
        'pelunasan_utang_bumdes.php',
        'omset_bumdes.php',
        'keuangan_bumdes.php',
        'kas_harian_bumdes.php'
    ];

    if (!in_array($halaman, $allowed_penjual_pages)) {
        header("Location: manejemen_penjualan_bumdes.php");
        exit();
    }
} else {
    // User biasa diarahkan ke inventori
    header("Location: inventori.php");
    exit();
}
