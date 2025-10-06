<?php
session_start();
require 'includes/db.php';

$login_message = '';
if (isset($_SESSION['flash_message'])) {
    $login_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $login_message = '<div class="alert alert-danger mt-2">Email dan password harus diisi!</div>';
    } else {
        // ➜ hanya admin yang boleh login
        $stmt = $conn->prepare("SELECT id, username, nim, email, password, role, profile_picture FROM users WHERE email = ? AND role = 'admin'");
        if ($stmt === false) {
            $login_message = '<div class="alert alert-danger mt-2">Error query: ' . $conn->error . '</div>';
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_data'] = [
                        'id'         => $row['id'],
                        'first_name' => $row['username'],
                        'last_name'  => $row['nim'],
                        'email'      => $row['email'],
                        'role'       => 'admin',
                        'profile_picture' => $row['profile_picture'] ?? 'img/default_profile.png'
                    ];
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    $login_message = '<div class="alert alert-danger mt-2">Password salah!</div>';
                }
            } else {
                $login_message = '<div class="alert alert-danger mt-2">Admin tidak ditemukan!</div>';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .visitor-form-card { max-width: 500px; }
    </style>
</head>
<body>
    <div class="container-fluid min-vh-100 bg-custom d-flex flex-column">
        <div class="pt-4 ps-4">
            <img src="img/poltek.png" alt="Polibatam Logo" class="logo-polibatam">
        </div>
        <div class="flex-grow-1 d-flex justify-content-center align-items-center">
            <div class="card card-form shadow-sm visitor-form-card">
                <form action="" method="POST">
                    <h4 class="fw-semibold">Welcome back!</h4>
                    <div class="mb-3 small">Silakan masukkan data admin dengan lengkap!</div>
                    <?php if ($login_message): ?>
                        <div class="mb-3"><?= $login_message ?></div>
                    <?php endif; ?>
                    <input type="email" class="form-control mb-2" name="email" placeholder="Email" required>
                    <input type="password" class="form-control mb-2" name="password" placeholder="Password" required>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember for 30 days</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign in</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>