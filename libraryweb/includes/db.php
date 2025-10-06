<?php
// includes/db.php

$db_host = 'localhost';
$db_user = 'root'; // Ganti dengan username database Anda
$db_pass = '';     // Ganti dengan password database Anda
$db_name = 'libraryweb'; // Ganti dengan nama database Anda

// Buat koneksi
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set charset ke utf8
$conn->set_charset("utf8");
?>
 