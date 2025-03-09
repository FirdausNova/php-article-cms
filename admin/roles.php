<?php
session_start();

// Include database connection and roles configuration
require_once '../config/database.php';
require_once '../config/roles.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get all roles above subscriber level
$sql = "SELECT * FROM user_roles WHERE id < ? ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$subscriber_role_id = ROLE_SUBSCRIBER;
$stmt->bind_param("i", $subscriber_role_id);
$stmt->execute();
$result = $stmt->get_result();
$roles = [];

while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}

// Get users with their roles
$sql = "SELECT a.*, r.name as role_name 
        FROM admin a 
        JOIN user_roles r ON a.role_id = r.id 
        ORDER BY a.id ASC";
$result = $conn->query($sql);
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

// Handle role assignment update
$update_error = '';
$update_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    $admin_id = $_POST['admin_id'];
    $role_id = $_POST['role_id'];
    
    // Update user role
    $sql = "UPDATE admin SET role_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $role_id, $admin_id);
    
    if ($stmt->execute()) {
        $update_success = 'Role berhasil diperbarui';
    } else {
        $update_error = 'Gagal memperbarui role: ' . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Role - Admin Panel</title>
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
                        <a class="nav-link px-3" href="kategori.php"><i class="fas fa-list me-1"></i> Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="komentar.php"><i class="fas fa-comments me-1"></i> Komentar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active px-3" href="roles.php"><i class="fas fa-user-tag me-1"></i> Role</a>
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
                            <a class="nav-link" href="komentar.php">
                                <i class="fas fa-comments"></i> Komentar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="roles.php">
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
                        <li class="breadcrumb-item active" aria-current="page">Manajemen Role</li>
                    </ol>
                </nav>
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manajemen Role</h1>
                </div>
                
                <?php if (!empty($update_success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $update_success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($update_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $update_error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Roles List -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Daftar Role</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Role</th>
                                        <th>Deskripsi</th>
                                        <th>Permissions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($roles) > 0): ?>
                                        <?php foreach ($roles as $role): ?>
                                        <tr>
                                            <td><?php echo $role['id']; ?></td>
                                            <td><?php echo htmlspecialchars($role['name']); ?></td>
                                            <td><?php echo htmlspecialchars($role['description']); ?></td>
                                            <td>
                                                <?php 
                                                $permissions = json_decode($role['permissions'], true);
                                                foreach ($permissions as $key => $value): 
                                                    if ($value === true): ?>
                                                        <span class="badge bg-success"><?php echo str_replace('_', ' ', $key); ?></span>
                                                    <?php elseif ($value === 'own'): ?>
                                                        <span class="badge bg-warning"><?php echo str_replace('_', ' ', $key); ?> (own)</span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">Tidak ada role</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Users with Roles -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Pengguna dan Role</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role Saat Ini</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($users) > 0): ?>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($user['role_name']); ?></span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-warning edit-role" 
                                                        data-bs-toggle="modal" data-bs-target="#editRoleModal"
                                                        data-id="<?php echo $user['id']; ?>"
                                                        data-nama="<?php echo htmlspecialchars($user['nama']); ?>"
                                                        data-role="<?php echo $user['role_id']; ?>">
                                                    <i class="fas fa-edit"></i> Ubah Role
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Tidak ada pengguna</td>
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
    
    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleModalLabel">Ubah Role Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="admin_id" id="edit_admin_id">
                        <div class="mb-3">
                            <label class="form-label">Nama Pengguna</label>
                            <input type="text" class="form-control" id="edit_admin_nama" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Role</label>
                            <select class="form-select" id="role_id" name="role_id" required>
                                <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
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
        // Handle edit role modal
        document.querySelectorAll('.edit-role').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');
                const role = this.getAttribute('data-role');
                
                document.getElementById('edit_admin_id').value = id;
                document.getElementById('edit_admin_nama').value = nama;
                document.getElementById('role_id').value = role;
            });
        });
    </script>
</body>
</html>