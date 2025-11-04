<!-- views/admin/laporan_ditemukan_form.php -->
<?php
// Pastikan hanya satpam yang bisa akses
if (!isset($sessionManager) || $sessionManager->get('role') !== 'satpam') {
    header('Location: ../../index.php?action=login');
    exit;
}

// Inisialisasi pesan
$message = '';
$success = false;

// Proses submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaBarang = trim($_POST['nama_barang'] ?? '');
    $deskripsiFisik = trim($_POST['deskripsi_fisik'] ?? '');
    $kategori = $_POST['kategori'] ?? '';
    $lokasi = $_POST['lokasi'] ?? '';
    $waktu = $_POST['waktu'] ?? '';

    // Validasi wajib
    if (empty($namaBarang) || empty($deskripsiFisik) || empty($kategori) || empty($lokasi) || empty($waktu)) {
        $message = 'Semua field wajib diisi';
        $success = false;
    } else {
        $result = $laporanController->submitLaporanDitemukan(
            $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu
        );

        // Redirect dengan pesan
        $msg = $result['success'] ? 'success' : 'error';
        header("Location: index.php?action=laporan_ditemukan_form&msg=$msg");
        exit;
    }
}

// Baca pesan dari URL (setelah redirect)
$msg = $_GET['msg'] ?? '';
if ($msg === 'success') {
    $message = 'Laporan barang ditemukan berhasil disimpan';
    $success = true;
} elseif ($msg === 'error') {
    $message = 'Gagal menyimpan laporan';
    $success = false;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lapor Barang Ditemukan - Lost & Found FILKOM UB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="../../css/laporan_form.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white text-center">
                <h3 class="mb-0">Lapor Barang Ditemukan</h3>
                <p class="mb-0">Bantu civitas FILKOM menemukan barangnya kembali</p>
            </div>

            <div class="card-body p-5">
                <!-- PESAN SUKSES/GAGAL -->
                <?php if ($message): ?>
                    <div class="alert alert-<?= $success ? 'success' : 'danger' ?> alert-dismissible fade show">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- FORM -->
                <form method="POST" action="" class="needs-validation" novalidate>
                    <!-- Nama Barang -->
                    <div class="mb-4">
                        <label for="nama_barang" class="form-label fw-bold">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="nama_barang" name="nama_barang"
                               placeholder="Contoh: Laptop Dell, Dompet Kulit, KTP" 
                               value="<?= htmlspecialchars($_POST['nama_barang'] ?? '') ?>" required>
                        <div class="invalid-feedback">Nama barang wajib diisi</div>
                    </div>

                    <!-- Kategori & Lokasi -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="kategori" class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="kategori" name="kategori" required>
                                <option value="">Pilih Kategori</option>
                                <option value="elektronik" <?= ($_POST['kategori'] ?? '') === 'elektronik' ? 'selected' : '' ?>>Elektronik</option>
                                <option value="dokumen" <?= ($_POST['kategori'] ?? '') === 'dokumen' ? 'selected' : '' ?>>Dokumen</option>
                                <option value="pakaian" <?= ($_POST['kategori'] ?? '') === 'pakaian' ? 'selected' : '' ?>>Pakaian</option>
                                <option value="lainnya" <?= ($_POST['kategori'] ?? '') === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                            <div class="invalid-feedback">Pilih kategori</div>
                        </div>
                        <div class="col-md-6">
                            <label for="lokasi" class="form-label fw-bold">Lokasi Ditemukan <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg" id="lokasi" name="lokasi" required>
                                <option value="">Pilih Lokasi</option>
                                <option value="Area Parkir" <?= ($_POST['lokasi'] ?? '') === 'Area Parkir' ? 'selected' : '' ?>>Area Parkir</option>
                                <option value="auditorium algoritma" <?= ($_POST['lokasi'] ?? '') === 'auditorium algoritma' ? 'selected' : '' ?>>Auditorium Algoritma</option>
                                <option value="EduTech" <?= ($_POST['lokasi'] ?? '') === 'EduTech' ? 'selected' : '' ?>>EduTech</option>
                                <option value="Gazebo lantai 4" <?= ($_POST['lokasi'] ?? '') === 'Gazebo lantai 4' ? 'selected' : '' ?>>Gazebo Lantai 4</option>
                                <option value="Gedung Kreativitas Mahasiswa (GKM)" <?= ($_POST['lokasi'] ?? '') === 'Gedung Kreativitas Mahasiswa (GKM)' ? 'selected' : '' ?>>Gedung Kreativitas Mahasiswa (GKM)</option>
                                <option value="Junction" <?= ($_POST['lokasi'] ?? '') === 'Junction' ? 'selected' : '' ?>>Junction</option>
                                <option value="kantin" <?= ($_POST['lokasi'] ?? '') === 'kantin' ? 'selected' : '' ?>>Kantin</option>
                                <option value="Laboratorium Pembelajaran" <?= ($_POST['lokasi'] ?? '') === 'Laboratorium Pembelajaran' ? 'selected' : '' ?>>Laboratorium Pembelajaran</option>
                                <option value="Mushola Ulul Al-Baab" <?= ($_POST['lokasi'] ?? '') === 'Mushola Ulul Al-Baab' ? 'selected' : '' ?>>Mushola Ulul Al-Baab</option>
                                <option value="Ruang Baca" <?= ($_POST['lokasi'] ?? '') === 'Ruang Baca' ? 'selected' : '' ?>>Ruang Baca</option>
                                <option value="Ruang Ujian" <?= ($_POST['lokasi'] ?? '') === 'Ruang Ujian' ? 'selected' : '' ?>>Ruang Ujian</option>
                                <option value="ruang tunggu" <?= ($_POST['lokasi'] ?? '') === 'ruang tunggu' ? 'selected' : '' ?>>Ruang Tunggu</option>
                                <option value="Smart Class Gedung F" <?= ($_POST['lokasi'] ?? '') === 'Smart Class Gedung F' ? 'selected' : '' ?>>Smart Class Gedung F</option>
                            </select>
                            <div class="invalid-feedback">Pilih lokasi</div>
                        </div>
                    </div>

                    <!-- Deskripsi Fisik -->
                    <div class="mb-4">
                        <label for="deskripsi_fisik" class="form-label fw-bold">Deskripsi Fisik Barang <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-lg" id="deskripsi_fisik" name="deskripsi_fisik" rows="4"
                                  placeholder="Contoh: Laptop warna hitam, ada stiker UB, charger terpisah, ada goresan di pojok" required><?= htmlspecialchars($_POST['deskripsi_fisik'] ?? '') ?></textarea>
                        <div class="invalid-feedback">Deskripsi wajib diisi</div>
                    </div>

                    <!-- Waktu Ditemukan -->
                    <div class="mb-4">
                        <label for="waktu" class="form-label fw-bold">Waktu Ditemukan <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control form-control-lg" id="waktu" name="waktu" 
                               value="<?= $_POST['waktu'] ?? '' ?>" required>
                        <div class="invalid-feedback">Waktu wajib diisi</div>
                    </div>

                    <!-- Tombol -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="../../index.php?action=dashboard" class="btn btn-secondary btn-lg me-md-2">Batal</a>
                        <button type="submit" class="btn btn-success btn-lg">Laporkan Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validasi Bootstrap
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>

</html>