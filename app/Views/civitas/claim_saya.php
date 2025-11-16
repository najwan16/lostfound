<?php
$current_page = 'claim_saya';
$tab = $_GET['tab'] ?? 'diajukan';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include realpath(dirname(__DIR__) . '/layouts/navbar.php'); ?>


    <div class="container mt-4">
        <h3>Claim Saya</h3>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'diajukan' ? 'active' : '' ?>" href="index.php?action=claim_saya&tab=diajukan">
                    Diajukan <span class="badge bg-warning"><?= $counts['diajukan'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'diverifikasi' ? 'active' : '' ?>" href="index.php?action=claim_saya&tab=diverifikasi">
                    Diverifikasi <span class="badge bg-success"><?= $counts['diverifikasi'] ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'ditolak' ? 'active' : '' ?>" href="index.php?action=claim_saya&tab=ditolak">
                    Ditolak <span class="badge bg-danger"><?= $counts['ditolak'] ?></span>
                </a>
            </li>
        </ul>

        <div class="row">
            <?php if (empty($claimList)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Belum ada claim <?= $tab ?>.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($claimList as $claim): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6>#<?= $claim['id_claim'] ?> - <?= htmlspecialchars($claim['nama_barang']) ?></h6>
                                <p class="small text-muted">Diajukan: <?= date('d M Y', strtotime($claim['created_at'])) ?></p>
                                <span class="badge <?= $claim['status_claim'] === 'diverifikasi' ? 'bg-success' : ($claim['status_claim'] === 'ditolak' ? 'bg-danger' : 'bg-warning') ?>">
                                    <?= ucfirst($claim['status_claim']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>