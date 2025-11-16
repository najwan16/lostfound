<?php
$current_page = 'claim';
$user = $this->model->getUser($this->session->get('userId'));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klaim Kepemilikan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/assets/css/claim-form.css" rel="stylesheet">

</head>

<body>
    <?php include realpath(dirname(__DIR__) . '/layouts/navbar.php'); ?>


    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Klaim Kepemilikan Barang</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="index.php?action=submit_claim" enctype="multipart/form-data">
                            <input type="hidden" name="id_laporan" value="<?= $idLaporan ?>">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">NIM</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['nomor_induk']) ?>" disabled>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi Ciri Khusus <span class="text-danger">*</span></label>
                                <textarea name="deskripsi_ciri" class="form-control" rows="4" placeholder="Contoh: Ada stiker nama di belakang..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Bukti Kepemilikan <span class="text-danger">*</span></label>
                                <input type="file" name="bukti_kepemilikan" class="form-control" accept="image/*" required>
                                <small class="text-muted">JPG/PNG, max 3MB</small>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-success px-5">Ajukan Claim</button>
                                <a href="index.php?action=laporan-detail&id=<?= $idLaporan ?>" class="btn btn-secondary">Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>