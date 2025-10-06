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
$user = $_SESSION['user_data'];

/* ---------- Filter ---------- */
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date   = $_GET['end_date']   ?? date('Y-m-d');
$keyword    = trim($_GET['keyword'] ?? '');

/* ---------- Query ---------- */
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

$sql = "SELECT id, nim, nama, tanggal_kunjungan, jam_masuk
        FROM visits
        WHERE $where
        ORDER BY tanggal_kunjungan DESC, jam_masuk DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    /* prepare gagal → tampilkan error & berhenti */
    die('Prepare failed: ' . $conn->error);
}

/* bind dinamis (kompatibel PHP lama) */
$binds = array_merge([$types], $params);
$refs  = [];
foreach ($binds as $k => $v) $refs[$k] = &$binds[$k];
call_user_func_array([$stmt, 'bind_param'], $refs);

$stmt->execute();
$result = $stmt->get_result();
$visits = [];
while ($row = $result->fetch_assoc()) $visits[] = $row;
$stmt->close();

$total_visits = count($visits);

/* ---------- Gambar profil ---------- */
$profile_picture = trim($user['profile_picture'] ?? '');
if ($profile_picture === '' || !is_file($profile_picture)) {
    $profile_picture = 'img/default_profile.png';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Ruang Diskusi - Pencatatan Pengunjung</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <link rel="stylesheet" href="css/rekapitulasi.css">
    <!-- TAMBAHKAN CSS SESSION TIMEOUT -->
    <link rel="stylesheet" href="css/session_timeout.css">
</head>
<body class="admin-page">
    <div class="wrapper">
        <!-- Top Header -->
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
            <!-- Sidebar -->
            <aside class="sidebar" id="sidebar">
                <div class="profile">
                    <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Admin">
                    <div>
                        <p><strong><?= htmlspecialchars($user['first_name']) ?></strong></p>
                        <span class="online-status">● Online</span>
                    </div>
                </div>
                <div class="search-container">
                    <form method="get" action="">
                        <input type="text" name="keyword" placeholder="Cari NIM/Nama..." value="<?= htmlspecialchars($keyword) ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="navigation-header">MENU ADMIN</div>
                <nav>
                    <ul>
                        <li><a href="admin_dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
                        <li><a href="manajemen_pengguna.php"><i class="fas fa-users-cog"></i><span>Manajemen Pengguna</span></a></li>
                        <li>
                            <a href="javascript:void(0);" class="has-submenu active" id="rekapitulasiToggle">
                                <i class="fas fa-clipboard-list"></i>
                                <span>Rekapitulasi</span>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <ul class="submenu show" id="rekapitulasiSubmenu">
                                <li><a href="rekapitulasi_ruang_baca.php"><i class="fas fa-book"></i><span>Ruang Baca</span></a></li>
                                <li><a href="rekapitulasi_ruang_diskusi.php" class="active"><i class="fas fa-comments"></i><span>Ruang Diskusi</span></a></li>
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

            <!-- Main Content -->
            <main class="main-content" id="mainContent">
                <div class="main-header">
                    <h1>Rekapitulasi Ruang Diskusi <span>Data Pengunjung Ruang Diskusi</span></h1>
                    <ol class="breadcrumb">
                        <li><a href="admin_dashboard.php" style="text-decoration: none;"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li> / Rekapitulasi Ruang Diskusi</li>
                    </ol>
                </div>

                <!-- Card Total -->
                <section class="cards">
                    <div class="card green">
                        <div class="card-content">
                            <h3><?= $total_visits ?></h3>
                            <p>TOTAL PENGUNJUNG RUANG DISKUSI</p>
                        </div>
                        <div class="card-icon"><i class="fas fa-comments"></i></div>
                    </div>
                </section>

                <!-- Filter Section -->
                <section class="filter-section">
                    <h3 style="margin-bottom: 15px; color: #495057;">Filter Data</h3>
                    <form method="get" action="" class="filter-form">
                        <div class="form-group">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                        </div>
                        <div class="form-group">
                            <label>Cari NIM/Nama</label>
                            <input type="text" name="keyword" placeholder="Ketik di sini..." value="<?= htmlspecialchars($keyword) ?>">
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filter</button>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <a href="rekapitulasi_ruang_diskusi.php" class="btn-reset"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <a href="export_ruang_diskusi.php?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&keyword=<?= urlencode($keyword) ?>" class="btn-export"><i class="fas fa-file-excel"></i> Export Excel</a>
                        </div>
                    </form>
                </section>

                <!-- Tabel -->
                <section class="recent-visitors">
                    <h3>Daftar Pengunjung Ruang Diskusi</h3>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIM</th>
                                    <th>Nama</th>
                                    <th>Tanggal Kunjungan</th>
                                    <th>Waktu Masuk</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($visits): ?>
                                    <?php foreach ($visits as $i => $v): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($v['nim']) ?></td>
                                        <td><?= htmlspecialchars($v['nama']) ?></td>
                                        <td><?= htmlspecialchars($v['tanggal_kunjungan']) ?></td>
                                        <td><?= htmlspecialchars($v['jam_masuk']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">Tidak ada data pengunjung Ruang Diskusi.</td></tr>
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
    const rekapToggle = document.getElementById('rekapitulasiToggle');
    if (rekapToggle) {
        rekapToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (!sidebar.classList.contains('collapsed')) {
                const submenu = document.getElementById('rekapitulasiSubmenu');
                submenu.classList.toggle('show');
                this.classList.toggle('active');
            }
        });
    }
    </script>

    <!-- TAMBAHKAN JAVASCRIPT SESSION TIMEOUT -->
    <script src="js/session_timeout.js"></script>
</body>
</html>