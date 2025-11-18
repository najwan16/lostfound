<?php
require_once dirname(__DIR__, 2) . '../../config/db.php';
require_once dirname(__DIR__, 2) . '../../app/Controllers/AuthController.php';
require_once dirname(__DIR__, 2) . '../../app/Controllers/LaporanController.php';

$auth = new AuthController();
$sessionManager = $auth->getSessionManager();

if ($sessionManager->get('role') !== 'satpam') {
    header('Location: index.php?action=login');
    exit;
}

$laporanController = new LaporanController($sessionManager);

$alert_message = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaBarang     = trim($_POST['nama_barang'] ?? '');
    $deskripsiFisik = trim($_POST['deskripsi_fisik'] ?? '');
    $kategori       = $_POST['kategori'] ?? '';
    $lokasi         = $_POST['lokasi'] ?? '';
    $waktu          = $_POST['waktu'] ?? '';

    if (empty($namaBarang) || empty($deskripsiFisik) || empty($kategori) || empty($lokasi) || empty($waktu)) {
        $alert_message = 'Semua field wajib diisi!';
        $alert_type    = 'danger';
    } else {
        $result = $laporanController->submitLaporanDitemukan($namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu);
        $alert_message = $result['success'] ? 'Laporan berhasil disimpan!' : 'Gagal menyimpan laporan.';
        $alert_type    = $result['success'] ? 'success' : 'danger';
        if ($result['success']) $_POST = [];
    }
}

$GLOBALS['current_page'] = 'dashboard';
$title = 'Lapor Barang Ditemukan';
include 'app/Views/layouts/sidebar.php';
?>

<link rel="stylesheet" href="/public/assets/css/laporanSatpam-form.css">

<div class="laporan-pink-wrapper">
    <div class="laporan-pink-container">

        <form method="POST" action="" class="laporan-pink-form">

            <!-- Baris 1: Nama Barang & Kategori -->
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Barang <span class="required">*</span></label>
                    <input type="text" name="nama_barang" placeholder="Casan Laptop Asus"
                        value="<?= htmlspecialchars($_POST['nama_barang'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Kategori <span class="required">*</span></label>
                    <select name="kategori" required>
                        <option value="">Pilih</option>
                        <option value="elektronik" <?= ($_POST['kategori'] ?? '') === 'elektronik' ? 'selected' : '' ?>>Elektronik</option>
                        <option value="dokumen" <?= ($_POST['kategori'] ?? '') === 'dokumen' ? 'selected' : '' ?>>Dokumen</option>
                        <option value="pakaian" <?= ($_POST['kategori'] ?? '') === 'pakaian' ? 'selected' : '' ?>>Pakaian</option>
                        <option value="lainnya" <?= ($_POST['kategori'] ?? '') === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>
            </div>

            <!-- Baris 2: Lokasi & Waktu -->
            <div class="form-row">
                <div class="form-group">
                    <label>Tempat Kehilangan <span class="required">*</span></label>
                    <select name="lokasi" required>
                        <option value="">Pilih</option>
                        <?php
                        $locations = ['Area Parkir', 'auditorium algoritma', 'EduTech', 'Gazebo lantai 4', 'Gedung Kreativitas Mahasiswa (GKM)', 'Junction', 'kantin', 'Laboratorium Pembelajaran', 'Mushola Ulul Al-Baab', 'Ruang Baca', 'Ruang Ujian', 'ruang tunggu', 'Smart Class Gedung F'];
                        foreach ($locations as $loc): ?>
                            <option value="<?= $loc ?>" <?= ($_POST['lokasi'] ?? '') === $loc ? 'selected' : '' ?>><?= $loc ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Waktu ditemukan <span class="required">*</span></label>
                    <input type="datetime-local" name="waktu" value="<?= htmlspecialchars($_POST['waktu'] ?? '') ?>" required>
                </div>
            </div>

            <!-- Deskripsi -->
            <div class="form-group full-width">
                <label>Deskripsi Barang <span class="required">*</span></label>
                <textarea name="deskripsi_fisik" rows="6" placeholder="Tulis deskripsi barang..." required><?= htmlspecialchars($_POST['deskripsi_fisik'] ?? '') ?></textarea>
            </div>

            <!-- Tombol Submit -->
            <div class="submit-row">
                <button type="submit" class="btn-orange-full">
                    Buat Laporan
                </button>
            </div>

        </form>
    </div>
</div>

</div> <!-- end .page-container -->
</main>
</body>

</html>