<?php
session_start();
include 'koneksi_parkir.php';

$active_page = 'login_parkir';
$message = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $message = "Username dan password harus diisi.";
    } else {

        try {
            $stmt = $conn->prepare(
                "SELECT id_user, nama_lengkap, username, password, role, status_aktif
                 FROM tb_user
                 WHERE username = ?
                 LIMIT 1"
            );

            if (!$stmt) {
                throw new Exception($conn->error);
            }

            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // 🔒 Status akun aktif?
                if ((int)$user['status_aktif'] !== 1) {
                    $message = "Akun tidak aktif. Hubungi admin.";
                }
                // 🔐 Password sudah benar?
                elseif (!password_verify($password, $user['password'])) {
                    $message = "Password salah.";
                }
                else {
                    // ✅ Login berhasil yay 😀
                    session_regenerate_id(true);

                    $_SESSION['id_user']       = $user['id_user'];
                    $_SESSION['nama_lengkap']  = $user['nama_lengkap'];
                    $_SESSION['username']      = $user['username'];
                    $_SESSION['role']          = $user['role'];

                    // Redirect sesuai role
                    if ($user['role'] === 'admin') {
                        header("Location: dashboard_parkiran.php");
                    }
                    elseif ($user['role'] === 'petugas') {
                        header("Location: transaksi_parkir.php");
                    }
                    elseif ($user['role'] === 'owner') {
                        header("Location: rekap_transaksi_parkir.php");
                    }
                    else {
                        session_destroy();
                        $message = "Role tidak dikenali.";
                    }
                    exit();
                }

            } else {
                $message = "Nama pengguna tidak ditemukan.";
            }

            $stmt->close();

        } catch (Exception $e) {
            $message = "Terjadi kesalahan sistem.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Parkir</title>
    <link rel="stylesheet" href="desain_parkir.css">

</head>

<body class="body-login">
    <div class="login-wrapper">
        
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message error" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login_parkir.php">
            <div class="form-group">
                <label for="username">Nama Pengguna:</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Kata Sandi:</label>
                <div style="position: relative;">
                <input type="password" name="password" id="password" style="padding-right: 10px;" required>
                    <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                    👁️
                </button>
            </div>

            <button type="submit">Login</button>
        </form>
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

</body>
</html>