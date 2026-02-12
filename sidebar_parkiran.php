<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi_parkir.php';
include 'proteksi_role_parkir.php';

/* ======================================
   ACTIVE PAGE HANDLING (FIX & STABIL)
   ====================================== */

// AMBIL NAMA FILE AKTIF TANPA .php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3>Sidebar</h3>
    </div>

    <ul class="sidebar-nav">

    <!-- Khusus admin -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            
            <li><a href="dashboard_parkiran.php"class="<?= ($current_page === 'dashboard_parkiran') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            </li>
            
            <li><a href="tambah_user_parkir.php"class="<?= ($current_page === 'tambah_user_parkir') ? 'active' : '' ?>">
                    <i class="fa-solid fa-user-plus"></i> Tambah User
                </a>
            </li>

            <li>
                <a href="daftar_kendaraan.php"class="<?= ($current_page === 'daftar_kendaraan') ? 'active' : '' ?>">
                    <i class="fa-solid fa-motorcycle"></i> Daftar Kendaraan
                </a>
            </li>

            <li>
                <a href="area_parkir.php"class="<?= ($current_page === 'area_parkir') ? 'active' : '' ?>">
                    <i class="fa-solid fa-car-tunnel"></i> Area Parkir
                </a>
            </li>

            <li>
                <a href="log_aktivitas_parkiran.php"class="<?= ($current_page === 'log_aktivitas_parkiran') ? 'active' : '' ?>">
                    <i class="fa-regular fa-calendar-days"></i> Log Parkiran
                </a>
            </li>

    <!-- Khusus petugas -->

        <?php elseif ($_SESSION['role'] === 'petugas'): ?>

            <li><a href="dashboard_parkiran.php"class="<?= ($current_page === 'dashboard_parkiran') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>Dashboard
            </a>
            </li>

            <li>
                <a href="transaksi_parkir.php"class="<?= ($current_page === 'transaksi_parkir') ? 'active' : '' ?>">
                    <i class="fa-solid fa-ticket"></i> Tiket Parkir
                </a>
            </li>

    <!-- Khusus owner -->

        <?php elseif ($_SESSION['role'] === 'owner'): ?>

            <li>
                <a href="rekap_transaksi_parkir.php"class="<?= ($current_page === 'rekap_transaksi_parkir') ? 'active' : '' ?>">
                    <i class="fa-solid fa-user"></i> Riwayat User
                </a>
            </li>

        <?php endif; ?>

        <!-- Semua User -->

        <li class="mt-3">
            <a href="logout_parkir.php" class="logout-menu"onclick="return confirm('Yakin ingin logout?')">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
</li>

    </ul>
</div>

<!-- Sidebar toggle selalu aktif selama tidak dimatikan -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');

    const sidebarState = localStorage.getItem('sidebarState');

    if (sidebarState === 'open') {
        sidebar.classList.add('active');
        toggleButton.classList.add('active');
        if (mainContent) mainContent.classList.add('shifted');
    }

    toggleButton.addEventListener('click', function () {
        const isActive = sidebar.classList.toggle('active');
        toggleButton.classList.toggle('active');
        if (mainContent) mainContent.classList.toggle('shifted');

        localStorage.setItem('sidebarState', isActive ? 'open' : 'closed');
    });
});
</script>