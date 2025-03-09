<?php
session_start();

// Include database connection
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Check if article ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: artikel.php');
    exit;
}

$artikel_id = $_GET['id'];

// Get article details
$sql = "SELECT * FROM artikel WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $artikel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Article not found
    header('Location: artikel.php');
    exit;
}

$artikel = $result->fetch_assoc();

// Get all categories for the dropdown
$sql = "SELECT * FROM kategori ORDER BY nama ASC";
$result = $conn->query($sql);
$categories = [];

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $judul = trim($_POST['judul']);
    $slug = strtolower(str_replace(' ', '-', preg_replace('/[^A-Za-z0-9-]+/', '-', $judul)));
    $konten = $_POST['konten'];
    $kategori_id = $_POST['kategori_id'];
    $tanggal_publikasi = $_POST['tanggal_publikasi'];
    
    // Validate input
    if (empty($judul) || empty($konten) || empty($kategori_id) || empty($tanggal_publikasi)) {
        $error = 'Semua field harus diisi';
    } else {
        // Handle image upload if a new image is provided
        $gambar = $artikel['gambar']; // Keep existing image by default
        
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/images/uploads/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['gambar']['name']);
            $target_file = $upload_dir . $file_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES['gambar']['tmp_name']);
            if ($check === false) {
                $error = 'File yang diupload bukan gambar';
            }
            // Check file size (max 2MB)
            elseif ($_FILES['gambar']['size'] > 2000000) {
                $error = 'Ukuran file terlalu besar (maksimal 2MB)';
            }
            // Allow certain file formats
            elseif (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
                $error = 'Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan';
            }
            // If everything is ok, try to upload file
            else {
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                    // Delete old image if it exists and is not a default image
                    if (!empty($artikel['gambar']) && file_exists("../{$artikel['gambar']}") && strpos($artikel['gambar'], 'default') === false) {
                        unlink("../{$artikel['gambar']}");
                    }
                    
                    $gambar = 'assets/images/uploads/' . $file_name;
                } else {
                    $error = 'Terjadi kesalahan saat mengupload gambar';
                }
            }
        } elseif (isset($_POST['remove_image']) && $_POST['remove_image'] == 1) {
            // User wants to remove the image
            if (!empty($artikel['gambar']) && file_exists("../{$artikel['gambar']}") && strpos($artikel['gambar'], 'default') === false) {
                unlink("../{$artikel['gambar']}");
            }
            $gambar = 'assets/images/default.jpg';
        }
        
        if (empty($error)) {
            // Update article in database
            $sql = "UPDATE artikel SET judul = ?, slug = ?, konten = ?, gambar = ?, kategori_id = ?, tanggal_publikasi = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $judul, $slug, $konten, $gambar, $kategori_id, $tanggal_publikasi, $artikel_id);
            
            if ($stmt->execute()) {
                // Redirect to article list with success message
                header('Location: artikel.php?status=updated');
                exit;
            } else {
                $error = 'Gagal memperbarui artikel: ' . $stmt->error;
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
    <title>Edit Artikel - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Include Summernote CSS & JS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
        }
        .note-editor .dropdown-toggle::after {
            all: unset;
        }
        .note-editor .note-dropdown-menu {
            box-sizing: content-box;
        }
        .note-editor .note-modal-footer {
            box-sizing: content-box;
        }
        .article-preview-img {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
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
                        <a class="nav-link" href="../index.php"><i class="fas fa-home me-1"></i> Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="artikel.php"><i class="fas fa-newspaper me-1"></i> Artikel</a>
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
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="artikel.php">Artikel</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Artikel</li>
                    </ol>
                </nav>
                
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Edit Artikel</h1>
                    <a href="../artikel.php?id=<?php echo $artikel_id; ?>" class="btn btn-outline-primary" target="_blank"><i class="fas fa-eye"></i> Lihat Artikel</a>
                </div>
                
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="judul" class="form-label">Judul Artikel</label>
                                <input type="text" class="form-control" id="judul" name="judul" value="<?php echo htmlspecialchars($artikel['judul']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="kategori_id" class="form-label">Kategori</label>
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $artikel['kategori_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['nama']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="tanggal_publikasi" class="form-label">Tanggal Publikasi</label>
                                <input type="date" class="form-control" id="tanggal_publikasi" name="tanggal_publikasi" value="<?php echo $artikel['tanggal_publikasi']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Gambar Artikel</label>
                                <?php if (!empty($artikel['gambar'])): ?>
                                <div class="mb-2">
                                    <img src="../<?php echo htmlspecialchars($artikel['gambar']); ?>" class="article-preview-img border rounded" alt="<?php echo htmlspecialchars($artikel['judul']); ?>">
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="gambar" name="gambar">
                                <div class="form-text">Format: JPG, JPEG, PNG, GIF. Ukuran maksimal: 2MB. Biarkan kosong jika tidak ingin mengubah gambar.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="konten" class="form-label">Konten Artikel</label>
                                <textarea class="form-control" id="konten" name="konten" rows="10" required><?php echo htmlspecialchars($artikel['konten']); ?></textarea>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="artikel.php" class="btn btn-secondary me-md-2"><i class="fas fa-arrow-left"></i> Kembali</a>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#konten').summernote({
                placeholder: 'Tulis konten artikel di sini...',
                tabsize: 2,
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
</body>
</html>