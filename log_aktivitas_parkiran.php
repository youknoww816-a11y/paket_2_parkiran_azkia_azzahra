<?php
include 'koneksi_parkir.php';

$active_page = 'log_aktivitas_parkiran';

/* ===============================
   1. FILTER TANGGAL
================================ */

// Default: bulan berjalan
$currentMonth = date('Y-m');
$month = $_GET['month'] ?? $currentMonth;

$start_date = $_GET['start_date'] ?? ($month . '-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-t', strtotime($start_date));

$start_datetime = $start_date . ' 00:00:00';
$end_datetime   = $end_date . ' 23:59:59';

// Default: bulan ini
$current_month_year = date('Y-m');

// Default tanggal bulan ini
$start_date_month = date('Y-m-01');
$end_date_month   = date('Y-m-t');

// ================== HANDLE FILTER ==================
if (isset($_GET['month']) && $_GET['month'] !== '') {
    $current_month_year = $_GET['month'];
    $start_date_month = date('Y-m-01', strtotime($current_month_year));
    $end_date_month   = date('Y-m-t', strtotime($current_month_year));
}

// Kalau user pakai custom range
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $start_date_month = $_GET['start_date'];
    $end_date_month   = $_GET['end_date'];
}

/* ===============================
   2. QUERY DETAIL AKTIVITAS
================================ */

$sql = "
SELECT
    t.id_parkir,
    t.waktu_masuk,
    t.waktu_keluar,
    t.status,
    t.durasi_jam,
    t.biaya_total,

    u.username,
    u.nama_lengkap,

    k.plat_nomor,
    k.tipe_kendaraan,
    k.jenis_kendaraan,
    k.warna,
    k.pemilik,

    a.nama_area,

    tr.tarif_per_jam,

    CASE
        WHEN t.waktu_keluar IS NULL THEN
            CONCAT(
                'Masuk: ',
                DATE_FORMAT(t.waktu_masuk, '%d %b %Y %H:%i'),
                ' | Status: Masih Terparkir'
            )
        ELSE
            CONCAT(
                'Masuk: ',
                DATE_FORMAT(t.waktu_masuk, '%d %b %Y %H:%i'),
                ' | Keluar: ',
                DATE_FORMAT(t.waktu_keluar, '%d %b %Y %H:%i')
            )
    END AS aktivitas
FROM tb_transaksi t
JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
JOIN tb_user u ON t.id_user = u.id_user
JOIN tb_area_parkir a ON t.id_area = a.id_area
LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif
WHERE t.waktu_masuk BETWEEN ? AND ?
ORDER BY t.waktu_masuk DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_datetime, $end_datetime);
$stmt->execute();
$result_aktivitas = $stmt->get_result();

/* ===============================
   3. RINGKASAN DATA
================================ */

// Total kendaraan masuk
$q_masuk = $conn->prepare("
    SELECT COUNT(*) 
    FROM tb_transaksi 
    WHERE waktu_masuk BETWEEN ? AND ?
");
$q_masuk->bind_param("ss", $start_datetime, $end_datetime);
$q_masuk->execute();
$q_masuk->bind_result($g_total_kendaraan_masuk);
$q_masuk->fetch();
$q_masuk->close();

// Total kendaraan keluar
$q_keluar = $conn->prepare("
    SELECT COUNT(*) 
    FROM tb_transaksi 
    WHERE status = 'keluar'
    AND waktu_keluar BETWEEN ? AND ?
");
$q_keluar->bind_param("ss", $start_datetime, $end_datetime);
$q_keluar->execute();
$q_keluar->bind_result($g_total_kendaraan_keluar);
$q_keluar->fetch();
$q_keluar->close();

// Kendaraan masih terparkir (GLOBAL)
$q_parkir = $conn->query("
    SELECT COUNT(*) 
    FROM tb_transaksi 
    WHERE status = 'masuk'
");
$g_total_kendaraan_terparkir = $q_parkir->fetch_row()[0];

// Total transaksi (keluar saja)
$q_transaksi = $conn->prepare("
    SELECT SUM(biaya_total) 
    FROM tb_transaksi 
    WHERE status = 'keluar'
    AND waktu_keluar BETWEEN ? AND ?
");
$q_transaksi->bind_param("ss", $start_datetime, $end_datetime);
$q_transaksi->execute();
$q_transaksi->bind_result($g_total_transaksi);
$q_transaksi->fetch();
$q_transaksi->close();

$g_total_transaksi = $g_total_transaksi ?? 0;
?>
<!-- Ini line ke 127 -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Log Laporan Parkiran | Aktivitas Bulan Ini</title>
    <link rel="stylesheet" href="desain_parkir.css">
        <link rel="stylesheet" href="desain_parkir.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

        <div class="wrapper">
            <?php include 'sidebar_parkiran.php'; ?>
            <main class="main-content">
            
            <header class="main-header"><h2>Log Aktivitas Parkiran</h2></header>
            
            <div class="content-body">
        
    
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

<div class="empty-space"></div>

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
                <option value="username">Username</option>
                <option value="plat_nomor">Plat Nomor</option>
                <option value="warna">Warna Kendaraan</option>
            </select>        
            
            <input type="text" id="searchBox" placeholder="Cari Kendaraan...">
        </div>
    </div>
    
    <hr>
    <h3 class>Detail Aktivitas (<?php echo date('F Y',strtotime($start_date_month));?>)</h3>

    <!-- ================= TABLE AREA ================= -->
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Nama Pemilik</th>
                            <th>Tipe Kendaraan</th>
                            <th>Jenis</th>
                            <th>Plat Nomor</th>
                            <th>Warna</th>
                            <th>Status</th> <!-- Ini isinya panjang lebar kaya status masuk atau keluar dan waktunya digabung, makanya kita bakal pake yb_log_aktivitas kolom aktivitas varchar 100 -->
                            <th>Tarif</th> <!-- Kalau baru masuk tulis keterangan 'Kendaraan masih di area parkir -->
                            <th>Durasi</Th> <!-- Agar tau berapa lama kendaraan masih terparkir -->
                            <th>Area parkir</th>
                        </tr>
                    </thead>
                    <tbody>

<?php if ($result_aktivitas && $result_aktivitas->num_rows > 0): ?>
    <?php while ($row = $result_aktivitas->fetch_assoc()): ?>

        <?php
        // Hitung durasi realtime kalau masih parkir
        if ($row['status'] === 'masuk') {
            $masuk = new DateTime($row['waktu_masuk']);
            $now   = new DateTime();
            $diff  = $masuk->diff($now);
            $durasi = ($diff->days * 24) + $diff->h;
            if ($durasi < 1) $durasi = 1;
        } else {
            $durasi = $row['durasi_jam'];
        }
        ?>

        <tr>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['pemilik']) ?></td>
            <td><?= htmlspecialchars($row['tipe_kendaraan']) ?></td>
            <td><?= htmlspecialchars($row['jenis_kendaraan']) ?></td>
            <td><?= htmlspecialchars($row['plat_nomor']) ?></td>
            <td><?= htmlspecialchars($row['warna']) ?></td>

            <!-- Status panjang -->
            <td><?= htmlspecialchars($row['aktivitas']) ?></td>

            <!-- Tarif -->
            <td>
                <?php if ($row['status'] === 'masuk'): ?>
                    <em>Kendaraan masih di area parkir</em>
                <?php else: ?>
                    Rp <?= number_format($row['tarif_per_jam'], 0, ',', '.') ?>/jam
                <?php endif; ?>
            </td>

            <!-- Durasi -->
            <td><?= $durasi ?> jam</td>

            <!-- Area Parkir -->
            <td><?= htmlspecialchars($row['nama_area']) ?></td>
        </tr>

    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="10" class="text-center text-muted">
            Tidak ada data aktivitas pada periode ini
        </td>
    </tr>
<?php endif; ?>

</tbody>
                </table>



<!-- Dropdown Search -->
<script>
document.getElementById('searchBox').addEventListener('keyup', function () {
    const keyword = this.value.toLowerCase();
    const type = document.getElementById('searchType').value;
    const rows = document.querySelectorAll("table tr");

    rows.forEach((row, index) => {
        if (index === 0) return; // skip header

        let cellText = "";

        if (type === "jenis") {
            cellText = row.cells[2].innerText.toLowerCase(); // kolom jenis
        } else if (type === "pemilik") {
            cellText = row.cells[4].innerText.toLowerCase(); // kolom pemilik
        }

        row.style.display = cellText.includes(keyword) ? "" : "none";
    });
});
</script>    


<!-- -->
<script>
const searchType = document.getElementById('searchType');
const searchBox = document.getElementById('searchBox');

searchType.addEventListener('change', function () {
    if (this.value === "") {
        searchBox.disabled = true;
        searchBox.value = "";
        searchBox.placeholder = "Pilih filter dulu...";
    } else {
        searchBox.disabled = false;
        searchBox.placeholder = "Cari " + this.options[this.selectedIndex].text + "...";
        searchBox.focus();
    }
});

searchBox.addEventListener('keyup', function () {
    const keyword = this.value.toLowerCase();
    const type = searchType.value;
    const rows = document.querySelectorAll("table tr");

    rows.forEach((row, index) => {
        if (index === 0) return;

        let text = "";
        if (type === "jenis") {
            text = row.cells[2].innerText.toLowerCase();
        } else if (type === "pemilik") {
            text = row.cells[4].innerText.toLowerCase();
        }

        row.style.display = text.includes(keyword) ? "" : "none";
    });
});
</script>