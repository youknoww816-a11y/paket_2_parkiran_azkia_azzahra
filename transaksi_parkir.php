<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

$active_page = 'transaksi_parkir';

include 'koneksi_parkir.php';
include 'proteksi_role_parkir.php';

$message = '';
$message_type = '';

$data_tiket = null;
$status_terakhir = null;

/* ===============================
   PROSES KELUAR PARKIR (FIX TOTAL)
   =============================== */

if (isset($_GET['keluar'])) {

    $id_parkir = (int)$_GET['keluar'];

    $qKeluar = $conn->query("
        SELECT 
            t.*,
            k.plat_nomor AS plat_kendaraan,
            k.jenis_kendaraan,
            k.tipe_kendaraan,
            u.nama_lengkap,
            u.username,
            a.nama_area
        FROM tb_transaksi t
        LEFT JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
        LEFT JOIN tb_user u ON t.id_user = u.id_user
        LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area
        WHERE t.id_parkir = $id_parkir
        AND t.status = 'masuk'
        LIMIT 1
    ");

    if ($qKeluar && $qKeluar->num_rows > 0) {

        $trx = $qKeluar->fetch_assoc();

        $waktu_masuk  = strtotime($trx['waktu_masuk']);
        $waktu_keluar = time();

        $durasi = ceil(($waktu_keluar - $waktu_masuk) / 3600);
        if ($durasi < 1) $durasi = 1;

        $selisih_detik = $waktu_keluar - $waktu_masuk;
        
        $jam = floor($selisih_detik / 3600);
        $menit = floor(($selisih_detik % 3600) / 60);
        
        $durasi_detail = $jam . ' jam ' . $menit . ' menit';

        // ================= CEK TAMU =================
        $is_tamu = empty($trx['id_kendaraan']);

        $id_tarif = NULL;
        $tarif_per_jam = 0;
        
        if (!$is_tamu) {

    $tipe = $trx['tipe_kendaraan'] ?? null;
    $qTarif = null;

    if ($qTarif && $qTarif->num_rows > 0) {
        $tarif = $qTarif->fetch_assoc();

        $id_tarif = (int)$tarif['id_tarif'];
        $tarif_per_jam = (int)$tarif['tarif_per_jam'];

    } else {
        die("Tarif tidak ditemukan untuk tipe: " . $tipe);
    }

        $tarif = $qTarif->fetch_assoc();

        $id_tarif = (int)$tarif['id_tarif'];
        $tarif_per_jam = (int)$tarif['tarif_per_jam'];

    } else {

        die("Tarif tidak ditemukan untuk tipe: " . $tipe);
    }

    // ================= HITUNG TOTAL =================
        if ($is_tamu) {

            $total = 4000;

            if ($durasi > 1) {
                $tambahan = ceil(($durasi - 1) / 4) * 1500;
                $total += $tambahan;
            }

        } else {

            if ($durasi <= 24) {

                $blok = ceil($durasi / 4);
                $total = $blok * $tarif_per_jam;

            } else {

                $blok24 = ceil(24 / 4);
                $harga_normal_24 = $blok24 * $tarif_per_jam;
                $harga_diskon_24 = $harga_normal_24 * 0.25;

                $sisa_jam = $durasi - 24;

                $blok_sisa = ceil($sisa_jam / 5);
                $harga_sisa = $blok_sisa * $tarif_per_jam;

                $total = $harga_diskon_24 + $harga_sisa;
            }
        }

        $now = date('Y-m-d H:i:s');

        // ================= UPDATE =================
        $conn->query("
            UPDATE tb_transaksi
            SET
                waktu_keluar = '$now',
                id_tarif = " . ($id_tarif !== NULL ? $id_tarif : "NULL") . ",
                durasi_jam = $durasi,
                biaya_total = $total,
                status = 'keluar'
            WHERE id_parkir = $id_parkir
        ");

        // ================= LOG =================
        $plat = $trx['plat_kendaraan'] ?? $trx['plat_nomor'] ?? $trx['plat_nomor_tamu'];

        $conn->query("
            INSERT INTO tb_log_aktivitas (id_user, aktivitas, waktu_aktivitas)
            VALUES (
                " . ($trx['id_user'] ?: "NULL") . ",
                'Keluar parkir - $plat dari area {$trx['nama_area']}',
                '$now'
            )
        ");

        // ================= UPDATE AREA =================
        $conn->query("
            UPDATE tb_area_parkir
            SET terisi = terisi - 1
            WHERE id_area = {$trx['id_area']}
        ");

        // ================= DATA TIKET =================
        $data_tiket = [
            'mode' => 'KELUAR',
            'waktu_masuk' => $trx['waktu_masuk'],
            'waktu_keluar' => $now,
            'durasi' => $durasi,
            'durasi_detail' => $durasi_detail,
            'total' => $total,
            'kendaraan' => [
                'plat_nomor' => $plat,
                'nama_lengkap' => $trx['nama_lengkap'] ?? 'Pengunjung',
                'username' => $trx['username'] ?? '-',
                'jenis_kendaraan' => $trx['jenis_kendaraan'] ?? '-'
            ],
            'area' => [
                'nama_area' => $trx['nama_area']
            ]
        ];

        $_SESSION['tiket_terakhir'] = $data_tiket;

        $message = "Kendaraan berhasil keluar parkir.";
        $message_type = "success";
    }
}

/* ===============================
   AMBIL USER UNTUK DROPDOWN
   =============================== */

$users_dropdown = $conn->query("
    SELECT  
        u.username,
        u.nama_lengkap,
        k.plat_nomor,
        k.id_kendaraan,
        (
            SELECT status
            FROM tb_transaksi t
            WHERE t.id_kendaraan = k.id_kendaraan
            ORDER BY id_parkir DESC
            LIMIT 1
        ) AS status_parkir
    FROM tb_user u
    JOIN tb_kendaraan k ON k.id_user = u.id_user
    ORDER BY u.nama_lengkap ASC
");

/* ===============================
   INPUT USERNAME ATAU PLAT
   =============================== */

$username = trim($_POST['username'] ?? '');
$plat     = strtoupper(trim($_POST['plat_nomor'] ?? ''));

$kendaraan = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($username !== '' || $plat !== '')) {

    if ($username !== '') {

        $stmt = $conn->prepare("
            SELECT k.*, u.id_user, u.nama_lengkap
            FROM tb_user u
            LEFT JOIN tb_kendaraan k ON k.id_user = u.id_user
            WHERE u.username = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $q = $stmt->get_result();

        if ($q->num_rows > 0) {
            $kendaraan = $q->fetch_assoc();
        } else {
            $message = "Username tidak ditemukan.";
            $message_type = "error";
        }

    } else {

        $stmt = $conn->prepare("
            SELECT k.*, u.id_user, u.nama_lengkap
            FROM tb_kendaraan k
            LEFT JOIN tb_user u ON k.id_user = u.id_user
            WHERE k.plat_nomor = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $plat);
        $stmt->execute();
        $q = $stmt->get_result();

        if ($q->num_rows > 0) {
            $kendaraan = $q->fetch_assoc();
        } else {

            $kendaraan = [
                'id_kendaraan'   => null,
                'id_user'        => null,
                'plat_nomor'     => $plat,
                'jenis_kendaraan'=> null,
                'tipe_kendaraan' => null,
                'nama_lengkap'   => 'Pengunjung'
            ];
        }
    }

    if (!empty($kendaraan)) {

        $id_kendaraan = (int)($kendaraan['id_kendaraan'] ?? 0);
        $id_user      = (int)($kendaraan['id_user'] ?? 0);
        
        $is_tamu = empty($kendaraan['id_kendaraan']);
        $plat_user = null;
        $plat_tamu = null;
        $jenis_transaksi = 'user';
        
        if ($is_tamu) {
            $plat_tamu = $plat;
            $jenis_transaksi = 'tamu';
        
        } else {
            $plat_user = $kendaraan['plat_nomor'];
        }

        $status_terakhir = null;
        if ($id_kendaraan > 0) {
            
            $cek = $conn->query("
                SELECT status
                FROM tb_transaksi
                WHERE id_kendaraan = $id_kendaraan
                ORDER BY id_parkir DESC
                LIMIT 1
            ");
            
            $parkir = $cek->fetch_assoc();
            $status_terakhir = $parkir['status'] ?? null;
            
            if ($status_terakhir === 'masuk') {
                $message = "Kendaraan masih terparkir (belum keluar).";
                $message_type = "error";
            }
        }

        if ($message === '') {

    // Cek dulu: ada area yang tidak ditutup?
    $cekAreaAktif = $conn->query("
        SELECT id_area
        FROM tb_area_parkir
        WHERE status_area_parkir != 'ditutup'
    ");

    if ($cekAreaAktif->num_rows == 0) {

        $message = "Semua area parkir sedang ditutup.";
        $message_type = "error";

    } else {

        $tipe_kendaraan = $kendaraan['tipe_kendaraan'] ?? 'motor';

        $qArea = $conn->query("
            SELECT id_area, nama_area
            FROM tb_area_parkir
            WHERE terisi < kapasitas
            AND status_area_parkir != 'ditutup'
            AND LOWER(tipe_kendaraan) = LOWER('$tipe_kendaraan')
            LIMIT 1
        ");

        if ($qArea->num_rows == 0) {

    $cekTipe = $conn->query("
        SELECT id_area 
        FROM tb_area_parkir
        WHERE LOWER(tipe_kendaraan) = LOWER('$tipe_kendaraan')
    ");

    if ($cekTipe->num_rows == 0) {
        $message = "Tidak ada area untuk tipe kendaraan ini.";
    
    } else {

        $cekBuka = $conn->query("
            SELECT id_area 
            FROM tb_area_parkir
            WHERE LOWER(tipe_kendaraan) = LOWER('$tipe_kendaraan')
            AND status_area_parkir != 'ditutup'
        ");

        if ($cekBuka->num_rows == 0) {
            $message = "Area parkir untuk kendaraan ini sedang ditutup.";
        
        } else {
            $message = "Area parkir penuh.";
        }
    }

    $message_type = "error";

            } else {

                $area = $qArea->fetch_assoc();

                $id_area   = (int)$area['id_area'];
                $nama_area = $area['nama_area'];
                $now       = date('Y-m-d H:i:s');

            /* ===============================
                INSERT TRANSAKSI MASUK 
               =============================== */
               
            $stmtInsert = $conn->prepare("
               INSERT INTO tb_transaksi (
               id_kendaraan,
               plat_nomor,
               plat_nomor_tamu,
               waktu_masuk,
               status,
               id_user,
               id_area,
               jenis_transaksi
               ) VALUES (?, ?, ?, ?, 'masuk', ?, ?, ?)
                ");
                
                $idK = $id_kendaraan ?: null;
                $idU = $id_user ?: null;
                
                $stmtInsert->bind_param(
                    "isssiis",
                    $idK,
                    $plat_user,
                    $plat_tamu,
                    $now,
                    $idU,
                    $id_area,
                    $jenis_transaksi
                );
                
                if (!$stmtInsert->execute()) {
                    die("Gagal menyimpan transaksi masuk: " . $stmtInsert->error);
                }

            $id_transaksi_baru = $stmtInsert->insert_id;
            $stmtInsert->close();
            
            $plat_log = $plat_user ?? $plat_tamu;
            $aktivitas = "Masuk parkir - $plat_log di area $nama_area";
            
            $conn->query("
                INSERT INTO tb_log_aktivitas (
                id_user,
                aktivitas,
                waktu_aktivitas
                ) VALUES (
            " . ($id_user ?: "NULL") . ",
                '$aktivitas',
                '$now'
                )
            ");

                $conn->query("
                    UPDATE tb_area_parkir
                    SET terisi = terisi + 1
                    WHERE id_area = $id_area
                ");

                $message = "Kendaraan berhasil masuk parkir.";
                $message_type = "success";

                $data_tiket = [
                    'mode' => 'MASUK',
                    'waktu_masuk' => $now,
                    'kendaraan' => $kendaraan,
                    'area' => ['nama_area' => $nama_area]
                ];

                $_SESSION['tiket_terakhir'] = $data_tiket;
            
                }
            }
        }
    }
}

/* ===============================
   DATA KENDARAAN MASIH PARKIR
   =============================== */

$data_parkir = $conn->query("
    SELECT  
        t.id_parkir,
        t.waktu_masuk,
        COALESCE(k.plat_nomor, t.plat_nomor, t.plat_nomor_tamu) AS plat_nomor,
        u.nama_lengkap,
        u.username
    FROM tb_transaksi t
    LEFT JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
    LEFT JOIN tb_user u ON t.id_user = u.id_user
    WHERE t.status = 'masuk'
    ORDER BY t.waktu_masuk ASC
");

/* ===============================
   JIKA TIDAK ADA TIKET BARU
   =============================== */

if (empty($data_tiket) && isset($_SESSION['tiket_terakhir'])) {
    $data_tiket = $_SESSION['tiket_terakhir'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Parkir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="desain_parkir.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

</head>

<body>    
<div class="wrapper">
    <?php include 'sidebar_parkiran.php'; ?>
    
    <main class="main-content">
        
    <!-- HEADER -->
    <header class="main-header">
        <h2>Transaksi Parkir</h2>
    </header>

    <!-- MESSAGE -->
    <?php if (!empty($message)): ?>
        <div class="message <?= $message_type ?>">
       
        <?= $message ?>
        </div>
    <?php endif; ?>
    
    <div class="content-body">

     <!-- Jam Analog -->
     <div class="clock-container">
        <div class="analog-clock-wrapper">
            <div class="analog-clock" id="clock">
                <div class="hand hour" id="hourHand"></div>
                <div class="hand minute" id="minuteHand"></div>
                <div class="hand second" id="secondHand"></div>
                <div class="center-dot"></div>

     <div class="clock-number" data-number="1">1</div>
     <div class="clock-number" data-number="2">2</div>
     <div class="clock-number" data-number="3">3</div>
     <div class="clock-number" data-number="4">4</div>
     <div class="clock-number" data-number="5">5</div>
     <div class="clock-number" data-number="6">6</div>
     <div class="clock-number" data-number="7">7</div>
     <div class="clock-number" data-number="8">8</div>
     <div class="clock-number" data-number="9">9</div>
     <div class="clock-number" data-number="10">10</div>
     <div class="clock-number" data-number="11">11</div>
     <div class="clock-number" data-number="12">12</div>
    </div>
</div>

<!-- Jam Digital -->

    <!-- Note : Bagi yang enggak bisa baca jam analog, tidak ada diskriminasi. . .
                Tapi ada baiknya kalau belajar lagi karena in my opinion, analog clock is underated -->

<div class="current-time-display">Jam : <span id="current-time"></span>
</div>
</div>

 <!-- ================= FORM TRANSAKSI ================= -->
<form method="POST" class="form-transaksi" style="margin-bottom:20px;">
    
    <!-- USER TERDAFTAR -->
    <label>User Terdaftar</label>
    <select name="username">
        <option value="">-- Pilih User --</option>

        <?php while ($u = $users_dropdown->fetch_assoc()): ?>
            <option 
                value="<?= htmlspecialchars($u['username']) ?>"
                <?= ($u['status_parkir'] === 'masuk') ? 'disabled' : '' ?>
            >
                <?= $u['username'] ?>
                -
                <?= $u['nama_lengkap'] ?>
                (<?= $u['plat_nomor'] ?>)
                <?= ($u['status_parkir'] === 'masuk') ? ' — Sudah Parkir' : '' ?>
            </option>
        <?php endwhile; ?>
    </select>

    <div style="text-align:center; margin:6px 0;">ATAU</div>

    <!-- PENGUNJUNG -->
    <label>Plat Nomor Pengunjung</label>
    <input
        type="text"
        name="plat_nomor"
        placeholder="Masukkan plat nomor..."
        value="<?= htmlspecialchars($plat ?? '') ?>"
    >

    <button type="submit">Masuk Parkir</button>
</form>

<!-- ================= HASIL TRANSAKSI ================= -->

<?php if (!empty($data_tiket)): ?>
    
    <div class="aksi-tiket" style="margin-top:15px;">
        <button type="button" onclick="printTiket()" class="btn btn-primary">
            <i class="fa-solid fa-print"></i> Print Tiket
        </button>
    </div>

    <div class="hasil-transaksi" id="tiketParkir">
        <h3>Detail Transaksi</h3>

        <p><strong>Area Parkir:</strong> <?= $data_tiket['area']['nama_area'] ?? '-' ?></p>
        <p><strong>Nama:</strong> <?= $data_tiket['kendaraan']['nama_lengkap'] ?? '-' ?></p>
        <p><strong>Plat Nomor:</strong> <?= $data_tiket['kendaraan']['plat_nomor'] ?? '-' ?></p>
        <p><strong>Jenis Kendaraan:</strong> <?= ucfirst($data_tiket['kendaraan']['jenis_kendaraan'] ?? '-') ?></p>
        <p><strong>Tipe:</strong> <?= ucfirst($data_tiket['kendaraan']['tipe_kendaraan'] ?? '-') ?></p>

        <?php if ($data_tiket['mode'] === 'MASUK'): ?>
            <p><strong>Status:</strong> Masuk Parkir</p>
            <p><strong>Waktu Masuk:</strong> <?= $data_tiket['waktu_masuk'] ?></p>
        <?php else: ?>
            <p><strong>Status:</strong> Keluar Parkir</p>
            <p><strong>Waktu Masuk:</strong> <?= $data_tiket['waktu_masuk'] ?></p>
            <p><strong>Waktu Keluar:</strong> <?= $data_tiket['waktu_keluar'] ?></p>
            <p><strong>Durasi:</strong> <?= $data_tiket['durasi_detail'] ?> </p>
            <p><strong>Total Bayar:</strong> Rp <?= number_format($data_tiket['total'],0,',','.') ?></p>
        <?php endif; ?>
    </div>

<?php endif; ?>

<div class="empty-space"></div>

<!-- TABLE KENDARAAN TERPARKIR -->
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Plat Nomor</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Terparkir Sejak</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>

    <?php $no = 1; ?>

    <?php if ($data_parkir && $data_parkir->num_rows > 0): ?>

        <?php while ($row = $data_parkir->fetch_assoc()): ?>

        <tr>
            <td><?= $no++ ?></td>

            <td><?= htmlspecialchars($row['plat_nomor'] ?? '-') ?></td>

            <!-- Kosong jika pengunjung -->
            <td><?= htmlspecialchars($row['nama_lengkap'] ?? '-') ?></td>

            <td><?= htmlspecialchars($row['username'] ?? '-') ?></td>

            <td><?= date('d-m-Y H:i', strtotime($row['waktu_masuk'])) ?></td>

            <td>
                <a class="action-link"
                   href="?keluar=<?= $row['id_parkir'] ?>">
                   Keluar Parkir
                </a>
            </td>
        </tr>

        <?php endwhile; ?>

    <?php else: ?>

        <tr>
            <td colspan="6" style="text-align:center;">
                Tidak ada kendaraan yang sedang parkir
            </td>
        </tr>

    <?php endif; ?>

    </tbody>
</table>

    </div>
    </main>
</div>


<script>
// Jam Digital
function updateDigitalTime() {
    const now = new Date();
    document.getElementById('current-time').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
}
setInterval(updateDigitalTime, 1000);
updateDigitalTime();

// Jam Analog
  function updateClock() {
    const now = new Date();
    const seconds = now.getSeconds();
    const minutes = now.getMinutes();
    const hours = now.getHours();

    const secondDeg = seconds * 6; // 360 / 60
    const minuteDeg = minutes * 6 + seconds * 0.1;
    const hourDeg = ((hours % 12) / 12) * 360 + (minutes / 60) * 30;

    document.getElementById("secondHand").style.transform = `translateX(-50%) rotate(${secondDeg}deg)`;
    document.getElementById("minuteHand").style.transform = `translateX(-50%) rotate(${minuteDeg}deg)`;
    document.getElementById("hourHand").style.transform = `translateX(-50%) rotate(${hourDeg}deg)`;

    const timeStr = now.toLocaleTimeString();
    document.getElementById("current-time").textContent = timeStr;
  }

  // Posisi angka secara melingkar
  function positionClockNumbers() {
    const numbers = document.querySelectorAll(".clock-number");
    const centerX = 125;
    const centerY = 125;
    const radius = 100;

    numbers.forEach(num => {
      const value = parseInt(num.dataset.number);
      const angle = ((value - 3) * 30) * (Math.PI / 180); // -3 agar jam 12 ada di atas
      const x = centerX + radius * Math.cos(angle);
      const y = centerY + radius * Math.sin(angle);
      num.style.left = `${x}px`;
      num.style.top = `${y}px`;
    });
  }

  positionClockNumbers();
  updateClock();
  setInterval(updateClock, 1000);
</script>

<!-- ================= PRINT ================= -->

<script>
function printTiket() {
    const tiket = document.getElementById("tiketParkir");

    const printWindow = window.open('', '', 'width=400,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <title>Tiket Parkir</title>
            <style>
                body { font-family: Arial; padding: 20px; }
                h3 { text-align: center; }
                p { font-size: 14px; margin: 6px 0; }
            </style>
        </head>
        <body>
            ${tiket.innerHTML}
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}
</script>

</body>
</html>