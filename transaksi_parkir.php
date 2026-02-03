<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
include 'koneksi_parkir.php';

$active_page = 'transaksi_parkir';
$message = '';
$message_type = '';

$data_tiket = null;
$status_terakhir = null;

$aksi = $_POST['aksi'] ?? null;
$username = trim($_POST['username'] ?? '');

/* ===============================
   AMBIL USER + KENDARAAN
   =============================== */
if ($username !== '') {

    $stmt = $conn->prepare("
        SELECT 
            k.id_kendaraan,
            k.plat_nomor,
            k.tipe_kendaraan,
            k.jenis_kendaraan,
            k.warna,
            u.id_user,
            u.nama_lengkap
        FROM tb_kendaraan k
        JOIN tb_user u ON k.id_user = u.id_user
        WHERE u.username = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $q = $stmt->get_result();

    if ($q->num_rows == 0) {
        $message = "User atau kendaraan tidak ditemukan.";
        $message_type = "error";
    } else {

        $kendaraan = $q->fetch_assoc();
        $id_kendaraan = $kendaraan['id_kendaraan'];
        $id_user      = $kendaraan['id_user'];
        $tipe         = strtolower($kendaraan['tipe_kendaraan']);

        /* ===============================
           CEK TRANSAKSI TERAKHIR
           =============================== */
        $cek = $conn->query("
            SELECT *
            FROM tb_transaksi
            WHERE id_kendaraan = $id_kendaraan
            ORDER BY id_parkir DESC
            LIMIT 1
        ");

        $parkir = $cek->fetch_assoc();
        $status_terakhir = $parkir['status'] ?? null;

        /* =================== MASUK PARKIR =================== */
        if ($aksi === 'masuk') {

            if ($status_terakhir === 'masuk') {
                $message = "Kendaraan masih berada di area parkir.";
                $message_type = "error";
            } else {

                $qArea = $conn->query("
                    SELECT id_area 
                    FROM tb_area_parkir 
                    WHERE tipe_kendaraan = '$tipe'
                    AND status_area_parkir = 'tempat kosong masih tersedia'
                    AND terisi < kapasitas
                    ORDER BY id_area ASC
                    LIMIT 1
                ");

                if ($qArea->num_rows == 0) {
                    $message = "Area parkir untuk kendaraan ini penuh.";
                    $message_type = "error";
                } else {

                    $area = $qArea->fetch_assoc();
                    $id_area = $area['id_area'];
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
                            $id_area
                        )
                    ");

                    // Tambah isi area
                    $conn->query("
                        UPDATE tb_area_parkir 
                        SET terisi = terisi + 1
                        WHERE id_area = $id_area
                    ");

                    $data_tiket = [
                        'mode' => 'MASUK',
                        'waktu_masuk' => $now,
                        'kendaraan' => $kendaraan
                    ];
                }
            }
        }

        /* =================== KELUAR PARKIR =================== */
        elseif ($aksi === 'keluar' && $status_terakhir === 'masuk') {

            $masuk  = strtotime($parkir['waktu_masuk']);
            $keluar = time();

            $durasi = ceil(($keluar - $masuk) / 3600);
            if ($durasi < 1) $durasi = 1;

            $total = 0;
            $id_tarif = 0;

            if ($tipe === 'motor') {
                $id_tarif = 1;
                if ($durasi <= 1) $total = 2000;
                elseif ($durasi < 24) $total = 2000 + (($durasi - 1) * 2000);
                elseif ($durasi == 24) $total = 15000;
                else $total = 15000 + (($durasi - 24) * 2000);
            }
            elseif ($tipe === 'mobil') {
                $id_tarif = 2;
                if ($durasi <= 1) $total = 5000;
                elseif ($durasi < 24) $total = 5000 + (($durasi - 1) * 3000);
                else $total = 20000;
            }
            else {
                $id_tarif = 3;
                if ($durasi <= 1) $total = 6000;
                elseif ($durasi < 24) $total = 6000 + (($durasi - 1) * 5000);
                else $total = 35000;
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
                AND status = 'masuk'
            ");

            // Kurangi isi area
            $conn->query("
                UPDATE tb_area_parkir 
                SET terisi = IF(terisi > 0, terisi - 1, 0)
                WHERE id_area = {$parkir['id_area']}
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
    <link rel="stylesheet" href="desain_parkir.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
</head>

<body>    
    <div class="wrapper">
        <main class="main-content">
            
        <!-- HEADER -->
         <header class="main-header"><h2>Transaksi Parkir</h2></header>

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

    <!-- Angka Jam -->
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
<div class="current-time-display">Jam : <span id="current-time"></span>
</div>
</div>

    <!-- ================= FORM TRANSAKSI ================= -->
     <form method="POST" class="form-transaksi" style="margin-bottom:20px;">
        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username..." value="<?= htmlspecialchars($username ?? '') ?>"required>
        <label>Status</label>
        <select name="aksi" required>
            <option value="">-- Pilih Status --</option>
            <option value="masuk" <?= ($status_terakhir === 'masuk') ? 'disabled' : '' ?>>Masuk Parkir</option>
            <option value="keluar" <?= (!$status_terakhir || $status_terakhir === 'keluar') ? 'disabled' : '' ?>>Keluar Parkir</option>
        </select>

        <button type="submit">OK</button>
    </form>

        <!-- ================= HASIL TRANSAKSI ================= -->
    <?php if (!empty($data_tiket)): ?>
        <div class="hasil-transaksi">
            <h3>Detail Transaksi</h3>
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
                <p><strong>Durasi:</strong> <?= $data_tiket['durasi'] ?> jam</p>
                <p><strong>Total Bayar:</strong> Rp <?= number_format($data_tiket['total'],0,',','.') ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <!-- =================================================== -->

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

</body>
</html>