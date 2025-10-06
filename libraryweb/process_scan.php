<?php
// process_scan.php
require_once 'includes/db.php';
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses tidak valid.");
}

$nim   = trim($_POST['nim'] ?? '');
$nama  = trim($_POST['nama'] ?? '');
$tujuan= trim($_POST['tujuan'] ?? '');
// BARU : ambil ruang (bila ada)
$ruang = trim($_POST['ruang'] ?? '');   // tambahan

if ($nim === '' || $nama === '' || $tujuan === '') {
    die("Data tidak lengkap.");
}

$tanggal = date('Y-m-d');
$jam     = date('H:i:s');

$stmt = $conn->prepare("INSERT INTO visits (nim, nama, tujuan, tanggal_kunjungan, jam_masuk) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nim, $nama, $tujuan, $tanggal, $jam);
if (!$stmt->execute()) {
    die("Insert gagal: " . $stmt->error);
}

header("Location: scan_success.php?nim=" . urlencode($nim));
exit;