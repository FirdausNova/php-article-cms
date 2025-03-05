<?php
// Include database connection
require_once 'config/database.php';

// Check if article ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$artikel_id = $_GET['id'];

// Get article details
$sql = "SELECT a.*, k.nama as kategori_nama, k.slug as kategori_slug 
        FROM artikel a 
        JOIN kategori k ON a.kategori_id = k.id 
        WHERE a.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artikel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Article not found
    header('Location: index.php');
    exit;
}

$artikel = $result->fetch_assoc();

// Get related articles from the same category
$sql = "SELECT id, judul, slug, gambar, tanggal_publikasi 
        FROM artikel 
        WHERE kategori_id = ? AND id != ? 
        ORDER BY tanggal_publikasi DESC 
        LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $artikel['kategori_id'], $artikel_id);
$stmt->execute();
$related_result = $stmt->get_result();
$related_articles = [];

while ($row = $related_result->fetch_assoc()) {
    $related_articles[] = $row;
}

// Get comments for this article
$sql = "SELECT * FROM komentar WHERE artikel_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artikel_id);
$stmt->execute();
$comments_result = $stmt->get_result();
$comments = [];

while ($row = $comments_result->fetch_assoc()) {
    $comments[] = $row;
}

// Handle comment submission
$comment_error = '';
$comment_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $isi = trim($_POST['isi']);
    
    // Simple validation
    if (empty($nama) || empty($email) || empty($isi)) {
        $comment_error = 'Semua field harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $comment_error = 'Email tidak valid';
    } else {
        // Insert comment
        $sql = "INSERT INTO komentar (artikel_id, nama, email, isi) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $artikel_id, $nama, $email, $isi);
        
        if ($stmt->execute()) {
            $comment_success = 'Komentar berhasil ditambahkan';
            // Refresh the page to show the new comment
            header("Location: artikel.php?id=$artikel_id&comment_added=1");
            exit;
        } else {
            $comment_error = 'Gagal menambahkan komentar: ' . $stmt->error;
        }
    }
}

// Check if comment was just added
if (isset($_GET['comment_added']) && $_GET['comment_added'] == 1) {
    $comment_success = 'Komentar berhasil ditambahkan';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artikel['judul']); ?> - Portal Artikel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta property="og:title" content="<?php echo htmlspecialchars($artikel['judul']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars(substr(strip_tags($artikel['konten']), 0, 160)); ?>...">
    <meta property="og:image" content="<?php echo htmlspecialchars($artikel['gambar']); ?>">
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
                            <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                            <li class="nav-item"><a class="nav-link" href="kategori.php">Kategori</a></li>
                            <li class="nav-item"><a class="nav-link" href="tentang.php">Tentang Kami</a></li>
                            <li class="nav-item"><a class="nav-link" href="admin/login.php">Login Admin</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Article Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="kategori.php?id=<?php echo $artikel['kategori_id']; ?>"><?php echo htmlspecialchars($artikel['kategori_nama']); ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($artikel['judul']); ?></li>
                        </ol>
                    </nav>
                    
                    <!-- Article Header -->
                    <h1 class="mb-3"><?php echo htmlspecialchars($artikel['judul']); ?></h1>
                    <div class="d-flex align-items-center mb-4">
                        <span class="badge bg-primary me-2"><?php echo htmlspecialchars($artikel['kategori_nama']); ?></span>
                        <span class="text-muted"><i class="far fa-calendar-alt me-1"></i> <?php echo date('d F Y', strtotime($artikel['tanggal_publikasi'])); ?></span>
                    </div>
                    
                    <!-- Featured Image -->
                    <img src="<?php echo htmlspecialchars($artikel['gambar']); ?>" class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($artikel['judul']); ?>">
                    
                    <!-- Article Content -->
                    <div class="article-content mb-5">
                        <?php echo $artikel['konten']; ?>
                    </div>
                    
                    <!-- Share Buttons -->
                    <div class="mb-5">
                        <h5>Bagikan Artikel:</h5>
                        <div class="d-flex">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" class="btn btn-outline-primary me-2" target="_blank"><i class="fab fa-facebook-f"></i> Facebook</a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($artikel['judul']); ?>" class="btn btn-outline-info me-2" target="_blank"><i class="fab fa-twitter"></i> Twitter</a>
                            <a href="https://wa.me/?text=<?php echo urlencode($artikel['judul'] . ' - http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" class="btn btn-outline-success" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                        </div>
                    </div>
                    
                    <!-- Comments Section -->
                    <div class="mb-5">
                        <h3 class="mb-4">Komentar (<?php echo count($comments); ?>)</h3>
                        
                        <?php if (!empty($comment_success)): ?>
                        <div class="alert alert-success">
                            <?php echo $comment_success; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($comment_error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $comment_error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Comment Form -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Tinggalkan Komentar</h5>
                                <form action="" method="POST">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama</label>
                                        <input type="text" class="form-control" id="nama" name="nama" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="form-text">Email Anda tidak akan dipublikasikan.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="isi" class="form-label">Komentar</label>
                                        <textarea class="form-control" id="isi" name="isi" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" name="submit_comment" class="btn btn-primary">Kirim Komentar</button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Comments List -->
                        <?php if (count($comments) > 0): ?>
                            <?php foreach ($comments as $comment): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="card-subtitle"><?php echo htmlspecialchars($comment['nama']); ?></h6>
                                        <small class="text-muted"><?php echo date('d M Y H:i', strtotime($comment['created_at'])); ?></small>
                                    </div>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['isi'])); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Belum ada komentar. Jadilah yang pertama berkomentar!</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Related Articles -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Artikel Terkait</h5>
                        </div>
                        <div class="card-body">
                            <?php if (count($related_articles) > 0): ?>
                                <?php foreach ($related_articles as $related): ?>
                                <div class="d-flex mb-3">
                                    <img src="<?php echo htmlspecialchars($related['gambar']); ?>" class="me-3" alt="<?php echo htmlspecialchars($related['judul']); ?>" style="width: 80px; height: 60px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1"><a href="artikel.php?id=<?php echo $related['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($related['judul']); ?></a></h6>
                                        <small class="text-muted"><?php echo date('d M Y', strtotime($related['tanggal_publikasi'])); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Tidak ada artikel terkait.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Categories -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Kategori</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php 
                                $sql = "SELECT k.*, COUNT(a.id) as artikel_count 
                                        FROM kategori k 
                                        LEFT JOIN artikel a ON k.id = a.kategori_id 
                                        GROUP BY k.id 
                                        ORDER BY k.nama ASC";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()): 
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="kategori.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['nama']); ?></a>
                                    <span class="badge bg-primary rounded-pill"><?php echo $row['artikel_count']; ?></span>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Popular Articles -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Artikel Populer</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $sql = "SELECT id, judul, gambar, tanggal_publikasi FROM artikel ORDER BY RAND() LIMIT 3";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                            ?>
                            <div class="d-flex mb-3">
                                <img src="<?php echo htmlspecialchars($row['gambar']); ?>" class="me-3" alt="<?php echo htmlspecialchars($row['judul']); ?>" style="width: 80px; height: 60px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-1"><a href="artikel.php?id=<?php echo $row['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($row['judul']); ?></a></h6>
                                    <small class="text-muted"><?php echo date('d M Y', strtotime($row['tanggal_publikasi'])); ?></small>
                                </div>
                            </div>
                            <?php
                                endwhile;
                            else:
                            ?>
                            <p class="text-muted">Tidak ada artikel populer.</p>
                            <?php endif; ?>
                        </div>
                    </div>
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
                    <h5>Ikuti Kami</h5>
                    <div class="d-flex">
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