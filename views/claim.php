<!-- views/claim.php -->
<?php
// === CEK DB & SESSION ===
require_once dirname(__DIR__) . '/config/db.php';

if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'civitas') {
    header('Location: index.php?action=login');
    exit;
}

$userId = $_SESSION['userId'];

// === AMBIL ID LAPORAN ===
$id_laporan = $_GET['id'] ?? 0;
if (!$id_laporan || !is_numeric($id_laporan)) {
    die('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
}

// === CEK LAPORAN ===
$stmt = getDB()->prepare("
    SELECT l.*, a.nama AS nama_pembuat 
    FROM laporan l 
    JOIN akun a ON l.id_akun = a.id_akun 
    WHERE l.id_laporan = ? AND l.status = 'sudah_diambil'
");
$stmt->execute([$id_laporan]);
$laporan = $stmt->fetch();

if (!$laporan) {
    die('<div class="alert alert-danger">Laporan tidak valid atau belum diambil.</div>');
}

if ($laporan['id_akun'] == $userId) {
    die('<div class="alert alert-danger">Anda tidak bisa mengklaim laporan sendiri.</div>');
}

$stmt = getDB()->prepare("SELECT 1 FROM klaim WHERE id_laporan = ? AND id_akun = ?");
$stmt->execute([$id_laporan, $userId]);
if ($stmt->fetch()) {
    die('<div class="alert alert-warning">Anda sudah mengajukan klaim untuk barang ini.</div>');
}

// === DATA USER ===
$stmt = getDB()->prepare("
    SELECT a.nama, c.nomor_induk 
    FROM civitas c
    JOIN akun a ON c.id_akun = a.id_akun
    WHERE c.id_akun = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die('<div class="alert alert-danger">Data profil tidak ditemukan.</div>');
}

$namaUser = $user['nama'];
$nimUser = $user['nomor_induk'];

$imgSrc = !empty($laporan['foto'])
    ? "/" . $laporan['foto']
    : 'https://via.placeholder.com/300x200/eeeeee/999999?text=No+Image';

// SET HALAMAN AKTIF UNTUK SIDEBAR
$current_page = 'klaim';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klaim Kepemilikan - Lost & Found FILKOM UB</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="css/claim.css" rel="stylesheet">
</head>

<body>
    <?php include 'header.php'; ?>


    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="container-fluid p-4">

            <!-- PAGE TITLE -->
            <div class="page-header mb-4">
                <h3 class="mb-1">Klaim Kepemilikan</h3>
                <p class="text-muted mb-0">Ajukan klaim untuk barang yang sudah diambil</p>
            </div>

            <div class="row g-4">
                <!-- Preview Kiri -->
                <div class="col-lg-4">
                    <div class="item-preview-card">
                        <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($laporan['nama_barang']) ?>" class="preview-image">
                        <h5 class="preview-title"><?= htmlspecialchars($laporan['nama_barang']) ?></h5>
                        <div class="preview-meta">
                            <div class="preview-meta-item">
                                <span class="material-symbols-outlined">pin_drop</span>
                                <span><?= htmlspecialchars($laporan['lokasi']) ?></span>
                            </div>
                            <div class="preview-meta-item">
                                <span class="material-symbols-outlined">category</span>
                                <span><?= ucfirst($laporan['kategori']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Kanan -->
                <div class="col-lg-8">
                    <div class="form-section">
                        <h4 class="mb-4">
                            <i class="bi bi-shield-check text-primary"></i> Ajukan Klaim
                        </h4>

                        <form id="klaimForm" method="POST" action="index.php?action=submit_klaim" enctype="multipart/form-data">
                            <input type="hidden" name="id_laporan" value="<?= $id_laporan ?>">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Pengeklaim <span class="required">*</span></label>
                                    <input type="text" class="form-input" value="<?= htmlspecialchars($namaUser) ?>" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">NIM <span class="required">*</span></label>
                                    <input type="text" class="form-input" value="<?= htmlspecialchars($nimUser) ?>" disabled>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">Bukti Kepemilikan <span class="required">*</span></label>
                                <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                                    <div id="image-preview" class="mb-3 d-none">
                                        <img id="preview-img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                                    </div>
                                    <div id="upload-placeholder">
                                        <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="upload-text"><strong>Klik untuk upload bukti</strong></p>
                                        <p class="upload-hint">Foto KTM, struk, atau bukti lain (JPG/PNG, max 3MB)</p>
                                    </div>
                                </div>
                                <input type="file" id="fileInput" name="bukti_kepemilikan" class="file-input" accept="image/*" required>
                                <div id="file-name" class="mt-2 text-success small"></div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">Deskripsi Ciri Khusus <span class="required">*</span></label>
                                <textarea name="deskripsi_ciri" class="form-textarea" rows="4" placeholder="Contoh: Ada stiker nama di belakang..." required></textarea>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="submit-btn">
                                    <i class="bi bi-send"></i> Ajukan Klaim
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const fileNameDiv = document.getElementById('file-name');
        const uploadArea = document.querySelector('.upload-area');
        const imagePreview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        const uploadPlaceholder = document.getElementById('upload-placeholder');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    uploadPlaceholder.style.display = 'none';
                    imagePreview.classList.remove('d-none');
                    previewImg.src = e.target.result;
                };
                reader.readAsDataURL(file);
                fileNameDiv.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${file.name} (${(file.size/1024/1024).toFixed(2)} MB)`;
                uploadArea.style.borderColor = '#10b981';
                uploadArea.style.backgroundColor = '#f0fdf4';
            }
        });

        ['dragover', 'dragleave', 'drop'].forEach(event => {
            uploadArea.addEventListener(event, e => {
                e.preventDefault();
                if (event === 'dragover') {
                    uploadArea.style.borderColor = '#f97316';
                    uploadArea.style.backgroundColor = '#fff7ed';
                } else if (event === 'dragleave') {
                    uploadArea.style.borderColor = '#cbd5e1';
                    uploadArea.style.backgroundColor = 'white';
                } else {
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        fileInput.files = files;
                        fileInput.dispatchEvent(new Event('change'));
                    }
                }
            });
        });
    </script>
</body>

</html>