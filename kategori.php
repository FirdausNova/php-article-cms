<?php
session_start();

// Include database connection
require_once 'config/database.php';

// Get category from URL parameter
$category_id = isset($_GET['id']) ? $_GET['id'] : null;
$category_slug = isset($_GET['slug']) ? $_GET['slug'] : null;

// Initialize variables
$category = null;
$articles = [];

// Fetch category information
if ($category_id) {
    $sql = "SELECT * FROM kategori WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
} elseif ($category_slug) {
    $sql = "SELECT * FROM kategori WHERE slug = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category_slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
}

// If category found, get articles from that category
if ($category) {
    $sql = "SELECT a.*, k.nama as kategori_nama 
            FROM artikel a 
            JOIN kategori k ON a.kategori_id = k.id 
            WHERE a.kategori_id = ? 
            ORDER BY a.tanggal_publikasi DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $articles[] = $row;
    }
}

// If no specific category, get all categories
$sql = "SELECT k.*, COUNT(a.id) as artikel_count 
        FROM kategori k 
        LEFT JOIN artikel a ON k.id = a.kategori_id 
        GROUP BY k.id 
        ORDER BY k.nama ASC";
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
    <title><?php echo $category ? htmlspecialchars($category['nama']) . ' - ' : ''; ?>Kategori - Portal Artikel</title>
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
                        <li class="nav-item"><a class="nav-link active px-3" href="kategori.php"><i class="fas fa-list me-1"></i> Kategori</a></li>
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

    <!-- Main Content -->
    <div class="container py-5">
        <?php if ($category): ?>
            <!-- Category Articles -->
            <div class="mb-4">
                <h2 class="mb-4">Artikel Kategori: <?php echo htmlspecialchars($category['nama']); ?></h2>
                
                <?php if (count($articles) > 0): ?>
                    <div class="row">
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
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p class="mb-0">Belum ada artikel dalam kategori ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- All Categories -->
            <h2 class="mb-4">Semua Kategori</h2>
            
            <div class="row">
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $cat): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="<?php echo !empty($cat['icon']) ? $cat['icon'] : 'fas fa-folder'; ?> fa-3x mb-3 text-primary"></i>
                                <h5 class="card-title"><?php echo htmlspecialchars($cat['nama']); ?></h5>
                                <p class="card-text">Artikel seputar <?php echo htmlspecialchars(strtolower($cat['nama'])); ?>.</p>
                                <p><span class="badge bg-primary"><?php echo $cat['artikel_count']; ?> artikel</span></p>
                                <a href="kategori.php?id=<?php echo $cat['id']; ?>" class="btn btn-outline-primary">Lihat Artikel</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <p class="mb-0">Belum ada kategori yang tersedia.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

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
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-youtube"></i></a>
                    </div>
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