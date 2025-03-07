<?php
session_start();

// Include database connection
require_once 'config/database.php';

// Get search keyword
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 9; // Number of articles per page
$offset = ($page - 1) * $per_page;

// Initialize variables
$articles = [];
$total_articles = 0;

// Search articles if keyword is provided
if (!empty($keyword)) {
    // Count total matching articles for pagination
    $sql = "SELECT COUNT(*) as total FROM artikel 
            WHERE judul LIKE ? OR konten LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_term = "%$keyword%";
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_articles = $result->fetch_assoc()['total'];
    
    // Get articles for current page
    $sql = "SELECT a.*, k.nama as kategori_nama, k.slug as kategori_slug 
            FROM artikel a 
            JOIN kategori k ON a.kategori_id = k.id 
            WHERE a.judul LIKE ? OR a.konten LIKE ? 
            ORDER BY a.tanggal_publikasi DESC 
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $search_term, $search_term, $offset, $per_page);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
}

// Calculate total pages for pagination
$total_pages = ceil($total_articles / $per_page);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo !empty($keyword) ? 'Hasil Pencarian: ' . htmlspecialchars($keyword) : 'Pencarian'; ?> - Portal Artikel</title>
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

    <!-- Search Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <h2 class="mb-4 text-center">Cari Artikel</h2>
                    <form action="search.php" method="GET" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Masukkan kata kunci..." name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                            <button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i> Cari</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Results -->
    <section class="py-5">
        <div class="container">
            <?php if (!empty($keyword)): ?>
                <h3 class="mb-4">Hasil Pencarian: "<?php echo htmlspecialchars($keyword); ?>" (<?php echo $total_articles; ?> artikel)</h3>
                
                <?php if (count($articles) > 0): ?>
                    <div class="row">
                        <?php foreach ($articles as $article): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <img src="<?php echo !empty($article['gambar']) ? $article['gambar'] : 'assets/images/placeholder.jpg'; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($article['judul']); ?>">
                                <div class="card-body">
                                    <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($article['kategori_nama']); ?></span>
                                    <h5 class="card-title"><?php echo htmlspecialchars($article['judul']); ?></h5>
                                    <?php 
                                    $content = strip_tags($article['konten']);
                                    $excerpt = substr($content, 0, 100) . '...';
                                    
                                    // Highlight search term in excerpt if found
                                    if (stripos($excerpt, $keyword) !== false) {
                                        $excerpt = preg_replace('/(' . preg_quote($keyword, '/') . ')/i', '<span class="bg-warning">$1</span>', $excerpt);
                                    }
                                    ?>
                                    <p class="card-text"><?php echo $excerpt; ?></p>
                                </div>
                                <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                                    <small class="text-muted">Dipublikasikan: <?php echo date('d M Y', strtotime($article['tanggal_publikasi'])); ?></small>
                                    <a href="artikel.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-outline-primary">Baca Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="search.php?keyword=<?php echo urlencode($keyword); ?>&page=<?php echo $page-1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="search.php?keyword=<?php echo urlencode($keyword); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="search.php?keyword=<?php echo urlencode($keyword); ?>&page=<?php echo $page+1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p class="mb-0">Tidak ada artikel yang sesuai dengan kata kunci "<?php echo htmlspecialchars($keyword); ?>".</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <p class="mb-0">Silakan masukkan kata kunci untuk mencari artikel.</p>
                </div>
            <?php endif; ?>
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