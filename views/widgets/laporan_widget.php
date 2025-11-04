<?php
/*  laporan_widget.php
    Widget reusable untuk menampilkan daftar laporan barang hilang.
    
    Variabel yang diperlukan:
      $laporanList → array dari database (wajib)
      $filter      → 'semua' | 'belum_ditemukan' | 'selesai' (opsional, default: semua)
*/

if (!isset($laporanList)) {
    $laporanList = [];
}
if (!isset($filter)) {
    $filter = 'semua';
}

// MAPPING FILTER KE NILAI DATABASE
$statusMap = [
    'semua'           => null,
    'belum_ditemukan' => 'belum_ditemukan',
    'selesai'         => 'selesai'
];

$targetStatus = $statusMap[$filter] ?? null;

// Filter data
$filtered = ($filter === 'semua')
    ? $laporanList
    : array_filter($laporanList, fn($item) => ($item['status'] ?? '') === $targetStatus);
?>

<div class="row" id="laporan-container">
    <?php if (empty($filtered)): ?>
        <div class="col-12 text-center py-5">
            <p class="text-muted fs-5">Tidak ada laporan untuk kategori ini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($filtered as $item): ?>
            <?php
            // --- STATUS TAMPILAN ---
            $isSelesai = ($item['status'] ?? 'belum_ditemukan') === 'selesai';
            $statusText = $isSelesai ? 'Selesai' : 'Belum Selesai';
            $statusClass = $isSelesai ? 'status-selesai' : 'status-belum';

            // --- GAMBAR ---
            $defaultImg = 'https://via.placeholder.com/300x200/eeeeee/999999?text=No+Image';
            $imgSrc = $defaultImg;

            if (!empty($item['foto'])) {
                $serverPath = __DIR__ . '/../../' . $item['foto'];
                $urlPath = '../' . $item['foto'];
                if (file_exists($serverPath)) {
                    $imgSrc = $urlPath;
                }
            }
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="laporan-card h-100 d-flex flex-column">
                    <div class="card-image position-relative overflow-hidden">
                        <img src="<?= htmlspecialchars($imgSrc) ?>"
                            alt="<?= htmlspecialchars($item['nama_barang'] ?? 'Barang') ?>"
                            class="img-fluid w-100 h-100"
                            style="object-fit: cover; transition: transform 0.3s ease;"
                            onerror="this.src='<?= $defaultImg ?>';">
                    </div>

                    <div class="card-body d-flex flex-column flex-grow-1">
                        <span class="status-badge <?= $statusClass ?>">
                            <?= $statusText ?>
                        </span>
                        <h5 class="card-title mt-2 mb-3">
                            <?= htmlspecialchars($item['nama_barang'] ?? 'Tanpa Nama') ?>
                        </h5>
                        <div class="card-info mt-auto">
                            <div class="d-flex align-items-center mb-2 text-muted">
                                <i class="bi bi-geo-alt me-2 text-warning"></i>
                                <small><?= htmlspecialchars($item['lokasi'] ?? 'Lokasi tidak diketahui') ?></small>
                            </div>
                            <div class="d-flex align-items-center text-muted">
                                <i class="bi bi-tag me-2 text-primary"></i>
                                <small><?= htmlspecialchars($item['kategori'] ?? 'Umum') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>