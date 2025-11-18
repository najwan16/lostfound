<?php
$current_page = 'claim';
$user = $user ?? null;  // safety
$laporan = $laporan ?? null;
$id_laporan = $id_laporan ?? 0;

if (!$user || !$laporan) {
    die('Data tidak lengkap.');
}

$imgSrc = !empty($laporan['foto'])
    ? '/public/uploads/laporan/' . basename($laporan['foto'])
    : 'https://via.placeholder.com/400x300/eeeeee/999999?text=No+Image';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klaim Kepemilikan - Lost & Found FILKOM UB</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="public/assets/css/claim-form.css" rel="stylesheet">
</head>

<body>

    <?php include realpath(dirname(__DIR__) . '/layouts/navbar.php'); ?>

    <div class="claim-page">
        <div class="claim-container">
            <!-- KIRI: Preview Barang -->
            <div class="item-preview-card">
                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($laporan['nama_barang']) ?>" class="preview-image">
                <h5 class="preview-title"><?= htmlspecialchars($laporan['nama_barang']) ?></h5>
                <div class="preview-meta">
                    <div class="preview-meta-item">
                        <span class="material-symbols-outlined">location_on</span>
                        <span><?= htmlspecialchars($laporan['lokasi']) ?></span>
                    </div>
                    <div class="preview-meta-item">
                        <span class="material-symbols-outlined">category</span>
                        <span><?= ucfirst($laporan['kategori']) ?></span>
                    </div>
                </div>
            </div>

            <!-- KANAN: Form Klaim -->
            <div class="form-section">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger mb-4"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>

                <form id="klaimForm" method="POST" action="index.php?action=submit_claim" enctype="multipart/form-data">
                    <input type="hidden" name="id_laporan" value="<?= $id_laporan ?>">

                    <!-- Nama & NIM -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label">Nama Pengeklaim <span class="required">*</span></label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($user['nama']) ?>" disabled>
                        </div>
                        <div class="col-12">
                            <label class="form-label">NIM <span class="required">*</span></label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($user['nomor_induk']) ?>" disabled>
                        </div>
                    </div>

                    <!-- Upload Bukti -->
                    <div class="form-group mb-4">
                        <label class="form-label">Bukti Kepemilikan <span class="required">*</span></label>
                        <div class="upload-area" onclick="document.getElementById('buktiFile').click()">
                            <div id="image-preview" class="mb-3 d-none">
                                <img id="preview-img" src="" alt="Preview" class="img-fluid rounded" style="max-height: 220px;">
                            </div>
                            <div id="upload-placeholder">
                                <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="upload-text"><strong>Masukkan gambar barangmu</strong> <span class="required">*</span></p>
                                <p class="upload-hint">Klik atau drag langsung!</p>
                            </div>
                        </div>
                        <input type="file" id="buktiFile" name="bukti_kepemilikan" class="file-input" accept="image/*" required>
                        <div id="file-name" class="mt-2 text-success small"></div>
                    </div>

                    <!-- Deskripsi Klaim -->
                    <div class="form-group mb-4">
                        <label class="form-label">Deskripsi Klaim <span class="required">*</span></label>
                        <textarea name="deskripsi_ciri" class="form-textarea" rows="5"
                            placeholder="Tulis ciri-ciri khusus barangmu agar mudah diverifikasi..." required></textarea>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit" class="submit-btn">
                        Klaim Kepemilikan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('buktiFile');
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
                fileNameDiv.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${file.name}`;
                uploadArea.style.borderColor = '#f97316';
                uploadArea.style.backgroundColor = '#fff7ed';
            }
        });

        ['dragover', 'dragleave', 'drop'].forEach(event => {
            uploadArea.addEventListener(event, e => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        uploadArea.addEventListener('dragover', () => {
            uploadArea.style.borderColor = '#f97316';
            uploadArea.style.backgroundColor = '#fff7ed';
        });
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = '#cbd5e1';
            uploadArea.style.backgroundColor = 'white';
        });
        uploadArea.addEventListener('drop', e => {
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>

</html>