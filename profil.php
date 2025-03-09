<?php
session_start();

// Include database connection
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get user data with role information
$user_id = $_SESSION['user_id'];
$sql = "SELECT u.*, r.name as role_name FROM users u 
        LEFT JOIN user_roles r ON u.role_id = r.id 
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Update session with current role information
if ($user) {
    $_SESSION['user_role_id'] = $user['role_id'];
    $_SESSION['user_role_name'] = $user['role_name'];
    
    // If user has admin role, update admin session variables
    if ($user['role_id'] == 1) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_nama'] = $user['nama'];
    } else {
        // Remove admin session variables if user is not an admin
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_nama']);
    }
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Handle profile photo upload
    $foto = $user['foto']; // Default to current photo
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        // Validate file extension
        if (!in_array($file_ext, $allowed)) {
            $error = 'Format file tidak didukung. Gunakan format JPG, JPEG, PNG, atau GIF.';
        } else {
            // Generate unique filename
            $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_dir = 'assets/images/profiles/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                // Delete old profile photo if it exists and is not a default image
                if (!empty($user['foto'])) {
                    if (file_exists($user['foto']) && is_file($user['foto'])) {
                        unlink($user['foto']);
                    } else if (file_exists('assets/images/' . $user['foto']) && is_file('assets/images/' . $user['foto'])) {
                        unlink('assets/images/' . $user['foto']);
                    }
                }
                $foto = 'assets/images/profiles/' . $new_filename;
            } else {
                $error = 'Gagal mengunggah foto. Silakan coba lagi.';
            }
        }
    }
    
    // Validate input
    if (empty($nama) || empty($email)) {
        $error = 'Nama dan email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        // Check if email is already used by another user
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email sudah digunakan oleh pengguna lain';
        } else {
            // Update user profile
            if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
                // User wants to change password
                if ($new_password !== $confirm_password) {
                    $error = 'Password baru dan konfirmasi password tidak cocok';
                } elseif (!password_verify($current_password, $user['password'])) {
                    $error = 'Password saat ini tidak valid';
                } else {
                    // Password is valid, update profile with new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET nama = ?, email = ?, bio = ?, password = ?, foto = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssssi", $nama, $email, $bio, $hashed_password, $foto, $user_id);
                }
            } else {
                // Update profile without changing password
                $sql = "UPDATE users SET nama = ?, email = ?, bio = ?, foto = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $nama, $email, $bio, $foto, $user_id);
            }
            
            if (empty($error)) {
                if ($stmt->execute()) {
                    // Update session data
                    $_SESSION['user_nama'] = $nama;
                    $_SESSION['user_foto'] = $foto;
                    
                    $success = 'Profil berhasil diperbarui';
                    
                    // Refresh user data with role information
                    $sql = "SELECT u.*, r.name as role_name FROM users u 
                            LEFT JOIN user_roles r ON u.role_id = r.id 
                            WHERE u.id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                    
                    // Update session with current role information
                    if ($user) {
                        $_SESSION['user_role_id'] = $user['role_id'];
                        $_SESSION['user_role_name'] = $user['role_name'];
                        
                        // If user has admin role, update admin session variables
                        if ($user['role_id'] == 1) {
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_id'] = $user['id'];
                            $_SESSION['admin_username'] = $user['username'];
                            $_SESSION['admin_nama'] = $user['nama'];
                        } else {
                            // Remove admin session variables if user is not an admin
                            unset($_SESSION['admin_logged_in']);
                            unset($_SESSION['admin_id']);
                            unset($_SESSION['admin_username']);
                            unset($_SESSION['admin_nama']);
                        }
                    }
                } else {
                    $error = 'Terjadi kesalahan saat memperbarui profil: ' . $conn->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Portal Artikel</title>
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
                            <a class="nav-link dropdown-toggle active px-3" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <?php if (!empty($user['foto']) && file_exists($user['foto'])): ?>
                                <img src="<?php echo htmlspecialchars($user['foto']); ?>" alt="Profile" class="rounded-circle me-1" style="width: 24px; height: 24px; object-fit: cover;">
                                <?php else: ?>
                                <i class="fas fa-user-circle me-1"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($_SESSION['user_nama']); ?>
                                <?php if(isset($_SESSION['user_role_name'])): ?>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($_SESSION['user_role_name']); ?></span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item active" href="profil.php"><i class="fas fa-user me-2"></i> Profil Saya</a></li>
                                <?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                                <li><a class="dropdown-item" href="admin/index.php"><i class="fas fa-user-shield me-2"></i> Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <div class="container my-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <?php if (!empty($user['foto']) && file_exists($user['foto'])): ?>
                            <img src="<?php echo htmlspecialchars($user['foto']); ?>" alt="Profile Picture" class="rounded-circle img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                                <i class="fas fa-user fa-5x text-white"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                        <h4><?php echo htmlspecialchars($user['nama']); ?></h4>
                        <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <p><?php echo !empty($user['bio']) ? htmlspecialchars($user['bio']) : 'Belum ada bio'; ?></p>
                        <p class="mb-1"><i class="fas fa-envelope me-2"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><i class="fas fa-calendar me-2"></i> Bergabung: <?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                        <?php if(isset($_SESSION['user_role_name'])): ?>
                        <p><i class="fas fa-user-tag me-2"></i> Role: <?php echo htmlspecialchars($_SESSION['user_role_name']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Edit Profil</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                        <?php endif; ?>
                        
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="foto" class="form-label">Foto Profil</label>
                                <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,image/gif">
                                <small class="text-muted">Format yang didukung: JPG, JPEG, PNG, GIF. Maksimal 2MB.</small>
                            </div>
                            
                            <h5 class="mt-4 mb-3">Ubah Password</h5>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="py-4 bg-dark text-white mt-5">
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
</body>
</html>