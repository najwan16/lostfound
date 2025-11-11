<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'satpam') {
    header('Location: ../../index.php?action=login');
    exit;
}

$id_laporan = $_GET['id'] ?? 0;
if (!$id_laporan || !is_numeric($id_laporan)) {
    die('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
}

$pdo = getDB();
$stmt = $pdo->prepare("
    SELECT l.*, a.nama AS nama_pembuat, a.nomor_kontak, c.nomor_induk
    FROM laporan l
    JOIN akun a ON l.id_akun = a.id_akun
    LEFT JOIN civitas c ON a.id_akun = c.id_akun
    WHERE l.id_laporan = ?
");
$stmt->execute([$id_laporan]);
$laporan = $stmt->fetch();

if (!$laporan) {
    die('<div class="alert alert-danger">Laporan tidak ditemukan.</div>');
}

$alreadyTaken = $laporan['status'] === 'sudah_diambil';
$imgSrc = $laporan['foto'] ? "/{$laporan['foto']}" : 'https://via.placeholder.com/500x500/eeeeee/999999?text=No+Image';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail #<?= $id_laporan ?> - Satpam</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="../../css/admin.css" rel="stylesheet">

</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'widgets/sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="container-fluid p-4">

            <!-- PAGE HEADER -->
            <div class="page-header mb-4">
                <h3 class="mb-1">Detail Laporan #<?= $id_laporan ?></h3>
                <p class="text-muted mb-0">
                    Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Satpam') ?></strong>
                </p>
            </div>

            <!-- CARD: DETAIL LAPORAN -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-text"></i> Informasi Laporan</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="text-center">
                                <img src="<?= $imgSrc ?>" alt="Foto Barang" class="img-fluid rounded shadow-sm" style="max-height: 400px;">
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <h3 class="mb-3"><?= htmlspecialchars($laporan['nama_barang']) ?></h3>
                            <div class="d-flex flex-wrap gap-3 mb-3 text-muted small">
                                <span><i class="bi bi-geo-alt-fill text-primary"></i> <?= htmlspecialchars($laporan['lokasi']) ?></span>
                                <span><i class="bi bi-tag-fill text-success"></i> <?= ucfirst($laporan['kategori']) ?></span>
                                <span><i class="bi bi-calendar-event text-info"></i> <?= date('d M Y, H:i', strtotime($laporan['waktu'])) ?></span>
                            </div>

                            <hr>

                            <div class="mb-4">
                                <h6 class="fw-bold">Deskripsi Fisik</h6>
                                <p class="text-muted"><?= nl2br(htmlspecialchars($laporan['deskripsi_fisik'])) ?></p>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-bold">Dilaporkan Oleh</h6>
                                <p class="mb-1"><?= htmlspecialchars($laporan['nama_pembuat']) ?></p>
                                <p class="mb-1 text-muted"><?= htmlspecialchars($laporan['nomor_kontak']) ?></p>
                                <?php if ($laporan['nomor_induk']): ?>
                                    <p class="mb-0 text-primary fw-bold">NIM: <?= htmlspecialchars($laporan['nomor_induk']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <span class="badge <?= $laporan['status'] === 'belum_ditemukan' ? 'bg-warning' : 'bg-success' ?> fs-6 px-3 py-2">
                                    <?= ucfirst(str_replace('_', ' ', $laporan['status'])) ?>
                                </span>
                            </div>

                            <!-- JIKA SUDAH DIAMBIL -->
                            <?php if ($alreadyTaken): ?>
                                <div class="alert alert-success p-4 rounded">
                                    <h6 class="fw-bold"><i class="bi bi-check-circle-fill"></i> Barang Sudah Diambil</h6>
                                    <p class="mb-1"><strong>NIM Pengambil:</strong> <?= htmlspecialchars($laporan['nim_pengambil']) ?></p>
                                    <p class="mb-2"><strong>Waktu Diambil:</strong> <?= date('d M Y, H:i', strtotime($laporan['waktu_diambil'])) ?></p>
                                    <?php if ($laporan['foto_bukti']): ?>
                                        <div class="mt-3">
                                            <strong>Bukti Nomor Induk:</strong><br>
                                            <a href="/<?= $laporan['foto_bukti'] ?>" target="_blank">
                                                <img src="/<?= $laporan['foto_bukti'] ?>" class="img-fluid rounded shadow-sm" style="max-height: 220px;">
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM CATAT PENGAMBIL -->
            <?php if (!$alreadyTaken): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-gradient-warning text-white">
                        <h5 class="mb-0"><i class="bi bi-person-check"></i> Catat Pengambil Barang</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="../../index.php?action=catat_pengambil" enctype="multipart/form-data" id="formCatat">
                            <input type="hidden" name="id_laporan" value="<?= $id_laporan ?>">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">NIM Pengambil <span class="text-danger">*</span></label>
                                    <input type="text" name="nim_pengambil" id="nimSearch" class="form-control"
                                        placeholder="Ketik NIM..." autocomplete="off" required>
                                    <div id="suggestions" class="list-group position-absolute w-100 mt-1"
                                        style="z-index:1000; max-height:200px; overflow-y:auto; display:none;"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" id="namaDisplay" class="form-control" readonly placeholder="Akan muncul otomatis">
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label">Foto Bukti Nomor Induk <span class="text-danger">*</span></label>
                                <input type="file" name="foto_bukti" class="form-control" accept="image/*" required onchange="previewBukti(event)">
                                <div id="bukti-preview" class="mt-3 rounded overflow-hidden shadow-sm" style="display:none; max-height:220px;">
                                    <img id="preview-img" src="" alt="Preview" class="w-100">
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-success btn-lg px-5">
                                    Tandai Sudah Diambil
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <script>
                    const nimInput = document.getElementById('nimSearch');
                    const namaDisplay = document.getElementById('namaDisplay');
                    const suggestions = document.getElementById('suggestions');
                    let timeout;

                    nimInput.addEventListener('input', function() {
                        clearTimeout(timeout);
                        const q = this.value.trim();
                        if (q.length < 3) {
                            suggestions.style.display = 'none';
                            return;
                        }
                        timeout = setTimeout(() => {
                            fetch(`../../api/search_civitas.php?nim=${encodeURIComponent(q)}`)
                                .then(r => r.json())
                                .then(data => {
                                    suggestions.innerHTML = '';
                                    if (data.length === 0) {
                                        suggestions.style.display = 'none';
                                        return;
                                    }
                                    data.forEach(item => {
                                        const div = document.createElement('div');
                                        div.className = 'list-group-item list-group-item-action py-2';
                                        div.innerHTML = `<strong>${item.nomor_induk}</strong><br><small class="text-muted">${item.nama}</small>`;
                                        div.onclick = () => {
                                            nimInput.value = item.nomor_induk;
                                            namaDisplay.value = item.nama;
                                            suggestions.style.display = 'none';
                                        };
                                        suggestions.appendChild(div);
                                    });
                                    suggestions.style.display = 'block';
                                });
                        }, 300);
                    });

                    function previewBukti(e) {
                        const file = e.target.files[0];
                        const preview = document.getElementById('bukti-preview');
                        const img = document.getElementById('preview-img');
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = e => {
                                img.src = e.target.result;
                                preview.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                        }
                    }

                    document.addEventListener('click', e => {
                        if (!nimInput.contains(e.target) && !suggestions.contains(e.target)) {
                            suggestions.style.display = 'none';
                        }
                    });
                </script>
            <?php endif; ?>

            <div class="text-center mt-5">
                <a href="../../index.php?action=dashboard" class="btn btn-outline-secondary px-4">
                    Kembali ke Dashboard
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>