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

    if (empty($namaBarang) || empty($deskripsiFisik) || empty($kategori) || empty($lokasi) || empty($waktu)) {
        $message = 'Semua field wajib diisi';
        $success = false;
    } else {
        $result = $laporanController->submitLaporanDitemukan(
            $namaBarang,
            $deskripsiFisik,
            $kategori,
            $lokasi,
            $waktu
        );
        $msg = $result['success'] ? 'success' : 'error';
        header("Location: index.php?action=laporan_ditemukan_form&msg=$msg");
        exit;
    }
}

// Baca pesan dari URL
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
    <title>Lapor Barang Ditemukan</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="../../css/laporan_ditemukan_form.css" rel="stylesheet">
</head>

<body>

    <!-- SIDEBAR KIRI -->
    <div class="sidebar-wrapper">
        <div class="sidebar">
            <div class="atas">
                <img src="../../images/head.png" alt="Logo" onerror="this.src='https://via.placeholder.com/40';">
                <div class="sidebar-btn">
                    <a href="#" class="sidebar-item"><span class="material-symbols-outlined">inbox</span> Kotak Masuk</a>
                    <a href="#" class="sidebar-item active"><span class="material-symbols-outlined">assignment</span> Laporan</a>
                    <a href="#" class="sidebar-item"><span class="material-symbols-outlined">checked_bag_question</span> Klaim</a>
                </div>
            </div>
            <div class="bawah">
                <a href="../../index.php?action=dashboard" class="btn-keluar">
                    <span class="material-symbols-outlined">chip_extraction</span> Keluar
                </a>
            </div>
        </div>
    </div>

    <!-- KONTEN KANAN -->
    <div class="container">
        <div class="form-card">

            <!-- PESAN -->
            <?php if ($message): ?>
                <div class="alert alert-<?= $success ? 'success' : 'danger' ?> alert-dismissible fade show"
                    style="border-radius: 12px; margin: 1.5rem 0;">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- FORM -->
            <form method="POST" action="" id="laporanForm">
                <!-- Nama Barang -->
                <div class="form-group">
                    <label>Nama Barang <span>*</span></label>
                    <input type="text" name="nama_barang" placeholder="Contoh: Laptop Dell, Dompet Kulit"
                        value="<?= htmlspecialchars($_POST['nama_barang'] ?? '') ?>" required>
                </div>

                <!-- Kategori & Lokasi -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Kategori <span>*</span></label>
                        <select name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="elektronik" <?= ($_POST['kategori'] ?? '') === 'elektronik' ? 'selected' : '' ?>>Elektronik</option>
                            <option value="dokumen" <?= ($_POST['kategori'] ?? '') === 'dokumen' ? 'selected' : '' ?>>Dokumen</option>
                            <option value="pakaian" <?= ($_POST['kategori'] ?? '') === 'pakaian' ? 'selected' : '' ?>>Pakaian</option>
                            <option value="lainnya" <?= ($_POST['kategori'] ?? '') === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Lokasi Ditemukan <span>*</span></label>
                        <select name="lokasi" required>
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
                    </div>
                </div>

                <!-- Deskripsi Fisik -->
                <div class="form-group">
                    <label>Deskripsi Fisik Barang <span>*</span></label>
                    <textarea name="deskripsi_fisik" rows="4" placeholder="Contoh: Laptop warna hitam, ada stiker UB, charger terpisah..." required><?= htmlspecialchars($_POST['deskripsi_fisik'] ?? '') ?></textarea>
                </div>

                <!-- Waktu Ditemukan -->
                <div class="form-group">
                    <label>Waktu Ditemukan <span>*</span></label>
                    <input type="datetime-local" name="waktu" value="<?= htmlspecialchars($_POST['waktu'] ?? '') ?>" required>
                </div>

                <!-- Tombol -->
                <button type="submit" class="btn-submit">Laporkan Barang</button>
            </form>
        </div>
    </div>

    <script>
        // Validasi sederhana
        document.getElementById('laporanForm').addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[required], select[required], textarea[required]');
            let valid = true;
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = '#ef4444';
                    valid = false;
                } else {
                    input.style.borderColor = '#e2e8f0';
                }
            });
            if (!valid) {
                e.preventDefault();
                alert('Harap isi semua field yang wajib.');
            }
        });
    </script>
</body>

</html>