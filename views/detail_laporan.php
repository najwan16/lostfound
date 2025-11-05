<!-- views/detail_laporan.php -->
<?php
require_once 'config/db.php';

// Ambil ID dari URL
$id_laporan = $_GET['id'] ?? 0;
if (!$id_laporan || !is_numeric($id_laporan)) {
    die('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
}

// Ambil data laporan
$stmt = getDB()->prepare("
    SELECT 
        l.*, a.nama AS nama_pembuat, a.nomor_kontak, c.nomor_induk
    FROM laporan l
    JOIN akun a ON l.id_akun = a.id_akun
    LEFT JOIN civitas c ON a.id_akun = c.id_akun
    WHERE l.id_laporan = ?
");
$stmt->execute([$id_laporan]);
$laporan = $stmt->fetch();

if (!$laporan) {
    die('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
}

// GAMBAR UTAMA DARI DATABASE
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
                <!-- TIDAK ADA THUMBNAIL -->
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
                    <div class="pembuat mt-3">
                        <strong>Dilaporkan oleh:</strong><br>
                        <?= htmlspecialchars($laporan['nama_pembuat']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($laporan['nomor_kontak']) ?></small>
                        <?php if ($laporan['nomor_induk']): ?>
                            <div class="nim">NIM: <?= htmlspecialchars($laporan['nomor_induk']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TOMBOL KLAIM: TEKS DI TENGAH -->
                <a href="index.php?action=claim&id=<?= $laporan['id_laporan'] ?>" 
                   class="claim-btn d-flex align-items-center justify-content-center">
                    Klaim Kepemilikan
                </a>
            </div>
        </div>
    </div>

    <!-- TIDAK ADA SCRIPT THUMBNAIL -->
</body>
</html>