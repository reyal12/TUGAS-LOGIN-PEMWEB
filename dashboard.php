<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['nama'])) {
    header('Location: auth.php');
    exit();
}

// Logika penghapusan data - hanya admin yang bisa menghapus
if (isset($_GET['hapus']) && $_SESSION['nama'] === 'admin') {
    $hapusId = intval($_GET['hapus']);

    if ($hapusId > 0) {
        $stmtDelete = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmtDelete->bind_param('i', $hapusId);
        $stmtDelete->execute();
        $stmtDelete->close();

        header('Location: dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h2>
    <p><a href="logout.php">Logout</a></p>

    <?php if ($_SESSION['nama'] === 'admin'): ?>
        <h3>Menu Admin: Kelola Pengguna</h3>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Aksi</th>
            </tr>
            <?php
            $query = $conn->query('SELECT id, nama FROM users ORDER BY id ASC');
            while ($row = $query->fetch_assoc()):
            ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                    <a href="dashboard.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin hapus data?');">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</body>
</html>