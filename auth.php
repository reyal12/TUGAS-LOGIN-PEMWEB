<?php
session_start();

if (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit();
}
require 'koneksi.php';
$pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    
    if (isset($_POST['login'])) {
        $nama = trim($_POST['nama']);
        $password = $_POST['password'];

        if (empty($nama) || empty($password)) {
            $pesan = "Validasi Gagal: Nama dan password wajib diisi.";
        } else {
            $stmt = $conn->prepare('SELECT password FROM users WHERE nama = ? LIMIT 1');
            $stmt->bind_param('s', $nama);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($hashed_password);
                $stmt->fetch();

                if (password_verify($password, $hashed_password)) {
                    $_SESSION['nama'] = $nama;
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $pesan = "Login Gagal: Password salah.";
                }
            } else {
                $pesan = "Login Gagal: Pengguna tidak ditemukan.";
            }

            $stmt->close();
        }
    }

    
    if (isset($_POST['register'])) {
        $nama = trim($_POST['nama']);
        $password = $_POST['password'];

        if (empty($nama) || empty($password)) {
            $pesan = "Validasi Gagal: Nama dan password wajib diisi.";
        } elseif (strlen($password) < 6) {
            $pesan = "Validasi Gagal: Password minimal 6 karakter.";
        } else {
            $stmt_check = $conn->prepare('SELECT id FROM users WHERE nama = ?');
            $stmt_check->bind_param('s', $nama);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $pesan = "Registrasi Gagal: Nama sudah terdaftar.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare('INSERT INTO users (nama, password) VALUES (?, ?)');
                $stmt->bind_param('ss', $nama, $hashed_password);

                if ($stmt->execute()) {
                    $pesan = "Registrasi Berhasil. Silakan login.";
                } else {
                    $pesan = "Kesalahan Server: " . $stmt->error;
                }
                $stmt->close();
            }

            $stmt_check->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Login & Register</title>   
</head>
<body>
    <div class="container">
        <?php if (!empty($pesan)): ?>
            <div class="message"><?php echo htmlspecialchars($pesan); ?></div>
        <?php endif; ?>

        <h2>Registrasi</h2>
        <form method="POST" action="">
            <input type="text" name="nama" placeholder="Nama Pengguna" required><br><br>
            <input type="password" name="password" placeholder="Password (Min. 6 Karakter)" required><br><br>
            <button type="submit" name="register">Register</button>
        </form>

        <hr>

        <h2>Login</h2>
        <form method="POST" action="">
            <input type="text" name="nama" placeholder="Nama Pengguna" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>
