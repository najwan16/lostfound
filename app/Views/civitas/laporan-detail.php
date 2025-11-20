<!-- app/Views/civitas/laporan-detail.php -->
<?php
require_once 'config/db.php';

$userId   = $_SESSION['userId'] ?? null;
$userRole = $_SESSION['role'] ?? null;

if (!$userId) {
    header('Location: index.php?action=login');
    exit;
}

$id_laporan = $_GET['id'] ?? 0;
if (!$id_laporan || !is_numeric($id_laporan)) {
    die('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
}

// Ambil data laporan lengkap
$stmt = getDB()->prepare("
    SELECT l.*, a.nama AS nama_pembuat, a.nomor_kontak, c.nomor_induk AS nim_pembuat,
           p.nama AS nama_pengambil, pc.nomor_induk AS nim_pengambil
    FROM laporan l
    JOIN akun a ON l.id_akun = a.id_akun
    LEFT JOIN civitas c ON a.id_akun = c.id_akun
    LEFT JOIN civitas pc ON l.nim_pengambil = pc.nomor_induk
    LEFT JOIN akun p ON pc.id_akun = p.id_akun
    WHERE l.id_laporan = ?
");
$stmt->execute([$id_laporan]);
$laporan = $stmt->fetch();

if (!$laporan) {
    die('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
}

// Path gambar
$baseUpload = 'public/uploads';
$fotoLaporan = !empty($laporan['foto'])
    ? "/{$baseUpload}/laporan/" . basename($laporan['foto'])
    : 'https://via.placeholder.com/500x500/eeeeee/999999?text=No+Image';

$fotoBukti = !empty($laporan['foto_bukti'])
    ? "/{$baseUpload}/bukti/" . basename($laporan['foto_bukti'])
    : null;

// === LOGIKA claim YANG BARU & SEMPURNA ===
$claimInfo = null; // null = tidak muncul apa-apa, 'button' = tombol claim, 'already' = sudah claim

if ($userRole === 'civitas' && $laporan['status'] === 'sudah_diambil') {
    if ($laporan['id_akun'] !== $userId) { // Bukan laporan sendiri
        $check = getDB()->prepare("SELECT 1 FROM claim WHERE id_laporan = ? AND id_akun = ?");
        $check->execute([$id_laporan, $userId]);
        if ($check->fetch()) {
            $claimInfo = 'already';
        } else {
            $claimInfo = 'button';
        }
    }
}
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
    <link href="public/assets/css/laporan-detail.css" rel="stylesheet">
</head>

<body>
    <?php include realpath(dirname(__DIR__) . '/layouts/navbar.php'); ?>

    <div class="container mt-4">
        <div class="item-card">
            <!-- Gambar -->
            <div class="item-image-section">
                <img src="<?= $fotoLaporan ?>" alt="<?= htmlspecialchars($laporan['nama_barang']) ?>" class="main-image">
            </div>

            <!-- Detail -->
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
                    <p class="item-description"><?= nl2br(htmlspecialchars($laporan['deskripsi_fisik'])) ?></p>

                    <!-- Dilaporkan oleh -->
                    <div class="pembuat mt-3">
                        <strong>Dilaporkan oleh:</strong><br>
                        <?= htmlspecialchars($laporan['nama_pembuat']) ?><br>
                        <small class="text-muted"><?= htmlspecialchars($laporan['nomor_kontak']) ?></small>
                        <?php if ($laporan['nim_pembuat']): ?>
                            <div class="nim">NIM: <?= htmlspecialchars($laporan['nim_pembuat']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Status -->
                    <div class="mt-3">
                        <span class="badge <?= $laporan['status'] === 'belum_ditemukan' ? 'bg-warning text-dark' : 'bg-success' ?>">
                            <?= ucfirst(str_replace('_', ' ', $laporan['status'])) ?>
                        </span>
                    </div>

                    <!-- Jika sudah diambil -->
                    <?php if ($laporan['status'] === 'sudah_diambil' && $laporan['nama_pengambil']): ?>
                        <div class="taken-info mt-4 p-3 border rounded bg-light">
                            <h6 class="text-success"><i class="bi bi-check-circle-fill"></i> Barang Sudah Diambil</h6>
                            <div class="mt-2">
                                <strong>Oleh:</strong> <?= htmlspecialchars($laporan['nama_pengambil']) ?><br>
                                <strong>NIM:</strong> <?= htmlspecialchars($laporan['nim_pengambil']) ?><br>
                                <strong>Tanggal:</strong> <?= date('d M Y, H:i', strtotime($laporan['waktu_diambil'])) ?>
                            </div>
                            <?php if ($fotoBukti): ?>
                                <div class="mt-3 text-center">
                                    <p><strong>Bukti Pengambilan:</strong></p>
                                    <a href="<?= $fotoBukti ?>" target="_blank">
                                        <img src="<?= $fotoBukti ?>" alt="Bukti" class="img-fluid rounded shadow-sm" style="max-height: 200px;">
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- LOGIKA claim BARU -->
                <div class="mt-4 text-center">
                    <?php if ($claimInfo === 'button'): ?>
                        <a href="index.php?action=claim&id=<?= $laporan['id_laporan'] ?>"
                            class="claim-btn d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-shield-check me-2"></i> klaim Kepemilikan
                        </a>
                    <?php elseif ($claimInfo === 'already'): ?>
                        <div class="alert alert-info d-inline-block p-3">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Anda sudah mengajukan klaim untuk barang ini
                        </div>
                    <?php elseif ($laporan['id_akun'] == $userId): ?>
                        <small class="text-muted d-block">
                            <i class="bi bi-person-fill me-1"></i> Ini laporan Anda
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const card = document.querySelector('.item-card');
            card.style.opacity = '0';
            setTimeout(() => {
                card.style.transition = 'opacity 0.5s ease';
                card.style.opacity = '1';
            }, 100);
        });
    </script>
</body>

</html>