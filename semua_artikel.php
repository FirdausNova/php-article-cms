<?php
session_start();

// Include database connection
require_once 'config/database.php';

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9; // Number of articles per page
$offset = ($page - 1) * $per_page;

// Get total number of articles
$sql = "SELECT COUNT(*) as total FROM artikel";
$result = $conn->query($sql);
$total_articles = $result->fetch_assoc()['total'];

// Get articles for current page
$sql = "SELECT a.*, k.nama as kategori_nama, k.slug as kategori_slug 
        FROM artikel a 
        JOIN kategori k ON a.kategori_id = k.id 
        ORDER BY a.tanggal_publikasi DESC 
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $per_page);
$stmt->execute();
$result = $stmt->get_result();
$articles = [];

while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
}

// Calculate total pages for pagination
$total_pages = ceil($total_articles / $per_page);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Artikel - Portal Artikel</title>
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
                        <li class="nav-item"><a class="nav-link px-3" href="index.php"><i class="fas fa-home me-1"></i> Beranda</a></li>
                        <li class="nav-item"><a class="nav-link active px-3" href="semua_artikel.php"><i class="fas fa-newspaper me-1"></i> Semua Artikel</a></li>
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

    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="border-bottom pb-3">Semua Artikel</h2>
                    <p class="text-muted">Menampilkan semua artikel yang tersedia (<?php echo $total_articles; ?> artikel)</p>
                </div>
            </div>

            <!-- Articles Grid -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                <?php if (count($articles) > 0): ?>
                    <?php foreach ($articles as $article): ?>
                        <div class="col">
                            <div class="card h-100 shadow-sm hover-shadow">
                                <?php if (!empty($article['gambar'])): ?>
                                <img src="<?php echo htmlspecialchars($article['gambar']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($article['judul']); ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                <div class="bg-light text-center py-5">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <a href="kategori.php?slug=<?php echo htmlspecialchars($article['kategori_slug']); ?>" class="badge bg-primary text-decoration-none">
                                            <?php echo htmlspecialchars($article['kategori_nama']); ?>
                                        </a>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo date('d M Y', strtotime($article['tanggal_publikasi'])); ?>
                                        </small>
                                    </div>
                                    <h5 class="card-title">
                                        <a href="artikel.php?id=<?php echo $article['id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($article['judul']); ?>
                                        </a>
                                    </h5>
                                    <p class="card-text">
                                        <?php 
                                        $excerpt = strip_tags($article['konten']);
                                        echo strlen($excerpt) > 100 ? substr($excerpt, 0, 100) . '...' : $excerpt; 
                                        ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="artikel.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        Baca Selengkapnya <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Tidak ada artikel yang ditemukan.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="semua_artikel.php?page=<?php echo $page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="semua_artikel.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="semua_artikel.php?page=<?php echo $page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-book-open me-2"></i> Portal Artikel</h5>
                    <p>Situs berbagi artikel dan pengetahuan untuk semua.</p>
                </div>
                <div class="col-md-3">
                    <h5>Navigasi</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Beranda</a></li>
                        <li><a href="semua_artikel.php" class="text-white">Semua Artikel</a></li>
                        <li><a href="kategori.php" class="text-white">Kategori</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Tautan</h5>
                    <ul class="list-unstyled">
                        <li><a href="login.php" class="text-white">Login</a></li>
                        <?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <li><a href="admin/index.php" class="text-white">Admin Panel</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Portal Artikel. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>