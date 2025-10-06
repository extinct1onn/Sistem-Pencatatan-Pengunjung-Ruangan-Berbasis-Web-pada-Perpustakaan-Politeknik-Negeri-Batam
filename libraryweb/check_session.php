<?php
// File: check_session.php
// Letakkan file ini di root folder project Anda (sejajar dengan admin_dashboard.php)
// Endpoint untuk cek status session via AJAX

session_start();
header('Content-Type: application/json');

// Konfigurasi timeout (dalam detik)
$SESSION_TIMEOUT = 600; // 15 menit (bisa disesuaikan: 300 = 5 menit, 600 = 10 menit, dll)
$WARNING_BEFORE = 300;   // Warning 1 menit sebelum timeout

// Cek apakah user sudah login
if (!isset($_SESSION['user_data']['id'])) {
    echo json_encode([
        'status' => 'logged_out',
        'message' => 'Session tidak ditemukan'
    ]);
    exit();
}

// Inisialisasi last activity jika belum ada
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

$current_time = time();
$last_activity = $_SESSION['last_activity'];
$elapsed = $current_time - $last_activity;
$remaining = $SESSION_TIMEOUT - $elapsed;

// Jika sudah timeout
if ($elapsed > $SESSION_TIMEOUT) {
    // Hapus session
    session_unset();
    session_destroy();
    
    echo json_encode([
        'status' => 'timeout',
        'message' => 'Session telah berakhir karena tidak ada aktivitas'
    ]);
    exit();
}

// Jika mendekati timeout (1 menit sebelum habis)
if ($remaining <= $WARNING_BEFORE) {
    echo json_encode([
        'status' => 'warning',
        'remaining' => $remaining,
        'message' => 'Session akan berakhir dalam ' . $remaining . ' detik'
    ]);
    exit();
}

// Session masih aman dan aktif
echo json_encode([
    'status' => 'active',
    'remaining' => $remaining,
    'elapsed' => $elapsed,
    'last_activity' => date('Y-m-d H:i:s', $last_activity)
]);
exit();
?>