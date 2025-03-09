# 📚 Portal Artikel

![version](https://img.shields.io/badge/versi-1.0-blue) ![php](https://img.shields.io/badge/PHP-7.4%2B-purple) ![mysql](https://img.shields.io/badge/MySQL-5.7%2B-orange)

## 🌟 Tentang Aplikasi

Portal Artikel adalah sistem manajemen konten berbasis web yang modern dan responsif untuk mengelola artikl, kategori, dan interaksi pengguna. Dibangun dengan PHP dan MySQL, dilengkapi dengan antarmuka Bootstrap 5 yang elegan dan mudah digunakan.

## ✨ Fitur Utama

### 👤 Manajemen Pengguna
- **Sistem Autentikasi** - Login dan registrasi yang aman
- **Kontrol Akses** - Pembagian peran (Admin dan Pengguna biasa)
- **Profil Pengguna** - Pengelolaan informasi dan foto profil

### 📝 Pengelolaan Artikel
- **CRUD Artikel** - Membuat, membaca, memperbarui, dan menghapus artikel
- **Kategori** - Pengorganisasian artikel berdasarkan kategori
- **Media** - Dukungan unggah dan pengelolaan gambar
- **Editor Teks** - Pemformatan konten yang kaya fitur

### 💬 Sistem Komentar
- **Interaksi Pengguna** - Komentar pada artikel
- **Moderasi** - Pengelolaan komentar oleh admin
- **Notifikasi** - Pemberitahuan aktivitas komentar

### 🔍 Pencarian & Navigasi
- **Pencarian** - Menemukan artikel berdasarkan kata kunci
- **Paginasi** - Navigasi halaman yang efisien
- **Filter Kategori** - Penyaringan artikel berdasarkan kategori

### 📱 Antarmuka Responsif
- **Bootstrap 5** - Desain modern dan responsif
- **Mobile-Friendly** - Tampilan optimal di semua perangkat
- **UI/UX** - Pengalaman pengguna yang intuitif

## 🛠️ Persyaratan Sistem

<<<<<<< HEAD
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Server web Apache/XAMPP
- Browser web modern (Chrome, Firefox, Safari, Edge)
=======
1. Clone or download the repository to your web server directory:
   ```
   git clone https://github.com/FirdausNova/php-article-cms
   ```
>>>>>>> 918b48ff9ab09263047238ca1669c2da843f3d9e

## 📥 Instalasi

### 1️⃣ Persiapan Proyek
Clone atau unduh repositori ke direktori server web Anda:
```bash
git clone https://github.com/FirdausNova/php-article-cms
```

### 2️⃣ Konfigurasi Database
- Buat database MySQL baru
- Impor file `artikel_db.sql` ke database yang telah dibuat

### 3️⃣ Pengaturan Koneksi
Buka dan edit file `config/database.php`:
```php
$host = 'localhost';      // Host database
$username = 'username';   // Username database
$password = 'password';   // Password database
$database = 'artikel_db'; // Nama database
```

### 4️⃣ Konfigurasi Server
- Pastikan proyek berada di direktori root server web (htdocs untuk XAMPP)
- Atur izin direktori `assets/images/uploads` menjadi dapat ditulis (writeable)
- Akses aplikasi melalui browser: `http://localhost/artikel`

## 🚀 Cara Penggunaan

### 👨‍💼 Panel Admin
1. Akses panel admin di `/admin/login.php`
2. Masuk dengan kredensial administrator
3. Kelola artikel, kategori, komentar, dan pengguna
4. Pantau statistik dan aktivitas melalui dashboard

### 👥 Area Pengguna
1. Daftar atau masuk melalui halaman `/login.php`
2. Jelajahi artikel berdasarkan kategori atau pencarian
3. Berikan komentar pada artikel yang menarik
4. Kelola profil dan preferensi pribadi
5. Lihat semua artikel di halaman "Semua Artikel"

## 📂 Struktur Direktori

```
/
├── admin/           # File panel administrasi
├── assets/          # Aset statis (CSS, JS, gambar)
│   ├── css/         # Stylesheet
│   ├── js/          # File JavaScript
│   └── images/      # Gambar dan unggahan
├── config/          # File konfigurasi sistem
└── artikel_db.sql   # Skema database
```

## 🔒 Keamanan

- Perlindungan injeksi SQL dengan prepared statements
- Pencegahan serangan XSS melalui sanitasi input
- Autentikasi berbasis sesi yang aman
- Penyimpanan password dengan hashing

## 📋 Lisensi

Dikembangkan untuk tujuan pendidikan dan penggunaan pribadi.

---

© 2025 Portal Artikel. Hak Cipta Dilindungi.