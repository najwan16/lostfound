<?php
require_once 'config/db.php';

$userRole = $_SESSION['role'] ?? null;

// ===============================
// FILTER TIPE LAPORAN
// ===============================
$tipeFilter = $_GET['tipe'] ?? 'hilang';
$allowedFilters = ['hilang', 'ditemukan', 'sudah_diambil'];
$tipeFilter = in_array($tipeFilter, $allowedFilters) ? $tipeFilter : 'hilang';

// FILTER FORM INPUT
$keyword  = trim($_GET['keyword'] ?? '');
$kategori = $_GET['kategori'] ?? '';
$lokasi   = $_GET['lokasi'] ?? '';

// ===============================
// QUERY BUILDER
// ===============================
$where = [];
$params = [];

// 1. TIPE LAPORAN
if ($tipeFilter === 'sudah_diambil') {
    $where[] = "l.status = 'sudah_diambil'";
} else {
    $where[] = "l.tipe_laporan = :tipe";
    $where[] = "l.status != 'sudah_diambil'";
    $params[':tipe'] = $tipeFilter;
}

// 2. KEYWORD
if ($keyword !== '') {
    $where[] = "(l.nama_barang LIKE :keyword OR l.deskripsi_fisik LIKE :keyword)";
    $params[':keyword'] = "%$keyword%";
}

// 3. KATEGORI
if ($kategori !== '') {
    $where[] = "l.kategori = :kategori";
    $params[':kategori'] = $kategori;
}

// 4. LOKASI
if ($lokasi !== '') {
    $where[] = "l.lokasi = :lokasi";
    $params[':lokasi'] = $lokasi;
}

$whereClause = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

// ===============================
// FINAL QUERY
// ===============================
$query = "
    SELECT 
        l.*, 
        a.nama AS nama_pembuat,
        pengambil.nama AS nama_pengambil
    FROM laporan l
    JOIN akun a ON l.id_akun = a.id_akun
    LEFT JOIN akun pengambil ON pengambil.id_akun = (
        SELECT c2.id_akun 
        FROM civitas c2 
        WHERE c2.nomor_induk = l.nim_pengambil
        LIMIT 1
    )
    $whereClause
    ORDER BY l.created_at DESC
";

$stmt = getDB()->prepare($query);
$stmt->execute($params);
$laporan_list = $stmt->fetchAll();

// ===============================
// LIST KATEGORI & LOKASI
// ===============================
$categories = ['elektronik', 'dokumen', 'pakaian', 'lainnya'];
$locations = [
    'Area Parkir',
    'auditorium algoritma',
    'EduTech',
    'Gazebo lantai 4',
    'Gedung Kreativitas Mahasiswa (GKM)',
    'Junction',
    'kantin',
    'Laboratorium Pembelajaran',
    'Mushola Ulul Al-Baab',
    'Ruang Baca',
    'Ruang Ujian',
    'ruang tunggu',
    'Smart Class Gedung F'
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Laporan - Lost & Found FILKOM UB</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <link href="public/assets/css/search.css" rel="stylesheet">
</head>

<body>

    <?php include realpath(dirname(__DIR__) . '/layouts/navbar.php'); ?>

    <div class="container mt-4">
        <div class="row g-4">

            <!-- LEFT FILTER -->
            <div class="col-lg-3 col-md-4">

                <!-- FILTER TIPE (hilang & ditemukan) -->
                <div class="filter-block mb-4">
                    <a href="index.php?action=search&tipe=hilang"
                        class="btn-filter <?= $tipeFilter === 'hilang' ? 'btn-filter-active' : '' ?>">
                        Laporan Hilang
                    </a>

                    <a href="index.php?action=search&tipe=ditemukan"
                        class="btn-filter <?= $tipeFilter === 'ditemukan' ? 'btn-filter-active' : '' ?>">
                        Laporan Ditemukan
                    </a>
                    <a href="index.php?action=search&tipe=sudah_diambil"
                        class="btn-filter <?= $tipeFilter === 'sudah_diambil' ? 'btn-filter-active' : '' ?>">
                        Sudah Diambil
                    </a>
                </div>

                <!-- FILTER FORM -->
                <div class="filter-block">
                    <form method="GET" action="index.php">
                        <input type="hidden" name="action" value="search">
                        <input type="hidden" name="tipe" value="<?= $tipeFilter ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Barang</label>
                            <input type="text" name="keyword" class="form-control"
                                placeholder="Masukkan Nama Barang" value="<?= htmlspecialchars($keyword) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori Barang</label>
                            <select name="kategori" class="form-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat ?>" <?= $kategori === $cat ? 'selected' : '' ?>>
                                        <?= ucfirst($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Lokasi Barang</label>
                            <select name="lokasi" class="form-select">
                                <option value="">Semua Lokasi</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc ?>" <?= $lokasi === $loc ? 'selected' : '' ?>>
                                        <?= $loc ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-search w-100">Cari</button>
                    </form>
                </div>

            </div>


            <!-- RIGHT CONTENT -->
            <div class="col-lg-9 col-md-8">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 g-4">

                    <?php if (empty($laporan_list)): ?>
                        <div class="col-12 text-center py-5">
                            <p class="text-muted fs-4">Tidak ada laporan ditemukan</p>
                        </div>
                    <?php else: ?>

                        <?php foreach ($laporan_list as $l): ?>
                            <?php
                            $detailAction = ($userRole === 'satpam') ? 'laporanSatpam-detail' : 'laporan-detail';
                            $detailUrl = "index.php?action=$detailAction&id={$l['id_laporan']}";

                            $isDitemukan = ($l['tipe_laporan'] === 'ditemukan');
                            $imgSrc = (!empty($l['foto']) && !$isDitemukan)
                                ? '/public/uploads/laporan/' . basename($l['foto'])
                                : null;
                            ?>

                            <div class="col">
                                <a href="<?= $detailUrl ?>" class="card-link">
                                    <div class="item-card">

                                        <!-- GAMBAR / HIDDEN BOX -->
                                        <div class="item-image">
                                            <?php if ($isDitemukan): ?>
                                                <div class="hidden-image-box">
                                                    <span class="material-symbols-outlined">inventory_2</span>
                                                    <p>Barang Ditemukan</p>
                                                </div>
                                            <?php else: ?>
                                                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($l['nama_barang']) ?>">
                                            <?php endif; ?>
                                        </div>

                                        <div class="item-info">
                                            <h5 class="item-title"><?= htmlspecialchars($l['nama_barang']) ?></h5>

                                            <div class="item-meta">
                                                <div class="info-item">
                                                    <span class="material-symbols-outlined">pin_drop</span>
                                                    <span><?= htmlspecialchars($l['lokasi']) ?></span>
                                                </div>

                                                <div class="info-item">
                                                    <span class="material-symbols-outlined">category</span>
                                                    <span><?= ucfirst($l['kategori']) ?></span>
                                                </div>

                                                <?php if ($l['status'] === 'sudah_diambil' && $l['nama_pengambil']): ?>
                                                    <div class="info-item mt-1">
                                                        <span class="material-symbols-outlined">verified_user</span>
                                                        <span><b>Diambil oleh:</b> <?= htmlspecialchars($l['nama_pengambil']) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                    </div>
                                </a>
                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>

</body>

</html>