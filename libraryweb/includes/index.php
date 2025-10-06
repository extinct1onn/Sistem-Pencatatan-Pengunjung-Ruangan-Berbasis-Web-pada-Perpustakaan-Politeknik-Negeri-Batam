<?php
session_start();

// Kalau belum login → ke login
if (!isset($_SESSION['user_data']['id']) || !$_SESSION['user_data']['id']) {
    header('Location: login.php');
    exit();
}

// Kalau sudah login → cek role
$userRole = $_SESSION['user_data']['role'] ?? 'user';

if ($userRole === 'admin') {
    header('Location: admin_dashboard.php');
    exit();
} else {
    // User biasa: kasih info & tombol ke scan
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Selamat Datang</title>
        <link rel="stylesheet" href="css/scan.css">
    </head>
    <body>
        <h1>Halo, <?= htmlspecialchars($_SESSION['user_data']['first_name']) ?>!</h1>
        <p>Untuk mencatat kunjungan, silakan scan QR Code di perpustakaan.</p>
        <a href="scan.php" class="btn">📷 Scan QR Code</a>
        <br><br>
        <a href="logout.php">Logout</a>
    </body>
    </html>
    <?php
    exit();
}
?>