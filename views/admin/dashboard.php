<!-- views/admin/dashboard.php -->
<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/controllers/AuthController.php';

$auth = new AuthController();
$session = $auth->getSessionManager();

if ($session->get('role') !== 'satpam') {
    header('Location: ' . dirname(__DIR__, 2) . '/index.php?action=home');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT 
        l.id_laporan, l.nama_barang, l.deskripsi_fisik, l.kategori, l.lokasi, l.waktu, l.status, l.created_at,
        a.nama AS nama_pembuat, a.nomor_kontak, c.nomor_induk
    FROM laporan l
    JOIN akun a ON l.id_akun = a.id_akun
    LEFT JOIN civitas c ON a.id_akun = c.id_akun
    WHERE l.tipe_laporan = 'hilang'
    ORDER BY l.created_at DESC
");
$stmt->execute();
$laporan_list = $stmt->fetchAll();

$root = dirname(__DIR__, 2);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Satpam</title>
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
            padding: 2rem;
            border-radius: 16px 16px 0 0;
            text-align: center;
        }

        .table th {
            background-color: #e3f2fd;
            font-weight: 600;
        }

        .badge-status {
            font-size: 0.85rem;
        }

        .pembuat {
            font-size: 0.9rem;
            color: #555;
        }

        .nim {
            font-weight: bold;
            color: #1e40af;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="card">
            <div class="header">
                <h3 class="mb-0">Dashboard Satpam</h3>
                <p class="mb-0">Selamat datang, <strong><?= htmlspecialchars($session->get('nama')) ?></strong></p>
            </div>

            <div class="card-body p-4">
                <h5 class="mb-4">Daftar Laporan Barang Hilang</h5>

                <?php if (empty($laporan_list)): ?>
                    <div class="alert alert-info text-center">
                        <strong>Belum ada laporan hilang saat ini.</strong>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Barang</th>
                                    <th>Deskripsi</th>
                                    <th>Kategori</th>
                                    <th>Lokasi</th>
                                    <th>Waktu Hilang</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th>Pembuat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($laporan_list as $i => $l): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($l['nama_barang']) ?></strong></td>
                                        <td><?= htmlspecialchars($l['deskripsi_fisik'] ?: '-') ?></td>
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

                <div class="text-center mt-4">
                    <a href="/logout.php" class="btn btn-outline-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>