<?php
include 'koneksi_parkir.php';

$active_page = 'daftar_kendaraan';

/* ===================== VAR ===================== */
$message = $_GET['message'] ?? '';
$message_type = $_GET['type'] ?? '';

$id_kendaraan = '';
$plat_nomor = '';
$tipe_kendaraan = '';
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
        $tipe_kendaraan   = $data['tipe_kendaraan'];
        $jenis_kendaraan  = $data['jenis_kendaraan'];
        $warna            = $data['warna'];
        $pemilik          = $data['pemilik'];
        $id_user          = $data['id_user'];
        $form_action      = 'edit';
    }
}

/* ===================== SIMPAN ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_kendaraan     = intval($_POST['id_kendaraan'] ?? 0);
    $plat_nomor       = trim($_POST['plat_nomor']);
    $tipe_kendaraan   = $_POST['tipe_kendaraan'];
    $jenis_kendaraan  = trim($_POST['jenis_kendaraan']);
    $warna            = trim($_POST['warna']);
    $pemilik          = trim($_POST['pemilik']);
    $id_user          = intval($_POST['id_user']);
    $form_action      = $_POST['form_action'];

    /* Ambil nama pemilik otomatis */
    if ($pemilik === '') {
        $q = $conn->prepare("SELECT nama_lengkap FROM tb_user WHERE id_user = ?");
        $q->bind_param("i", $id_user);
        $q->execute();
        $q->bind_result($nama_db);
        $q->fetch();
        $q->close();

        $pemilik = $nama_db ?? '';
    }

    /* ===================== ADD ===================== */
    if ($form_action === 'add') {
        $stmt = $conn->prepare("
            INSERT INTO tb_kendaraan 
            (plat_nomor, tipe_kendaraan, jenis_kendaraan, warna, pemilik, id_user)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sssssi",
            $plat_nomor,
            $tipe_kendaraan,
            $jenis_kendaraan,
            $warna,
            $pemilik,
            $id_user
        );
        $stmt->execute();

        header("Location: daftar_kendaraan.php?message=Kendaraan berhasil ditambahkan&type=success");
        exit();
    }

    /* ===================== EDIT ===================== */
    if ($form_action === 'edit') {
        $stmt = $conn->prepare("
            UPDATE tb_kendaraan 
            SET plat_nomor=?, tipe_kendaraan=?, jenis_kendaraan=?, warna=?, pemilik=?, id_user=?
            WHERE id_kendaraan=?
        ");
        $stmt->bind_param(
            "sssssii",
            $plat_nomor,
            $tipe_kendaraan,
            $jenis_kendaraan,
            $warna,
            $pemilik,
            $id_user,
            $id_kendaraan
        );
        $stmt->execute();

        header("Location: daftar_kendaraan.php?message=Data kendaraan diperbarui&type=success");
        exit();
    }
}

/* ===================== USER DROPDOWN ===================== */
$user_list = $conn->query("
    SELECT 
        u.id_user,
        u.username,
        u.nama_lengkap,
        k.id_kendaraan
    FROM tb_user u
    LEFT JOIN tb_kendaraan k ON u.id_user = k.id_user
    ORDER BY u.username ASC
")->fetch_all(MYSQLI_ASSOC);

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
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link rel="stylesheet" href="desain_parkir.css">
    </head>

    <body>
        
    <main class="main-content">
        <div class="message-area"></div>
        
        <header class="main-header">
            <h2>Manajemen Kendaraan</h2>
        </header>

            <!-- TOOLBAR -->
             <div class="toolbar-parkir">
                <button type="button" class="btn-icon" data-bs-toggle="modal" data-bs-target="#modalKendaraan">
                    <i class="fa-solid fa-plus"></i>
                </button>
                
                <div class="search-wrapper">
                    <select id="searchType">
                        <option value="jenis">Jenis Kendaraan</option>
                        <option value="pemilik">Nama Pemilik</option>
                    </select>
                    
                    <input type="text" id="searchBox" placeholder="Cari Kendaraan...">
                </div>
             </div>
            
            <br>        

            <?php if ($message): ?>
                <div class="message <?= $message_type ?>">
                    <?= $message ?>
                </div>
                <?php endif; ?>

    <hr>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Plat</th>
                    <th>Tipe</th>
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
                        <td><?= ucfirst($row['tipe_kendaraan']) ?></td>
                        <td><?= $row['jenis_kendaraan'] ?></td>
                        <td><?= $row['warna'] ?></td>
                        <td><?= $row['pemilik'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td><button type="button"class="btn btn-sm btn-warning btnEdit"
                        data-id="<?= $row['id_kendaraan'] ?>"
                        data-plat="<?= $row['plat_nomor'] ?>"
                        data-tipe="<?= $row['tipe_kendaraan'] ?>"
                        data-jenis="<?= $row['jenis_kendaraan'] ?>"
                        data-warna="<?= $row['warna'] ?>"
                        data-pemilik="<?= $row['pemilik'] ?>"
                        data-user="<?= $row['id_user'] ?>">
                        Edit</button>
                        <a class="action-link delete-link" href="?action=delete&id=<?= $row['id_kendaraan'] ?>" onclick="return confirm('Hapus kendaraan?')">Hapus</a></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </main>

    <!-- MODAL TAMBAH KENDARAAN -->
<div class="modal fade" id="modalKendaraan" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kendaraan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="form_action" id="form_action" value="add">
                    <input type="hidden" name="id_kendaraan" id="id_kendaraan">
                    <input type="hidden" name="pemilik" id="pemilik">

                    <div class="mb-3">
                        <label class="form-label">Plat Nomor</label>
                        <input type="text" name="plat_nomor" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Kendaraan</label>
                        <select name="tipe_kendaraan" class="form-select" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="motor">Motor</option>
                            <option value="mobil">Mobil</option>
                            <option value="lain">Kendaraan Lain</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Kendaraan</label>
                        <input type="text" name="jenis_kendaraan" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Warna</label>
                        <input type="text" name="warna" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Pemilik</label>
                        <input type="text" id="pemilik_view" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">User</label>
                        <select name="id_user" id="userSelect" class="form-select" required>
                            <option value="">-- Pilih User --</option>
                            <?php foreach ($user_list as $u): ?>
                            <?php $dipakai = $u['id_kendaraan'] !== null;?>
                            <option value="<?= $u['id_user'] ?>"data-nama="<?= $u['nama_lengkap'] ?>"
                            <?= ($dipakai ? 'disabled' : '') ?>>
                            <?= $u['id_user'] ?> - <?= $u['username'] ?>
                            <?= ($dipakai ? ' (sudah punya kendaraan)' : '') ?>
                        </option>
                        <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Simpan
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Untuk mengisi nama otomatis dari username -->
<script>
document.getElementById('userSelect').addEventListener('change', function () {
    const nama = this.options[this.selectedIndex].dataset.nama || '';
    document.getElementById('pemilik').value = nama;
    document.getElementById('pemilik_view').value = nama;
});
</script>

<!-- Modal Form Edit -->
<script>
document.querySelectorAll('.btnEdit').forEach(btn => {
    btn.addEventListener('click', function () {

        // Ambil data dari tombol
        const id      = this.dataset.id;
        const plat    = this.dataset.plat;
        const tipe    = this.dataset.tipe;
        const jenis   = this.dataset.jenis;
        const warna   = this.dataset.warna;
        const pemilik = this.dataset.pemilik;
        const userId  = this.dataset.user;

        // Set ke form
        document.getElementById('form_action').value = 'edit';
        document.getElementById('id_kendaraan').value = id;
        document.querySelector('[name="plat_nomor"]').value = plat;
        document.querySelector('[name="tipe_kendaraan"]').value = tipe;
        document.querySelector('[name="jenis_kendaraan"]').value = jenis;
        document.querySelector('[name="warna"]').value = warna;
        document.getElementById('pemilik_view').value = pemilik;
        document.getElementById('pemilik').value = pemilik;
        document.getElementById('userSelect').value = userId;

        const userSelect = document.getElementById('userSelect');
        const selectedOption = userSelect.querySelector(`option[value="${userId}"]`);
        
        if (selectedOption) {selectedOption.disabled = false;}

        document.getElementById('userSelect').dispatchEvent(new Event('change'));

        // Ubah judul modal
        document.querySelector('.modal-title').innerText = 'Edit Kendaraan';

        // Buka modal
        const modal = new bootstrap.Modal(document.getElementById('modalKendaraan'));
        modal.show();
    });
});
</script>

<!-- Dropdown Search -->
<script>
document.getElementById('searchBox').addEventListener('keyup', function () {
    const keyword = this.value.toLowerCase();
    const type = document.getElementById('searchType').value;
    const rows = document.querySelectorAll("table tr");

    rows.forEach((row, index) => {
        if (index === 0) return; // skip header

        let cellText = "";

        if (type === "jenis") {
            cellText = row.cells[2].innerText.toLowerCase(); // kolom jenis
        } else if (type === "pemilik") {
            cellText = row.cells[4].innerText.toLowerCase(); // kolom pemilik
        }

        row.style.display = cellText.includes(keyword) ? "" : "none";
    });
});
</script>    


<!-- -->
<script>
const searchType = document.getElementById('searchType');
const searchBox = document.getElementById('searchBox');

searchType.addEventListener('change', function () {
    if (this.value === "") {
        searchBox.disabled = true;
        searchBox.value = "";
        searchBox.placeholder = "Pilih filter dulu...";
    } else {
        searchBox.disabled = false;
        searchBox.placeholder = "Cari " + this.options[this.selectedIndex].text + "...";
        searchBox.focus();
    }
});

searchBox.addEventListener('keyup', function () {
    const keyword = this.value.toLowerCase();
    const type = searchType.value;
    const rows = document.querySelectorAll("table tr");

    rows.forEach((row, index) => {
        if (index === 0) return;

        let text = "";
        if (type === "jenis") {
            text = row.cells[2].innerText.toLowerCase();
        } else if (type === "pemilik") {
            text = row.cells[4].innerText.toLowerCase();
        }

        row.style.display = text.includes(keyword) ? "" : "none";
    });
});
</script>

</body>
</html>