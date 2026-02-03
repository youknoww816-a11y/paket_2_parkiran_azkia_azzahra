<?php
session_start();
include 'koneksi_parkir.php';

$active_page = 'area_parkir';
$message = '';
$message_type = '';

/* ================== TAMBAH AREA PARKIR ================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_area'])) {

    $nama = $_POST['nama_area'];
    $kapasitas = $_POST['kapasitas'];
    $tipe = $_POST['tipe_kendaraan'];
    $status = $_POST['status_area'];

    $stmt = $conn->prepare("
    INSERT INTO tb_area_parkir 
    (nama_area, tipe_kendaraan, kapasitas, terisi, status_area_parkir)
    VALUES (?, ?, ?, 0, ?)
    ");
    $stmt->bind_param("ssis", $nama, $tipe, $kapasitas, $status);

    if ($stmt->execute()) {
        header("Location: area_parkir.php?msg=tambah_sukses");
    } else {
        header("Location: area_parkir.php?msg=tambah_gagal");
    }
    exit;
}

/* ================== UPDATE STATUS AREA ================== */
/* Note: Cuma bisa mengubah status dan Kapasitas saja */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_area'])) {

    $id_area = $_POST['id_area'];
    $status = $_POST['status_area'];
    $kapasitas = $_POST['kapasitas'];

    $stmt = $conn->prepare("
        UPDATE tb_area_parkir 
        SET status_area_parkir = ?, kapasitas = ?
        WHERE id_area = ?
    ");
    $stmt->bind_param("sii", $status, $kapasitas, $id_area);

    if ($stmt->execute()) {
        header("Location: area_parkir.php?msg=update_sukses");
    } else {
        header("Location: area_parkir.php?msg=update_gagal");
    }
    exit;
}

/* ================== HAPUS AREA PARKIR ================== */
if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    $stmt = $conn->prepare("DELETE FROM tb_area_parkir WHERE id_area = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: area_parkir.php?msg=hapus_sukses");
    } else {
        header("Location: area_parkir.php?msg=hapus_gagal");
    }
    exit;
}

// ================== MESSAGE ==================
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'tambah_sukses':
            $message = "Area parkir berhasil ditambahkan";
            $message_type = "success";
            break;

        case 'tambah_gagal':
            $message = "Gagal menambahkan area parkir";
            $message_type = "danger";
            break;

        case 'update_sukses':
            $message = "Area parkir berhasil diperbarui";
            $message_type = "success";
            break;

        case 'update_gagal':
            $message = "Gagal memperbarui area";
            $message_type = "danger";
            break;

        case 'hapus_sukses':
            $message = "Area parkir berhasil dihapus";
            $message_type = "success";
            break;

        case 'hapus_gagal':
            $message = "Gagal menghapus area";
            $message_type = "danger";
            break;
    }
}

// ================== FILTER STATUS ==================
    $status_filter = $_GET['status'] ?? '';
    $where = '';
    
    if ($status_filter == 'penuh') {
        $where = "WHERE terisi >= kapasitas";
    } elseif ($status_filter == 'ditutup') {
        $where = "WHERE status_area_parkir = 'ditutup'";
    } elseif ($status_filter == 'tersedia') {
        $where = "WHERE terisi < kapasitas AND status_area_parkir != 'ditutup'";
    }

/* ================== DATA AREA PARKIR ================== */
    $area = $conn->query("
    SELECT *,
    CASE 
    WHEN terisi >= kapasitas THEN 'penuh'
    WHEN status_area_parkir = 'ditutup' THEN 'ditutup'
    ELSE 'tempat kosong masih tersedia'
    END AS status_final
    FROM tb_area_parkir
    $where
    ");
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Area Parkir</title>
        <link rel="stylesheet" href="desain_parkir.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="wrapper">
            <main class="main-content">
                
    <!-- HEADER -->
     <header class="main-header">
        <h2>Manajemen Area Parkir</h2>
    </header>

    <!-- MESSAGE -->
     <?php if ($message): ?>
        <div class="message <?= $message_type ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- ================= PLACEHOLDER, JANGAN DULU DIPAKE! =================
        ================== VISUAL AREA PARKIR ==================

     <div class="card mb-4">
        <div class="card-header">
            <strong>Visual Area Parkir</strong>
        </div>
        
        <div class="card-body">
            <div class="area-visual d-flex flex-wrap gap-3">
                <//?php foreach ($area as $a): 
                    $class = $a['status_final'] == 'penuh' ? 'full' :
                            ($a['status_final'] == 'ditutup' ? 'closed' : 'empty');
                ?>
                    <div class="area-box <//?= $class ?>">
                        <div class="icon">🚗</div>
                        <strong><//?= $a['nama_area'] ?></strong>
                        <div class="info">
                            <//?= $a['terisi'] ?> / <//?= $a['kapasitas'] ?><br>
                            <//?= strtoupper($a['status_final']) ?>
                        </div>
                    </div>
                <//?php endforeach; ?>
            </div>
        </div>
    </div>
                -->
        <div class="row">
                <div class="p-4">

        <!-- Filter Status -->
         <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="tersedia" <?= $status_filter == 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="penuh" <?= $status_filter == 'penuh' ? 'selected' : '' ?>>Penuh</option>
                        <option value="ditutup" <?= $status_filter == 'ditutup' ? 'selected' : '' ?>>Ditutup</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <a href="area_parkir.php" class="btn btn-outline-secondary"><i class="fas fa-refresh me-2"></i>Reset Filter</a>
                </div>
        <!-- Modal Form Tambah Area Parkir -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fas fa-plus"></i> Tambah Area Parkir</button>
            </div>
        </form>
    </div>
    
            <div class="modal fade" id="modalTambah" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Area Parkir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Area</label>
                        <input type="text" name="nama_area" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Kendaraan</label>
                        <select name="tipe_kendaraan" class="form-select" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="motor">Motor</option>
                            <option value="mobil">Mobil</option>
                            <option value="lainnya">Kendaraan Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kapasitas</label>
                        <input type="number" name="kapasitas" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status_area" class="form-select">
                            <option value="tempat kosong masih tersedia">Tersedia</option>
                            <option value="ditutup">Ditutup</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_area" class="btn btn-primary">Simpan</button>
                </div>
            
            </form>
        </div>
    </div>
</div>

<!-- ================= TABLE AREA ================= -->
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Area</th>
                            <th>Khusus Kendaraan</th>
                            <th>Kapasitas</th>
                            <th>Terisi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    
                    <tbody>
                    
                    <?php
                    $area->data_seek(0);
                    while ($row = $area->fetch_assoc()):
                        $status_badge =
                        $row['status_final'] == 'penuh' ? 'danger' :
                        ($row['status_final'] == 'ditutup' ? 'secondary' : 'success');
                    ?>
                    
                    <tr>
                        <td><?= $row['id_area'] ?></td>
                        <td><?= $row['nama_area'] ?></td>
                        <td><?= $row['tipe_kendaraan'] ?></td>
                        <td><?= $row['kapasitas'] ?></td>
                        <td><?= $row['terisi'] ?></td>
                        <td><span class="badge bg-<?= $status_badge ?>"><?= $row['status_final'] ?></span></td>    
                        <td><button class="btn btn-sm btn-warning"data-bs-toggle="modal"data-bs-target="#edit<?= $row['id_area'] ?>">Update</button>
                        <a href="area_parkir.php?hapus=<?= $row['id_area'] ?>"class="btn btn-sm btn-danger"onclick="return confirm('Yakin hapus area ini?')">Hapus</a>
                        </td>
                    </tr>

                    <div class="modal fade" id="edit<?= $row['id_area'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Area Parkir</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                
                                <div class="modal-body">
                                    <input type="hidden" name="id_area" value="<?= $row['id_area'] ?>">
                                    <div class="mb-3">
                                        <label>Kapasitas</label>
                                        <input type="number" name="kapasitas"
                                        value="<?= $row['kapasitas'] ?>"
                                        class="form-control" required>
                                    </div>
                                    
                                <div class="mb-3">
                                    <label>Status</label>
                                    <select name="status_area" class="form-select">
                                        <option value="tempat kosong masih tersedia"
                                        <?= $row['status_area_parkir'] == 'tempat kosong masih tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                        <option value="ditutup"
                                        <?= $row['status_area_parkir'] == 'ditutup' ? 'selected' : '' ?>>Ditutup</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" name="update_area" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>        
        </div>
    </div>
</div>
            
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</div>
</div>

</main>
</div>
</body>
</html>