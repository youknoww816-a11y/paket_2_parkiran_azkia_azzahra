<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include 'koneksi_parkir.php';

$pesan = '';
$data_tiket = null;

if (isset($_POST['username'])) {

    $username = trim($_POST['username']);

    // ===============================
    // AMBIL USER + KENDARAAN
    // ===============================
    $q = $conn->query("
        SELECT 
            k.id_kendaraan,
            k.plat_nomor,
            k.jenis_kendaraan,
            k.warna,
            u.id_user,
            u.username,
            u.nama_lengkap
        FROM tb_kendaraan k
        JOIN tb_user u ON k.id_user = u.id_user
        WHERE u.username = '$username'
        LIMIT 1
    ");

    if ($q->num_rows == 0) {
        $pesan = "User atau kendaraan tidak ditemukan.";
    } else {

        $kendaraan = $q->fetch_assoc();
        $id_kendaraan = $kendaraan['id_kendaraan'];
        $id_user = $kendaraan['id_user'];

        // ===============================
        // CEK TRANSAKSI TERAKHIR
        // ===============================
        $cek = $conn->query("
            SELECT *
            FROM tb_transaksi
            WHERE id_kendaraan = $id_kendaraan
            ORDER BY id_parkir DESC
            LIMIT 1
        ");

        $parkir = ($cek->num_rows > 0) ? $cek->fetch_assoc() : null;

        // ===================================================
        // MASUK PARKIR
        // ===================================================
        if (!$parkir || $parkir['status'] == 'keluar') {

            $now = date('Y-m-d H:i:s');

            $conn->query("
                INSERT INTO tb_transaksi (
                    id_kendaraan,
                    waktu_masuk,
                    status,
                    id_user,
                    id_area
                ) VALUES (
                    $id_kendaraan,
                    '$now',
                    'masuk',
                    $id_user,
                    1
                )
            ");

            $data_tiket = [
                'mode' => 'MASUK',
                'waktu_masuk' => $now,
                'kendaraan' => $kendaraan
            ];
        }

        // ===================================================
        // KELUAR PARKIR
        // ===================================================
        else {

            $masuk  = strtotime($parkir['waktu_masuk']);
            $keluar = time();
            $durasi = ceil(($keluar - $masuk) / 3600);

            if ($durasi < 1) $durasi = 1;

            // ================= TARIF =================
            $jenis = strtolower($kendaraan['jenis_kendaraan']);

            if ($jenis == 'motor') {
                $total = 2000 + (($durasi - 1) * 2000);
                $id_tarif = 1;
            } elseif ($jenis == 'mobil') {
                $total = 5000 + (($durasi - 1) * 3000);
                $id_tarif = 2;
            } else {
                $total = 6000 + (($durasi - 1) * 5000);
                $id_tarif = 3;
            }

            $now = date('Y-m-d H:i:s');

            $conn->query("
                UPDATE tb_transaksi SET
                    waktu_keluar = '$now',
                    durasi_jam = $durasi,
                    biaya_total = $total,
                    id_tarif = $id_tarif,
                    status = 'keluar'
                WHERE id_parkir = {$parkir['id_parkir']}
            ");

            $data_tiket = [
                'mode' => 'KELUAR',
                'waktu_masuk' => $parkir['waktu_masuk'],
                'waktu_keluar' => $now,
                'durasi' => $durasi,
                'total' => $total,
                'kendaraan' => $kendaraan
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Parkir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="desain_parkir.css">
</head>

<body>
<div class="wrapper">
<main class="main-content">

    <header class="main-header">
        <h2>Transaksi Parkir</h2>
    </header>

    <!-- TOOLBAR -->
    <div class="toolbar-layanan">
        <button id="btnTambah"><i class="fa-solid fa-plus"></i></button>

        <div class="filter-container">
            <button id="btnFilter"><i class="fa-solid fa-filter"></i></button>
            <div id="filterMenu" class="filter-menu hidden">
                <button data-sort="nama_asc">Nama A-Z</button>
                <button data-sort="nama_desc">Nama Z-A</button>
                <button data-sort="tanggal_asc">Tanggal Terlama</button>
                <button data-sort="tanggal_desc">Tanggal Terkini</button>
            </div>
        </div>

        <button id="btnRefresh"><i class="fa-solid fa-arrows-rotate"></i></button>
        <input type="text" id="searchBox" placeholder="Cari...">
    </div>

    <br>

    <!-- TABEL -->
    <table id="tabelParkir" class="display" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>Username</th>
                <th>Nama</th>
                <th>Preview</th>
            </tr>
        </thead>
        <tbody>

        <?php
        $no = 1;
        $q = $conn->query("
            SELECT 
                t.waktu_masuk,
                t.waktu_keluar,
                t.status,
                t.biaya_total,
                k.jenis_kendaraan,
                k.plat_nomor,
                u.username,
                u.nama_lengkap
            FROM tb_transaksi t
            JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
            JOIN tb_user u ON t.id_user = u.id_user
            ORDER BY t.id_parkir DESC
        ");

        while ($row = $q->fetch_assoc()):
        ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
            <td>
                <button class="btn-preview"
                    data-nama="<?= $row['nama_lengkap'] ?>"
                    data-kendaraan="<?= $row['jenis_kendaraan'] ?>"
                    data-plat="<?= $row['plat_nomor'] ?>"
                    data-status="<?= $row['status'] ?>"
                    data-masuk="<?= $row['waktu_masuk'] ?>"
                    data-keluar="<?= $row['waktu_keluar'] ?>"
                    data-total="<?= $row['biaya_total'] ?>"
                >
                    <i class="fa fa-eye"></i> Preview
                </button>
            </td>
        </tr>
        <?php endwhile; ?>

        </tbody>
    </table>

</main>
</div>

<!-- MODAL PREVIEW -->
<div id="modalPreview" class="my-modal">
    <div class="bon-content">
        <span class="close">&times;</span>

        <p>Nama : <span id="p_nama"></span></p>
        <p>Kendaraan : <span id="p_kendaraan"></span></p>
        <p>Plat : <span id="p_plat"></span></p>
        <p>Status : <span id="p_status"></span></p>
        <p>Masuk : <span id="p_masuk"></span></p>

        <div id="keluarArea" style="display:none;">
            <p>Keluar : <span id="p_keluar"></span></p>
            <p>Total : Rp <span id="p_total"></span></p>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
$(function(){

    // 1. Inisialisasi DataTable (WAJIB PERTAMA)
    const table = $('#tabelParkir').DataTable({
        searching: false,
        lengthChange: false,
        info: false
    });

    // 2. Search manual dari input "Cari..."
    $('#searchBox').on('keyup', function () {
        table.search(this.value).draw();
    });

    // 3. Tombol preview
    $('.btn-preview').on('click', function(){
        $('#modalPreview').fadeIn();

        $('#p_nama').text($(this).data('nama'));
        $('#p_kendaraan').text($(this).data('kendaraan'));
        $('#p_plat').text($(this).data('plat'));
        $('#p_status').text($(this).data('status'));
        $('#p_masuk').text($(this).data('masuk'));

        if ($(this).data('status') === 'keluar') {
            $('#keluarArea').show();
            $('#p_keluar').text($(this).data('keluar'));
            $('#p_total').text($(this).data('total'));
        } else {
            $('#keluarArea').hide();
        }
    });

    // 4. Tutup modal
    $('.close').on('click', function(){
        $('#modalPreview').fadeOut();
    });

});
</script>

</body>
</html>