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

// PENTING: Pastikan hanya user yang sudah login yang bisa mengakses halaman ini
if (!isset($_SESSION['user_data']['id']) || !$_SESSION['user_data']['id']) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user_data'];
$message = '';

// Tentukan link dashboard sesuai role
$dashboard_link = 'index.php'; // default
if (($user['role'] ?? '') === 'admin') {
    $dashboard_link = 'admin_dashboard.php';
} else {
    header('Location: index.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nim = $user['last_name']; // NIM dari sesi
    $nama = $user['first_name']; // Nama dari sesi
    $tujuan = trim($_POST['tujuan']);
    
    if (empty($tujuan)) {
        $message = '<div class="alert alert-danger">Tujuan kunjungan harus dipilih!</div>';
    } else {
        $tanggal_kunjungan = date('Y-m-d');
        $jam_masuk = date('H:i:s');
        
        $insert_sql = "INSERT INTO visits (nim, nama, tujuan, tanggal_kunjungan, jam_masuk) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $insert_sql);
        
        if ($stmt_insert) {
            mysqli_stmt_bind_param($stmt_insert, "sssss", $nim, $nama, $tujuan, $tanggal_kunjungan, $jam_masuk);
            if (mysqli_stmt_execute($stmt_insert)) {
                $message = '<div class="alert alert-success">Pencatatan kunjungan berhasil!</div>';
            } else {
                $message = '<div class="alert alert-danger">Gagal mencatat kunjungan. Error: ' . mysqli_error($conn) . '</div>';
            }
            mysqli_stmt_close($stmt_insert);
        } else {
            $message = '<div class="alert alert-danger">Gagal mempersiapkan statement.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Kunjungan - Pencatatan Pengunjung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <!-- TAMBAHKAN CSS SESSION TIMEOUT -->
    <link rel="stylesheet" href="css/session_timeout.css">
    <style>
        .visitor-form-card {
            max-width: 500px;
        }
    </style>
</head>
<body class="admin-page">
    <div class="container-fluid min-vh-100 bg-custom d-flex flex-column">
        <div class="pt-4 ps-4">
            <img src="img/poltek.png" alt="Polibatam Logo" class="logo-polibatam">
        </div>
        <div class="flex-grow-1 d-flex justify-content-center align-items-center">
            <div class="card card-form shadow-sm visitor-form-card">
                <form action="" method="POST">
                    <h4 class="fw-semibold">Catat Kunjungan Anda</h4>
                    <div class="mb-3 small">Silakan pilih tujuan kunjungan Anda.</div>
                    <?php if ($message) echo $message; ?>
                    <p><strong>NIM:</strong> <?= htmlspecialchars($user['last_name']) ?></p>
                    <p><strong>Nama:</strong> <?= htmlspecialchars($user['first_name']) ?></p>
                    
                    <select class="form-select mb-2" name="tujuan" required>
                        <option value="">-- Pilih Tujuan Kunjungan --</option>
                        <option value="Ruang Baca">Ruang Baca</option>
                        <option value="Ruang Diskusi">Ruang Diskusi</option>
                        <option value="Ruang Komputer">Ruang Komputer</option>
                    </select>
                    
                    <button type="submit" class="btn btn-primary w-100">Catat Kunjungan</button>
                    <div class="text-center small mt-2">
                        <a href="<?= $dashboard_link ?>" class="text-decoration-none">Kembali ke Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- TAMBAHKAN JAVASCRIPT SESSION TIMEOUT -->
    <script src="js/session_timeout.js"></script>
</body>
</html>