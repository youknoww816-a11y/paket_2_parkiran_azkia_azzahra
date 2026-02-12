<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$active_page = 'proteksi_role_parkir';

/* ================= Cek log-in ================ */
if (!isset($_SESSION['role'])) {
    header("Location: login_parkir.php");
    exit();
}

$halaman = basename($_SERVER['PHP_SELF']);

// Akses untuk user 'admin'
if ($_SESSION['role'] === 'admin') {
    $allowed = [
        'dashboard_parkiran.php',
        'tambah_user_parkir.php',
        'daftar_kendaraan.php',
        'area_parkir.php',
        'log_aktivitas_parkiran.php'
    ];
}

// Akses untuk user 'petugas'
elseif ($_SESSION['role'] === 'petugas') {
    $allowed = [
        'dashboard_parkiran.php',
        'transaksi_parkir.php'
    ];
}

// Akses untuk user 'owner' 
elseif ($_SESSION['role'] === 'owner') {
    $allowed = [
        'rekap_transaksi_parkir.php'
    ];
}

// Unauthorized Redirect
else {
    header("Location: login_parkir.php");
    exit();
}

if (!in_array($halaman, $allowed)) {
    header("Location: login_parkir.php");
    exit();
}