<?php
session_start();

// Include database connection
require_once 'config/database.php';

// Fetch latest articles from database
$sql = "SELECT a.*, k.nama as kategori_nama, k.slug as kategori_slug 
        FROM artikel a 
        JOIN kategori k ON a.kategori_id = k.id 
        ORDER BY a.tanggal_publikasi DESC 
        LIMIT 6";
$result = $conn->query($sql);
$articles = [];

while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
}

// Fetch all categories
$sql = "SELECT * FROM kategori ORDER BY nama ASC LIMIT 4";
$result = $conn->query($sql);
$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Artikel - Temukan Artikel Menarik dan Informatif</title>
    <meta name="description" content="Portal Artikel adalah situs berbagi artikel dan pengetahuan untuk semua. Temukan berbagai artikel menarik dan informatif dari berbagai kategori.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header/Navbar -->
    <header class="shadow-sm sticky-top">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-dark p-0 py-2">
                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    <i class="fas fa-book-open me-2"></i>
                    <h1 class="h3 mb-0">Portal Artikel</h1>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link active px-3" href="index.php"><i class="fas fa-home me-1"></i> Beranda</a></li>
                        <li class="nav-item"><a class="nav-link px-3" href="semua_artikel.php"><i class="fas fa-newspaper me-1"></i> Semua Artikel</a></li>
                        <li class="nav-item"><a class="nav-link px-3" href="kategori.php"><i class="fas fa-list me-1"></i> Kategori</a></li>
                        <?php if(isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle px-3" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="<?php echo !empty($_SESSION['user_foto']) ? (file_exists($_SESSION['user_foto']) ? htmlspecialchars($_SESSION['user_foto']) : 'assets/images/default.jpg') : 'assets/images/default.jpg'; ?>" alt="Profile" class="rounded-circle me-1" style="width: 24px; height: 24px; object-fit: cover;"> <?php echo htmlspecialchars($_SESSION['user_nama']); ?>
                                <?php if(isset($_SESSION['user_role_name'])): ?>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($_SESSION['user_role_name']); ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i> Profil Saya</a></li>
                                <?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                                <li><a class="dropdown-item" href="admin/index.php"><i class="fas fa-user-shield me-2"></i> Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                        <?php else: ?>
                        <li class="nav-item"><a class="nav-link px-3" href="login.php"><i class="fas fa-sign-in-alt me-1"></i> Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <h2 class="display-4 fw-bold">Temukan Artikel Menarik & Informatif</h2>
                    <p class="lead">Portal Artikel menyediakan berbagai konten berkualitas dari berbagai kategori untuk menambah wawasan dan pengetahuan Anda.</p>
                    <div class="mt-4">
                        <form action="search.php" method="GET" class="d-flex">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" placeholder="Cari artikel..." name="keyword" aria-label="Search">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <img src="assets/images/hero-illustration.svg" alt="Ilustrasi Portal Artikel" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Articles -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="fas fa-fire text-primary me-2"></i>Artikel Terbaru</h2>
                <a href="semua_artikel.php" class="btn btn-outline-primary">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div class="row g-4">
                <?php if (count($articles) > 0): ?>
                    <?php foreach ($articles as $index => $article): ?>
                    <?php if ($index === 0): ?>
                    <!-- Featured Article (Larger) -->
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm overflow-hidden">
                            <div class="row g-0">
                                <div class="col-md-6">
                                    <img src="<?php echo !empty($article['gambar']) ? $article['gambar'] : 'assets/images/placeholder.jpg'; ?>" 
                                         class="img-fluid h-100 w-100" style="object-fit: cover;" 
                                         alt="<?php echo htmlspecialchars($article['judul']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <div class="card-body d-flex flex-column h-100 p-4">
                                        <div class="mb-2">
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($article['kategori_nama']); ?></span>
                                            <span class="text-muted ms-2"><i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($article['tanggal_publikasi'])); ?></span>
                                        </div>
                                        <h3 class="card-title"><?php echo htmlspecialchars($article['judul']); ?></h3>
                                        <p class="card-text flex-grow-1"><?php echo substr(strip_tags($article['konten']), 0, 200) . '...'; ?></p>
                                        <a href="artikel.php?id=<?php echo $article['id']; ?>" class="btn btn-primary mt-auto">Baca Selengkapnya</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Regular Articles -->
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="position-relative">
                                <img src="<?php echo !empty($article['gambar']) ? $article['gambar'] : 'assets/images/placeholder.jpg'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($article['judul']); ?>">
                                <span class="badge bg-primary position-absolute" style="top: 10px; left: 10px;"><?php echo htmlspecialchars($article['kategori_nama']); ?></span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($article['judul']); ?></h5>
                                <p class="card-text"><?php echo substr(strip_tags($article['konten']), 0, 100) . '...'; ?></p>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                                <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> <?php echo date('d M Y', strtotime($article['tanggal_publikasi'])); ?></small>
                                <a href="artikel.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline-primary">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Belum ada artikel yang tersedia.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="mb-4 text-center"><i class="fas fa-th-large text-primary me-2"></i>Jelajahi Kategori</h2>
            <div class="row g-4 justify-content-center">
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $category): ?>
                    <div class="col-6 col-md-3">
                        <a href="kategori.php?slug=<?php echo $category['slug']; ?>" class="text-decoration-none">
                            <div class="category-card">
                                <div class="icon">
                                    <i class="<?php echo !empty($category['icon']) ? $category['icon'] : 'fas fa-folder'; ?>"></i>
                                </div>
                                <h5 class="mt-3"><?php echo htmlspecialchars($category['nama']); ?></h5>
                                <p class="text-muted mb-0">Artikel seputar <?php echo htmlspecialchars(strtolower($category['nama'])); ?></p>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>Belum ada kategori yang tersedia.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Main content ends here -->

    <!-- Footer -->
    <footer class="py-4 bg-dark text-white">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <h5><i class="fas fa-book-open me-2"></i>Portal Artikel</h5>
                    <p>Situs web yang menyediakan berbagai artikel menarik dan informatif dari berbagai kategori untuk menambah wawasan dan pengetahuan Anda.</p>
                    <div class="social-links d-flex gap-3 mt-3">
                        <a href="#" class="text-white"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Beranda</a></li>
                        <li><a href="semua_artikel.php" class="text-white">Semua Artikel</a></li>
                        <li><a href="kategori.php" class="text-white">Kategori</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h5>Kontak Kami</h5>
                    <p><i class="fas fa-envelope me-2"></i> info@portalartikel.com</p>
                    <p><i class="fas fa-phone me-2"></i> +62 123 4567 890</p>
                    <p><i class="fas fa-map-marker-alt me-2"></i> Jl. Artikel No. 123, Jakarta</p>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Portal Artikel. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>