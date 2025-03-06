<?php
session_start();

// Include database connection
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle article deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get article image path before deleting
    $stmt = $conn->prepare("SELECT gambar FROM artikel WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $artikel = $result->fetch_assoc();
        $gambar_path = $artikel['gambar'];
        
        // Delete the article
        $stmt = $conn->prepare("DELETE FROM artikel WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Delete the image file if it exists and is not a default image
            if (!empty($gambar_path) && file_exists("../$gambar_path") && strpos($gambar_path, 'default') === false) {
                unlink("../$gambar_path");
            }
            
            // Redirect with success message
            header('Location: artikel.php?status=deleted');
            exit;
        } else {
            // Redirect with error message
            header('Location: artikel.php?status=error');
            exit;
        }
    }
}

// Get all articles with category names
$sql = "SELECT a.*, k.nama as kategori_nama 
        FROM artikel a 
        JOIN kategori k ON a.kategori_id = k.id 
        ORDER BY a.tanggal_publikasi DESC";
$result = $conn->query($sql);
$articles = [];

while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
}

// Status messages
$status_messages = [
    'added' => ['type' => 'success', 'message' => 'Artikel berhasil ditambahkan'],
    'updated' => ['type' => 'success', 'message' => 'Artikel berhasil diperbarui'],
    'deleted' => ['type' => 'success', 'message' => 'Artikel berhasil dihapus'],
    'error' => ['type' => 'danger', 'message' => 'Terjadi kesalahan. Silakan coba lagi']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Artikel - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
        }
        .article-thumbnail {
            width: 80px;
            height: 60px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-tachometer-alt me-2"></i>
                <span>Admin Panel</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link px-3" href="index.php"><i class="fas fa-home me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active px-3" href="artikel.php"><i class="fas fa-newspaper me-1"></i> Artikel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="kategori.php"><i class="fas fa-list me-1"></i> Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="komentar.php"><i class="fas fa-comments me-1"></i> Komentar</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-3" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['admin_nama']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i> Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
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
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="artikel.php">
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
                    <h1 class="h2">Kelola Artikel</h1>
                    <a href="artikel_tambah.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Artikel Baru</a>
                </div>
                
                <?php if (isset($_GET['status']) && array_key_exists($_GET['status'], $status_messages)): ?>
                <div class="alert alert-<?php echo $status_messages[$_GET['status']]['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $status_messages[$_GET['status']]['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th width="50">ID</th>
                                        <th width="80">Gambar</th>
                                        <th>Judul</th>
                                        <th>Kategori</th>
                                        <th>Tanggal Publikasi</th>
                                        <th width="150">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($articles) > 0): ?>
                                        <?php foreach ($articles as $article): ?>
                                        <tr>
                                            <td><?php echo $article['id']; ?></td>
                                            <td>
                                                <img src="../<?php echo htmlspecialchars($article['gambar']); ?>" class="article-thumbnail" alt="<?php echo htmlspecialchars($article['judul']); ?>">
                                            </td>
                                            <td>
                                                <a href="../artikel.php?id=<?php echo $article['id']; ?>" target="_blank"><?php echo htmlspecialchars($article['judul']); ?></a>
                                            </td>
                                            <td><?php echo htmlspecialchars($article['kategori_nama']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($article['tanggal_publikasi'])); ?></td>
                                            <td>
                                                <a href="artikel_edit.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-edit"></i> Edit</a>
                                                <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $article['id']; ?>"><i class="fas fa-trash"></i> Hapus</a>
                                                
                                                <!-- Delete Confirmation Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $article['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Apakah Anda yakin ingin menghapus artikel "<?php echo htmlspecialchars($article['judul']); ?>"?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <a href="artikel.php?action=delete&id=<?php echo $article['id']; ?>" class="btn btn-danger">Hapus</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Belum ada artikel</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>