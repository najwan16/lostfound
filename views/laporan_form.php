<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporkan Barang Hilang - Lost and Found FILKOM UB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/laporan_form.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="container mt-5">
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-custom">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="row report-layout">
            <div class="col-md-12">
                <div class="input-section">
                    <div class="form-wrapper">
                        <!-- FORM MULAI -->
                        <form method="POST" action="../index.php?action=submit_laporan" class="report-form" enctype="multipart/form-data">
                            <div class="report-wrapper">

                                <!-- KIRI: 30% - UPLOAD GAMBAR (TINGGI TETAP) -->
                                <div class="image-upload-section" id="image-upload-section">
                                    <input type="file" class="file-input" id="foto" name="foto" accept="image/*">
                                    <label for="foto" class="image-upload-label">
                                        <div class="upload-icon">
                                            <i class="bi bi-image" style="font-size: 64px;"></i>
                                        </div>
                                        <p class="upload-text">Masukkan gambar barangmu <span class="required">*</span></p>
                                        <p class="upload-hint">Klik atau drag langsung!</p>
                                    </label>

                                    <div class="image-preview" id="image-preview" style="display:none;">
                                        <img id="preview-img" src="" alt="Preview">
                                        <button type="button" class="btn-close-preview" onclick="removeImage()">×</button>
                                    </div>
                                </div>

                                <!-- KANAN: 70% - FORM -->
                                <div class="form-section">
                                    <div class="form-scroll">

                                        <!-- NAMA BARANG -->
                                        <div class="form-group">
                                            <label for="nama_barang">Nama Barang<span class="required">*</span></label>
                                            <input type="text" class="form-control" id="nama_barang" name="nama_barang" placeholder="Contoh: Laptop Dell" required>
                                        </div>

                                        <!-- KATEGORI & LOKASI -->
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="kategori">Kategori<span class="required">*</span></label>
                                                <select class="form-select" id="kategori" name="kategori" required>
                                                    <option value="">Pilih Kategori</option>
                                                    <option value="elektronik">Elektronik</option>
                                                    <option value="dokumen">Dokumen</option>
                                                    <option value="pakaian">Pakaian</option>
                                                    <option value="lainnya">Lainnya</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="lokasi">Tempat Kehilangan<span class="required">*</span></label>
                                                <select class="form-select" id="lokasi" name="lokasi" required>
                                                    <option value="">Pilih Lokasi</option>
                                                    <option value="Area Parkir">Area Parkir</option>
                                                    <option value="auditorium algoritma">Auditorium Algoritma</option>
                                                    <option value="EduTech">EduTech</option>
                                                    <option value="Gazebo lantai 4">Gazebo lantai 4</option>
                                                    <option value="Gedung Kreativitas Mahasiswa (GKM)">Gedung Kreativitas Mahasiswa (GKM)</option>
                                                    <option value="Junction">Junction</option>
                                                    <option value="kantin">Kantin</option>
                                                    <option value="Laboratorium Pembelajaran">Laboratorium Pembelajaran</option>
                                                    <option value="Mushola Ulul Al-Baab">Mushola Ulul Al-Baab</option>
                                                    <option value="Ruang Baca">Ruang Baca</option>
                                                    <option value="Ruang Ujian">Ruang Ujian</option>
                                                    <option value="ruang tunggu">Ruang Tunggu</option>
                                                    <option value="Smart Class Gedung F">Smart Class Gedung F</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- DESKRIPSI FISIK -->
                                        <div class="form-group">
                                            <label for="deskripsi_fisik">Deskripsi Barang<span class="required">*</span></label>
                                            <textarea class="form-control" id="deskripsi_fisik" name="deskripsi_fisik" rows="4" placeholder="Tulis deskripsi barang..." required></textarea>
                                        </div>

                                        <!-- WAKTU KEHILANGAN -->
                                        <div class="form-group">
                                            <label for="waktu">Tanggal Kehilangan<span class="required">*</span></label>
                                            <input type="datetime-local" class="form-control" id="waktu" name="waktu" required>
                                        </div>

                                    </div>

                                    <!-- TOMBOL SUBMIT -->
                                    <button type="submit" class="btn-submit w-100">Buat Laporan</button>
                                </div>
                            </div>
                        </form>
                        <!-- FORM SELESAI -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const dropZone = document.getElementById('image-upload-section');
        const fileInput = document.getElementById('foto');
        const preview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');
        const label = dropZone.querySelector('.image-upload-label');

        // Drag & Drop Events
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
        });

        dropZone.addEventListener('drop', e => {
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                handleFile(files[0]);
            }
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                handleFile(fileInput.files[0]);
            }
        });

        function handleFile(file) {
            const reader = new FileReader();
            reader.onload = e => {
                previewImg.src = e.target.result;
                preview.style.display = 'flex';
                label.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        function removeImage() {
            fileInput.value = '';
            preview.style.display = 'none';
            previewImg.src = '';
            label.style.display = 'flex';
        }
    </script>
</body>

</html>