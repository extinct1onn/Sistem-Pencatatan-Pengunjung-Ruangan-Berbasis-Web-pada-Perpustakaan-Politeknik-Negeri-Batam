<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/config.php';

/* ---------- SESSION TIMEOUT CONFIGURATION ---------- */
$SESSION_TIMEOUT = 900; // 15 menit (dalam detik)

// Inisialisasi last activity untuk session baru
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

/* ---------- Hak akses admin ---------- */
if (!isset($_SESSION['user_data']['id']) || ($_SESSION['user_data']['role'] ?? 'guest') !== 'admin') {
    header('Location: login.php');
    exit();
}

// tambah pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $username = $_POST['username'];
    $nim      = $_POST['nim'];
    $email    = $_POST['email'];
    $role     = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, nim, email, password, role, profile_picture, created_at, updated_at) 
            VALUES ('$username','$nim','$email','$password','$role','img/default_profile.png', NOW(), NOW())";
    mysqli_query($conn, $sql);
}

// hapus pengguna
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
}

// ambil semua user
$result = mysqli_query($conn, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- TAMBAHKAN CSS SESSION TIMEOUT -->
    <link rel="stylesheet" href="css/session_timeout.css">
</head>
<body class="bg-light">

<div class="container mt-4">

    <!-- Header dengan tombol kembali -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manajemen Pengguna</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary">⬅ Kembali ke Dashboard</a>
    </div>

    <!-- Form Tambah Pengguna -->
    <div class="card mb-4">
        <div class="card-header">Tambah Pengguna</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>NIM</label>
                    <input type="text" name="nim" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
            </form>
        </div>
    </div>

    <!-- Tabel Data Pengguna -->
    <div class="card">
        <div class="card-header">Daftar Pengguna</div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Username</th>
                        <th>NIM</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Dibuat</th>
                        <th>Diupdate</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><img src="<?= $row['profile_picture']; ?>" width="40" class="rounded-circle"></td>
                        <td><?= $row['username']; ?></td>
                        <td><?= $row['nim']; ?></td>
                        <td><?= $row['email']; ?></td>
                        <td>
                            <?php if($row['role'] == 'admin') { ?>
                                <span class="badge bg-success">Admin</span>
                            <?php } else { ?>
                                <span class="badge bg-secondary">User</span>
                            <?php } ?>
                        </td>
                        <td><?= $row['created_at']; ?></td>
                        <td><?= $row['updated_at']; ?></td>
                        <td>
                            <a href="edit_pengguna.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?hapus=<?= $row['id']; ?>" onclick="return confirm('Yakin mau hapus?');" class="btn btn-danger btn-sm">Hapus</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- TAMBAHKAN JAVASCRIPT SESSION TIMEOUT -->
<script src="js/session_timeout.js"></script>
</body>
</html>