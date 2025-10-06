<?php
require_once 'includes/db.php';
require_once 'includes/config.php';

$message = '';
$ruang   = trim($_GET['ruang'] ?? '');
$allowRuang = ['Ruang Baca','Ruang Diskusi','Ruang Komputer'];

$nim = trim($_GET['nim'] ?? '');
if (!$nim) {
    $message = '<div class="alert alert-warning" style="background-color:#e3f2fd;color:#000;border-color:#90caf9;">
                  <strong>Silakan masukkan NIM Anda.</strong>
                  <form method="get" class="mt-2">
                    <input type="hidden" name="ruang" value="'.htmlspecialchars($ruang).'">
                    <input class="form-control mb-3" name="nim" placeholder="NIM" required autofocus>
                    <button class="btn btn-primary w-100" type="submit">Lanjut</button>
                  </form>
                </div>';
} else {
    $stmt = $conn->prepare("SELECT * FROM users WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $message = '<div class="alert alert-danger">NIM tidak terdaftar. Hubungi admin untuk mendaftar.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Catat Kunjungan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Supaya card tidak mentok ke tepi HP */
    @media (max-width: 576px) {
      .card {
        margin: 0 10px;
        padding: 1.5rem !important;
      }
      h4 { font-size: 1.25rem; }
    }
  </style>
</head>
<body class="bg-light">
  <div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow" style="max-width:420px;width:100%;">
      <div class="card-body">
        <h4 class="mb-3">Catat Kunjungan</h4>

        <?php if ($message): ?>
          <?= $message ?>
        <?php elseif (!empty($user)): ?>
          <p><strong>NIM:</strong> <?= htmlspecialchars($user['nim']) ?></p>
          <p><strong>Nama:</strong> <?= htmlspecialchars($user['username']) ?></p>

          <form method="POST" action="process_scan.php">
            <input type="hidden" name="nim" value="<?= htmlspecialchars($user['nim']) ?>">
            <input type="hidden" name="nama" value="<?= htmlspecialchars($user['username']) ?>">
            <input type="hidden" name="ruang" value="<?= htmlspecialchars($ruang) ?>">

            <label class="form-label">Pilih Tujuan:</label>
            <select class="form-select mb-3" name="tujuan" required>
              <option value="">-- Pilih Tujuan --</option>
              <option <?= $ruang==='Ruang Baca'?'selected':'' ?>>Ruang Baca</option>
              <option <?= $ruang==='Ruang Diskusi'?'selected':'' ?>>Ruang Diskusi</option>
              <option <?= $ruang==='Ruang Komputer'?'selected':'' ?>>Ruang Komputer</option>
            </select>

            <button type="submit" class="btn btn-primary w-100">Catat Kunjungan</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>