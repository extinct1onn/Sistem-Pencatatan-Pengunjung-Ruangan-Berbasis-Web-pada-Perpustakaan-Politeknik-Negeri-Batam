<?php
require_once 'includes/db.php';
require_once 'includes/config.php';

$nim = trim($_GET['nim'] ?? '');
$stmt = $conn->prepare("SELECT * FROM users WHERE nim = ?");
$stmt->bind_param("s", $nim);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sukses Scan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Supaya card tidak mentok ke tepi HP */
    @media (max-width: 576px) {
      .card {
        margin: 0 10px;
        padding: 1.5rem !important;
      }
      h2 { font-size: 1.25rem; }
    }
  </style>
</head>
<body class="bg-light">
  <div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow text-center" style="max-width:420px;width:100%;">
      <div class="card-body">
        <h2 class="text-success mb-3">✅ Kunjungan Berhasil Dicatat</h2>
        <?php if ($user): ?>
          <p class="mb-0">Terima kasih, <b><?= htmlspecialchars($user['username']) ?></b>.</p>
        <?php else: ?>
          <p class="mb-0 text-danger">Data user tidak ditemukan.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>