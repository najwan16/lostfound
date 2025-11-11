<!-- views/detail_laporan.php -->
<?php
require_once 'config/db.php';

// === CEK SESSION & USER ID ===
$userId = $_SESSION['userId'] ?? null;
$userRole = $_SESSION['role'] ?? null;

if (!$userId) {
    header('Location: index.php?action=login');
    exit;
}

// Ambil ID dari URL
$id_laporan = $_GET['id'] ?? 0;
if (!$id_laporan || !is_numeric($id_laporan)) {
    die('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
}

// === AMBIL DATA LAPORAN + PENGAMBIL ===
$stmt = getDB()->prepare("
    SELECT 
        l.*, 
        a.nama AS nama_pembuat, 
        a.nomor_kontak, 
        c.nomor_induk AS nim_pembuat,
        -- Data pengambil
        pengambil.nama AS nama_pengambil,
        pengambil_civitas.nomor_induk AS nim_pengambil
    FROM laporan l
    JOIN akun a ON l.id_akun = a.id_akun
    LEFT JOIN civitas c ON a.id_akun = c.id_akun
    -- JOIN PENGAMBIL: dari nim_pengambil → civitas → akun
    LEFT JOIN civitas pengambil_civitas ON l.nim_pengambil = pengambil_civitas.nomor_induk
    LEFT JOIN akun pengambil ON pengambil_civitas.id_akun = pengambil.id_akun
    WHERE l.id_laporan = ?
");
$stmt->execute([$id_laporan]);
$laporan = $stmt->fetch();

if (!$laporan) {
    die('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
}

// === LOGIKA TOMBOL KLAIM ===
$showClaimButton = false;
if ($userRole === 'civitas') {
    $isOwnReport = ($laporan['id_akun'] == $userId);
    $isTaken = ($laporan['status'] === 'sudah_diambil');
    if (!$isOwnReport && $isTaken) {
        $showClaimButton = true;
    }
}

// GAMBAR UTAMA
$mainImage = $laporan['foto']
    ? "/{$laporan['foto']}"
    : 'https://via.placeholder.com/500x500/eeeeee/999999?text=No+Image';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($laporan['nama_barang']) ?> - Lost & Found FILKOM UB</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="../css/detail_laporan.css" rel="stylesheet">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="item-card">
            <!-- Bagian Gambar -->
            <div class="item-image-section">
                <img src="<?= $mainImage ?>"
                    alt="<?= htmlspecialchars($laporan['nama_barang']) ?>"
                    class="main-image"
                    id="mainImage">
            </div>

            <!-- Bagian Detail -->
            <div class="item-details">
                <div>
                    <h1 class="item-title"><?= htmlspecialchars($laporan['nama_barang']) ?></h1>
                    <div class="item-meta">
                        <span><span class="material-symbols-outlined">pin_drop</span> <?= htmlspecialchars($laporan['lokasi']) ?></span>
                        <span class="meta-divider">|</span>
                        <span><i class="bi bi-tag"></i> <?= ucfirst($laporan['kategori']) ?></span>
                        <span class="meta-divider">|</span>
                        <span><i class="bi bi-calendar"></i> <?= date('d M Y, H:i', strtotime($laporan['waktu'])) ?></span>
                    </div>
                    <hr>
                    <p class="item-description">
                        <?= nl2br(htmlspecialchars($laporan['deskripsi_fisik'])) ?>
                    </p>

                    <!-- DILAPORKAN OLEH -->
                    <div class="pembuat mt-3">
                        <strong>Dilaporkan oleh:</strong><br>
                        <?= htmlspecialchars($laporan['nama_pembuat']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($laporan['nomor_kontak']) ?></small>
                        <?php if ($laporan['nim_pembuat']): ?>
                            <div class="nim">NIM: <?= htmlspecialchars($laporan['nim_pembuat']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- STATUS BARANG -->
                    <div class="mt-3">
                        <span class="badge 
                            <?= $laporan['status'] === 'belum_ditemukan' ? 'bg-warning text-dark' : ($laporan['status'] === 'ditemukan' ? 'bg-info text-white' : 'bg-success') ?>">
                            <?= ucfirst(str_replace('_', ' ', $laporan['status'])) ?>
                        </span>
                    </div>

                    <!-- JIKA SUDAH DIAMBIL: TAMPILKAN PENGAMBIL -->
                    <?php if ($laporan['status'] === 'sudah_diambil' && $laporan['nama_pengambil']): ?>
                        <div class="taken-info mt-4 p-3 border rounded bg-light">
                            <h6 class="text-success">
                                <i class="bi bi-check-circle-fill"></i> Barang Sudah Diambil
                            </h6>
                            <div class="mt-2">
                                <strong>Oleh:</strong> <?= htmlspecialchars($laporan['nama_pengambil']) ?><br>
                                <strong>NIM:</strong> <?= htmlspecialchars($laporan['nim_pengambil']) ?><br>
                                <strong>Tanggal:</strong> <?= date('d M Y, H:i', strtotime($laporan['waktu_diambil'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- TOMBOL KLAIM -->
                <?php if ($showClaimButton): ?>
                    <a href="index.php?action=claim&id=<?= $laporan['id_laporan'] ?>"
                        class="claim-btn d-flex align-items-center justify-content-center mt-4">
                        <i class="bi bi-shield-check me-2"></i> Klaim Kepemilikan
                    </a>
                <?php else: ?>
                    <?php if ($laporan['status'] === 'sudah_diambil' && $laporan['id_akun'] == $userId): ?>
                        <div class="text-center mt-4 text-success">
                            <i class="bi bi-check-circle-fill"></i> Anda telah mengambil barang ini.
                        </div>
                    <?php elseif ($laporan['id_akun'] == $userId): ?>
                        <div class="text-center mt-4 text-muted">
                            <i class="bi bi-person-fill"></i> Ini laporan Anda.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const card = document.querySelector('.item-card');
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.transition = 'opacity 0.5s';
                card.style.opacity = '1';
            }, 100);
        });
    </script>
</body>

</html>