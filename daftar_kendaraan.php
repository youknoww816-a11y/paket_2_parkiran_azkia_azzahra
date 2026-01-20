<?php
include 'koneksi.php';

$active_page = 'daftar_kendaraan';

/* ===================== Place Holder ===================== 
$query = $conn->query("SELECT tema, tampilan_menu FROM tb_tampilan LIMIT 1");
$row = $query->fetch_assoc();
$tema_global = $row['tema'] ?? 'normal';
$menu_mode_global = $row['tampilan_menu'] ?? 'sidebar';

if (isset($_COOKIE['theme'])) $tema_global = $_COOKIE['theme'];
if (isset($_COOKIE['menu_mode'])) $menu_mode_global = $_COOKIE['menu_mode'];
*/

/* ===================== VAR ===================== */
$message = '';
$message_type = '';

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

    header("Location: daftar_kendaraan.php?message=Data kendaraan dihapus&type=success");
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
        $id_kendaraan = $data['id_kendaraan'];
        $plat_nomor = $data['plat_nomor'];
        $jenis_kendaraan = $data['jenis_kendaraan'];
        $warna = $data['warna'];
        $pemilik = $data['pemilik'];
        $id_user = $data['id_user'];
        $form_action = 'edit';
    }
}

/* ===================== SIMPAN ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_kendaraan = $_POST['id_kendaraan'] ?? '';
    $plat_nomor = trim($_POST['plat_nomor']);
    $jenis_kendaraan = trim($_POST['jenis_kendaraan']);
    $warna = trim($_POST['warna']);
    $pemilik = trim($_POST['pemilik']);
    $id_user = intval($_POST['id_user']);
    $form_action = $_POST['form_action'];

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

/* ===================== DATA USER (DROPDOWN) ===================== */
$user_list = [];
$sql_user = "
    SELECT id_user, nama_lengkap 
    FROM tb_user 
    WHERE id_user NOT IN (
        SELECT id_user FROM tb_kendaraan
    )
    OR id_user = ?
";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ===================== DATA KENDARAAN ===================== */
$data_kendaraan = $conn->query("
    SELECT k.*, u.nama_lengkap 
    FROM tb_kendaraan k
    JOIN tb_user u ON k.id_user = u.id_user
    ORDER BY k.id_kendaraan DESC
");
?>

<!DOCTYPE html>
<html lang="id" class="<?= $tema_global ?>">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kendaraan</title>
    <link rel="stylesheet" href="desain.css">
</head>
<body>

<div class="wrapper">
<?php include ($menu_mode_global === 'ribbon' ? 'ribbon.php' : 'sidebar.php'); ?>

<main class="main-content">
<header class="main-header">
    <h2>Manajemen Kendaraan</h2>
</header>

<div class="content-body">

<form method="POST">
<input type="hidden" name="form_action" value="<?= $form_action ?>">
<input type="hidden" name="id_kendaraan" value="<?= $id_kendaraan ?>">

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
    <label>Pemilik</label>
    <input type="text" name="pemilik" required value="<?= $pemilik ?>">
</div>

<div class="form-group">
    <label>User</label>
    <select name="id_user" required>
        <option value="">-- Pilih User --</option>
        <?php foreach ($user_list as $u): ?>
            <option value="<?= $u['id_user'] ?>" <?= $u['id_user'] == $id_user ? 'selected' : '' ?>>
                <?= $u['id_user'] ?> - <?= $u['nama_lengkap'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<button type="submit"><?= $form_action === 'edit' ? 'Update' : 'Tambah' ?></button>
<?php if ($form_action === 'edit'): ?>
    <button type="button" onclick="window.location='daftar_kendaraan.php'">Batal</button>
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
    <td><?= $row['nama_lengkap'] ?></td>
    <td>
        <a href="?action=edit&id=<?= $row['id_kendaraan'] ?>">Edit</a>
        <a href="?action=delete&id=<?= $row['id_kendaraan'] ?>" onclick="return confirm('Hapus kendaraan?')">Hapus</a>
    </td>
</tr>
<?php endwhile; ?>
</table>

</div>
</main>
</div>
</body>
</html>