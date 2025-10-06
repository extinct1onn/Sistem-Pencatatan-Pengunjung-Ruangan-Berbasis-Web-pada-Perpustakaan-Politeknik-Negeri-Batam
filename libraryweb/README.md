# 📚 LibraryWeb - Sistem Pencatatan Pengunjung Ruangan Berbasis Web pada Perpustakaan Politeknik Negeri Batam

Selamat datang di repositori **LibraryWeb**, sebuah sistem pencatatan kunjungan perpustakaan berbasis web yang memudahkan mahasiswa untuk mencatat kunjungan melalui scan QR Code. Sistem ini dirancang untuk mempermudah monitoring aktivitas perpustakaan secara real-time dan akurat.

Aplikasi ini memiliki dua peran utama: **Admin** untuk manajemen pengguna dan laporan, serta **User (Mahasiswa)** untuk mencatat kunjungan ke ruangan perpustakaan.

---

## 📸 Tampilan Aplikasi (Screenshot)

<!-- Tambahkan screenshot di sini -->
<img width="1920" height="1080" alt="LibraryWeb Dashboard" src="https://github.com/user-attachments/assets/your-screenshot-here.png" />

---

## 🚀 Fitur Utama

### Untuk Pengguna (Mahasiswa)
- 📱 **Scan QR Code**: Mahasiswa scan QR code tanpa perlu login
- 🔢 **Input NIM**: Validasi otomatis berdasarkan NIM yang terdaftar
- 🏢 **Pilih Ruangan**: Memilih ruangan tujuan (Ruang Baca, Ruang Diskusi, atau Ruang Komputer)
- ✅ **Konfirmasi Otomatis**: Sistem mencatat kunjungan secara otomatis dengan detail lengkap
- ⚠️ **Validasi Real-time**: Notifikasi jika NIM tidak terdaftar, mahasiswa harus menghubungi admin

### Untuk Administrator (Admin)
- 🛠️ **Dashboard Admin**: Statistik kunjungan real-time per ruangan
- 👥 **Manajemen Pengguna**: Mendaftarkan mahasiswa baru dengan NIM (CRUD lengkap)
- 📊 **Laporan Kunjungan**: Filter berdasarkan tanggal dan ruangan
- 📈 **Rekapitulasi**: Data kunjungan per ruangan (Baca, Diskusi, Komputer)
- 🔐 **Session Management**: Auto-logout untuk keamanan

---

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP Native 7.4+
- **Database**: MySQL 5.7+ 
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Server Lokal**: XAMPP 8.0+

---

## 📂 Struktur Database

### Tabel: `users`
Menyimpan data pengguna (admin dan mahasiswa).

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT(11) | Primary Key |
| username | VARCHAR(100) | Username (UNIQUE) |
| nim | VARCHAR(100) | Nomor Induk Mahasiswa |
| email | VARCHAR(255) | Email (UNIQUE) |
| password | VARCHAR(255) | Password terenkripsi |
| role | ENUM('admin','user') | Peran pengguna |
| device_id | VARCHAR(255) | ID perangkat (UNIQUE) |
| profile_picture | VARCHAR(255) | Path foto profil |
| created_at | TIMESTAMP | Waktu pembuatan |
| updated_at | TIMESTAMP | Waktu update |

### Tabel: `visits`
Mencatat kunjungan mahasiswa.

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT(11) | Primary Key |
| nim | VARCHAR(100) | NIM mahasiswa |
| nama | VARCHAR(255) | Nama mahasiswa |
| tujuan | VARCHAR(255) | Tujuan kunjungan |
| tanggal_kunjungan | DATE | Tanggal kunjungan |
| jam_masuk | TIME | Waktu check-in |
| device_id | VARCHAR(255) | ID perangkat |
| scan_location | VARCHAR(100) | Ruangan (ruang_baca/ruang_diskusi/ruang_komputer) |
| visit_method | ENUM('manual','scan') | Metode pencatatan |
| created_at | TIMESTAMP | Waktu pencatatan |

---

## ⚙️ Cara Instalasi & Setup Lokal

Untuk menjalankan proyek ini di komputer Anda, ikuti langkah-langkah berikut:

### 1️⃣ Clone Repositori
```bash
git clone https://github.com/YourUsername/libraryweb.git
```

### 2️⃣ Pindahkan Folder Proyek
- Pindahkan folder `libraryweb` ke dalam direktori `htdocs` di folder instalasi XAMPP Anda.
  ```
  C:\xampp\htdocs\libraryweb
  ```

### 3️⃣ Setup Database
1. Buka **XAMPP Control Panel**, start **Apache** dan **MySQL**
2. Buka **phpMyAdmin** melalui browser: `http://localhost/phpmyadmin`
3. Buat database baru dengan nama `libraryweb`:
   ```sql
   CREATE DATABASE libraryweb;
   ```
4. Import file SQL:
   - Klik database `libraryweb`
   - Pilih tab **Import**
   - Pilih file `database/libraryweb.sql`
   - Klik **Go**

### 4️⃣ Konfigurasi Koneksi Database
Buka file `includes/db.php` dan pastikan konfigurasi sudah sesuai:

```php
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
 
```

### 5️⃣ Buat Folder Upload
Pastikan folder `uploads/` ada di root project:
```
libraryweb/uploads/
```

### 6️⃣ Jalankan Aplikasi
1. Pastikan **Apache** dan **MySQL** sudah running di XAMPP
2. Buka browser dan akses:
   ```
   http://localhost/libraryweb
   ```
   atau
   ```
   http://localhost/libraryweb/login.php
   ```

---

## 🔐 Akun Default

Setelah import database, gunakan akun berikut:

| Role | Username | Password |
|------|----------|----------|
| **Admin** | `admin` | `admin123` |

> ⚠️ **Penting**: Ganti password default setelah login pertama!

---

## 📊 Import Database via Terminal

Jika ingin import via command line:

```bash
mysql -u root -p libraryweb < database/libraryweb.sql
```

Masukkan password MySQL Anda (kosongkan jika default XAMPP).

---

## 🎯 Cara Penggunaan

### Untuk Mahasiswa:
1. **Scan QR Code** di perpustakaan
2. **Input NIM** pada form yang muncul
3. **Pilih Ruangan** yang akan dikunjungi:
   - 📖 Ruang Baca
   - 💬 Ruang Diskusi
   - 💻 Ruang Komputer
4. **Konfirmasi** - Kunjungan berhasil dicatat!

### Untuk Admin:
1. **Login** dengan akun admin
2. **Daftarkan mahasiswa** melalui menu Manajemen Pengguna
   - Pastikan **NIM** diisi dengan benar
3. **Lihat laporan** kunjungan di Dashboard
4. **Export data** untuk dokumentasi

---

## 🐛 Troubleshooting

### ❌ Database Connection Error
**Solusi:**
- Pastikan MySQL running di XAMPP
- Cek konfigurasi di `includes/config.php`
- Pastikan database `libraryweb` sudah dibuat dan diimport

### ❌ NIM Tidak Ditemukan
**Solusi:**
- Mahasiswa belum didaftarkan oleh Admin
- Hubungi Admin perpustakaan untuk pendaftaran
- Admin: Tambahkan mahasiswa di menu Manajemen Pengguna

### ❌ Upload Foto Gagal
**Solusi:**
- Buat folder `uploads/` di root project
- Set permission folder (CHMOD 755 untuk Linux/Mac)
- Pastikan ukuran file < 2MB dan format JPG/PNG

---

## 📁 Struktur Folder Lengkap

```
libraryweb/
│
├── css/                              # Folder Stylesheet
│   ├── admin_dashboard.css          # Style dashboard admin
│   ├── profile.css                  # Style halaman profil
│   ├── rekapitulasi.css            # Style laporan rekapitulasi
│   ├── scan.css                     # Style halaman scan QR
│   ├── session_timeout.css          # Style notifikasi timeout
│   └── style.css                    # Style global aplikasi
│
├── database/                         # Folder Database
│   └── libraryweb.sql               # File SQL database
│
├── img/                             # Folder Gambar & Ikon
│   └── (berbagai gambar aplikasi)
│
├── includes/                         # Folder Konfigurasi
│   ├── config.php                   # Konfigurasi database & aplikasi
│   ├── db.php                       # File koneksi database
│   └── index.php                    # Route handler
│
├── js/                              # Folder JavaScript
│   └── session_timeout.js           # Handler session timeout otomatis
│
├── admin_dashboard.php               # Dashboard admin dengan statistik
├── catat_kunjungan_scan.php         # Halaman scan QR code untuk user
├── catat_kunjungan.php              # Form pencatatan kunjungan manual
├── check_session.php                # Validasi session pengguna
├── edit_pengguna.php                # Form edit data pengguna
├── extend_session.php               # Perpanjang durasi session
├── laporan_statistik.php            # Halaman laporan & statistik kunjungan
├── login.php                        # Halaman login (admin only)
├── logout.php                       # Proses logout & destroy session
├── manajemen_pengguna.php           # CRUD manajemen pengguna
├── process_scan.php                 # Proses scan QR & validasi NIM
├── profile.php                      # Halaman profil admin
├── rekapitulasi_ruang_baca.php      # Rekapitulasi kunjungan ruang baca
├── rekapitulasi_ruang_diskusi.php   # Rekapitulasi kunjungan ruang diskusi
├── rekapitulasi_ruang_komputer.php  # Rekapitulasi kunjungan ruang komputer
└── scan_success.php                 # Halaman konfirmasi scan berhasil
```

---

## 👨‍💻 Dibuat Oleh

**Developer:** Edward  
**Institusi:** Politeknik Negeri Batam  
**Project:** Sistem Pencatatan Pengunjung Ruangan Berbasis Web pada Perpustakaan Politeknik Negeri Batam
**Tahun:** 2025  

---

## 📄 Lisensi

Project ini dibuat untuk keperluan akademik Politeknik Negeri Batam.

© 2025 LibraryWeb - Politeknik Negeri Batam

---

## 🙏 Kontribusi

Kontribusi sangat diterima! Silakan fork repository ini dan buat pull request.

---

**⭐ Star repository ini jika bermanfaat!**