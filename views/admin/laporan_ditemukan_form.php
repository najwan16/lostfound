<!-- views/admin/laporan_ditemukan_form.php -->
<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/controllers/AuthController.php';
require_once dirname(__DIR__, 2) . '/controllers/LaporanController.php';

$auth = new AuthController();
$sessionManager = $auth->getSessionManager();

if ($sessionManager->get('role') !== 'satpam') {
    header('Location: ' . dirname(__DIR__, 2) . '/index.php?action=login');
    exit;
}

// PASS getDB() KE CONSTRUCTOR
$laporanController = new LaporanController(getDB());

// Proses submit
$alert_message = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaBarang = trim($_POST['nama_barang'] ?? '');
    $deskripsiFisik = trim($_POST['deskripsi_fisik'] ?? '');
    $kategori = $_POST['kategori'] ?? '';
    $lokasi = $_POST['lokasi'] ?? '';
    $waktu = $_POST['waktu'] ?? '';

    if (empty($namaBarang) || empty($deskripsiFisik) || empty($kategori) || empty($lokasi) || empty($waktu)) {
        $alert_message = 'Semua field wajib diisi';
        $alert_type = 'danger';
    } else {
        $result = $laporanController->submitLaporanDitemukan($namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu);
        $alert_message = $result['success'] ? 'Laporan berhasil disimpan' : 'Gagal menyimpan laporan';
        $alert_type = $result['success'] ? 'success' : 'danger';
    }
}

$current_page = 'laporan_ditemukan';
$page_title = 'Lapor Barang Ditemukan';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="../../css/admin.css" rel="stylesheet">
</head>
<body>

    <!-- SIDEBAR -->
    <?php include 'widgets/sidebar.php'; ?>

    <!-- HEADER -->
    <?php include 'widgets/header.php'; ?>

        <!-- ALERT -->
        <?php include 'widgets/alert.php'; ?>

        <!-- CARD: FORM -->
        <?php
        $card_header = 'Lapor Barang Ditemukan';
        $header_class = 'bg-gradient-success';
        ob_start();
        ?>
        <form method="POST" action="" id="laporanForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" name="nama_barang" class="form-control" placeholder="Contoh: Laptop Dell"
                           value="<?= htmlspecialchars($_POST['nama_barang'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select name="kategori" class="form-select" required>
                        <option value="">Pilih Kategori</option>
                        <option value="elektronik" <?= ($_POST['kategori'] ?? '') === 'elektronik' ? 'selected' : '' ?>>Elektronik</option>
                        <option value="dokumen" <?= ($_POST['kategori'] ?? '') === 'dokumen' ? 'selected' : '' ?>>Dokumen</option>
                        <option value="pakaian" <?= ($_POST['kategori'] ?? '') === 'pakaian' ? 'selected' : '' ?>>Pakaian</option>
                        <option value="lainnya" <?= ($_POST['kategori'] ?? '') === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label class="form-label">Lokasi Ditemukan <span class="text-danger">*</span></label>
                    <select name="lokasi" class="form-select" required>
                        <option value="">Pilih Lokasi</option>
                        <?php
                        $locations = ['Area Parkir', 'auditorium algoritma', 'EduTech', 'Gazebo lantai 4', 'Gedung Kreativitas Mahasiswa (GKM)', 'Junction', 'kantin', 'Laboratorium Pembelajaran', 'Mushola Ulul Al-Baab', 'Ruang Baca', 'Ruang Ujian', 'ruang tunggu', 'Smart Class Gedung F'];
                        foreach ($locations as $loc): ?>
                            <option value="<?= $loc ?>" <?= ($_POST['lokasi'] ?? '') === $loc ? 'selected' : '' ?>><?= $loc ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Waktu Ditemukan <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="waktu" class="form-control" value="<?= htmlspecialchars($_POST['waktu'] ?? '') ?>" required>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Deskripsi Fisik <span class="text-danger">*</span></label>
                <textarea name="deskripsi_fisik" class="form-control" rows="4" placeholder="Contoh: Warna hitam, ada stiker..." required><?= htmlspecialchars($_POST['deskripsi_fisik'] ?? '') ?></textarea>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-success btn-lg">
                    Laporkan Barang
                </button>
            </div>
        </form>
        <?php
        $card_content = ob_get_clean();
        include 'widgets/card.php';
        ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>