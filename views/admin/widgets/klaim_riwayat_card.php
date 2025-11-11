<!-- views/admin/partials/klaim_riwayat_card.php -->
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

            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge <?= $klaim['status_klaim'] === 'diverifikasi' ? 'bg-success' : 'bg-danger' ?>">
                        <?= ucfirst($klaim['status_klaim']) ?>
                    </span>
                    <small class="text-muted d-block">
                        Diproses: <?= date('d M Y H:i', strtotime($klaim['updated_at'])) ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>