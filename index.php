<?php
session_start();

// Include database connection
require_once 'config/database.php';

// Fetch latest articles from database
$sql = "SELECT a.*, k.nama as kategori_nama, k.slug as kategori_slug 
        FROM artikel a 
        JOIN kategori k ON a.kategori_id = k.id 
        ORDER BY a.tanggal_publikasi DESC 
        LIMIT 3";
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
    <title>Portal Artikel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header/Navbar -->
    <header class="bg-primary text-white py-3 shadow-sm sticky-top">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-dark p-0">
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
                                <img src="<?php echo strpos($_SESSION['user_foto'] ?? '', 'uploads/') === 0 ? 'assets/images/' . htmlspecialchars($_SESSION['user_foto']) : 'assets/images/default.jpg'; ?>" alt="Profile" class="rounded-circle me-1" style="width: 24px; height: 24px; object-fit: cover;"> <?php echo htmlspecialchars($_SESSION['user_nama']); ?>
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
                <?php if (count($articles) > 0): ?>
                    <?php foreach ($articles as $article): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <img src="<?php echo !empty($article['gambar']) ? $article['gambar'] : 'assets/images/placeholder.jpg'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($article['judul']); ?>">
                            <div class="card-body">
                                <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($article['kategori_nama']); ?></span>
                                <h5 class="card-title"><?php echo htmlspecialchars($article['judul']); ?></h5>
                                <p class="card-text"><?php echo substr(strip_tags($article['konten']), 0, 100) . '...'; ?></p>
                            </div>
                            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                                <small class="text-muted">Dipublikasikan: <?php echo date('d M Y', strtotime($article['tanggal_publikasi'])); ?></small>
                                <a href="artikel.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline-primary">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p>Belum ada artikel yang tersedia.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="semua_artikel.php" class="btn btn-primary">Lihat Semua Artikel</a>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="mb-4">Kategori Artikel</h2>
            <div class="row">
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $category): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="<?php echo !empty($category['icon']) ? $category['icon'] : 'fas fa-folder'; ?> fa-3x mb-3 text-primary"></i>
                                <h5 class="card-title"><?php echo htmlspecialchars($category['nama']); ?></h5>
                                <p class="card-text">Artikel seputar <?php echo htmlspecialchars(strtolower($category['nama'])); ?>.</p>
                                <a href="kategori.php?slug=<?php echo $category['slug']; ?>" class="btn btn-outline-primary">Lihat Artikel</a>
                            </div>
                        </div>
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
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5>Portal Artikel</h5>
                    <p>Situs web yang menyediakan berbagai artikel menarik dan informatif dari berbagai kategori.</p>
                </div>
                <div class="col-md-6">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Beranda</a></li>
                        <li><a href="semua_artikel.php" class="text-white">Semua Artikel</a></li>
                        <li><a href="kategori.php" class="text-white">Kategori</a></li>
                    </ul>
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