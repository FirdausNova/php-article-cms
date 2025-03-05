<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Artikel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="bg-primary text-white py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0">Portal Artikel</h1>
                </div>
                <div class="col-md-6">
                    <nav class="navbar navbar-expand navbar-dark justify-content-end">
                        <ul class="navbar-nav">
                            <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
                            <li class="nav-item"><a class="nav-link" href="kategori.php">Kategori</a></li>
                            <li class="nav-item"><a class="nav-link" href="tentang.php">Tentang Kami</a></li>
                            <li class="nav-item"><a class="nav-link" href="admin/login.php">Login Admin</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mx-auto text-center">
                    <h2 class="display-4">Selamat Datang di Portal Artikel</h2>
                    <p class="lead">Temukan berbagai artikel menarik dan informatif dari berbagai kategori.</p>
                    <div class="mt-4">
                        <form action="search.php" method="GET" class="d-flex justify-content-center">
                            <div class="input-group mb-3" style="max-width: 500px;">
                                <input type="text" class="form-control" placeholder="Cari artikel..." name="keyword">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Articles -->
    <section class="py-5">
        <div class="container">
            <h2 class="mb-4">Artikel Terbaru</h2>
            <div class="row">
                <!-- Article cards will be generated here from database -->
                <!-- Placeholder for demonstration -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="assets/images/placeholder.jpg" class="card-img-top" alt="Artikel Image">
                        <div class="card-body">
                            <span class="badge bg-primary mb-2">Teknologi</span>
                            <h5 class="card-title">Judul Artikel Pertama</h5>
                            <p class="card-text">Ini adalah deskripsi singkat dari artikel pertama yang akan ditampilkan di halaman beranda.</p>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                            <small class="text-muted">Dipublikasikan: 10 Juni 2023</small>
                            <a href="artikel.php?id=1" class="btn btn-sm btn-outline-primary">Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="assets/images/placeholder.jpg" class="card-img-top" alt="Artikel Image">
                        <div class="card-body">
                            <span class="badge bg-success mb-2">Kesehatan</span>
                            <h5 class="card-title">Judul Artikel Kedua</h5>
                            <p class="card-text">Ini adalah deskripsi singkat dari artikel kedua yang akan ditampilkan di halaman beranda.</p>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                            <small class="text-muted">Dipublikasikan: 8 Juni 2023</small>
                            <a href="artikel.php?id=2" class="btn btn-sm btn-outline-primary">Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="assets/images/placeholder.jpg" class="card-img-top" alt="Artikel Image">
                        <div class="card-body">
                            <span class="badge bg-warning mb-2">Pendidikan</span>
                            <h5 class="card-title">Judul Artikel Ketiga</h5>
                            <p class="card-text">Ini adalah deskripsi singkat dari artikel ketiga yang akan ditampilkan di halaman beranda.</p>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                            <small class="text-muted">Dipublikasikan: 5 Juni 2023</small>
                            <a href="artikel.php?id=3" class="btn btn-sm btn-outline-primary">Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="semua-artikel.php" class="btn btn-primary">Lihat Semua Artikel</a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="mb-4">Kategori Artikel</h2>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-laptop fa-3x mb-3 text-primary"></i>
                            <h5 class="card-title">Teknologi</h5>
                            <p class="card-text">Artikel seputar teknologi terbaru dan perkembangannya.</p>
                            <a href="kategori.php?id=1" class="btn btn-outline-primary">Lihat Artikel</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-heartbeat fa-3x mb-3 text-danger"></i>
                            <h5 class="card-title">Kesehatan</h5>
                            <p class="card-text">Tips dan informasi seputar kesehatan dan gaya hidup sehat.</p>
                            <a href="kategori.php?id=2" class="btn btn-outline-primary">Lihat Artikel</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-graduation-cap fa-3x mb-3 text-warning"></i>
                            <h5 class="card-title">Pendidikan</h5>
                            <p class="card-text">Artikel tentang dunia pendidikan dan perkembangannya.</p>
                            <a href="kategori.php?id=3" class="btn btn-outline-primary">Lihat Artikel</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="fas fa-utensils fa-3x mb-3 text-success"></i>
                            <h5 class="card-title">Kuliner</h5>
                            <p class="card-text">Informasi seputar kuliner dan resep makanan.</p>
                            <a href="kategori.php?id=4" class="btn btn-outline-primary">Lihat Artikel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h3>Berlangganan Newsletter</h3>
                    <p>Dapatkan update artikel terbaru langsung ke email Anda.</p>
                </div>
                <div class="col-md-6">
                    <form action="subscribe.php" method="POST" class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Alamat Email Anda" required>
                        <button type="submit" class="btn btn-light">Berlangganan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5>Portal Artikel</h5>
                    <p>Situs web yang menyediakan berbagai artikel menarik dan informatif dari berbagai kategori.</p>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Beranda</a></li>
                        <li><a href="kategori.php" class="text-white">Kategori</a></li>
                        <li><a href="tentang.php" class="text-white">Tentang Kami</a></li>
                        <li><a href="kontak.php" class="text-white">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Hubungi Kami</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> info@portalartikel.com</li>
                        <li><i class="fas fa-phone me-2"></i> (021) 1234-5678</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> Jl. Contoh No. 123, Jakarta</li>
                    </ul>
                    <div class="mt-3">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; 2023 Portal Artikel. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>