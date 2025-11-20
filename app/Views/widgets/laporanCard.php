<?php
// Ambil data dari view
$laporanList = $laporanList ?? [];
$filter      = $filter ?? 'semua';

// Mapping filter ke status di DB
$statusMap = [
    'semua'           => null,
    'belum_ditemukan' => 'belum_ditemukan',
    'sudah_diambil'   => 'sudah_diambil'
];
$targetStatus = $statusMap[$filter] ?? null;

// Filter data
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

        // Default gambar
        $defaultImg = 'https://via.placeholder.com/300x200/eeeeee/999999?text=No+Image';
        $imgSrc = $defaultImg;

        if (!empty($item['foto'])) {
            // PAKAI /public/uploads/... → SAMA SEPERTI laporan-detail.php
            $imgSrc = '/public/uploads/laporan/' . basename($item['foto']);
        }
        ?>
        <a href="/index.php?action=laporan-detail&id=<?= $item['id_laporan'] ?>"
            class="text-decoration-none text-dark d-block mb-4">
            <div class="card">
                <div class="card-image">
                    <img src="<?= htmlspecialchars($imgSrc) ?>"
                        alt="<?= htmlspecialchars($item['nama_barang'] ?? 'Barang') ?>"
                        onerror="this.src='<?= $defaultImg ?>'; this.onerror=null;"
                        style="width:100%; height:200px; object-fit:cover; display:block; border-radius:8px;">

                </div>
                <div class="card-content">
                    <h3 class="card-title">
                         <div class="info-item">
                            <span class="card-status <?= $statusClass ?>">
                                <?= $statusText ?>
                        </div>

                        <?= htmlspecialchars($item['nama_barang'] ?? 'Tanpa Nama') ?>
                    </h3>
                    <div class="card-info">            
                        </span>
                        <div class="info-item">
                            <span class="material-symbols-outlined">pin_drop</span>
                            <span><?= htmlspecialchars($item['lokasi'] ?? 'Lokasi tidak diketahui') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="material-symbols-outlined">category</span>
                            <span><?= ucfirst(htmlspecialchars($item['kategori'] ?? 'Umum')) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
<?php endif; ?>