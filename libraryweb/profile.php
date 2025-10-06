<?php
session_start();
require_once 'includes/db.php'; // Pastikan ini mengarah ke file koneksi database Anda

/* ---------- SESSION TIMEOUT CONFIGURATION ---------- */
$SESSION_TIMEOUT = 900; // 15 menit (dalam detik)

// Inisialisasi last activity untuk session baru
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

/* ---------- HANYA ADMIN YANG BOLEH AKSES ---------- */
if (!isset($_SESSION['user_data']['role']) || $_SESSION['user_data']['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Tambahkan kode ini untuk menangani logout
if (isset($_POST['logout'])) {
    // Hapus semua variabel sesi
    $_SESSION = array();

    // Hapus sesi cookie jika ada
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Hancurkan sesi
    session_destroy();

    // Redirect ke halaman login
    header('Location: login.php');
    exit();
}

// Pastikan ada user ID di sesi (dari login)
if (!isset($_SESSION['user_data']['id']) || !$_SESSION['user_data']['id']) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_data']['id'];
$user = $_SESSION['user_data']; // Ambil data user dari sesi untuk ditampilkan di form
$message = ''; // Untuk pesan sukses/error
$errorOccurred = false; // Flag untuk melacak apakah ada error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_update = trim($_POST['first_name']);
    $email_update    = trim($_POST['email']);

    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validasi input Username dan Email
    if (empty(trim($username_update))) {
        $message .= '<p class="error-message">Username tidak boleh kosong!</p>';
        $errorOccurred = true;
    }
    if (empty($email_update)) {
        $message .= '<p class="error-message">Email tidak boleh kosong!</p>';
        $errorOccurred = true;
    } elseif (!filter_var($email_update, FILTER_VALIDATE_EMAIL)) {
        $message .= '<p class="error-message">Format email tidak valid!</p>';
        $errorOccurred = true;
    }

    // Bagian Penanganan Gambar (SEKARANG KE DATABASE)
    $profilePicturePathToSave = $_SESSION['user_data']['profile_picture']; // Default ke path di sesi/DB

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "img/"; // Direktori penyimpanan gambar
        $fileName = uniqid() . '_' . basename($_FILES["profile_picture"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $allowTypes = array('jpg','png','jpeg','gif');
        if(in_array($fileType, $allowTypes)){
            // Hapus gambar lama HANYA JIKA BUKAN DEFAULT dan file-nya ada di server
            if ($profilePicturePathToSave && $profilePicturePathToSave !== 'img/default_profile.png' && file_exists($profilePicturePathToSave)) {
                unlink($profilePicturePathToSave);
            }
            // Upload file baru ke server
            if(move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)){
                $profilePicturePathToSave = $targetFilePath; // Path baru untuk disimpan ke DB
            } else {
                $message .= '<p class="error-message">Maaf, ada masalah saat mengunggah gambar Anda.</p>';
                $errorOccurred = true;
            }
        } else {
            $message .= '<p class="error-message">Maaf, hanya file JPG, JPEG, PNG, & GIF yang diizinkan.</p>';
            $errorOccurred = true;
        }
    }

    // Bagian Penanganan Password
    $hashedPassword = null;
    if (!empty($newPassword)) {
        if ($newPassword !== $confirmPassword) {
            $message .= '<p class="error-message">Password baru dan konfirmasi password tidak cocok!</p>';
            $errorOccurred = true;
        } elseif (strlen($newPassword) < 6) {
            $message .= '<p class="error-message">Password minimal 6 karakter!</p>';
            $errorOccurred = true;
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        }
    }

    if (!$errorOccurred) {
        $conn->begin_transaction();
        $updateSuccessful = true;

        // Update kolom 'username', 'email', dan 'profile_picture' di DATABASE
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?");
        if ($stmt === false) {
            $message = '<p class="error-message">Error preparing username/email/picture statement: ' . $conn->error . '</p>';
            $updateSuccessful = false;
        } else {
            $stmt->bind_param("sssi", $username_update, $email_update, $profilePicturePathToSave, $userId);
            if (!$stmt->execute()) {
                $message = '<p class="error-message">Gagal memperbarui username/email/gambar di database: ' . $stmt->error . '</p>';
                $updateSuccessful = false;
            }
            $stmt->close();
        }

        // Update Password di DATABASE (jika ada password baru yang valid)
        if ($updateSuccessful && $hashedPassword !== null) {
            $stmtPass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmtPass === false) {
                $message .= '<p class="error-message">Error preparing password statement: ' . $conn->error . '</p>';
                $updateSuccessful = false;
            } else {
                $stmtPass->bind_param("si", $hashedPassword, $userId);
                if (!$stmtPass->execute()) {
                    $message .= '<p class="error-message">Gagal memperbarui password di database: ' . $stmtPass->error . '</p>';
                    $updateSuccessful = false;
                }
                $stmtPass->close();
            }
        }

        if ($updateSuccessful) {
            $conn->commit();
            // Perbarui data di sesi setelah sukses update di database
            $_SESSION['user_data']['first_name'] = $username_update;
            $_SESSION['user_data']['email'] = $email_update;
            $_SESSION['user_data']['profile_picture'] = $profilePicturePathToSave; // Update path gambar di sesi

            // Redirect ke dashboard setelah sukses menyimpan perubahan
            header('Location: admin_dashboard.php?status=success');
            exit();
        } else {
            $conn->rollback();
        }
    }
    $user = $_SESSION['user_data'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/session_timeout.css">
</head>
<body class="admin-page">
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <img id="profileImage" src="<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture">
                <label for="imageUpload" class="upload-icon">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" id="imageUpload" name="profile_picture_upload" accept="image/*" style="display: none;">
            </div>

            <div class="profile-info">
                <h2><?= htmlspecialchars($user['first_name']) ?></h2>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>

            <form action="profile.php" method="POST" enctype="multipart/form-data" class="profile-form">
                <?= $message ?>
                <div class="form-group">
                    <label for="first_name">Username</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="new_password">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" id="new_password" name="new_password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password Baru</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>

                <input type="file" id="profile_picture_file" name="profile_picture" accept="image/*" style="display: none;">

                <div class="form-actions">
                    <button type="submit" name="save_changes" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="admin_dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
                    <button type="submit" name="logout" class="btn btn-danger">Logout</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('imageUpload').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profileImage').src = e.target.result;
                    document.getElementById('profile_picture_file').files = event.target.files;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <script src="js/session_timeout.js"></script>
</body>
</html>