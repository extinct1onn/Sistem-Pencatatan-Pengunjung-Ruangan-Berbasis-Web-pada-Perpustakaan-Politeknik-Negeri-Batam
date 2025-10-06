<?php
// koneksi database
include 'includes/db.php';

// cek apakah ada parameter id
if (!isset($_GET['id'])) {
    header("Location: manajemen_pengguna.php");
    exit;
}

$id = $_GET['id'];

// ambil data user
$result = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "Pengguna tidak ditemukan!";
    exit;
}

// update data user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $nim      = $_POST['nim'];
    $email    = $_POST['email'];
    $role     = $_POST['role'];
    $password = $_POST['password'];
    $profile_picture = $user['profile_picture']; // default tetap pakai yg lama

    // cek jika upload foto baru
    if (!empty($_FILES['profile_picture']['name'])) {
        $file_name = time() . "_" . $_FILES['profile_picture']['name'];
        $target = "uploads/" . $file_name;
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target)) {
            $profile_picture = $target;
        }
    }

    // jika password tidak kosong, update password juga
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET username='$username', nim='$nim', email='$email', role='$role', password='$hashed_password', profile_picture='$profile_picture', updated_at=NOW() WHERE id='$id'";
    } else {
        $sql = "UPDATE users SET username='$username', nim='$nim', email='$email', role='$role', profile_picture='$profile_picture', updated_at=NOW() WHERE id='$id'";
    }

    if (mysqli_query($conn, $sql)) {
        header("Location: manajemen_pengguna.php");
        exit;
    } else {
        echo "Gagal update data: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">

    <h2>Edit Pengguna</h2>
    <a href="manajemen_pengguna.php" class="btn btn-secondary mb-3">⬅ Kembali</a>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= $user['username']; ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>NIM</label>
                    <input type="text" name="nim" value="<?= $user['nim']; ?>" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= $user['email']; ?>" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="user" <?= ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Password Baru (kosongkan jika tidak ingin ganti)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Foto Profil</label><br>
                    <img src="<?= $user['profile_picture']; ?>" width="80" class="mb-2 rounded">
                    <input type="file" name="profile_picture" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>

</div>

</body>
</html>
