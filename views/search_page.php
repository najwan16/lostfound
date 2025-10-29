<!-- views/search_page.php -->
<?php
require_once 'config/db.php';

$filter = $_GET['filter'] ?? 'semua';
$allowed = ['semua', 'hilang', 'ditemukan'];
$filter = in_array($filter, $allowed) ? $filter : 'semua';

$where = '';
if ($filter === 'hilang') {
    $where = "WHERE l.tipe_laporan = 'hilang'";
} elseif ($filter === 'ditemukan') {
    $where = "WHERE l.tipe_laporan = 'ditemukan'";
}

$query = "
    SELECT 
        l.tipe_laporan, l.nama_barang, l.deskripsi_fisik, l.kategori, 
        l.lokasi, l.waktu, l.status, l.created_at,
        a.nama AS nama_pembuat, a.nomor_kontak, c.nomor_induk
    FROM laporan l
    JOIN akun a ON l.id_akun = a.id_akun
    LEFT JOIN civitas c ON a.id_akun = c.id_akun
    $where
    ORDER BY l.created_at DESC
";

$stmt = getDB()->prepare($query);
$stmt->execute();
$laporan_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Laporan - Lost & Found FILKOM UB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 1.5rem;
            border-radius: 16px 16px 0 0;
            text-align: center;
        }

        .filter-btn {
            margin: 0.5rem;
        }

        .table th {
            background-color: #e3f2fd;
            font-weight: 600;
        }

        .badge-tipe {
            font-size: 0.8rem;
        }

        .pembuat {
            font-size: 0.9rem;
            color: #555;
        }

        .nim {
            font-weight: bold;
            color: #1e40af;
        }

        .status-hilang {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-ditemukan {
            background-color: #d1edff;
            color: #0c5460;
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
                <div class="text-center mb-4">
                    <a href="index.php?action=search_page&filter=semua"
                        class="btn btn-outline-primary filter-btn <?= $filter === 'semua' ? 'active' : '' ?>">Semua</a>
                    <a href="index.php?action=search_page&filter=hilang"
                        class="btn btn-outline-warning filter-btn <?= $filter === 'hilang' ? 'active' : '' ?>">Barang Hilang</a>
                    <a href="index.php?action=search_page&filter=ditemukan"
                        class="btn btn-outline-success filter-btn <?= $filter === 'ditemukan' ? 'active' : '' ?>">Barang Ditemukan</a>
                </div>

                <?php if (empty($laporan_list)): ?>
                    <div class="alert alert-info text-center">
                        <strong>Belum ada laporan <?= $filter === 'semua' ? '' : $filter ?>.</strong>
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
                                    <tr class="<?= $l['tipe_laporan'] === 'hilang' ? 'status-hilang' : 'status-ditemukan' ?>">
                                        <td><?= $i + 1 ?></td>
                                        <td><span class="badge <?= $l['tipe_laporan'] === 'hilang' ? 'bg-warning' : 'bg-success' ?> badge-tipe">
                                                <?= ucfirst($l['tipe_laporan']) ?>
                                            </span></td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>