<!-- views/admin/dashboard_klaim.php -->
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

// === AMBIL SEMUA KLAIM YANG MENUNGGU VERIFIKASI ===
$stmt = $pdo->prepare("
    SELECT 
        k.id_klaim, k.id_laporan, k.id_akun, k.bukti_kepemilikan, k.deskripsi_ciri, k.created_at,
        l.nama_barang, l.lokasi, l.kategori, l.foto AS foto_barang,
        a.nama AS nama_pengaju, c.nomor_induk AS nim_pengaju
    FROM klaim k
    JOIN laporan l ON k.id_laporan = l.id_laporan
    JOIN akun a ON k.id_akun = a.id_akun
    LEFT JOIN civitas c ON a.id_akun = c.id_akun
    WHERE k.status_klaim = 'diajukan'
    ORDER BY k.created_at DESC
");
$stmt->execute();
$klaim_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Klaim - Dashboard Satpam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 2rem;
            border-radius: 16px 16px 0 0;
            text-align: center;
        }
        .klaim-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            transition: 0.2s;
        }
        .klaim-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .klaim-header {
            background: #f1f5f9;
            padding: 1rem;
            font-weight: 600;
        }
        .klaim-body {
            padding: 1.5rem;
        }
        .bukti-img {
            max-height: 200px;
            border-radius: 8px;
            cursor: pointer;
        }
        .btn-verify {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="header">
                <h3 class="mb-0">Verifikasi Klaim Kepemilikan</h3>
                <p class="mb-0">Selamat datang, <strong><?= htmlspecialchars($session->get('nama')) ?></strong></p>
            </div>

            <div class="card-body p-4">
                <h5 class="mb-4">
                    <i class="bi bi-shield-check"></i> Daftar Klaim Masuk
                    <span class="badge bg-warning text-dark ms-2"><?= count($klaim_list) ?></span>
                </h5>

                <?php if (empty($klaim_list)): ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-inbox"></i> <strong>Belum ada klaim masuk.</strong>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($klaim_list as $k): ?>
                            <div class="col-lg-6">
                                <div class="klaim-card">
                                    <div class="klaim-header">
                                        Klaim #<?= $k['id_klaim'] ?> - <?= htmlspecialchars($k['nama_barang']) ?>
                                    </div>
                                    <div class="klaim-body">
                                        <div class="row g-3">
                                            <!-- Pengaju -->
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Pengaju:</strong> <?= htmlspecialchars($k['nama_pengaju']) ?></p>
                                                <p class="mb-1"><strong>NIM:</strong> <?= htmlspecialchars($k['nim_pengaju']) ?></p>
                                                <p class="mb-0 text-muted"><small><?= date('d M Y, H:i', strtotime($k['created_at'])) ?></small></p>
                                            </div>
                                            <!-- Barang -->
                                            <div class="col-md-6 text-end">
                                                <p class="mb-1"><strong>Lokasi:</strong> <?= htmlspecialchars($k['lokasi']) ?></p>
                                                <p class="mb-1"><strong>Kategori:</strong> <?= ucfirst($k['kategori']) ?></p>
                                            </div>
                                        </div>

                                        <!-- Deskripsi Ciri -->
                                        <div class="mt-3">
                                            <strong>Deskripsi Ciri:</strong>
                                            <p class="text-muted small"><?= nl2br(htmlspecialchars($k['deskripsi_ciri'])) ?></p>
                                        </div>

                                        <!-- Bukti Kepemilikan -->
                                        <div class="mt-3 text-center">
                                            <strong>Bukti Kepemilikan:</strong><br>
                                            <a href="/<?= $k['bukti_kepemilikan'] ?>" target="_blank">
                                                <img src="/<?= $k['bukti_kepemilikan'] ?>" alt="Bukti" class="bukti-img img-fluid">
                                            </a>
                                        </div>

                                        <!-- Tombol Verifikasi -->
                                        <div class="mt-3 text-center">
                                            <form method="POST" action="index.php?action=verifikasi_klaim" class="d-inline">
                                                <input type="hidden" name="id_klaim" value="<?= $k['id_klaim'] ?>">
                                                <input type="hidden" name="id_laporan" value="<?= $k['id_laporan'] ?>">
                                                <input type="hidden" name="status" value="diverifikasi">
                                                <button type="submit" class="btn btn-success btn-verify me-2">
                                                    <i class="bi bi-check-circle"></i> Setujui
                                                </button>
                                            </form>
                                            <form method="POST" action="index.php?action=verifikasi_klaim" class="d-inline">
                                                <input type="hidden" name="id_klaim" value="<?= $k['id_klaim'] ?>">
                                                <input type="hidden" name="status" value="ditolak">
                                                <button type="submit" class="btn btn-danger btn-verify" 
                                                        onclick="return confirm('Tolak klaim ini?')">
                                                    <i class="bi bi-x-circle"></i> Tolak
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="index.php?action=dashboard" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>