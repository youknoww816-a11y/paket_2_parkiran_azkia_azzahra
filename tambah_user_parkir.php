<?php
$active_page = 'tambah_user_parkir.php';

include 'koneksi_parkir.php';
include 'proteksi_role_parkir.php';

$message = '';
$type = '';

/* ================= DELETE ================= */
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM tb_user WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: tambah_user_parkir.php?message=User berhasil dihapus&type=success");
    exit();
}

/* ================= EDIT LOAD ================= */
$edit_mode = false;
$edit_id = $edit_nama = $edit_username = $edit_role = '';

if (isset($_GET['action']) && $_GET['action'] === 'edit') {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM tb_user WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $edit_mode = true;
        $edit_id = $row['id_user'];
        $edit_nama = $row['nama_lengkap'];
        $edit_username = $row['username'];
        $edit_role = $row['role'];
    }
}

/* ================= SIMPAN ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $edit_id = intval($_POST['edit_id'] ?? 0);
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    /* ===== MODE EDIT ===== */
    if ($edit_id > 0) {

        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                UPDATE tb_user 
                SET nama_lengkap=?, username=?, password=?, role=? 
                WHERE id_user=?
            ");
            $stmt->bind_param("ssssi", $nama, $username, $hash, $role, $edit_id);
        } else {
            $stmt = $conn->prepare("
                UPDATE tb_user 
                SET nama_lengkap=?, username=?, role=? 
                WHERE id_user=?
            ");
            $stmt->bind_param("sssi", $nama, $username, $role, $edit_id);
        }

        $stmt->execute();
        header("Location: tambah_user_parkir.php?message=User berhasil diperbarui&type=success");
        exit();
    }
    
    /* ===== MODE TAMBAH ===== */
    if (empty($password)) {
        header("Location: tambah_user_parkir.php?message=Password wajib diisi&type=error");
        exit();
    }
    
    $cek = $conn->prepare("SELECT id_user FROM tb_user WHERE username=?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $cek->store_result();
    
    if ($cek->num_rows > 0) {
        header("Location: tambah_user_parkir.php?message=Username sudah digunakan&type=error");
        exit();
    }
    
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("
    INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif)
    VALUES (?, ?, ?, ?, 1)
    ");
    
    $stmt->bind_param("ssss", $nama, $username, $hash, $role);
    $stmt->execute(); // ⬅️ INI YANG TADI HILANG
    
    header("Location: tambah_user_parkir.php?message=User berhasil ditambahkan&type=success");
    exit();


}
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Tambah User Parkir</title>
        <link rel="stylesheet" href="desain_parkir.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    </head>

<body>
    <div class="wrapper">
        
        <main class="main-content">
            
        <header class="main-header"><h2>Tambah User Parkir</h2></header>
        
        <main class="main-content">
            <div class="message-area"></div>
            <form method="POST" action="tambah_user_parkir.php">

        
    <?php include 'sidebar_parkiran.php'; ?>
    
    <!-- FORM -->
     <form method="POST">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="edit_id" value="<?= $edit_id ?>">
        <?php endif; ?>

    <label>Nama Lengkap</label>
    <input type="text" name="nama" value="<?= $edit_nama ?>" required>

    <label>Username</label>
    <input type="text" name="username" value="<?= $edit_username ?>" required>

    <label for="password">Password: <?php echo $edit_mode ? '(Kosongkan jika tidak ingin mengubah)' : ''; ?></label><br>
    <div style="position: relative;">
        <input type="password" name="password" id="password" style="padding-right: 10px;">
        <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
            👁️
        </button>
    </div>
    <br><br>

    <label>Role</label>
    <select name="role">
        <option value="admin" <?= $edit_role=='admin'?'selected':'' ?>>Admin</option>
        <option value="petugas" <?= $edit_role=='petugas'?'selected':'' ?>>Petugas</option>
        <option value="owner" <?= $edit_role=='owner'?'selected':'' ?>>Owner</option>
    </select>
    
    <button type="submit"><?= $edit_mode ? 'Update' : 'Tambah' ?></button>
    
    <?php if ($edit_mode): ?>
        <button type="button" onclick="window.location.href='tambah_user_parkir.php'" style="background-color: #6c757d;">Batal Edit</button>
        <?php endif; ?>
    </form>
        

     <hr>
     <h3>Daftar User</h3>
     <div class="toolbar-parkir">
        <div class="search-wrapper">
            <select id="searchType">
                <option value="username">Username</option>
                <option value="pemilik">Nama</option>
            </select>        
            
            <input type="text" id="searchBox" placeholder="Cari...">
        </div>
    </div>

    <!-- TABLE -->
     <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        
        <?php
        $no = 1;
        $data = $conn->query("SELECT * FROM tb_user ORDER BY nama_lengkap");
        while ($row = $data->fetch_assoc()):
        ?>
        
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= $row['role'] ?></td>
            <td><a class="action-link edit-link" href="?action=edit&id=<?= $row['id_user'] ?>">Edit</a>
                <a class="action-link delete-link" href="?action=delete&id=<?= $row['id_user'] ?>" onclick="return confirm('Hapus user ini?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</main>
</div>

<!-- Agar input password bisa dilihat  -->
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        this.textContent = type === 'password' ? '👁️' : '🙈';
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const message = urlParams.get('message');
        const type = urlParams.get('type');

        if (message && type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            messageDiv.textContent = decodeURIComponent(message);

            const contentBody = document.querySelector('.message-area'); 
            if (contentBody) {
                contentBody.appendChild(messageDiv);
            }

            history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>

<!--Searchbox/Searchbar. . . intinya cari -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchBox = document.getElementById("searchBox");
    const searchType = document.getElementById("searchType");
    const tableRows = document.querySelectorAll("table tbody tr");

    searchBox.addEventListener("keyup", function () {
        const keyword = searchBox.value.toLowerCase();
        const type = searchType.value;

        tableRows.forEach(row => {
            let text = "";

            if (type === "username") {
                // Username (kolom ke-3)
                text = row.cells[2].innerText.toLowerCase();
            } else if (type === "pemilik") {
                // Nama Lengkap (kolom ke-2)
                text = row.cells[1].innerText.toLowerCase();
            }

            row.style.display = text.includes(keyword) ? "" : "none";
        });
    });
});
</script>


</body>
</html>

<!-- Note :
        Yup, password user itu gampang untuk diubah KALAU kamu itu admin
        makanya kita perlu orang yang jujur dan hati-hati jangan sampai kaya pem-(isi sendiri)
-->