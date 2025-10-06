<?php
// File: extend_session.php
// Letakkan file ini di root folder project (sejajar dengan check_session.php)
// Endpoint untuk perpanjang session ketika user klik tombol "Perpanjang Session"

session_start();
header('Content-Type: application/json');

// Cek apakah user masih login
if (!isset($_SESSION['user_data']['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Session tidak ditemukan. Silakan login kembali.'
    ]);
    exit();
}

// Update waktu aktivitas terakhir ke waktu sekarang
$_SESSION['last_activity'] = time();

// Berhasil perpanjang session
echo json_encode([
    'success' => true,
    'message' => 'Session berhasil diperpanjang',
    'timestamp' => time(),
    'new_last_activity' => date('Y-m-d H:i:s', time())
]);
exit();
?>