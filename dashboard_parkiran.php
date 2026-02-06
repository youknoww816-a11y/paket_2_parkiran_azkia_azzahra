<?php
include 'koneksi_parkir.php';
// include 'proteksi_user_parkir.php';
$active_page = 'dashboard_parkiran';

date_default_timezone_set('Asia/Jakarta');
$tanggal_hari_ini = date('Y-m-d');


// Note : ini enggak bakal dihapus dulu, siapa tau disuruh ngebuat tema, untuk sementara jadi placeholder
//$query = $conn->query("SELECT tema, tampilan_menu, running_text_dashboard, kecepatan_running_text_dashboard FROM tb_tampilan LIMIT 1");
//if ($row = $query->fetch_assoc()) {
//$tema_global = $row['tema'];
//$menu_mode_global = $row['tampilan_menu'];
//$running_text_dashboard = $row['running_text_dashboard'];
//$kecepatan_dashboard = is_numeric($row['kecepatan_running_text_dashboard']) ? (float)$row['kecepatan_running_text_dashboard'] : 15;
//} else {
//    $tema_global = 'normal';
//    $menu_mode_global = 'sidebar';
//    $running_text_dashboard = 'Selamat datang di Dashboard'; // default jika kosong
//}
//if (isset($_COOKIE['theme'])) {
//    $tema_global = $_COOKIE['theme'];
//}
//if (isset($_COOKIE['menu_mode'])) {
//    $menu_mode_global = $_COOKIE['menu_mode'];
//}


/* =======================
   INISIALISASI
======================= */
$total_kendaraan = 0;

$total_motor_terparkir = 0;
$total_mobil_terparkir = 0;
$total_lainnya_terparkir = 0;
$total_semua_kendaraan_terparkir = 0;

$total_kendaraan_masuk_hari_ini = 0;
$total_kendaraan_keluar_hari_ini = 0;

$data_chart = [
    'masuk' => 0,
    'keluar' => 0,
    'masih_parkir' => 0
];

$message = '';
$message_type = '';

/* =======================
   QUERY DATABASE
======================= */
if ($conn && !$conn->connect_error) {

    /* TOTAL KENDARAAN TERDAFTAR */
    $res = $conn->query("SELECT COUNT(*) AS total FROM tb_kendaraan");
    if ($res) {
        $total_kendaraan = (int)$res->fetch_assoc()['total'];
    }

    /* ============================
       KENDARAAN MASIH TERPARKIR
       (STATUS = 'masuk', SEMUA HARI)
       ============================ */
    $sql = "
        SELECT k.jenis_kendaraan, COUNT(*) AS jumlah
        FROM tb_transaksi tr
        JOIN tb_kendaraan k ON tr.id_kendaraan = k.id_kendaraan
        WHERE tr.status = 'masuk'
        GROUP BY k.jenis_kendaraan
    ";

    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if ($row['jenis_kendaraan'] === 'motor') {
                $total_motor_terparkir = (int)$row['jumlah'];
            } elseif ($row['jenis_kendaraan'] === 'mobil') {
                $total_mobil_terparkir = (int)$row['jumlah'];
            } else {
                $total_lainnya_terparkir = (int)$row['jumlah'];
            }
        }
    }

    $total_semua_kendaraan_terparkir =
        $total_motor_terparkir +
        $total_mobil_terparkir +
        $total_lainnya_terparkir;

    /* ============================
       KENDARAAN MASUK HARI INI
       ============================ */
       
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM tb_transaksi 
        WHERE DATE(waktu_masuk) = ?
    ");

    $stmt->bind_param("s", $tanggal_hari_ini);
    $stmt->execute();
    $stmt->bind_result($total_kendaraan_masuk_hari_ini);
    $stmt->fetch();
    $stmt->close();

    /* ============================
       KENDARAAN KELUAR HARI INI
       ============================ */
       
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM tb_transaksi 
        WHERE status = 'keluar'
        AND DATE(waktu_keluar) = ?
    ");

    $stmt->bind_param("s", $tanggal_hari_ini);
    $stmt->execute();
    $stmt->bind_result($total_kendaraan_keluar_hari_ini);
    $stmt->fetch();
    $stmt->close();

    /* ============================
       DATA CHART
       ============================ */
    $data_chart['masuk'] = $total_kendaraan_masuk_hari_ini;
    $data_chart['keluar'] = $total_kendaraan_keluar_hari_ini;
    $data_chart['masih_parkir'] = $total_semua_kendaraan_terparkir;

} else {
    $message = "Koneksi database gagal";
    $message_type = "error";
}

$chart_data_json = json_encode($data_chart);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Parkir</title>
    <link rel="stylesheet" href="desain_parkir.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<div class="wrapper">

<main class="main-content">
    <header class="main-header"><h2>Dashboard Parkir</h2></header>

<?php include 'sidebar_parkiran.php'; ?>

<section class="summary-section">
<h3>Data Hari Ini (<?= htmlspecialchars($tanggal_hari_ini) ?>)</h3>

<div class="dashboard-grid">

    <div class="dashboard-card">
        <i class="fas fa-car"></i>
        <h4>Total Kendaraan Terdaftar</h4>
        <p><?= $total_kendaraan ?></p>
    </div>

    <div class="dashboard-card">
        <i class="fas fa-motorcycle"></i>
        <h4>Motor Terparkir</h4>
        <p><?= $total_motor_terparkir ?></p>
    </div>

    <div class="dashboard-card">
        <i class="fas fa-car-side"></i>
        <h4>Mobil Terparkir</h4>
        <p><?= $total_mobil_terparkir ?></p>
    </div>

    <div class="dashboard-card">
        <i class="fas fa-truck"></i>
        <h4>Kendaraan Lainnya</h4>
        <p><?= $total_lainnya_terparkir ?></p>
    </div>

    <div class="dashboard-card">
        <i class="fa-solid fa-square-parking"></i>
        <h4>Total Sedang Parkir</h4>
        <p><?= $total_semua_kendaraan_terparkir ?></p>
    </div>

    <div class="dashboard-card">
        <i class="fas fa-sign-in-alt"></i>
        <h4>Kendaraan Masuk Hari Ini</h4>
        <p><?= $total_kendaraan_masuk_hari_ini ?></p>
    </div>

    <div class="dashboard-card">
        <i class="fas fa-sign-out-alt"></i>
        <h4>Kendaraan Keluar Hari Ini</h4>
        <p><?= $total_kendaraan_keluar_hari_ini ?></p>
    </div>

</div>
</section>

<section class="chart-section">
<h3>Status Parkir Hari Ini</h3>

<div class="chart-container">
    <canvas id="statusParkirChart"></canvas>
</div>
</section>

</main>
</div>

<script>
const chartData = <?= $chart_data_json ?>;

new Chart(document.getElementById('statusParkirChart'), {
    type: 'bar',
    data: {
        labels: ['Masuk Hari Ini', 'Keluar Hari Ini', 'Masih Terparkir'],
        datasets: [{
            label: 'Jumlah Kendaraan',
            data: [
                chartData.masuk,
                chartData.keluar,
                chartData.masih_parkir
            ],
            backgroundColor: ['#2196F3', '#F44336', '#4CAF50']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>

</body>
</html>