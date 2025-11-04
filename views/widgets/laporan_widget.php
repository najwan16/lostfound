<?php
if (!isset($laporanList)) $laporanList = [];
if (!isset($filter)) $filter = 'semua';

$statusMap = [
    'semua' => null,
    'belum_ditemukan' => 'belum_ditemukan',
    'selesai' => 'selesai'
];

$targetStatus = $statusMap[$filter] ?? null;

$filtered = ($filter === 'semua')
    ? $laporanList
    : array_filter($laporanList, fn($item) => ($item['status'] ?? '') === $targetStatus);
?>

<div class="row" id="laporan-container">
    <?php if (empty($filtered)): ?>
        <div class="col-12 text-center py-5">
            <p class="text-muted fs-5">Kamu belum memiliki laporan.</p>
        </div>
    <?php else: ?>
        <?php foreach ($filtered as $item): ?>
            <?php
            $isSelesai = ($item['status'] ?? 'belum_ditemukan') === 'selesai';
            $statusText = $isSelesai ? 'Selesai' : 'Belum Selesai';
            $statusClass = $isSelesai ? 'status-selesai' : 'status-belum';

            // GAMBAR - PATH YANG BENAR
            $defaultImg = 'https://via.placeholder.com/300x200/eeeeee/999999?text=No+Image';
            $imgSrc = $defaultImg;

            if (!empty($item['foto'])) {
                // PATH SERVER: dari views/widgets/ ke LostFound/
                $serverPath = __DIR__ . '/../../' . $item['foto']; // HANYA 3 LEVEL KE ATAS

                // PATH URL: dari root web
                $urlPath = '/' . $item['foto']; // /uploads/laporan/...

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
                            style="object-fit: cover;"
                            onerror="this.src='<?= $defaultImg ?>';">
                    </div>
                    <div class="card-body d-flex flex-column flex-grow-1">
                        <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
                        <h5 class="card-title mt-2 mb-3"><?= htmlspecialchars($item['nama_barang'] ?? 'Tanpa Nama') ?></h5>
                        <div class="card-info mt-auto">
                            <div class="d-flex align-items-center mb-2 text-muted">
                                <small><?= htmlspecialchars($item['lokasi'] ?? 'Lokasi tidak diketahui') ?></small>
                            </div>
                            <div class="d-flex align-items-center text-muted">
                                <small><?= htmlspecialchars($item['kategori'] ?? 'Umum') ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>