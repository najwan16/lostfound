<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporkan Barang Hilang - Lost and Found FILKOM UB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="../css/laporan_form.css" rel="stylesheet"> <!-- Pastikan path ini benar -->
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
            <!-- Bagian Kiri: Input Gambar (30%) -->
            <div class="col-md-4">
                <div class="input-section">
                    <!-- <label for="gambar_barang" class="form-label">Unggah Gambar Barang<span class="required">*</span></label>
                    <input type="file" class="form-control" id="gambar_barang" name="gambar_barang" accept="image/*"> -->
                    <div class="form-group">
                        <label for="nim_pemilik">Nomor Induk Pemilik Laporan (NIM/NIP)</label>
                        <input type="text" class="form-control" id="nim_pemilik" name="nim_pemilik" value="<?php echo htmlspecialchars($nim ?? ''); ?>" disabled>
                    </div>
                </div>
            </div>
            <!-- Bagian Kanan: Form (70%) -->
            <div class="col-md-8">
                <div class="input-section">
                    <div class="form-wrapper">
                        <form method="POST" action="../index.php?action=submit_laporan" class="report-form">
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
                                    <select class="form-select" id="lokasi" name="lokasi">
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
                                <textarea class="form-control" id="deskripsi_fisik" name="deskripsi_fisik" placeholder="Tulis deskripsi barang..."></textarea>
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

</html>