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

/* ---------- 0. HANYA ADMIN YANG BOLEH MASUK ---------- */
if (
    !isset($_SESSION['user_data']['role']) ||
    $_SESSION['user_data']['role'] !== 'admin'
) {
    header('Location: login.php');
    exit();
}

/* ---------- 1. Cegah bolak-balik login (5-detik) ---------- */
$block = 'no_redirect_' . session_id();
if (!isset($_COOKIE[$block])) {
    setcookie($block, '1', time() + 5, '/');
}

/* ---------- 2. Ambil data user ---------- */
$user = $_SESSION['user_data'];

/* ---------- 3. Hitung kunjungan ---------- */
$total_visits_today = 0;
$total_visits_all   = 0;
$all_visits         = [];

// hari ini
$today = date('Y-m-d');
$stmt  = $conn->prepare("SELECT COUNT(*) AS total FROM visits WHERE tanggal_kunjungan = ?");
$stmt->bind_param('s', $today);
$stmt->execute();
$total_visits_today = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// keseluruhan
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM visits");
$stmt->execute();
$total_visits_all = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

/* ---------- 3b. Filter kalau ada kata kunci (NIM dan Nama saja) ---------- */
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$where   = '';
$params  = [];
$types   = '';

if ($keyword !== '') {
    $where  = ' WHERE nim LIKE ? OR nama LIKE ? ';
    $like   = "%$keyword%";
    $params = [$like, $like];
    $types  = 'ss';
}

// Ambil data dengan filter (atau semua jika tidak ada keyword)
$sql  = "SELECT * FROM visits $where ORDER BY created_at DESC LIMIT 100";
$stmt = $conn->prepare($sql);
if ($keyword !== '') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$all_visits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* ---------- 4. Gambar profil : cek relatif ---------- */
$profile_picture = trim($user['profile_picture'] ?? '');
if ($profile_picture === '' || !is_file($profile_picture)) {
    $profile_picture = 'img/default_profile.png';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pencatatan Pengunjung</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <link rel="stylesheet" href="css/session_timeout.css">
</head>
<body class="admin-page">
<div class="wrapper">
    <header class="top-header">
        <div class="top-header-left">
            <span class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></span>
            <span class="logo">Sistem Pengunjung (ADMIN)</span>
        </div>
        <div class="top-header-right">
            <i class="fas fa-bell"></i>
            <div class="admin-profile">
                <span><?= htmlspecialchars($user['first_name']) ?></span>
                <i class="fas fa-caret-down"></i>
            </div>
        </div>
    </header>

    <div class="content-body">
        <aside class="sidebar" id="sidebar">
            <div class="profile">
                <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile">
                <div>
                    <p><strong><?= htmlspecialchars($user['first_name']) ?></strong></p>
                    <span class="online-status">● Online</span>
                </div>
            </div>
            <div class="search-container">
                <form method="get" action="admin_dashboard.php" id="searchForm">
                    <input type="text" 
                           name="keyword" 
                           id="searchInput"
                           placeholder="Cari NIM/Nama..." 
                           value="<?= htmlspecialchars($keyword) ?>"
                           autocomplete="off">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="navigation-header">MENU ADMIN</div>
            <nav>
                <ul>
                    <li><a href="admin_dashboard.php" class="active"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
                    <li><a href="manajemen_pengguna.php"><i class="fas fa-users-cog"></i><span>Manajemen Pengguna</span></a></li>
                    <li>
                        <a href="#" class="has-submenu" id="rekapitulasiToggle">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Rekapitulasi</span>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="submenu" id="rekapitulasiSubmenu">
                            <li><a href="rekapitulasi_ruang_baca.php"><i class="fas fa-book"></i><span>Ruang Baca</span></a></li>
                            <li><a href="rekapitulasi_ruang_diskusi.php"><i class="fas fa-comments"></i><span>Ruang Diskusi</span></a></li>
                            <li><a href="rekapitulasi_ruang_komputer.php"><i class="fas fa-desktop"></i><span>Ruang Komputer</span></a></li>
                        </ul>
                    </li>
                    <li><a href="catat_kunjungan.php"><i class="fas fa-plus"></i><span>Catat Kunjungan</span></a></li>
                    <li><a href="laporan_statistik.php"><i class="fas fa-chart-bar"></i><span>Laporan Statistik</span></a></li>
                </ul>
            </nav>
            <div class="navigation-header">PENGATURAN</div>
            <nav>
                <ul>
                    <li><a href="profile.php"><i class="fas fa-user-circle"></i><span>Profil</span></a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <div class="main-header">
                <h1>Dashboard Admin <span>Overview Data Pengunjung</span></h1>
                <ol class="breadcrumb">
                    <li><a href="#" style="text-decoration:none;"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                </ol>
            </div>

            <section class="cards">
                <div class="card blue">
                    <div class="card-content">
                        <h3><?= $total_visits_today ?></h3>
                        <p>TOTAL KUNJUNGAN HARI INI</p>
                    </div>
                    <div class="card-icon"><i class="fas fa-chart-line"></i></div>
                </div>
                <div class="card green">
                    <div class="card-content">
                        <h3><?= $total_visits_all ?></h3>
                        <p>TOTAL KUNJUNGAN KESELURUHAN</p>
                    </div>
                    <div class="card-icon"><i class="fas fa-users"></i></div>
                </div>
                <div class="card orange">
                    <div class="card-content">
                        <h3>3</h3>
                        <p>RUANGAN DIPANTAU</p>
                    </div>
                    <div class="card-icon"><i class="fas fa-door-open"></i></div>
                </div>
            </section>

            <section class="recent-visitors mt-4">
                <h3>Daftar Pengunjung Terbaru (100 Data Terakhir)</h3>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Tujuan Kunjungan</th>
                                <th>Tanggal</th>
                                <th>Waktu Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($all_visits): ?>
                            <?php foreach ($all_visits as $v): ?>
                            <tr>
                                <td><?= htmlspecialchars($v['nim']) ?></td>
                                <td><?= htmlspecialchars($v['nama']) ?></td>
                                <td><?= htmlspecialchars($v['tujuan']) ?></td>
                                <td><?= htmlspecialchars($v['tanggal_kunjungan']) ?></td>
                                <td><?= htmlspecialchars($v['jam_masuk']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">
                                    Tidak ada data pengunjung.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
// Toggle Sidebar
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');

menuToggle.addEventListener('click', function() {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
});

// Toggle submenu Rekapitulasi
document.getElementById('rekapitulasiToggle').addEventListener('click', function(e) {
    e.preventDefault();
    const submenu = document.getElementById('rekapitulasiSubmenu');
    submenu.classList.toggle('show');
    this.classList.toggle('active');
});
</script>

<script src="js/session_timeout.js"></script>
</body>
</html>