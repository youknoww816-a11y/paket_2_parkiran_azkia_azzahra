<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($active_page)) {
    $active_page = '';
}

/* Placeholder untuk Tema 
$query = $conn->query("SELECT tema, tampilan_menu FROM tb_tampilan LIMIT 1");
if ($row = $query->fetch_assoc()) {
    $tema_global = $row['tema'];
    $menu_mode_global = $row['tampilan_menu'];
} else {
    $tema_global = 'normal';
    $menu_mode_global = 'sidebar';
}

if (isset($_COOKIE['theme'])) {
    $tema_global = $_COOKIE['theme'];
}
if (isset($_COOKIE['menu_mode'])) {
    $menu_mode_global = $_COOKIE['menu_mode'];
}
*/

if (!function_exists('display_message')) {
    function display_message($message, $type) {
        if (!empty($message)) {
            $class = '';
            switch ($type) {
                case 'success': $class = 'success'; break;
                case 'error':   $class = 'error'; break;
                case 'info':
                default:        $class = 'info'; break;
            }
            echo "<div class='message {$class}'>{$message}</div>";
        }
    }
}
?>

<?php
// Tambahkan ini sebelum <html>
//$tema_class = ($tema_global === 'dark') ? 'dark-mode' : str_replace(' ', '-', $tema_global);
?>

<!-- <html lang="id" class="<//?= htmlspecialchars($tema_class) ?>"> -->

<!-- Tombol Toggle Sidebar -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <!-- <img src="gambar.png" alt="Logo" class="logo-img"> Placeholder Logo -->
        <h3>Sidebar</h3>
    </div>


    <ul class="sidebar-nav">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="dashboard_parkiran.php" class="<?= $active_page == 'dashboard_parkiran' ? 'active' : '' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="daftar_kendaraan.php" class="<?= $active_page == 'daftar_kendaraan' ? 'active' : '' ?>"><i class="fa-solid fa-motorcycle"></i> Daftar Kendaraan</a></li>
                <li><a href="area_parkir.php" class="<?= $active_page == 'area_parkir' ? 'active' : '' ?>"><i class="fa-solid fa-car-tunnel"></i> Area Parkir</a></li>
                <li><a href="transaksi_parkir.php" class="<?= $active_page == 'transaksi_parkir' ? 'active' : '' ?>"><i class="fa-solid fa-ticket"></i> Tiket Parkir</a></li>
                <li><a href="log_aktivitas_parkiran.php" class="<?= $active_page == 'log_aktivitas_parkiran' ? 'active' : '' ?>"><i class="fa-regular fa-calendar-days"></i> Log Parkiran</a></li>
                <li><a href="pengaturan.php" class="<?= $active_page == 'pengaturan' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Pengaturan</a></li>
        <?php else: ?>
            <li><a href="catat_absensi.php" class="<?= $active_page == 'catat_absensi' ? 'active' : '' ?>"><i class="fas fa-clock"></i> Catat Absensi</a></li>
        <?php endif; ?>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleButton = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');

    toggleButton.addEventListener('click', function () {
        sidebar.classList.toggle('active');
        toggleButton.classList.toggle('active');
        if (mainContent) mainContent.classList.toggle('shifted');
    });
});
</script>
