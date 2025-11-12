<!-- views/civitas/klaim_saya.php -->
<?php
// ANTI-CACHE
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/controllers/AuthController.php';

$auth = new AuthController();
$sessionManager = $auth->getSessionManager();

if ($sessionManager->get('role') !== 'civitas') {
    header('Location: ' . dirname(__DIR__, 2) . '/index.php?action=home');
    exit;
}

$pdo = getDB();

// === TAB AKTIF ===
$tab = $_GET['tab'] ?? 'diajukan';
$valid_tabs = ['diajukan', 'diverifikasi', 'ditolak'];
if (!in_array($tab, $valid_tabs)) $tab = 'diajukan';

$id_akun = $sessionManager->get('userId');

// === AMBIL DATA KLAIM DENGAN LEFT JOIN + COALESCE ===
$stmt = $pdo->prepare("
    SELECT 
        k.id_klaim, k.id_laporan, k.status_klaim, k.created_at, k.updated_at,
        COALESCE(l.nama_barang, 'Barang Tidak Diketahui') AS nama_barang,
        COALESCE(l.foto, 'uploads/default.jpg') AS foto_barang,
        COALESCE(l.lokasi, 'Lokasi Tidak Diketahui') AS lokasi,
        COALESCE(l.kategori, 'Kategori Tidak Diketahui') AS kategori
    FROM klaim k
    LEFT JOIN laporan l ON k.id_laporan = l.id_laporan
    WHERE k.id_akun = ? AND k.status_klaim = ?
    ORDER BY k.created_at DESC
");
$stmt->execute([$id_akun, $tab]);
$klaim_list = $stmt->fetchAll();

// === HITUNG STATISTIK ===
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM klaim WHERE id_akun = ? AND status_klaim = ?");
$count_stmt->execute([$id_akun, 'diajukan']);
$total_diajukan = $count_stmt->fetchColumn();
$count_stmt->execute([$id_akun, 'diverifikasi']);
$total_diverifikasi = $count_stmt->fetchColumn();
$count_stmt->execute([$id_akun, 'ditolak']);
$total_ditolak = $count_stmt->fetchColumn();

$page_title = 'Klaim Saya';
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
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><?= $page_title ?></h3>
            <a href="index.php?action=laporan" class="btn btn-outline-secondary">
                Kembali ke Laporan
            </a>
        </div>

        <!-- TAB NAVIGATION -->
        <ul class="nav nav-tabs mb-4" id="klaimTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $tab === 'diajukan' ? 'active' : '' ?>"
                    data-bs-toggle="tab" data-bs-target="#tab-diajukan" type="button">
                    Diajukan
                    <span class="badge bg-warning text-dark ms-2"><?= $total_diajukan ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $tab === 'diverifikasi' ? 'active' : '' ?>"
                    data-bs-toggle="tab" data-bs-target="#tab-diverifikasi" type="button">
                    Diverifikasi
                    <span class="badge bg-success ms-2"><?= $total_diverifikasi ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $tab === 'ditolak' ? 'active' : '' ?>"
                    data-bs-toggle="tab" data-bs-target="#tab-ditolak" type="button">
                    Ditolak
                    <span class="badge bg-danger ms-2"><?= $total_ditolak ?></span>
                </button>
            </li>
        </ul>

        <!-- TAB CONTENT -->
        <div class="tab-content" id="klaimTabContent">
            <?php
            // FUNGSI TAMPILKAN CARD (REUSABLE)
            function renderKlaimCard($k, $status)
            {
                $badgeClass = $status === 'diajukan' ? 'bg-warning text-dark' : ($status === 'diverifikasi' ? 'bg-success' : 'bg-danger');
                $badgeText = ucfirst($status);
                $infoText = $status === 'diverifikasi' ? 'Silakan menuju ke pos satpam' : ($status === 'ditolak' ? 'Klaim tidak memenuhi syarat' : 'Menunggu verifikasi');
            ?>
                <div class="col-lg-6 col-xxl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 text-primary fw-bold">
                                    #<?= $k['id_klaim'] ?> - <?= htmlspecialchars($k['nama_barang']) ?>
                                </h6>
                                <small class="text-muted">
                                    <?= date('d M Y', strtotime($k['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <a href="../../<?= $k['foto_barang'] ?>" target="_blank">
                                    <img src="../../<?= $k['foto_barang'] ?>" alt="Foto Barang"
                                        class="img-fluid rounded shadow-sm"
                                        style="max-height: 140px; object-fit: cover;">
                                </a>
                                <p class="small text-muted mt-2 mb-0">Klik untuk perbesar</p>
                            </div>
                            <div class="border-top pt-3">
                                <p class="mb-1"><strong>Lokasi:</strong> <?= htmlspecialchars($k['lokasi']) ?></p>
                                <p class="mb-1"><strong>Kategori:</strong> <?= ucfirst($k['kategori']) ?></p>
                            </div>
                            <div class="mt-3">
                                <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                                <?php if ($status !== 'diajukan'): ?>
                                    <small class="text-muted d-block mt-1">
                                        Diproses: <?= date('d M Y H:i', strtotime($k['updated_at'])) ?>
                                    </small>
                                <?php endif; ?>
                                <small class="text-muted d-block mt-1"><?= $infoText ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>

            <!-- TAB: DIAJUKAN -->
            <div class="tab-pane fade <?= $tab === 'diajukan' ? 'show active' : '' ?>" id="tab-diajukan">
                <?php
                $stmt->execute([$id_akun, 'diajukan']);
                $klaim_list = $stmt->fetchAll();
                ?>
                <?php if (empty($klaim_list)): ?>
                    <div class="alert alert-light text-center p-5 rounded border">
                        <i class="bi bi-inbox fs-1 text-secondary"></i>
                        <p class="mt-3 mb-0 fs-5"><strong>Belum ada klaim diajukan.</strong></p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($klaim_list as $k): ?>
                            <?= renderKlaimCard($k, 'diajukan') ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB: DIVERIFIKASI -->
            <div class="tab-pane fade <?= $tab === 'diverifikasi' ? 'show active' : '' ?>" id="tab-diverifikasi">
                <?php
                $stmt->execute([$id_akun, 'diverifikasi']);
                $klaim_list = $stmt->fetchAll();
                ?>
                <?php if (empty($klaim_list)): ?>
                    <div class="alert alert-light text-center p-5 rounded border">
                        <i class="bi bi-inbox fs-1 text-secondary"></i>
                        <p class="mt-3 mb-0 fs-5"><strong>Belum ada klaim diverifikasi.</strong></p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($klaim_list as $k): ?>
                            <?= renderKlaimCard($k, 'diverifikasi') ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAB: DITOLAK -->
            <div class="tab-pane fade <?= $tab === 'ditolak' ? 'show active' : '' ?>" id="tab-ditolak">
                <?php
                $stmt->execute([$id_akun, 'ditolak']);
                $klaim_list = $stmt->fetchAll();
                ?>
                <?php if (empty($klaim_list)): ?>
                    <div class="alert alert-light text-center p-5 rounded border">
                        <i class="bi bi-inbox fs-1 text-secondary"></i>
                        <p class="mt-3 mb-0 fs-5"><strong>Belum ada klaim ditolak.</strong></p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($klaim_list as $k): ?>
                            <?= renderKlaimCard($k, 'ditolak') ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>