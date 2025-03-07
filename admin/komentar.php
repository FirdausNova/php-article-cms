<?php
session_start();

// Include database connection
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle comment deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Delete the comment
    $stmt = $conn->prepare("DELETE FROM komentar WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Redirect with success message
        header('Location: komentar.php?status=deleted');
        exit;
    } else {
        // Redirect with error message
        header('Location: komentar.php?status=error');
        exit;
    }
}

// Handle comment approval/rejection
if (isset($_GET['action']) && ($_GET['action'] == 'approve' || $_GET['action'] == 'reject') && isset($_GET['id'])) {
    $id = $_GET['id'];
    $status = ($_GET['action'] == 'approve') ? 1 : 0;
    
    // Update comment status
    $stmt = $conn->prepare("UPDATE komentar SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $id);
    
    if ($stmt->execute()) {
        // Redirect with success message
        $status_msg = ($status == 1) ? 'approved' : 'rejected';
        header("Location: komentar.php?status={$status_msg}");
        exit;
    } else {
        // Redirect with error message
        header('Location: komentar.php?status=error');
        exit;
    }
}

// Get all comments with article titles
$sql = "SELECT k.*, a.judul as artikel_judul 
        FROM komentar k 
        JOIN artikel a ON k.artikel_id = a.id 
        ORDER BY k.created_at DESC";
$result = $conn->query($sql);
$comments = [];

while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

// Status messages
$status_messages = [
    'approved' => ['type' => 'success', 'message' => 'Komentar berhasil disetujui'],
    'rejected' => ['type' => 'success', 'message' => 'Komentar berhasil ditolak'],
    'deleted' => ['type' => 'success', 'message' => 'Komentar berhasil dihapus'],
    'error' => ['type' => 'danger', 'message' => 'Terjadi kesalahan. Silakan coba lagi']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Komentar - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
        }
        .comment-content {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
                        <a class="nav-link px-3" href="artikel.php"><i class="fas fa-newspaper me-1"></i> Artikel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="kategori.php"><i class="fas fa-list me-1"></i> Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active px-3" href="komentar.php"><i class="fas fa-comments me-1"></i> Komentar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="roles.php"><i class="fas fa-user-tag me-1"></i> Role</a>
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
                            <a class="nav-link active" href="komentar.php">
                                <i class="fas fa-comments"></i> Komentar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="roles.php">
                                <i class="fas fa-user-tag"></i> Role
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
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Komentar</li>
                    </ol>
                </nav>
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manajemen Komentar</h1>
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
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Artikel</th>
                                        <th>Komentar</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($comments) > 0): ?>
                                        <?php foreach ($comments as $comment): ?>
                                        <tr>
                                            <td><?php echo $comment['id']; ?></td>
                                            <td><?php echo htmlspecialchars($comment['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($comment['email']); ?></td>
                                            <td>
                                                <a href="../artikel.php?id=<?php echo $comment['artikel_id']; ?>" target="_blank">
                                                    <?php echo htmlspecialchars($comment['artikel_judul']); ?>
                                                </a>
                                            </td>
                                            <td class="comment-content">
                                                <span title="<?php echo htmlspecialchars($comment['isi']); ?>">
                                                    <?php echo htmlspecialchars($comment['isi']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></td>
                                            <td>
                                                <?php if ($comment['status'] == 1): ?>
                                                    <span class="badge bg-success">Disetujui</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($comment['status'] == 0): ?>
                                                <a href="komentar.php?action=approve&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-success" title="Setujui">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <?php else: ?>
                                                <a href="komentar.php?action=reject&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-warning" title="Batalkan Persetujuan">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                                <?php endif; ?>
                                                <a href="komentar.php?action=delete&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus komentar ini?')" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-info view-comment" data-bs-toggle="modal" data-bs-target="#viewCommentModal" 
                                                        data-nama="<?php echo htmlspecialchars($comment['nama']); ?>"
                                                        data-email="<?php echo htmlspecialchars($comment['email']); ?>"
                                                        data-artikel="<?php echo htmlspecialchars($comment['artikel_judul']); ?>"
                                                        data-isi="<?php echo htmlspecialchars($comment['isi']); ?>"
                                                        data-tanggal="<?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Tidak ada komentar</td>
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
    
    <!-- View Comment Modal -->
    <div class="modal fade" id="viewCommentModal" tabindex="-1" aria-labelledby="viewCommentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCommentModalLabel">Detail Komentar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>Nama:</strong>
                        <p id="modal-nama"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong>
                        <p id="modal-email"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Artikel:</strong>
                        <p id="modal-artikel"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Tanggal:</strong>
                        <p id="modal-tanggal"></p>
                    </div>
                    <div class="mb-3">
                        <strong>Isi Komentar:</strong>
                        <p id="modal-isi"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle view comment modal
        document.querySelectorAll('.view-comment').forEach(button => {
            button.addEventListener('click', function() {
                const nama = this.getAttribute('data-nama');
                const email = this.getAttribute('data-email');
                const artikel = this.getAttribute('data-artikel');
                const isi = this.getAttribute('data-isi');
                const tanggal = this.getAttribute('data-tanggal');
                
                document.getElementById('modal-nama').textContent = nama;
                document.getElementById('modal-email').textContent = email;
                document.getElementById('modal-artikel').textContent = artikel;
                document.getElementById('modal-isi').textContent = isi;
                document.getElementById('modal-tanggal').textContent = tanggal;
            });
        });
    </script>
</body>
</html>