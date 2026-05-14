<?php
session_start();
include 'koneksi.php';

// Proteksi: hanya admin yang bisa mengakses
if (!isset($_SESSION['nama']) || $_SESSION['nama'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Proteksi: parameter ID harus ada
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$id = intval($_GET['id']);
$error = '';

if ($id <= 0) {
    header('Location: dashboard.php');
    exit();
}

// Ambil data pengguna berdasarkan ID
$stmt = $conn->prepare('SELECT id, nama FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Jika user tidak ditemukan, kembali ke dashboard
if (!$user) {
    header('Location: dashboard.php');
    exit();
}

// Logika pembaruan data
if (isset($_POST['update'])) {
    $nama = trim($_POST['nama']);
    $password = trim($_POST['password']);

    if ($nama === '' || $password === '') {
        $error = 'Nama dan password baru harus diisi.';
    } else {
        // Enkripsi ulang password baru menggunakan password_hash()
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare('UPDATE users SET nama = ?, password = ? WHERE id = ?');
        $updateStmt->bind_param('ssi', $nama, $hashPassword, $id);
        $success = $updateStmt->execute();
        $updateStmt->close();

        // Arahkan kembali ke dashboard setelah berhasil
        if ($success) {
            header('Location: dashboard.php');
            exit();
        }

        $error = 'Gagal memperbarui data. Silakan coba lagi.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Data Pengguna</title>
</head>
<body>
    <h2>Edit Data Pengguna</h2>

    <?php if ($error): ?>
        <div style="color: red;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="edit.php?id=<?php echo htmlspecialchars($id); ?>">
        <label for="nama">Nama Pengguna</label><br>
        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required><br><br>

        <label for="password">Password Baru</label><br>
        <input type="password" id="password" name="password" placeholder="Masukkan password baru..." required><br><br>

        <input type="submit" name="update" value="Simpan Perubahan">
    </form>
</body>
</html>