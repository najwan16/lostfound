<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Lost and Found FILKOM UB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/home.css" rel="stylesheet"> <!-- Pastikan path CSS benar -->
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container mt-5">
        <div class="row align-items-start home-layout"> <!-- Ubah ke align-items-start -->
            <!-- Bagian Kiri: Teks dan Tombol -->
            <div class="col-md-6">
                <h1>Temukan barang hilang dengan</h1>
                <h1 class="underline-orange">mudah</h1>
                <p class="lead">Kehilangan barang di lingkungan FILKOM? Cukup cari atau laporkan di sini. cepat, praktis, dan terpercaya.</p>
                <?php if ($controller->getSessionManager()->get('userId')): ?>
                    <a href="../index.php?action=laporan_form" class="btn btn-primary btn-lg">Laporkan Sekarang</a>
                <?php else: ?>
                    <a href="../index.php?action=login" class="btn btn-primary btn-lg">Login untuk Melapor</a>
                <?php endif; ?>
            </div>
            <!-- Bagian Kanan: Gambar -->
            <div class="col-md-6">
                <img src="../images/beranda.png" alt="Gambar Kampus FILKOM UB" class="img-fluid home-image" onerror="this.src='https://via.placeholder.com/500x300';">
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>