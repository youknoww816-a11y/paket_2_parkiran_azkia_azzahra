<?php
session_start();
include 'koneksi_parkir.php';
$active_page = 'login_parkir';

$message = '';
$username = ''; 

/* Placeholder tema yang 100% aku enggak bakal pake
$query = $conn->query("SELECT tema, tampilan_menu FROM tb_tampilan LIMIT 1");
if ($row = $query->fetch_assoc()) {
    $tema_global = $row['tema'];
    $menu_mode_global = $row['tampilan_menu'];

    if (isset($_COOKIE['theme'])) {
    $tema_global = $_COOKIE['theme'];
}
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $message = "Username dan password harus diisi.";
    } else {
        try {
            $stmt = $conn->prepare(
                "SELECT id_user, nama_lengkap, username, password, role, status_aktif FROM tb_user WHERE username = ?"
            );
            if (!$stmt) {
                throw new Exception("Prep stmt gagal: " . $conn->error);
            }

            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: dashboard_parkiran.php");
        } else {
            header("Location: transaksi_parkir.php");
        }

        exit();
    } else {
        $message = "Password salah.";
    }

} else {
    $message = "Nama pengguna tidak ditemukan.";
}
        } catch (Exception $e) {
            $message = "Terjadi kesalahan sistem. Silakan coba lagi nanti.";
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
                
            }   
        }
    }
}
?>

<!-- Placeholder tema (kayanya enggak sempet) -->
<?php
// Tambahkan ini sebelum <html>
//$tema_class = ($tema_global === 'dark') ? 'dark-mode' : str_replace(' ', '-', $tema_global);
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

    <script>
    // Toggle 👁️ untuk password
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        this.textContent = type === 'password' ? '👁️' : '🙈';
    });
</script>

</body>
</html>