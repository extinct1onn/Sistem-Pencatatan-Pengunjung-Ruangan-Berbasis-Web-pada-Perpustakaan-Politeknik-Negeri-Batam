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

// Hak akses admin
if (!isset($_SESSION['user_data']['id']) || ($_SESSION['user_data']['role'] ?? 'guest') !== 'admin') {
    header('Location: login.php');
    exit();
}
$user = $_SESSION['user_data'];

/* ---------- Helper tanggal Indonesia ---------- */
function tglIndo($tgl) {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $p = explode('-', $tgl);
    return $p[2] . ' ' . $bulan[(int)$p[1]] . ' ' . $p[0];
}

/* ---------- Data 7 hari terakhir ---------- */
$labels = [];
$data   = [];

$stmt = $conn->prepare("
    SELECT tanggal_kunjungan, COUNT(*) AS total 
    FROM visits 
    WHERE tanggal_kunjungan >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
    GROUP BY tanggal_kunjungan 
    ORDER BY tanggal_kunjungan ASC
");
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $labels[] = $row['tanggal_kunjungan'];
    $data[]   = $row['total'];
}
$stmt->close();

/* ---------- Rekap untuk tabel ---------- */
$rekap = [];
foreach ($labels as $i => $tgl) {
    $rekap[] = [
        'tanggal' => tglIndo($tgl),
        'jumlah'  => $data[$i]
    ];
}

/* ---------- CSRF token (opsional) ---------- */
$_SESSION['csrf'] = bin2hex(random_bytes(32));

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
    <title>Laporan Statistik - Pencatatan Pengunjung</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin_dashboard.css">
    <!-- TAMBAHKAN CSS SESSION TIMEOUT -->
    <link rel="stylesheet" href="css/session_timeout.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* Warna teks abu-abu gelap */
        body, .main-header h1, .main-header h1 span,
        .recent-visitors h3, .breadcrumb, .card-content p {
            color: #495057 !important;
        }
        /* Scroll tabel */
        .recent-visitors .table-responsive {
            max-height: 45vh;
            overflow-y: auto;
        }
    </style>
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
                        <input type="text" name="keyword" placeholder="Cari..." value="">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
                <div class="navigation-header">MENU ADMIN</div>
                <nav>
                    <ul>
                        <li><a href="admin_dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
                        <li><a href="manajemen_pengguna.php"><i class="fas fa-users-cog"></i><span>Manajemen Pengguna</span></a></li>
                        <li>
                            <a href="javascript:void(0);" class="has-submenu" id="rekapitulasiToggle">
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
                        <li><a href="laporan_statistik.php" class="active"><i class="fas fa-chart-bar"></i><span>Laporan Statistik</span></a></li>
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
                    <h1>Laporan Statistik <span>Grafik & Rekap Data</span></h1>
                    <ol class="breadcrumb">
                        <li><a href="admin_dashboard.php" style="text-decoration: none;"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    </ol>
                </div>

                <!-- Grafik -->
                <section class="chart-section">
                    <div class="chart-card">
                        <h3>Jumlah Kunjungan 7 Hari Terakhir</h3>
                        <div class="chart-container">
                            <canvas id="chartKunjungan"></canvas>
                        </div>
                    </div>
                </section>

                <!-- Tabel -->
                <section class="recent-visitors mt-4">
                    <h3>Rekap Kunjungan</h3>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jumlah Kunjungan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($rekap) > 0): ?>
                                    <?php foreach ($rekap as $r): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['tanggal']) ?></td>
                                            <td><?= htmlspecialchars($r['jumlah']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="2" class="text-center">Tidak ada data kunjungan.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- Chart.js -->
    <script>
    const ctx = document.getElementById('chartKunjungan').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map('tglIndo', $labels)) ?>,
            datasets: [{
                label: 'Jumlah Kunjungan',
                data: <?= json_encode($data) ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

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
            // Hanya bisa toggle submenu kalau sidebar tidak collapsed
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