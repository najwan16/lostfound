<!-- views/search_page.php -->
<?php
require_once 'config/db.php';

// === CEK SESSION ===
$userRole = $_SESSION['role'] ?? null;
$userId = $_SESSION['userId'] ?? null;

// Ambil filter dan parameter pencarian
$filter = $_GET['filter'] ?? 'semua';
$allowed = ['semua', 'hilang', 'ditemukan'];
$filter = in_array($filter, $allowed) ? $filter : 'semua';

$keyword = trim($_GET['keyword'] ?? '');
$kategori = $_GET['kategori'] ?? '';
$lokasi = $_GET['lokasi'] ?? '';

// Bangun kondisi WHERE
$where = [];
$params = [];

if ($filter === 'hilang') {
    $where[] = "l.tipe_laporan = 'hilang'";
} elseif ($filter === 'ditemukan') {
    $where[] = "l.tipe_laporan = 'ditemukan'";
}

if ($keyword !== '') {
    $where[] = "(l.nama_barang LIKE :keyword OR l.deskripsi_fisik LIKE :keyword)";
    $params[':keyword'] = "%$keyword%";
}

if ($kategori !== '') {
    $where[] = "l.kategori = :kategori";
    $params[':kategori'] = $kategori;
}

if ($lokasi !== '') {
    $where[] = "l.lokasi = :lokasi";
    $params[':lokasi'] = $lokasi;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Query
$query = "
    SELECT 
        l.id_laporan, l.tipe_laporan, l.nama_barang, l.deskripsi_fisik, l.kategori, 
        l.lokasi, l.waktu, l.status, l.created_at,
        a.nama AS nama_pembuat, a.nomor_kontak, c.nomor_induk
    FROM laporan l
    JOIN akun a ON l.id_akun = a.id_akun
    LEFT JOIN civitas c ON a.id_akun = c.id_akun
    $whereClause
    ORDER BY l.created_at DESC
";

$stmt = getDB()->prepare($query);
$stmt->execute($params);
$laporan_list = $stmt->fetchAll();

// Daftar kategori & lokasi
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/search.css" rel="stylesheet">
    <style>
        .clickable-row {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .clickable-row:hover {
            background-color: #f8f9fa !important;
        }

        .clickable-row td {
            position: relative;
        }

        .clickable-row::after {
            content: 'Klik untuk detail';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.75rem;
            color: #007bff;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .clickable-row:hover::after {
            opacity: 1;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container mt-5">
        <div class="card">
            <div class="header">
                <h3 class="mb-0">Cari Laporan Barang</h3>
                <p class="mb-0">Temukan barang hilang atau lihat yang sudah ditemukan</p>
            </div>

            <div class="card-body p-4">
                <!-- FORM PENCARIAN -->
                <form method="GET" action="index.php" class="search-form mb-4">
                    <input type="hidden" name="action" value="search_page">
                    <input type="hidden" name="filter" value="<?= $filter ?>">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="keyword" class="form-control" placeholder="Nama barang / deskripsi" value="<?= htmlspecialchars($keyword) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="kategori" class="form-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat ?>" <?= $kategori === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="lokasi" class="form-select">
                                <option value="">Semua Lokasi</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc ?>" <?= $lokasi === $loc ? 'selected' : '' ?>><?= $loc ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Cari</button>
                        </div>
                    </div>
                </form>

                <!-- TOMBOL FILTER TIPE -->
                <div class="text-center mb-4">
                    <?php
                    $currentParams = ['keyword' => $keyword, 'kategori' => $kategori, 'lokasi' => $lokasi];
                    ?>
                    <a href="index.php?action=search_page&filter=semua&<?= http_build_query($currentParams) ?>"
                        class="btn btn-outline-primary filter-btn <?= $filter === 'semua' ? 'active' : '' ?>">Semua</a>
                    <a href="index.php?action=search_page&filter=hilang&<?= http_build_query($currentParams) ?>"
                        class="btn btn-outline-warning filter-btn <?= $filter === 'hilang' ? 'active' : '' ?>">Barang Hilang</a>
                    <a href="index.php?action=search_page&filter=ditemukan&<?= http_build_query($currentParams) ?>"
                        class="btn btn-outline-success filter-btn <?= $filter === 'ditemukan' ? 'active' : '' ?>">Barang Ditemukan</a>
                </div>

                <!-- HASIL -->
                <?php if (empty($laporan_list)): ?>
                    <div class="alert alert-info text-center">
                        <strong>Tidak ada hasil untuk pencarian ini.</strong>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tipe</th>
                                    <th>Barang</th>
                                    <th>Kategori</th>
                                    <th>Lokasi</th>
                                    <th>Waktu</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th>Pembuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($laporan_list as $i => $l): ?>
                                    <?php
                                    // Tentukan URL detail berdasarkan role
                                    $detailAction = ($userRole === 'satpam')
                                        ? 'detail_laporan_satpam'
                                        : 'detail_laporan';
                                    $detailUrl = "index.php?action={$detailAction}&id={$l['id_laporan']}";
                                    ?>
                                    <tr class="clickable-row" data-href="<?= $detailUrl ?>">
                                        <td><?= $i + 1 ?></td>
                                        <td>
                                            <span class="badge <?= $l['tipe_laporan'] === 'hilang' ? 'bg-warning' : 'bg-success' ?> badge-tipe">
                                                <?= ucfirst($l['tipe_laporan']) ?>
                                            </span>
                                        </td>
                                        <td><strong><?= htmlspecialchars($l['nama_barang']) ?></strong></td>
                                        <td><span class="badge bg-primary"><?= ucfirst($l['kategori']) ?></span></td>
                                        <td><?= htmlspecialchars($l['lokasi']) ?></td>
                                        <td><?= date('d M Y, H:i', strtotime($l['waktu'])) ?></td>
                                        <td>
                                            <span class="badge <?= $l['status'] === 'belum_ditemukan' ? 'bg-warning text-dark' : 'bg-success' ?>">
                                                <?= ucfirst(str_replace('_', ' ', $l['status'])) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d M Y', strtotime($l['created_at'])) ?></td>
                                        <td>
                                            <div class="pembuat">
                                                <strong><?= htmlspecialchars($l['nama_pembuat']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($l['nomor_kontak']) ?></small>
                                                <?php if ($l['nomor_induk']): ?>
                                                    <div class="nim">NIM: <?= htmlspecialchars($l['nomor_induk']) ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SCRIPT KLIK BARIS -->
    <script>
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function() {
                const href = this.getAttribute('data-href');
                if (href) window.location = href;
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>