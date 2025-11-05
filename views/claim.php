<!-- views/claim.php -->
<?php
// Pastikan hanya user yang login
if (!isset($sessionManager) || !$sessionManager->get('userId')) {
    header('Location: index.php?action=login');
    exit;
}

// Ambil ID dari URL
$id_laporan = $_GET['id'] ?? 0;
if (!$id_laporan || !is_numeric($id_laporan)) {
    die('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
}

// Ambil data laporan
$stmt = getDB()->prepare("
    SELECT l.*, a.nama AS nama_pembuat 
    FROM laporan l 
    JOIN akun a ON l.id_akun = a.id_akun 
    WHERE l.id_laporan = ?
");
$stmt->execute([$id_laporan]);
$laporan = $stmt->fetch();

if (!$laporan) {
    die('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
}

// Ambil data user
$userId = $sessionManager->get('userId');
$stmt = getDB()->prepare("SELECT nama, nomor_induk FROM civitas WHERE id_akun = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$namaUser = $user['nama'] ?? 'User';
$nimUser = $user['nomor_induk'] ?? 'Tidak ada NIM';

// Gambar
$imgSrc = !empty($laporan['foto'])
    ? "/{$laporan['foto']}"
    : 'https://via.placeholder.com/300x200/eeeeee/999999?text=No+Image';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klaim Kepemilikan - Lost & Found FILKOM UB</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../css/claim.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <!-- Sidebar Preview Item -->
        <div class="item-preview-card">
            <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($laporan['nama_barang']) ?>" class="preview-image">
            <h2 class="preview-title"><?= htmlspecialchars($laporan['nama_barang']) ?></h2>
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

        <!-- Form Section -->
        <div class="form-section">
            <form id="klaimForm" method="POST" action="index.php?action=submit_klaim" enctype="multipart/form-data">
                <input type="hidden" name="id_laporan" value="<?= $id_laporan ?>">

                <!-- Nama Pengeklaim -->
                <div class="form-group">
                    <label class="form-label">Nama Pengeklaim <span class="required">*</span></label>
                    <input type="text" class="form-input" value="<?= htmlspecialchars($namaUser) ?>" disabled>
                </div>

                <!-- NIM -->
                <div class="form-group">
                    <label class="form-label">NIM <span class="required">*</span></label>
                    <input type="text" class="form-input" value="<?= htmlspecialchars($nimUser) ?>" disabled>
                </div>

                <!-- Bukti Kepemilikan -->
                <div class="form-group">
                    <label class="form-label">Bukti Kepemilikan <span class="required">*</span></label>
                    <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                        <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="upload-text"><strong>Masukkan gambar barangmu</strong></p>
                        <p class="upload-hint">Klik atau drag langsung!</p>
                    </div>
                    <input type="file" id="fileInput" name="bukti_kepemilikan" class="file-input" accept="image/*" required>
                </div>

                <!-- Deskripsi Klaim -->
                <div class="form-group">
                    <label class="form-label">Deskripsi Klaim <span class="required">*</span></label>
                    <textarea class="form-textarea" name="deskripsi_klaim" placeholder="Tulis deskripsi klaim..." required></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-btn">Klaim Kepemilikan</button>
            </form>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const uploadArea = document.querySelector('.upload-area');

        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                uploadArea.innerHTML = `
                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="#10b981">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <polyline points="22 4 12 14.01 9 11.01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <p class="upload-text"><strong>File berhasil dipilih</strong></p>
                    <p class="upload-hint">${fileName}</p>
                `;
            }
        });

        // Drag & Drop
        ['dragover', 'dragleave', 'drop'].forEach(event => {
            uploadArea.addEventListener(event, e => {
                e.preventDefault();
                if (event === 'dragover') {
                    uploadArea.style.borderColor = '#f97316';
                    uploadArea.style.background = '#fff7ed';
                } else if (event === 'dragleave') {
                    uploadArea.style.borderColor = '#cbd5e1';
                    uploadArea.style.background = 'white';
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