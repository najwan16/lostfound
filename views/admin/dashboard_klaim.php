<!-- views/admin/dashboard_klaim.php -->
<?php
// ANTI-CACHE
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/controllers/AuthController.php';

$auth = new AuthController();
$sessionManager = $auth->getSessionManager();

if ($sessionManager->get('role') !== 'satpam') {
    header('Location: ' . dirname(__DIR__, 2) . '/index.php?action=home');
    exit;
}

$pdo = getDB();

// === TAB AKTIF ===
$tab = $_GET['tab'] ?? 'masuk';
$valid_tabs = ['masuk', 'diverifikasi', 'ditolak'];
if (!in_array($tab, $valid_tabs)) $tab = 'masuk';

// === FUNGSI AMBIL DATA KLAIM ===
function getKlaimByStatus($pdo, $status)
{
    $stmt = $pdo->prepare("
        SELECT 
            k.id_klaim, k.id_laporan, k.id_akun, k.bukti_kepemilikan, k.deskripsi_ciri, 
            k.status_klaim, k.created_at, k.updated_at,
            COALESCE(l.nama_barang, 'Barang Tidak Ditemukan') AS nama_barang,
            COALESCE(l.lokasi, 'Lokasi Tidak Diketahui') AS lokasi,
            COALESCE(l.kategori, 'Kategori Tidak Diketahui') AS kategori,
            COALESCE(l.foto, 'uploads/default.jpg') AS foto_barang,
            COALESCE(a.nama, 'Pengaju Tidak Diketahui') AS nama_pengaju,
            COALESCE(c.nomor_induk, 'NIM Tidak Ada') AS nim_pengaju
        FROM klaim k
        LEFT JOIN laporan l ON k.id_laporan = l.id_laporan
        LEFT JOIN akun a ON k.id_akun = a.id_akun
        LEFT JOIN civitas c ON a.id_akun = c.id_akun
        WHERE k.status_klaim = ?
        ORDER BY k.created_at DESC
    ");
    $stmt->execute([$status]);
    return $stmt->fetchAll();
}

// === AMBIL DATA PER TAB ===
$klaim_masuk = getKlaimByStatus($pdo, 'diajukan');
$klaim_diverifikasi = getKlaimByStatus($pdo, 'diverifikasi');
$klaim_ditolak = getKlaimByStatus($pdo, 'ditolak');

// === HITUNG STATISTIK ===
$total_masuk = count($klaim_masuk);
$total_diverifikasi = count($klaim_diverifikasi);
$total_ditolak = count($klaim_ditolak);

$page_title = match ($tab) {
    'masuk' => 'Klaim Masuk',
    'diverifikasi' => 'Klaim Diverifikasi',
    'ditolak' => 'Klaim Ditolak'
};
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
    <link href="/css/admin.css" rel="stylesheet">
</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'widgets/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="container-fluid p-4">

            <!-- PAGE HEADER -->
            <div class="page-header mb-4">
                <h3 class="mb-1"><?= $page_title ?></h3>
                <p class="text-muted mb-0">
                    Selamat datang, <strong><?= htmlspecialchars($sessionManager->get('nama')) ?></strong>
                </p>
            </div>

            <!-- TAB NAVIGATION -->
            <ul class="nav nav-tabs mb-4" id="klaimTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $tab === 'masuk' ? 'active' : '' ?>"
                        data-bs-toggle="tab" data-bs-target="#tab-masuk" type="button">
                        Klaim Masuk
                        <span class="badge bg-warning text-dark ms-2"><?= $total_masuk ?></span>
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
                <!-- TAB: KLAIM MASUK -->
                <div class="tab-pane fade <?= $tab === 'masuk' ? 'show active' : '' ?>" id="tab-masuk">
                    <?php if (empty($klaim_masuk)): ?>
                        <div class="alert alert-light text-center p-5 rounded border">
                            <i class="bi bi-inbox fs-1 text-secondary"></i>
                            <p class="mt-3 mb-0 fs-5"><strong>Belum ada klaim masuk.</strong></p>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($klaim_masuk as $klaim): ?>
                                <div class="col-lg-6 col-xxl-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-light border-0 py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0 text-primary fw-bold">
                                                    #<?= $klaim['id_klaim'] ?> - <?= htmlspecialchars($klaim['nama_barang']) ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <?= date('d M Y', strtotime($klaim['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="card-body p-4">
                                            <div class="row g-3 mb-4">
                                                <div class="col-6">
                                                    <p class="mb-1"><strong>Pengaju:</strong></p>
                                                    <p class="mb-1 fw-semibold"><?= htmlspecialchars($klaim['nama_pengaju']) ?></p>
                                                    <p class="mb-0 text-primary fw-bold">NIM: <?= htmlspecialchars($klaim['nim_pengaju']) ?></p>
                                                </div>
                                                <div class="col-6 text-end">
                                                    <p class="mb-1"><strong>Lokasi:</strong> <?= htmlspecialchars($klaim['lokasi']) ?></p>
                                                    <p class="mb-0"><strong>Kategori:</strong> <?= ucfirst($klaim['kategori']) ?></p>
                                                </div>
                                            </div>

                                            <div class="text-center mb-4">
                                                <a href="../../<?= $klaim['foto_barang'] ?>" target="_blank">
                                                    <img src="../../<?= $klaim['foto_barang'] ?>" alt="Foto Barang" class="img-fluid rounded shadow-sm" style="max-height: 140px; object-fit: cover;">
                                                </a>
                                                <p class="small text-muted mt-2 mb-0">Klik untuk perbesar</p>
                                            </div>

                                            <div class="border-top pt-3 mb-4">
                                                <p class="mb-1 fw-bold">Deskripsi Ciri:</p>
                                                <p class="text-muted small"><?= nl2br(htmlspecialchars($klaim['deskripsi_ciri'])) ?: '<em>Tidak ada</em>' ?></p>
                                            </div>

                                            <div class="text-center mb-4">
                                                <p class="mb-2 fw-bold">Bukti Kepemilikan:</p>
                                                <a href="../../<?= $klaim['bukti_kepemilikan'] ?>" target="_blank">
                                                    <img src="../../<?= $klaim['bukti_kepemilikan'] ?>" alt="Bukti" class="img-fluid rounded shadow-sm" style="max-height: 160px; object-fit: cover;">
                                                </a>
                                                <p class="small text-muted mt-2 mb-0">Klik untuk perbesar</p>
                                            </div>

                                            <div class="d-grid d-md-flex gap-2 justify-content-center">
                                                <form method="POST" action="index.php?action=verifikasi_klaim" class="d-inline">
                                                    <input type="hidden" name="id_klaim" value="<?= $klaim['id_klaim'] ?>">
                                                    <input type="hidden" name="id_laporan" value="<?= $klaim['id_laporan'] ?>">
                                                    <input type="hidden" name="status" value="diverifikasi">
                                                    <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                                </form>
                                                <form method="POST" action="index.php?action=verifikasi_klaim" class="d-inline">
                                                    <input type="hidden" name="id_klaim" value="<?= $klaim['id_klaim'] ?>">
                                                    <input type="hidden" name="status" value="ditolak">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin tolak klaim ini?')">
                                                        Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB: DIVERIFIKASI -->
                <div class="tab-pane fade <?= $tab === 'diverifikasi' ? 'show active' : '' ?>" id="tab-diverifikasi">
                    <?php if (empty($klaim_diverifikasi)): ?>
                        <div class="alert alert-light text-center p-5 rounded border">
                            <i class="bi bi-inbox fs-1 text-secondary"></i>
                            <p class="mt-3 mb-0 fs-5"><strong>Tidak ada klaim diverifikasi.</strong></p>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($klaim_diverifikasi as $klaim): ?>
                                <?php include 'widgets/klaim_riwayat_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TAB: DITOLAK -->
                <div class="tab-pane fade <?= $tab === 'ditolak' ? 'show active' : '' ?>" id="tab-ditolak">
                    <?php if (empty($klaim_ditolak)): ?>
                        <div class="alert alert-light text-center p-5 rounded border">
                            <i class="bi bi-inbox fs-1 text-secondary"></i>
                            <p class="mt-3 mb-0 fs-5"><strong>Tidak ada klaim ditolak.</strong></p>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($klaim_ditolak as $klaim): ?>
                                <?php include 'widgets/klaim_riwayat_card.php'; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TOMBOL KEMBALI -->
            <div class="text-center mt-5">
                <a href="index.php?action=dashboard" class="btn btn-outline-secondary px-5 py-2">
                    Kembali ke Dashboard
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>