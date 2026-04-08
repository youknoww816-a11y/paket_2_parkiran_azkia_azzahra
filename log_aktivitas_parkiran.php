<?php
date_default_timezone_set('Asia/Jakarta');

$active_page = 'log_aktivitas_parkiran';

include 'koneksi_parkir.php';
include 'proteksi_role_parkir.php';

/* ===============================
   1. FILTER TANGGAL
================================ */

if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {

    $start_date = $_GET['start_date'];
    $end_date   = $_GET['end_date'];

} elseif (!empty($_GET['month'])) {

    $start_date = date('Y-m-01', strtotime($_GET['month']));
    $end_date   = date('Y-m-t', strtotime($_GET['month']));

} else {

    $start_date = date('Y-m-01');
    $end_date   = date('Y-m-t');
}

$start_datetime = $start_date . ' 00:00:00';
$end_datetime   = $end_date . ' 23:59:59';

$start_date_month = $start_date;
$end_date_month   = $end_date;

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
    t.id_kendaraan,
    t.waktu_masuk,
    t.waktu_keluar,
    t.status,
    t.durasi_jam,
    t.biaya_total,

    CASE 
    WHEN t.id_kendaraan IS NULL THEN 'Pengunjung'
    ELSE u.username
END AS username,

CASE 
    WHEN t.id_kendaraan IS NULL THEN 'Pengunjung'
    ELSE u.nama_lengkap
END AS nama_lengkap,

    -- Ambil plat nomor dari kendaraan atau input manual
    COALESCE(k.plat_nomor, t.plat_nomor, t.plat_nomor_tamu) AS plat_nomor,

    CASE 
    WHEN t.id_kendaraan IS NULL THEN 'manual'
    ELSE k.tipe_kendaraan
END AS tipe_kendaraan,

CASE 
    WHEN t.id_kendaraan IS NULL THEN ''
    ELSE k.jenis_kendaraan
END AS jenis_kendaraan,

CASE 
    WHEN t.id_kendaraan IS NULL THEN ''
    ELSE k.warna
END AS warna,

    COALESCE(k.pemilik, '-') AS pemilik,

    a.nama_area,

    tr.tarif_per_jam,

    CASE
    WHEN t.waktu_keluar IS NULL THEN
        CONCAT(
            'Masuk: ',
            DATE_FORMAT(t.waktu_masuk, '%d %b %Y %H:%i'),
            '\nStatus: Masih Terparkir'
        )
    ELSE
        CONCAT(
            'Masuk: ',
            DATE_FORMAT(t.waktu_masuk, '%d %b %Y %H:%i'),
            '\nKeluar: ',
            DATE_FORMAT(t.waktu_keluar, '%d %b %Y %H:%i')
        )
END AS aktivitas

FROM tb_transaksi t
LEFT JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
LEFT JOIN tb_user u ON t.id_user = u.id_user
LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area
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

// Kendaraan masih terparkir (dari hari kapanpun itu selama kendaraan belum keluar)
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

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Log Laporan Parkiran | Aktivitas Bulan Ini</title>
    <link rel="stylesheet" href="desain_parkir.css">
        <link rel="stylesheet" href="desain_parkir.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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

<?php if ($_SESSION['role'] !== 'owner'): ?>

<h3 class>Ringkasan (<?php echo date('d F Y',strtotime($start_date_month)).' s/d '.date('d F Y',strtotime($end_date_month)); ?>)</h3>

<div class="report-summary-cards">
    <div class="summary-card"><h4>Total Kendaraan Masuk</h4><p><?php echo $g_total_kendaraan_masuk;?> </p></div>
    <div class="summary-card"><h4>Total Kendaraan Keluar</h4><p><?php echo $g_total_kendaraan_keluar;?> </p></div>
    <div class="summary-card"><h4>Total Kendaraan Yang Masih Terparkir</h4><p><?php echo $g_total_kendaraan_terparkir;?></p></div>
    <div class="summary-card"><h4>Total Transaksi</h4><p>Rp <?= number_format($g_total_transaksi, 0, ',', '.') ?></p></div>
</div>

<?php endif; ?>

</div>

<div class="empty-space"></div>

    <!-- TOOLBAR -->
     <div class="toolbar-parkir">
        <button id="btnReset" data-tooltip="Reset" ><i class="fa-solid fa-rotate-right"></i></button>

        <div class="filter-container">
            <button id="btnFilter" data-tooltip="Sortir"><i class="fa-solid fa-filter"></i></button>
            <div id="filterMenu" class="filter-menu hidden">
                <button data-sort="mobil">Kendaraan Mobil</button>
                <button data-sort="motor">Kendaraan Motor</button>
                <button data-sort="lainnya">Kendaraan Lainnya</button>
                <button data-sort="manual">Kendaraan Tidak Terdaftar</button>
                <button data-sort="">Tanggal Terlama</button>
            </div>
        </div>

        <button id="btnExportExcel" data-tooltip="Ekspor ke Excel"><i class="fa-solid fa-file-excel"></i></button>
        
        <button id="btnExportPDF" data-tooltip="Download PDF"><i class="fa-solid fa-file-pdf"></i></button>
        
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
                <table id="tabelLogParkir" class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Nama Pemilik</th>
                            <th>Plat Nomor</th>
                            <th>Jenis</th>
                            <th>Tipe Kendaraan</th>
                            <th>Warna</th>
                            <th>Status</th>
                            <th>Tarif</th>
                            <th>Total Biaya</th>
                            <th>Durasi</Th>
                            <th>Area parkir</th>
                        </tr>
                    </thead>
                    <tbody>

<?php if ($result_aktivitas && $result_aktivitas->num_rows > 0): ?>
    <?php while ($row = $result_aktivitas->fetch_assoc()): ?>

    <?php
    
        $masuk = strtotime($row['waktu_masuk']);
        $keluar = $row['waktu_keluar'] ? strtotime($row['waktu_keluar']) : time();
        
        $selisih = $keluar - $masuk;
        
        $hari  = floor($selisih / 86400);
        $jam   = floor(($selisih % 86400) / 3600);
        $menit = floor(($selisih % 3600) / 60);
        
        $durasi = '';
        
        if ($hari > 0) {
            $durasi .= $hari . ' hari ';
        }
        
        if ($jam > 0) {
            $durasi .= $jam . ' jam ';
        }
        
        if ($menit > 0) {
            $durasi .= $menit . ' menit';
        }
        
        if ($durasi === '') {
            $durasi = '0 menit';
        }

        ?>

        <?php
        $isManual = empty($row['id_kendaraan']);
        
        if ($isManual) {
            $tarif = 4000;
        
        } else {        
            $tipe = strtolower($row['tipe_kendaraan']);
            if ($tipe === 'motor') {
                $tarif = 2000;
            
            } elseif ($tipe === 'mobil') {
                $tarif = 5000;
            
            } elseif ($tipe === 'lainnya') {
                $tarif = 6000;
            
            } else {
                $tarif = 0; // fallback kalau data aneh
            }
        }
    ?>

        <tr>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
            <td><?= htmlspecialchars($row['plat_nomor']) ?></td>
            <td><?= htmlspecialchars($row['tipe_kendaraan']) ?></td>
            <td><?= htmlspecialchars($row['jenis_kendaraan']) ?></td>
            <td><?= htmlspecialchars($row['warna']) ?></td>
            <td style="white-space: pre-line;"><?= htmlspecialchars($row['aktivitas']) ?></td>
            <td>Rp <?= number_format($tarif, 0, ',', '.') ?></td>
            <td><?php if ($row['status'] === 'keluar'): ?>Rp <?= number_format($row['biaya_total'], 0, ',', '.') ?><?php else: ?><em>Kendaraan masih terparkir</em><?php endif; ?></td>
            <td><?= $durasi ?></td>
            <td><?= htmlspecialchars($row['nama_area']) ?></td>
            
        </tr>

    <?php endwhile; ?>

    <?php else: ?>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-center text-muted">
                Tidak ada data aktivitas pada periode ini
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    <?php endif; ?>

        </tbody>
    </table>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<!-- Toolbar agar bisa bekerja -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchBox   = document.getElementById('searchBox');
    const searchType  = document.getElementById('searchType');
    const btnFilter   = document.getElementById('btnFilter');
    const filterMenu  = document.getElementById('filterMenu');
    const btnReset    = document.getElementById('btnReset');

    const monthInput  = document.getElementById('month');
    const startDate   = document.getElementById('start_date');
    const endDate     = document.getElementById('end_date');

    const tbody = document.querySelector("#tabelLogParkir tbody");
    let rows    = Array.from(tbody.querySelectorAll("tr"));

    const originalRows = rows.map(row => row.cloneNode(true));

/* ===================== SEARCH ====================== */

    searchBox.addEventListener('keyup', function () {
        const keyword = this.value.toLowerCase();
        const type    = searchType.value;

        rows.forEach(row => {
            let text = "";

            switch (type) {
                case "username":   text = row.cells[0].innerText; break;
                case "pemilik":    text = row.cells[1].innerText; break;
                case "plat_nomor": text = row.cells[2].innerText; break;
                case "jenis":      text = row.cells[3].innerText; break;
                case "warna":      text = row.cells[5].innerText; break;
                default:
                    row.style.display = "";
                    return;
            }

            row.style.display = text.toLowerCase().includes(keyword) ? "" : "none";
        });
    });

    searchType.addEventListener('change', function () {
        searchBox.value = "";
        searchBox.focus();
    });

/* ===================== SORT / FILTER ====================== */

    document.querySelectorAll('#filterMenu button').forEach(btn => {
        btn.addEventListener('click', function () {
            const filter = this.dataset.sort;

            rows.forEach(row => {
                const tipe = row.cells[3].innerText.toLowerCase();
                
                if (filter === "manual") {
                    row.style.display = (tipe === "manual") ? "" : "none";

                } else if (filter === "mobil" || filter === "motor" || filter === "lainnya") {
                    row.style.display = (tipe === filter) ? "" : "none";

                } else if (!filter) {
                    row.style.display = "";

                } else {
                    row.style.display = "none";
                }
            });

            /* SORT TANGGAL TERLAMA */
            if (filter === "") {
                rows.sort((a, b) => {
                    return new Date(a.cells[6].innerText) - new Date(b.cells[6].innerText);
                });

                rows.forEach(row => tbody.appendChild(row));
            }

            filterMenu.classList.add('hidden');
        });
    });

/* ===================== TOGGLE MENU ====================== */

    btnFilter.addEventListener('click', function (e) {
        e.stopPropagation();
        filterMenu.classList.toggle('hidden');
    });

    document.addEventListener('click', function () {
        filterMenu.classList.add('hidden');
    });

    filterMenu.addEventListener('click', function (e) {
        e.stopPropagation();
    });

/* ===================== RESET ====================== */
    
    btnReset.addEventListener('click', function () {

        monthInput.value = "";
        startDate.value  = "";
        endDate.value    = "";

        searchBox.value = "";
        searchType.value = "jenis";

        tbody.innerHTML = "";
        originalRows.forEach(row => tbody.appendChild(row.cloneNode(true)));

        rows = Array.from(tbody.querySelectorAll("tr"));

        filterMenu.classList.add('hidden');
    });

});
</script>

<script>
$(document).ready(function(){

    var table = $('#tabelLogParkir').DataTable({
        paging: false,
        dom: 'lrt',
        order: [[0, 'asc']],
        retrieve: true
    });

    new $.fn.dataTable.Buttons(table, {
    buttons: [
        {
            extend: 'excelHtml5',
            title: 'Log Aktivitas Parkiran',
            exportOptions: {
                columns: ':visible',
                rows: function (idx, data, node) {
                    return $(node).css('display') !== 'none';
                }
            }
        },
        {
            extend: 'pdfHtml5',
            title: 'Log Aktivitas Parkiran',
            orientation: 'landscape',
            pageSize: 'A4',
            exportOptions: {
                columns: ':visible',
                rows: function (idx, data, node) {
                    return $(node).css('display') !== 'none';
                }
            }
        }
    ]
});

    table.buttons().container().appendTo('#hiddenButtons');

    $('#btnExportExcel').on('click', function(){
        table.button('.buttons-excel').trigger();
    });

    $('#btnExportPDF').on('click', function(){
        table.button('.buttons-pdf').trigger();
    });

});
</script>

</body>
</html>