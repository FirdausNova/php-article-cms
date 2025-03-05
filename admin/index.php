<?php
session_start();

// Include database connection
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get counts for dashboard
$artikel_count = $conn->query("SELECT COUNT(*) as count FROM artikel")->fetch_assoc()['count'];
$kategori_count = $conn->query("SELECT COUNT(*) as count FROM kategori")->fetch_assoc()['count'];
$komentar_count = $conn->query("SELECT COUNT(*) as count FROM komentar")->fetch_assoc()['count'];

// Get recent articles
$recent_articles = [];
$result = $conn->query("SELECT a.id, a.judul, a.tanggal_publikasi, k.nama as kategori_nama 
                      FROM artikel a 
                      JOIN kategori k ON a.kategori_id = k.id 
                      ORDER BY a.tanggal_publikasi DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $recent_articles[] = $row;
}

// Get recent comments
$recent_comments = [];
$result = $conn->query("SELECT k.*, a.judul as artikel_judul 
                      FROM komentar k 
                      JOIN artikel a ON k.artikel_id = a.id 
                      ORDER BY k.created_at DESC LIMIT 5");
while ($row = $result->fetch_assoc()) {
    $recent_comments[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Portal Artikel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
        }
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="artikel.php">Artikel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kategori.php">Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="komentar.php">Komentar</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_nama']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user"></i> Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar py-4">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="artikel.php">
                                <i class="fas fa-newspaper"></i> Artikel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kategori.php">
                                <i class="fas fa-tags"></i> Kategori
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="komentar.php">
                                <i class="fas fa-comments"></i> Komentar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Lihat Website
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="artikel_tambah.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus"></i> Tambah Artikel</a>
                            <a href="kategori_tambah.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-plus"></i> Tambah Kategori</a>
                        </div>
                    </div>
                </div>

                <!-- Stats cards -->
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Artikel</h6>
                                        <h2 class="mb-0"><?php echo $artikel_count; ?></h2>
                                    </div>
                                    <i class="fas fa-newspaper fa-3x opacity-50"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="artikel.php" class="text-white text-decoration-none">Lihat Detail</a>
                                <i class="fas fa-arrow-right text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Kategori</h6>
                                        <h2 class="mb-0"><?php echo $kategori_count; ?></h2>
                                    </div>
                                    <i class="fas fa-tags fa-3x opacity-50"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="kategori.php" class="text-white text-decoration-none">Lihat Detail</a>
                                <i class="fas fa-arrow-right text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card stat-card bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Komentar</h6>
                                        <h2 class="mb-0"><?php echo $komentar_count; ?></h2>
                                    </div>
                                    <i class="fas fa-comments fa-3x opacity-50"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="komentar.php" class="text-white text-decoration-none">Lihat Detail</a>
                                <i class="fas fa-arrow-right text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Articles -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Artikel Terbaru</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Judul</th>
                                                <th>Kategori</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($recent_articles) > 0): ?>
                                                <?php foreach ($recent_articles as $article): ?>
                                                <tr>
                                                    <td><a href="artikel_edit.php?id=<?php echo $article['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($article['judul']); ?></a></td>
                                                    <td><?php echo htmlspecialchars($article['kategori_nama']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($article['tanggal_publikasi'])); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">Belum ada artikel</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="artikel.php" class="btn btn-sm btn-outline-primary">Lihat Semua Artikel</a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Comments -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Komentar Terbaru</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nama</th>
                                                <th>Artikel</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($recent_comments) > 0): ?>
                                                <?php foreach ($recent_comments as $comment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($comment['nama']); ?></td>
                                                    <td><a href="../artikel.php?id=<?php echo $comment['artikel_id']; ?>" class="text-decoration-none" target="_blank"><?php echo htmlspecialchars(substr($comment['artikel_judul'], 0, 30)) . (strlen($comment['artikel_judul']) > 30 ? '...' : ''); ?></a></td>
                                                    <td><?php echo date('d/m/Y', strtotime($comment['created_at'])); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">Belum ada komentar</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <a href="komentar.php" class="btn btn-sm btn-outline-primary">Lihat Semua Komentar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>