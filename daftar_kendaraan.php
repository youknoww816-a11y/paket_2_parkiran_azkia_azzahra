<?php
include 'koneksi_parkir.php';

$active_page = 'daftar_kendaraan';

/* ===================== VAR ===================== */
$message = $_GET['message'] ?? '';
$message_type = $_GET['type'] ?? '';

$id_kendaraan = '';
$plat_nomor = '';
$jenis_kendaraan = '';
$warna = '';
$pemilik = '';
$id_user = '';

$form_action = 'add';

/* ===================== DELETE ===================== */
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM tb_kendaraan WHERE id_kendaraan = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: daftar_kendaraan.php?message=Data kendaraan berhasil dihapus&type=success");
    exit();
}

/* ===================== EDIT LOAD ===================== */
if (isset($_GET['action']) && $_GET['action'] === 'edit') {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM tb_kendaraan WHERE id_kendaraan = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if ($data) {
        $id_kendaraan     = $data['id_kendaraan'];
        $plat_nomor       = $data['plat_nomor'];
        $jenis_kendaraan  = $data['jenis_kendaraan'];
        $warna            = $data['warna'];
        $pemilik          = $data['pemilik'];
        $id_user          = $data['id_user'];
        $form_action      = 'edit';
    }
}

/* ===================== SIMPAN ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_kendaraan = $_POST['id_kendaraan'] ?? '';
    $plat_nomor   = trim($_POST['plat_nomor']);
    $jenis_kendaraan = trim($_POST['jenis_kendaraan']);
    $warna        = trim($_POST['warna']);
    $pemilik      = trim($_POST['pemilik']);
    $id_user      = intval($_POST['id_user']);
    $form_action  = $_POST['form_action'];

if ($pemilik === '') {
    $q = $conn->prepare("SELECT nama_lengkap FROM tb_user WHERE id_user = ?");
    $q->bind_param("i", $id_user);
    $q->execute();
    $q->bind_result($nama_db);
    $q->fetch();
    $q->close();

    $pemilik = $nama_db ?? '';
}

    if ($form_action === 'add') {
        $stmt = $conn->prepare("
            INSERT INTO tb_kendaraan 
            (plat_nomor, jenis_kendaraan, warna, pemilik, id_user)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssi", $plat_nomor, $jenis_kendaraan, $warna, $pemilik, $id_user);
        $stmt->execute();

        header("Location: daftar_kendaraan.php?message=Kendaraan berhasil ditambahkan&type=success");
        exit();
    }

    if ($form_action === 'edit') {
        $stmt = $conn->prepare("
            UPDATE tb_kendaraan 
            SET plat_nomor=?, jenis_kendaraan=?, warna=?, pemilik=?, id_user=?
            WHERE id_kendaraan=?
        ");
        $stmt->bind_param("ssssii", $plat_nomor, $jenis_kendaraan, $warna, $pemilik, $id_user, $id_kendaraan);
        $stmt->execute();

        header("Location: daftar_kendaraan.php?message=Data kendaraan diperbarui&type=success");
        exit();
    }
}

/* ===================== USER DROPDOWN ===================== */
$sql_user = "
    SELECT id_user, username, nama_lengkap
    FROM tb_user
    WHERE id_user NOT IN (SELECT id_user FROM tb_kendaraan)
    OR id_user = ?
";

$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ===================== DATA KENDARAAN ===================== */
$data_kendaraan = $conn->query("
    SELECT k.*, u.username 
    FROM tb_kendaraan k
    JOIN tb_user u ON k.id_user = u.id_user
    ORDER BY k.id_kendaraan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Daftar Kendaraan</title>
        <link rel="stylesheet" href="desain_parkir.css">
    </head>
    <body>
        
    <main class="main-content">
        <div class="message-area"></div>
        
        <header class="main-header">
            <h2>Manajemen Kendaraan</h2>
        </header>
        
        <div class="content-body">
            <?php if ($message): ?>
                <div class="message <?= $message_type ?>">
                    <?= $message ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="form_action" value="<?= $form_action ?>">
                    <input type="hidden" name="id_kendaraan" value="<?= $id_kendaraan ?>">
                    <input type="hidden" name="pemilik" id="pemilik">
                    
                    <div class="form-group">
                        <label>Plat Nomor</label>
                        <input type="text" name="plat_nomor" required value="<?= $plat_nomor ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Jenis Kendaraan</label>
                        <input type="text" name="jenis_kendaraan" required value="<?= $jenis_kendaraan ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Warna</label>
                        <input type="text" name="warna" required value="<?= $warna ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Nama Pemilik</label>
                        <input type="text" id="pemilik_view" value="<?= $pemilik ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>User</label>
                        <select name="id_user" id="userSelect" required>
                            <option value="">-- Pilih User --</option>
                            <?php foreach ($user_list as $u): ?>
                                <option value="<?= $u['id_user'] ?>"data-nama="<?= $u['nama_lengkap'] ?>"
                                <?= $u['id_user'] == $id_user ? 'selected' : '' ?>>
                                <?= $u['id_user'] ?> - <?= $u['username'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit"><?= $form_action === 'edit' ? 'Update' : 'Tambah' ?></button>
                    <?php if ($form_action === 'edit'): ?>
                        <button type="button" onclick="window.location='daftar_kendaraan.php'" style="background:#6c757d">Batal</button>
                    <?php endif; ?>
                </form>
                
                <hr>
                <h3>Daftar Kendaraan</h3>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Plat</th>
                        <th>Jenis</th>
                        <th>Warna</th>
                        <th>Pemilik</th>
                        <th>User</th>
                        <th>Aksi</th>
                    </tr>
                    
                    <?php while ($row = $data_kendaraan->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id_kendaraan'] ?></td>
                            <td><?= $row['plat_nomor'] ?></td>
                            <td><?= $row['jenis_kendaraan'] ?></td>
                            <td><?= $row['warna'] ?></td>
                            <td><?= $row['pemilik'] ?></td>
                            <td><?= $row['username'] ?></td>
                            <td><a a class="action-link edit-link" href="?action=edit&id=<?= $row['id_kendaraan'] ?>">Edit</a>
                            <a class="action-link delete-link" href="?action=delete&id=<?= $row['id_kendaraan'] ?>" onclick="return confirm('Hapus kendaraan?')">Hapus</a></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
    </main>
    
    <script>
    const userSelect = document.getElementById('userSelect');
    const pemilikInput = document.getElementById('pemilik');
    const pemilikView = document.getElementById('pemilik_view');
    
    userSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const nama = selected.dataset.nama || '';
        pemilikInput.value = nama;
        pemilikView.value = nama;
        });
    </script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const userSelect = document.getElementById('userSelect');
        const pemilikInput = document.getElementById('pemilik');
        const pemilikView = document.getElementById('pemilik_view');

    // kalau edit & belum ada pemilik → isi otomatis
    if (userSelect.value && pemilikInput.value === '') {
        const selected = userSelect.options[userSelect.selectedIndex];
        const nama = selected.dataset.nama || '';
        pemilikInput.value = nama;
        pemilikView.value = nama;
    }
    
    userSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const nama = selected.dataset.nama || '';
        pemilikInput.value = nama;
        pemilikView.value = nama;
        });
    });
    </script>
    
</body>
</html>