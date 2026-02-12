<?php
session_start();

$active_page = 'rekap_transaksi_parkir';

include 'koneksi_parkir.php';
include 'proteksi_role_parkir.php';

/* ===============================
   1. VALIDASI LOGIN & ROLE
================================ */

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil role user
$stmtRole = $conn->prepare("
    SELECT role 
    FROM tb_user 
    WHERE id_user = ?
");
$stmtRole->bind_param("i", $id_user);
$stmtRole->execute();
$resultRole = $stmtRole->get_result();

if ($resultRole->num_rows === 0) {
    die("User tidak ditemukan");
}

$userData = $resultRole->fetch_assoc();

// Yup, petugas dan admin tidak bisa mengakses ini
if ($userData['role'] !== 'owner') {
    die("Akses ditolak. Halaman ini hanya untuk owner.");
}

/* ===============================
   2. FILTER TANGGAL (OPTIONAL)
================================ */

$start_date = $_GET['start_date'] ?? null;
$end_date   = $_GET['end_date'] ?? null;

$whereDate = "";
$params    = [$id_user];
$types     = "i";

if ($start_date && $end_date) {
    $whereDate = " AND DATE(t.waktu_masuk) BETWEEN ? AND ? ";
    $params[] = $start_date;
    $params[] = $end_date;
    $types   .= "ss";
}

/* ===============================
   3. QUERY UTAMA
   ❗ DURASI & TARIF BERSUMBER DARI tb_transaksi
================================ */

/* Note : Awalnya aku mau pake data dari tb_log_aktivitas,tapi karena buru-buru jadi pake data dari tb_transaksi
          Secara teknis sama aja soalnya data tb_log_aktivitas itu dari tb_transaksi */

$sql = "
SELECT
    t.id_parkir,
    t.waktu_masuk,
    t.waktu_keluar,
    t.durasi_jam,
    t.biaya_total,
    t.status,

    k.plat_nomor,
    k.tipe_kendaraan,
    k.jenis_kendaraan,
    k.warna,
    k.pemilik,

    a.nama_area,
    u.username

FROM tb_transaksi t
JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
JOIN tb_area_parkir a ON t.id_area = a.id_area
JOIN tb_user u ON t.id_user = u.id_user

WHERE t.id_user = ?
$whereDate
ORDER BY t.waktu_masuk DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

/* ===============================
   4. GROUP DATA PER TANGGAL
   ❗ TIDAK ADA HITUNG REALTIME
================================ */

$rekap_per_tanggal = [];

while ($row = $result->fetch_assoc()) {

    $tanggal = date('Y-m-d', strtotime($row['waktu_masuk']));

    // ✅ DURASI HANYA JIKA SUDAH KELUAR
    if ($row['status'] === 'keluar') {
        $durasi = (int)$row['durasi_jam'];
        $biaya  = (int)$row['biaya_total'];
    } else {
        $durasi = null;
        $biaya  = null;
    }

    $rekap_per_tanggal[$tanggal][] = [
        'username'        => $row['username'],
        'pemilik'         => $row['pemilik'],
        'tipe_kendaraan'  => $row['tipe_kendaraan'],
        'jenis_kendaraan' => $row['jenis_kendaraan'],
        'plat_nomor'      => $row['plat_nomor'],
        'warna'           => $row['warna'],

        // STATUS + WAKTU
        'status'          => $row['status'],
        'waktu_masuk'     => $row['waktu_masuk'],
        'waktu_keluar'    => $row['waktu_keluar'],

        // DURASI & TARIF
        'durasi_jam'      => $durasi,
        'biaya_total'     => $biaya,

        'nama_area'       => $row['nama_area']
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Laporan Pengguna Parkiran | Riwayat Pengguna Parkir</title>

    <link rel="stylesheet" href="desain_parkir.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>

<body>
<div class="wrapper">
    <?php include 'sidebar_parkiran.php'; ?>

    <main class="main-content">
        <header class="main-header">
            <h2>Rekap Riwayat Parkiran</h2>
        </header>

        <hr>

        <?php if (!empty($rekap_per_tanggal)): ?>
            <?php foreach ($rekap_per_tanggal as $tanggal => $data_harian): ?>

                <!-- ===== JUDUL PER TANGGAL ===== -->
                <h3 class="mt-4">
                    Detail Aktivitas Tanggal 
                    <?= date('d F Y', strtotime($tanggal)); ?>
                </h3>

                <!-- ===== TABLE PER TANGGAL ===== -->
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
                                    <th>Status</th>
                                    <th>Tarif</th>
                                    <th>Durasi</th>
                                    <th>Area Parkir</th>
                                </tr>
                            </thead>
                            <tbody>

                            <?php foreach ($data_harian as $row): ?>

                                <tr>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['pemilik']) ?></td>
                                    <td><?= htmlspecialchars($row['tipe_kendaraan']) ?></td>
                                    <td><?= htmlspecialchars($row['jenis_kendaraan']) ?></td>
                                    <td><?= htmlspecialchars($row['plat_nomor']) ?></td>
                                    <td><?= htmlspecialchars($row['warna']) ?></td>

                                    <!-- STATUS -->
                                    <td>
                                        <?php if ($row['status'] === 'masuk'): ?>
                                            Masuk: <?= date('H:i', strtotime($row['waktu_masuk'])) ?><br>
                                            <span class="text-warning">Masih Terparkir</span>
                                        <?php else: ?>
                                            Masuk: <?= date('H:i', strtotime($row['waktu_masuk'])) ?><br>
                                            Keluar: <?= date('H:i', strtotime($row['waktu_keluar'])) ?>
                                        <?php endif; ?>
                                    </td>

                                    <!-- TARIF -->
                                    <td>
                                        <?php if ($row['status'] === 'keluar'): ?>
                                            Rp <?= number_format($row['biaya_total'], 0, ',', '.') ?>
                                        <?php else: ?>
                                            <em>Belum keluar</em>
                                        <?php endif; ?>
                                    </td>

                                    <!-- DURASI -->
                                    <td><?= $row['durasi_jam'] ?> jam</td>

                                    <!-- AREA -->
                                    <td><?= htmlspecialchars($row['nama_area']) ?></td>
                                </tr>

                            <?php endforeach; ?>

                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php else: ?>

            <div class="alert alert-warning mt-4">
                Tidak ada riwayat parkir untuk akun ini.
            </div>

        <?php endif; ?>

    </main>
</div>
</body>
</html>