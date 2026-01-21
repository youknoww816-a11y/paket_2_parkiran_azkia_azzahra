<?php
session_start();
include 'koneksi_parkir.php';

$active_page = 'area_parkir';
$message = '';
$message_type = '';

/* ================== UPDATE STATUS AREA ================== */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_area'])) {
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
        $message = "Area parkir berhasil diperbarui";
        $message_type = "success";
    } else {
        $message = "Gagal memperbarui area";
        $message_type = "danger";
    }
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
        ");
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Area Parkir</title>
        <link rel="stylesheet" href="desain_parkir.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <div class="alert alert-<?= $message_type ?> mt-3">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- ================= VISUAL AREA PARKIR ================= -->
     <div class="card mb-4">
        <div class="card-header">
            <strong>Visual Area Parkir</strong>
        </div>
        
        <div class="card-body">
            <div class="area-visual d-flex flex-wrap gap-3">
                <?php foreach ($area as $a): 
                    $class = $a['status_final'] == 'penuh' ? 'full' :
                            ($a['status_final'] == 'ditutup' ? 'closed' : 'empty');
                ?>
                    <div class="area-box <?= $class ?>">
                        <div class="icon">🚗</div>
                        <strong><?= $a['nama_area'] ?></strong>
                        <div class="info">
                            <?= $a['terisi'] ?> / <?= $a['kapasitas'] ?><br>
                            <?= strtoupper($a['status_final']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ================= TABLE AREA ================= -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Manajemen Area Parkir</h5>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Area</th>
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
                        <td><?= $row['kapasitas'] ?></td>
                        <td><?= $row['terisi'] ?></td>
                        <td><span class="badge bg-<?= $status_badge ?>"><?= $row['status_final'] ?></span></td>    
                        <td><button class="btn btn-sm btn-warning"data-bs-toggle="modal"data-bs-target="#edit<?= $row['id_area'] ?>">Edit</button></td>
                    </tr>
                    
                    <!-- MODAL -->
                     <div class="modal fade" id="edit<?= $row['id_area'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST">

                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Area <?= $row['nama_area'] ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                
                                <div class="modal-body">
                                    <input type="hidden" name="id_area" value="<?= $row['id_area'] ?>">
                                    <div class="mb-3">
                                                <label class="form-label">Kapasitas</label>
                                                <input type="number" name="kapasitas"
                                                    class="form-control"
                                                    value="<?= $row['kapasitas'] ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status_area" class="form-select">
                                                    <option value="tempat kosong masih tersedia">Tersedia</option>
                                                    <option value="penuh">Penuh</option>
                                                    <option value="ditutup">Ditutup</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Batal
                                            </button>
                                            <button type="submit" name="update_area" class="btn btn-primary">
                                                Simpan
                                            </button>
                                        </div>
                                    </form>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>