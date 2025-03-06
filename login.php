<?php
session_start();

// Include database connection
require_once 'config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        // Validate input
        if (empty($username) || empty($password)) {
            $error = 'Silakan masukkan username dan password';
        } else {
            // First check in users table
            $sql = "SELECT u.*, r.name as role_name FROM users u 
                    LEFT JOIN user_roles r ON u.role_id = r.id 
                    WHERE u.username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Password is correct, create session
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_username'] = $user['username'];
                    $_SESSION['user_nama'] = $user['nama'];
                    $_SESSION['user_role_id'] = $user['role_id'];
                    $_SESSION['user_role_name'] = $user['role_name'];
                    $_SESSION['user_foto'] = $user['foto'];
                    
                    // Check if user has admin role (role_id = 1)
                    if ($user['role_id'] == 1) {
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $user['id'];
                        $_SESSION['admin_username'] = $user['username'];
                        $_SESSION['admin_nama'] = $user['nama'];
                        
                        // Show alert for admin login
                        echo "<script>alert('Anda berhasil login sebagai Admin!');</script>";
                        echo "<script>window.location.href = 'admin/index.php';</script>";
                        exit;
                    } else {
                        // Show alert for regular user login
                        echo "<script>alert('Anda berhasil login sebagai User!');</script>";
                        echo "<script>window.location.href = 'index.php';</script>";
                        exit;
                    }
                } else {
                    $error = 'Username atau password salah';
                }
            } else {
                // If not found in users table, check admin table (for backward compatibility)
                $sql = "SELECT * FROM admin WHERE username = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $admin = $result->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password, $admin['password'])) {
                        // Password is correct, create session
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $admin['id'];
                        $_SESSION['admin_username'] = $admin['username'];
                        $_SESSION['admin_nama'] = $admin['nama'];
                        
                        // Show alert for admin login
                        echo "<script>alert('Anda berhasil login sebagai Admin!');</script>";
                        echo "<script>window.location.href = 'admin/index.php';</script>";
                        exit;
                    } else {
                        $error = 'Username atau password salah';
                    }
                } else {
                    $error = 'Username atau password salah';
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'register') {
        // Handle registration
        $username = trim($_POST['reg_username']);
        $password = trim($_POST['reg_password']);
        $confirm_password = trim($_POST['reg_confirm_password']);
        $nama = trim($_POST['reg_nama']);
        $email = trim($_POST['reg_email']);
        
        // Validate input
        if (empty($username) || empty($password) || empty($confirm_password) || empty($nama) || empty($email)) {
            $error = 'Semua field harus diisi';
        } elseif ($password !== $confirm_password) {
            $error = 'Password dan konfirmasi password tidak cocok';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Format email tidak valid';
        } else {
            // Check if username or email already exists
            if (username_exists($username, $conn)) {
                $error = 'Username sudah digunakan';
            } elseif (email_exists($email, $conn)) {
                $error = 'Email sudah digunakan';
            } else {
                // Insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $default_role_id = 4; // Default to Subscriber role
                
                $sql = "INSERT INTO users (username, password, nama, email, role_id) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $username, $hashed_password, $nama, $email, $default_role_id);
                
                if ($stmt->execute()) {
                    $success = 'Registrasi berhasil! Silakan login dengan akun baru Anda.';
                } else {
                    $error = 'Terjadi kesalahan saat mendaftar: ' . $conn->error;
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
    <title>Login - Portal Artikel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .auth-tabs .nav-link {
            color: #495057;
            border-radius: 0;
            padding: 15px 25px;
        }
        .auth-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #0d6efd;
            background-color: transparent;
            color: #0d6efd;
        }
        .form-container {
            padding: 30px;
        }
    </style>
</head>
<body class="bg-light">
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
                                <img src="assets/images/<?php echo isset($_SESSION['user_foto']) ? htmlspecialchars($_SESSION['user_foto']) : 'default.jpg'; ?>" alt="Profile" class="rounded-circle me-1" style="width: 24px; height: 24px; object-fit: cover;"> <?php echo htmlspecialchars($_SESSION['user_nama']); ?>
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
                        <li class="nav-item"><a class="nav-link active px-3" href="login.php"><i class="fas fa-sign-in-alt me-1"></i> Login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow border-0">
                    <!-- Card Header with Tabs -->
                    <div class="card-header bg-white p-0">
                        <ul class="nav nav-tabs auth-tabs" id="authTabs" role="tablist">
                            <li class="nav-item" role="presentation" style="width: 50%;">
                                <button class="nav-link active w-100" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-tab-pane" type="button" role="tab" aria-controls="login-tab-pane" aria-selected="true">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </li>
                            <li class="nav-item" role="presentation" style="width: 50%;">
                                <button class="nav-link w-100" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-tab-pane" type="button" role="tab" aria-controls="register-tab-pane" aria-selected="false">
                                    <i class="fas fa-user-plus me-2"></i>Register
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Card Body with Tab Content -->
                    <div class="card-body p-0">
                        <div class="tab-content" id="authTabsContent">
                            <!-- Login Tab -->
                            <div class="tab-pane fade show active" id="login-tab-pane" role="tabpanel" aria-labelledby="login-tab" tabindex="0">
                                <div class="form-container">
                                    <?php if (!empty($error) && (!isset($_POST['action']) || $_POST['action'] === 'login')): ?>
                                    <div class="alert alert-danger">
                                        <?php echo $error; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($success)): ?>
                                    <div class="alert alert-success">
                                        <?php echo $success; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <form action="" method="POST">
                                        <input type="hidden" name="action" value="login">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="username" name="username" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="password" name="password" required>
                                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                            <label class="form-check-label" for="remember">Ingat saya</label>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">Login</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Register Tab -->
                            <div class="tab-pane fade" id="register-tab-pane" role="tabpanel" aria-labelledby="register-tab" tabindex="0">
                                <div class="form-container">
                                    <?php if (!empty($error) && isset($_POST['action']) && $_POST['action'] === 'register'): ?>
                                    <div class="alert alert-danger">
                                        <?php echo $error; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <form action="" method="POST">
                                        <input type="hidden" name="action" value="register">
                                        <div class="mb-3">
                                            <label for="reg_nama" class="form-label">Nama Lengkap</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" class="form-control" id="reg_nama" name="reg_nama" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reg_email" class="form-label">Email</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                <input type="email" class="form-control" id="reg_email" name="reg_email" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reg_username" class="form-label">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-at"></i></span>
                                                <input type="text" class="form-control" id="reg_username" name="reg_username" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reg_password" class="form-label">Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="reg_password" name="reg_password" required>
                                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="reg_password">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="reg_confirm_password" class="form-label">Konfirmasi Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" class="form-control" id="reg_confirm_password" name="reg_confirm_password" required>
                                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="reg_confirm_password">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                            <label class="form-check-label" for="terms">Saya menyetujui syarat dan ketentuan</label>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">Register</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <p class="text-muted">Portal Artikel &copy; <?php echo date('Y'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-password');
            
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const passwordInput = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    // Toggle password visibility
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
        });
    </script>
</body>
</html>