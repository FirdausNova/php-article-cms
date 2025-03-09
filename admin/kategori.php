<?php
session_start();

// Include database connection
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle category deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Check if category is used in any article
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM artikel WHERE kategori_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count > 0) {
        // Category is in use, redirect with error
        header('Location: kategori.php?status=in_use');
        exit;
    } else {
        // Delete the category
        $stmt = $conn->prepare("DELETE FROM kategori WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Redirect with success message
            header('Location: kategori.php?status=deleted');
            exit;
        } else {
            // Redirect with error message
            header('Location: kategori.php?status=error');
            exit;
        }
    }
}



// Handle category update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $nama = trim($_POST['nama']);
    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9-]+/', '-', $nama)));
    
    // Validate input
    if (empty($nama)) {
        $edit_error = 'Nama kategori harus diisi';
    } else {
        // Check if category with same name or slug already exists (excluding current category)
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM kategori WHERE (nama = ? OR slug = ?) AND id != ?");
        $stmt->bind_param("ssi", $nama, $slug, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            $edit_error = 'Kategori dengan nama atau slug yang sama sudah ada';
        } else {
            // Update category
            $stmt = $conn->prepare("UPDATE kategori SET nama = ?, slug = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nama, $slug, $id);
            
            if ($stmt->execute()) {
                // Redirect with success message
                header('Location: kategori.php?status=updated');
                exit;
            } else {
                $edit_error = 'Gagal memperbarui kategori: ' . $stmt->error;
            }
        }
    }
}

// Get all categories
$sql = "SELECT k.*, (SELECT COUNT(*) FROM artikel WHERE kategori_id = k.id) as artikel_count FROM kategori k ORDER BY nama ASC";
$result = $conn->query($sql);
$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

// Status messages
$status_messages = [
    'added' => ['type' => 'success', 'message' => 'Kategori berhasil ditambahkan'],
    'updated' => ['type' => 'success', 'message' => 'Kategori berhasil diperbarui'],
    'deleted' => ['type' => 'success', 'message' => 'Kategori berhasil dihapus'],
    'in_use' => ['type' => 'warning', 'message' => 'Kategori tidak dapat dihapus karena sedang digunakan oleh artikel'],
    'error' => ['type' => 'danger', 'message' => 'Terjadi kesalahan. Silakan coba lagi']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kategori - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
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
                        <a class="nav-link px-3" href="../index.php"><i class="fas fa-home me-1"></i> Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="index.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="artikel.php"><i class="fas fa-newspaper me-1"></i> Artikel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active px-3" href="kategori.php"><i class="fas fa-list me-1"></i> Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="komentar.php"><i class="fas fa-comments me-1"></i> Komentar</a>
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
                            <a class="nav-link active" href="kategori.php">
                                <i class="fas fa-tags"></i> Kategori
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="komentar.php">
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
                        <li class="breadcrumb-item active" aria-current="page">Kategori</li>
                    </ol>
                </nav>
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manajemen Kategori</h1>
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
                                        <th>Slug</th>
                                        <th>Jumlah Artikel</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($categories) > 0): ?>
                                        <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td><?php echo htmlspecialchars($category['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $category['artikel_count']; ?></span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-warning edit-category" 
                                                        data-bs-toggle="modal" data-bs-target="#editCategoryModal"
                                                        data-id="<?php echo $category['id']; ?>"
                                                        data-nama="<?php echo htmlspecialchars($category['nama']); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($category['artikel_count'] == 0): ?>
                                                <a href="kategori.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-danger" disabled title="Kategori sedang digunakan oleh artikel">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Tidak ada kategori</td>
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
    


    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (isset($edit_error)): ?>
                    <div class="alert alert-danger"><?php echo $edit_error; ?></div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_nama" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="edit_nama" name="nama" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit category modal
        document.querySelectorAll('.edit-category').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');
                
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_nama').value = nama;
            });
        });
    </script>
</body>
</html>