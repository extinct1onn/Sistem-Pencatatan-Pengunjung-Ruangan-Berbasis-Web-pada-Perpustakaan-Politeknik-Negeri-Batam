<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/config.php';

/* ---------- Hak akses admin ---------- */
if (!isset($_SESSION['user_data']['id']) || ($_SESSION['user_data']['role'] ?? 'guest') !== 'admin') {
    header('Location: login.php');
    exit();
}

/* ---------- Filter tanggal ---------- */
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date   = $_GET['end_date']   ?? date('Y-m-d');
$keyword    = trim($_GET['keyword'] ?? '');

/* ---------- Bangun WHERE clause ---------- */
$where  = "tujuan = ?";
$params = ['Ruang Diskusi'];
$types  = 's';

if ($start_date && $end_date) {
    $where  .= " AND tanggal_kunjungan BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types    .= 'ss';
}
if ($keyword !== '') {
    $where  .= " AND (nim LIKE ? OR nama LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
    $types    .= 'ss';
}

/* ---------- Query ---------- */
$sql = "SELECT nim, nama, tanggal_kunjungan, jam_masuk
        FROM visits
        WHERE $where
        ORDER BY tanggal_kunjungan DESC, jam_masuk DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}

/* ---------- Bind parameter ---------- */
$binds = array_merge([$types], $params);
$tmp   = [];
foreach ($binds as $k => $v) $tmp[$k] = &$binds[$k];
call_user_func_array([$stmt, 'bind_param'], $tmp);

$stmt->execute();
$result = $stmt->get_result();
$visits = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ---------- Set header untuk download Excel ---------- */
$filename = "Ruang_Diskusi_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

/* ---------- Output Excel (HTML Table format) ---------- */
echo "<html>";
echo "<head><meta charset='UTF-8'></head>";
echo "<body>";
echo "<h2>Rekapitulasi Pengunjung Ruang Diskusi</h2>";
echo "<p>Periode: " . htmlspecialchars($start_date) . " s/d " . htmlspecialchars($end_date) . "</p>";
echo "<p>Total Pengunjung: " . count($visits) . "</p>";
echo "<br>";

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<thead>";
echo "<tr style='background-color: #FF9800; color: white;'>";
echo "<th>No</th>";
echo "<th>NIM</th>";
echo "<th>Nama</th>";
echo "<th>Tanggal Kunjungan</th>";
echo "<th>Waktu Masuk</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

if ($visits) {
    foreach ($visits as $i => $v) {
        echo "<tr>";
        echo "<td>" . ($i + 1) . "</td>";
        echo "<td>" . htmlspecialchars($v['nim']) . "</td>";
        echo "<td>" . htmlspecialchars($v['nama']) . "</td>";
        echo "<td>" . htmlspecialchars($v['tanggal_kunjungan']) . "</td>";
        echo "<td>" . htmlspecialchars($v['jam_masuk']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5' style='text-align: center;'>Tidak ada data</td></tr>";
}

echo "</tbody>";
echo "</table>";
echo "</body>";
echo "</html>";
exit();
?>