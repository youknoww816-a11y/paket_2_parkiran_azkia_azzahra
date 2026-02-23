<?php
session_start();
include 'koneksi_parkir.php';

/* ============================
   CEK APAKAH USER SUDAH LOGIN
   (HARUS SAMA DENGAN login_parkir.php)
   ============================ */

if (!isset($_SESSION['id_user']) || !isset($_SESSION['role'])) {
    
    // ❌ Belum login → ke halaman login
    header("Location: login_parkir.php");
    exit();
}

/* ============================
   JIKA SUDAH LOGIN
   ARAHKAN BERDASARKAN ROLE
   ============================ */

$role = $_SESSION['role'];

switch ($role) {

    case 'admin':
        header("Location: dashboard_parkiran.php");
        break;

    case 'petugas':
        header("Location: dashboard_parkiran.php");
        break;

    case 'owner':
        header("Location: log_aktivitas_parkiran.php");
        break;

    default:
        // Role tidak dikenal → logout demi keamanan
        session_destroy();
        header("Location: login_parkir.php");
        break;
}

exit();
?>