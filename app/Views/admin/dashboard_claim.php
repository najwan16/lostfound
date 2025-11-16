<?php

$current_page = 'dashboard_claim';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Claim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link href="public/assets/css/admin.css" rel="stylesheet">

</head>

<body>
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid p-4">
            <h3 class="mb-4">Manajemen Claim</h3>

            <!-- TAB -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'masuk' ? 'active' : '' ?>"
                        href="index.php?action=dashboard_claim&tab=masuk">
                        Masuk <span class="badge bg-warning text-dark"><?= $counts['diajukan'] ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'diverifikasi' ? 'active' : '' ?>"
                        href="index.php?action=dashboard_claim&tab=diverifikasi">
                        Diverifikasi <span class="badge bg-success"><?= $counts['diverifikasi'] ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $tab === 'ditolak' ? 'active' : '' ?>"
                        href="index.php?action=dashboard_claim&tab=ditolak">
                        Ditolak <span class="badge bg-danger"><?= $counts['ditolak'] ?></span>
                    </a>
                </li>
            </ul>

            <!-- LIST CLAIM -->
            <?php if (empty($claimList)): ?>
                <div class="alert alert-light text-center p-5 rounded border">
                    <i class="material-symbols-outlined fs-1 text-secondary">inbox</i>
                    <p class="mt-3 mb-0 fs-5"><strong>Tidak ada claim <?= $tab ?>.</strong></p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($claimList as $claim): ?>
                        <div class="col-lg-6 col-xxl-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light border-0 py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 text-primary fw-bold">
                                            #<?= $claim['id_claim'] ?> - <?= htmlspecialchars($claim['nama_barang']) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= date('d M Y', strtotime($claim['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>

                                <div class="card-body p-4">
                                    <div class="row g-3 mb-4">
                                        <div class="col-6">
                                            <p class="mb-1"><strong>Pengaju:</strong></p>
                                            <p class="mb-1 fw-semibold"><?= htmlspecialchars($claim['nama_pengaju']) ?></p>
                                            <p class="mb-0 text-primary fw-bold">NIM: <?= htmlspecialchars($claim['nim_pengaju']) ?></p>
                                        </div>
                                        <div class="col-6 text-end">
                                            <p class="mb-1"><strong>Lokasi:</strong> <?= htmlspecialchars($claim['lokasi']) ?></p>
                                            <p class="mb-0"><strong>Kategori:</strong> <?= ucfirst($claim['kategori']) ?></p>
                                        </div>
                                    </div>

                                    <div class="text-center mb-4">
                                        <a href="/public/<?= $claim['foto'] ?>" target="_blank">
                                            <img src="/public/<?= $claim['foto'] ?>" alt="Foto Barang" class="img-fluid rounded shadow-sm card-img">
                                        </a>
                                        <p class="small text-muted mt-2 mb-0">Klik untuk perbesar</p>
                                    </div>

                                    <div class="border-top pt-3 mb-4">
                                        <p class="mb-1 fw-bold">Deskripsi Ciri:</p>
                                        <p class="text-muted small"><?= nl2br(htmlspecialchars($claim['deskripsi_ciri'])) ?: '<em>Tidak ada</em>' ?></p>
                                    </div>

                                    <div class="text-center mb-4">
                                        <p class="mb-2 fw-bold">Bukti Kepemilikan:</p>
                                        <a href="/public/<?= $claim['bukti_kepemilikan'] ?>" target="_blank">
                                            <img src="/public/<?= $claim['bukti_kepemilikan'] ?>" alt="Bukti" class="img-fluid rounded shadow-sm card-img">
                                        </a>
                                        <p class="small text-muted mt-2 mb-0">Klik untuk perbesar</p>
                                    </div>

                                    <?php if ($tab === 'masuk'): ?>
                                        <div class="d-grid d-md-flex gap-2 justify-content-center">
                                            <form method="POST" action="index.php?action=verifikasi_claim" class="d-inline">
                                                <input type="hidden" name="id_claim" value="<?= $claim['id_claim'] ?>">
                                                <input type="hidden" name="id_laporan" value="<?= $claim['id_laporan'] ?>">
                                                <input type="hidden" name="status" value="diverifikasi">
                                                <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                            </form>
                                            <form method="POST" action="index.php?action=verifikasi_claim" class="d-inline">
                                                <input type="hidden" name="id_claim" value="<?= $claim['id_claim'] ?>">
                                                <input type="hidden" name="status" value="ditolak">
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin tolak claim ini?')">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center">
                                            <span class="badge <?= $claim['status_claim'] === 'diverifikasi' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= ucfirst($claim['status_claim']) ?>
                                            </span>
                                            <small class="text-muted d-block">
                                                Diproses: <?= date('d M Y H:i', strtotime($claim['updated_at'])) ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- TOMBOL KEMBALI -->
            <div class="text-center mt-5">
                <a href="index.php?action=dashboard" class="btn btn-outline-secondary px-5 py-2">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</body>

</html>