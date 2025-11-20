<?php
$current_page = 'claim_saya';

$tab = $_GET['tab'] ?? 'semua';

// Filter data
$filteredClaims = $claimList;

if ($tab !== 'semua') {
    $filteredClaims = array_filter($claimList, function ($item) use ($tab) {
        return match ($tab) {
            'diajukan'     => $item['status_claim'] === 'diajukan',
            'diverifikasi' => $item['status_claim'] === 'diverifikasi',
            'ditolak'      => $item['status_claim'] === 'ditolak',
            default        => false
        };
    });
}

// Sembunyikan note di tab "semua"
$hideNote = ($tab === 'semua');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Klaim Saya</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link href="public/assets/css/claim.css" rel="stylesheet">

</head>

<body>
    <?php include realpath(dirname(__DIR__) . '/layouts/navbar.php'); ?>

    <div class="container">

        <div class="content-wrapper">
            <!-- Sidebar Filter -->
            <div class="sidebar">
                <div class="filter-box">
                    <a href="?action=claim_saya&tab=semua" class="filter-btn text-decoration-none <?= $tab === 'semua' ? 'active' : '' ?>">
                        <span class="material-symbols-outlined">list_alt</span> Semua Klaim
                    </a>
                    <a href="?action=claim_saya&tab=diajukan" class="filter-btn text-decoration-none <?= $tab === 'diajukan' ? 'active' : '' ?>">
                        <span class="material-symbols-outlined">schedule</span> Ditinjau
                    </a>
                    <a href="?action=claim_saya&tab=diverifikasi" class="filter-btn text-decoration-none <?= $tab === 'diverifikasi' ? 'active' : '' ?>">
                        <span class="material-symbols-outlined">check_circle</span> Disetujui
                    </a>
                    <a href="?action=claim_saya&tab=ditolak" class="filter-btn text-decoration-none <?= $tab === 'ditolak' ? 'active' : '' ?>">
                        <span class="material-symbols-outlined">cancel</span> Ditolak
                    </a>
                </div>
            </div>

            <!-- Daftar Klaim -->
            <div class="cards-grid">
                <?php if (empty($filteredClaims)): ?>
                    <div class="no-claim">
                        <p class="mb-0 fs-5 text-muted">
                            Belum ada klaim <?= $tab === 'semua' ? '' : strtolower($tab) ?>.
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($filteredClaims as $claim): ?>
                        <?php
                        $status = $claim['status_claim'] ?? 'diajukan';

                        $config = [
                            'diajukan' => [
                                'text'       => 'Ditinjau',
                                'class'      => 'status-pending',
                                'note'       => 'Satpam sedang meninjau bukti kepemilikan barangmu.',
                                'note_class' => 'note-pending'
                            ],
                            'diverifikasi' => [
                                'text'       => 'Disetujui',
                                'class'      => 'status-approved',
                                'note'       => 'Silakan ambil barang di Lobby Satpam (bawa KTM).',
                                'note_class' => 'note-approved'
                            ],
                            'ditolak' => [
                                'text'       => 'Ditolak',
                                'class'      => 'status-rejected',
                                'note'       => 'Maaf, klaim ini ditolak. Bukti kepemilikan kurang lengkap atau tidak sesuai.',
                                'note_class' => 'note-rejected'
                            ]
                        ];

                        $st = $config[$status] ?? $config['diajukan'];

                        $imgSrc = !empty($claim['foto_laporan'])
                            ? '/public/uploads/laporan/' . basename($claim['foto_laporan'])
                            : (!empty($claim['bukti_kepemilikan'])
                                ? '/public/' . $claim['bukti_kepemilikan']
                                : 'https://via.placeholder.com/400x300/eeeeee/999999?text=Barang');

                        // Nama barang dari LAPORAN
                        $namaBarang = htmlspecialchars($claim['nama_barang'] ?? 'Barang Tidak Diketahui');
                        ?>

                        <a href="index.php?action=claim_detail&id=<?= $claim['id_claim'] ?>" class="claim-card text-decoration-none">
                            <div class="card-image">
                                <img src="<?= $imgSrc ?>"
                                    alt="<?= $namaBarang ?>"
                                    onerror="this.src='https://via.placeholder.com/400x300/eeeeee/999999?text=Barang'">

                            </div>

                            <div class="card-content">
                                <!-- HANYA NAMA BARANG, TANPA ID -->
                                <span class="card-status <?= $st['class'] ?>">
                                    <?= $st['text'] ?>
                                </span>
                                <h3 class="card-title fw-bold">

                                    <?= $namaBarang ?>
                                </h3>

                                <div class="card-info">
                                    <div class="info-item">
                                        <span class="material-symbols-outlined">event</span>
                                        <span><?= date('d M Y', strtotime($claim['created_at'])) ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="material-symbols-outlined">pin_drop</span>
                                        <span><?= htmlspecialchars($claim['lokasi'] ?? 'Lokasi tidak diketahui') ?></span>
                                    </div>
                                </div>

                                <!-- Keterangan hanya muncul jika bukan tab "semua" -->
                                <?php if (!$hideNote): ?>
                                    <div class="status-note <?= $st['note_class'] ?>">
                                        <?= $st['note'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>