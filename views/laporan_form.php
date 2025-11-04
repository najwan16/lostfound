<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporkan Barang Hilang - Lost and Found FILKOM UB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/laporan_form.css" rel="stylesheet">
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
            <div class="row report-layout">
                <div class="col-md-4">
                    <div class="input-section">
                        <div class="form-group">
                            <label for="foto">Foto Barang</label>
                            <input type="file" class="form-control" id="foto" name="foto" accept="image/*" onchange="previewImage(event)">
                            <div id="image-preview" class="mt-2" style="max-height:200px; overflow:hidden; border-radius:8px; display:none;">
                                <img id="preview-img" src="" alt="Preview" style="width:100%; height:auto;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="input-section">
                        <div class="form-wrapper">
                            <form method="POST" action="index.php?action=submit_laporan" class="report-form" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="nama_barang">Nama Barang<span class="required">*</span></label>
                                    <input type="text" class="form-control" id="nama_barang" name="nama_barang" placeholder="Contoh: Laptop Dell" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label for="kategori">Kategori<span class="required">*</span></label>
                                        <select class="form-select" id="kategori" name="kategori" required>
                                            <option value="">Pilih Kategori</option>
                                            <option value="elektronik">Elektronik</option>
                                            <option value="dokumen">Dokumen</option>
                                            <option value="pakaian">Pakaian</option>
                                            <option value="lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 form-group">
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
                                <div class="form-group">
                                    <label for="deskripsi_fisik">Deskripsi Barang<span class="required">*</span></label>
                                    <textarea class="form-control" id="deskripsi_fisik" name="deskripsi_fisik" placeholder="Tulis deskripsi barang..." required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="waktu">Tanggal Kehilangan<span class="required">*</span></label>
                                    <input type="datetime-local" class="form-control" id="waktu" name="waktu" required>
                                </div>
                                <button type="submit" class="btn btn-submit">Buat Laporan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
    function previewImage(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('image-preview');
        const img = document.getElementById('preview-img');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    }
</script>

</html>