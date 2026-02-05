<?php
include 'koneksi_parkir.php';

$active_page = 'log_aktivitas_parkiran';
//Note: jadi ini untuk kaya log atau riyawat, untuk tau siapa yang masuk, siapa yang keluar, 
//      sama siapa yang masih ada di area parkir.

// Selain itu kamu bisa liat setiap kendaraan terparkir di area mana.

// Kamu bisa liat berapa kapasitas kendaraan dan area parkir yang kosong dan yang penuh.

// Bentukannya mirip riyawat_absensi.php tapi lebih rapi karena ada garis per-hari yang membatasi,
//  sama posisinya. . . masih belum yakin aku
// Soalnya yang pertama kamu liat itu semcam animasi kendaraan terparkir.

// Bisa tersortir atau difilter berdasarkan siapa yang baru masuk, siapa yang baru keluar, siapa yang masih belum keluar,
// siapa yang masih terparkir, bahkan dari hari yang kemarin, jadi filter dari tanggal sekian dan tanggal sekian,
// dan area spesifik kendaraan terparkir (termasuk kendaraan yang masih terparkir dari hari yang lalu).

// Kamu bisa liat kendaraannya, warna kendaraan, jenis kendaraan, plat nomornya, nama pemilik, nama usernamenya,
// kapan mereka keluar-masuk, berapa lama mereka terparkir, dan dimana area mereka terpakir

// Kamu juga bisa search username atau nama lengkap untuk pecarian riwayat spesifik


?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Log Laporan Parkiran | Aktivitas Bulan Ini</title>
    <link rel="stylesheet" href="desain_parkir.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.21/jspdf.plugin.autotable.min.js"></script>

        <div class="wrapper">
            <main class="main-content">
            <header class="main-header"><h2>Log Aktivitas Parkiran</h2></header>
            <div class="content-body">
        <?php if(!function_exists('display_message')){function display_message($m,$t){if($m) echo "<div class='message $t'>$m</div>";}} display_message($message,$message_type); ?>
    
<!-- Filter Tanggal -->
<div class="laporan-filter-container">
    <form method="GET" action="log_aktivitas_parkiran.php" class="laporan-filter-form">

        <!-- Filter per-Bulan -->
        <div class="filter-group">
            <label for="month">Pilih Bulan:</label>
            <input type="month" id="month" name="month" value="<?php echo htmlspecialchars($current_month_year); ?>">
        </div>

        <!-- Filter tanggal dari -->
        <div class="filter-group">
            <label for="start_date">Dari Tanggal:</label>
            <input type="date" name="start_date" id="start_date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
        </div>

        <!-- sampai tanggal -->
        <div class="filter-group">
            <label for="end_date">Sampai Tanggal:</label>
            <input type="date" name="end_date" id="end_date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
        </div>

        <div class="filter-group">
            <button type="submit">Filter</button>
        </div>
    </form>
</div>

<h3 class>Ringkasan (<?php echo date('d F Y',strtotime($start_date_month)).' s/d '.date('d F Y',strtotime($end_date_month)); ?>)</h3>
<div class="report-summary-cards">
<div class="summary-card"><h4>Total Kendaraan Masuk</h4><p><?php echo $g_total_kendaraan_masuk;?> </p></div>
<div class="summary-card"><h4>Total Kendaraan Keluar</h4><p><?php echo $g_total_kendaraan_keluar;?> </p></div>
<div class="summary-card"><h4>Total Kendaraan Yang Masih Terparkir</h4><p><?php echo $g_total_kendaraan_terparkir;?></p></div>
<div class="summary-card"><h4>Total Transaksi </h4><p><?php echo $g_total_transaksi;?></p></div> <!-- Ini untuk kalau total rupiah dari semua kendaraan yang telah bayar keluar parkir bulan ini -->
</div>

</div>

    <!-- TOOLBAR -->
     <div class="toolbar-parkir">
        <div class="filter-container">
            <button id="btnFilter" data-tooltip="Sortir"><i class="fa-solid fa-filter"></i></button>
            <div id="filterMenu" class="filter-menu hidden">
                <button data-sort="mobil">Kendaraan Mobil</button>
                <button data-sort="motor">Kendaraan Motor</button>
                <button data-sort="lainnya">Kendaraan Lainnya</button>
                <button data-sort="">Tanggal Terlama</button>
            </div>
        </div>
        
        <div class="search-wrapper">
            <select id="searchType">
                <option value="jenis">Jenis Kendaraan</option>
                <option value="pemilik">Nama Pemilik</option>
            </select>        
            
            <input type="text" id="searchBox" placeholder="Cari Kendaraan...">
        </div>
    </div>
    
    <hr>
    <h3 class>Detail Aktivitas (<?php echo date('F Y',strtotime($start_date_month));?>)</h3>
