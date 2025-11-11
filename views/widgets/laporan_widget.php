<!-- views/widgets/laporan_widget.php -->
<?php
if (!isset($laporanList)) $laporanList = [];
if (!isset($filter)) $filter = 'semua';

// === MAP FILTER KE STATUS DATABASE ===
$statusMap = [
    'semua'           => null,
    'belum_ditemukan' => 'belum_ditemukan',
    'sudah_diambil'         => 'sudah_diambil'
];

$targetStatus = $statusMap[$filter] ?? null;

// === FILTER DATA ===
$filtered = ($filter === 'semua')
    ? $laporanList
    : array_filter($laporanList, fn($item) => ($item['status'] ?? '') === $targetStatus);
?>

<?php if (empty($filtered)): ?>
    <div class="text-center py-5 w-100">
        <p class="text-muted fs-5">Tidak ada laporan untuk kategori ini.</p>
    </div>
<?php else: ?>
    <?php foreach ($filtered as $item): ?>
        <?php
        $dbStatus = $item['status'] ?? 'belum_ditemukan';
        $isSelesai = $dbStatus === 'sudah_diambil';
        $statusClass = $isSelesai ? 'status-completed' : 'status-pending';
        $statusText = $isSelesai ? 'Sudah Diambil' : 'Belum Ditemukan';

        $defaultImg = 'https://via.placeholder.com/300x200/eeeeee/999999?text=No+Image';
        $imgSrc = $defaultImg;
        if (!empty($item['foto'])) {
            $serverPath = __DIR__ . '/../../' . $item['foto'];
            $urlPath = '/' . $item['foto'];
            if (file_exists($serverPath)) {
                $imgSrc = $urlPath;
            }
        }
        ?>
        <a href="index.php?action=detail_laporan&id=<?= $item['id_laporan'] ?>"
            class="text-decoration-none text-dark d-block">
            <div class="card">
                <div class="card-image">
                    <img src="<?= htmlspecialchars($imgSrc) ?>"
                        alt="<?= htmlspecialchars($item['nama_barang'] ?? 'Barang') ?>"
                        onerror="this.src='<?= $defaultImg ?>';">
                    <span class="card-status <?= $statusClass ?>">
                        <?= $statusText ?>
                    </span>
                </div>
                <div class="card-content">
                    <h3 class="card-title">
                        <?= htmlspecialchars($item['nama_barang'] ?? 'Tanpa Nama') ?>
                    </h3>
                    <div class="card-info">
                        <div class="info-item">
                            <span class="material-symbols-outlined">pin_drop</span>
                            <span><?= htmlspecialchars($item['lokasi'] ?? 'Lokasi tidak diketahui') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="material-symbols-outlined">category</span>
                            <span><?= ucfirst($item['kategori'] ?? 'Umum') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
<?php endif; ?>