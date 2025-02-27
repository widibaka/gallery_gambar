<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Direktori untuk menyimpan file upload
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$array_extensions = $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc','docx','ppt','pptx','xls','xlsx','mp4','mkv','mp3','aac');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proses penghapusan gambar
    if (isset($_POST['delete_image'])) {
        $delete_password = $_POST['delete_password'] ?? '';
        if ($delete_password !== 'widi2024') {
            $message = '<div class="alert alert-danger">Password salah untuk menghapus!</div>';
        } else {
            $filename = $_POST['filename'] ?? '';
            $filename = basename($filename); // Menghindari path traversal
            if (file_exists($upload_dir . $filename)) {
                if (unlink($upload_dir . $filename)) {
                    $message = '<div class="alert alert-success">Gambar berhasil dihapus.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Terjadi error saat menghapus gambar.</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Gambar tidak ditemukan.</div>';
            }
        }
    }
    // Proses upload gambar
    else {
        $password = $_POST['password'] ?? '';
        if ($password !== 'widi2024') {
            $message = '<div class="alert alert-danger">Password salah!</div>';
        } else {
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileName = $_FILES['image']['name'];
                $fileNameCmps = explode(".", $fileName);
                $fileExtension = strtolower(end($fileNameCmps));
    
                // Ekstensi yang diperbolehkan
                $allowedfileExtensions = $array_extensions;
    
                if (in_array($fileExtension, $allowedfileExtensions)) {
                    // Membuat nama file unik
                    $newFileName = time() . '-_-' . $fileNameCmps . '.' . $fileExtension;
                    $dest_path = $upload_dir . $newFileName;
    
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $message = '<div class="alert alert-success">File berhasil diupload.</div>';
                    } else {
                        $message = '<div class="alert alert-danger">Terjadi error saat memindahkan file.</div>';
                    }
                } else {
                    $message = '<div class="alert alert-danger">Upload gagal. Format file tidak diperbolehkan.</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Tidak ada file yang diupload.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Galeri Gambar</title>
  <!-- Bootstrap CSS CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Fancybox CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
  <style>
    body {
      background-color: #f8f9fa;
    }
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        display: flex; /* Ubah menjadi 'flex' untuk menampilkan */
        align-items: center;
        justify-content: center;
    }
    .navbar-brand {
      font-weight: bold;
      font-size: 1.5rem;
    }
    .upload-card {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }
    .gallery-item {
      position: relative;
      overflow: hidden;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      background: #fff;
      padding: 10px;
    }
    .gallery-item img {
      transition: transform 0.3s;
      border-radius: 8px;
    }
    .gallery-item:hover img {
      transform: scale(1.05);
    }
    footer {
      padding: 20px 0;
      text-align: center;
      background-color: #343a40;
      color: #fff;
      margin-top: 50px;
    }
  </style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="#">Galeri Gambar</a>
  </div>
</nav>

<div class="container my-5">
  <!-- Form Upload -->
  <div class="upload-card">
    <h1 class="mb-4">Upload Gambar</h1>
    <?php echo $message; ?>
    <form id="uploadForm" method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" class="form-control" id="password" placeholder="Masukkan password" required>
      </div>
      <div class="mb-3">
        <label for="image" class="form-label">Pilih Gambar</label>
        <input type="file" name="image" class="form-control" id="image" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">
          <br>
          <strong>Upload</strong>
          <br>
          <br>
      </button>
    </form>
  </div>

  <!-- Galeri -->
  <h2 class="mb-4">Galeri</h2>
  <div class="row">
    <?php
    // Mengambil semua file gambar di direktori uploads
    $images = glob($upload_dir . "*.{". implode(',', $allowedfileExtensions) ."}", GLOB_BRACE);
    $images = array_reverse($images);
    $counter = 0;
    foreach($images as $image) {

    if(@getimagesize($image) === false){
        $imgSrc = 'unknown.png'; // pastikan file unknown.jpg tersedia di path yang sesuai
    } else {
        $imgSrc = $image;
    }

      $counter++;
      $basename = basename($image);
      echo '<div class="col-sm-6 col-md-4 col-lg-3 mb-4">';
      echo '  <div class="gallery-item">';
      echo '    <a href="'.$image.'" data-fancybox="gallery">';
      echo '      <center><img src="'.$imgSrc.'" alt="File" class="img-fluid"></center>';
      echo '    </a>';
      echo "<br><br><div class=\"text-center\">";
      echo str_replace('uploads/', '', $image);
      echo "</div>";
      // Tombol untuk memunculkan form hapus
      echo '    <button class="btn btn-sm btn-danger mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#deleteForm'.$counter.'" aria-expanded="false" aria-controls="deleteForm'.$counter.'">Hapus</button>';
      echo '    <div class="collapse mt-2" id="deleteForm'.$counter.'">';
      echo '      <form method="post">';
      echo '        <input type="hidden" name="filename" value="'.$basename.'">';
      echo '        <div class="mb-2">';
      echo '          <input type="password" name="delete_password" class="form-control form-control-sm" placeholder="Masukkan password" required>';
      echo '        </div>';
      echo '        <button type="submit" name="delete_image" class="btn btn-sm btn-warning">Konfirmasi Hapus</button>';
      echo '      </form>';
      echo '    </div>';
      echo '  </div>';
      echo '</div>';
    }
    ?>
  </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none;">
  <div class="spinner-border text-light" role="status">
    <span class="visually-hidden">Loading...</span>
  </div>
</div>

<!-- Footer -->
<footer>
  <div class="container">
    <p>&copy; <?php echo date("Y"); ?> Galeri Gambar. All rights reserved.</p>
  </div>
</footer>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Fancybox JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
<script>
  // Tampilkan loading overlay ketika form upload disubmit
  $(document).ready(function(){
    $("#uploadForm").on('submit', function(){
      $("#loadingOverlay").show();
    });
  });
</script>
</body>
</html>
