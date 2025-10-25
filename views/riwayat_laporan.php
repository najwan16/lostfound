<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Laporan - Lost and Found FILKOM UB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet"> <!-- Tambah ini -->
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container mt-5">
        <h2>Riwayat & Status Laporan</h2>
        <?php if (isset($message) && $message): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($riwayat)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Dibuat Pada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayat as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                            <td><?php echo htmlspecialchars($item['kategori']); ?></td>
                            <td><?php echo htmlspecialchars($item['lokasi'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($item['waktu']); ?></td>
                            <td><?php echo htmlspecialchars($item['status']); ?></td>
                            <td><?php echo htmlspecialchars($item['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Tidak ada riwayat laporan saat ini.</p>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>