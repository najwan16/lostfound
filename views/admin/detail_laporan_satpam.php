<!-- views/admin/detail_laporan_satpam.php -->
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .card {
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 1.5rem;
            border-radius: 16px 16px 0 0;
        }

        .item-image img {
            border-radius: 12px;
        }

        .taken-info {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 1rem;
        }

        .form-catatan {
            margin-top: 2rem;
        }

        .btn-submit {
            background: #10b981;
            color: white;
        }

        .btn-submit:hover {
            background: #059669;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="card">
            <div class="header text-center">
                <h4 class="mb-0">Detail Laporan Barang Hilang</h4>
                <p class="mb-0">ID: #<?= $id_laporan ?></p>
            </div>

            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-5">
                        <div class="item-image">
                            <img src="<?= $imgSrc ?>" alt="Barang" class="img-fluid">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h3><?= htmlspecialchars($laporan['nama_barang']) ?></h3>
                        <div class="d-flex flex-wrap gap-3 mb-3 text-muted">
                            <span><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($laporan['lokasi']) ?></span>
                            <span><i class="bi bi-tag-fill"></i> <?= ucfirst($laporan['kategori']) ?></span>
                            <span><i class="bi bi-calendar-event"></i> <?= date('d M Y, H:i', strtotime($laporan['waktu'])) ?></span>
                        </div>
                        <hr>
                        <p><strong>Deskripsi:</strong><br><?= nl2br(htmlspecialchars($laporan['deskripsi_fisik'])) ?></p>
                        <div class="mt-4">
                            <strong>Dilaporkan oleh:</strong><br>
                            <?= htmlspecialchars($laporan['nama_pembuat']) ?><br>
                            <small><?= htmlspecialchars($laporan['nomor_kontak']) ?></small>
                            <?php if ($laporan['nomor_induk']): ?>
                                <div><strong>NIM:</strong> <?= htmlspecialchars($laporan['nomor_induk']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-3">
                            <span class="badge <?= $laporan['status'] === 'belum_ditemukan' ? 'bg-warning' : 'bg-success' ?>">
                                <?= ucfirst(str_replace('_', ' ', $laporan['status'])) ?>
                            </span>
                        </div>

                        <!-- JIKA SUDAH DIAMBIL -->
                        <?php if ($alreadyTaken): ?>
                            <div class="taken-info mt-4">
                                <h6><i class="bi bi-check-circle-fill text-success"></i> Sudah Diambil</h6>
                                <p><strong>NIM:</strong> <?= htmlspecialchars($laporan['nim_pengambil']) ?></p>
                                <p><strong>Waktu:</strong> <?= date('d M Y, H:i', strtotime($laporan['waktu_diambil'])) ?></p>
                                <?php if ($laporan['foto_bukti']): ?>
                                    <div class="mt-2">
                                        <strong>Bukti Nomor Induk:</strong><br>
                                        <a href="/<?= $laporan['foto_bukti'] ?>" target="_blank">
                                            <img src="/<?= $laporan['foto_bukti'] ?>" class="img-fluid rounded" style="max-height:200px;">
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- FORM CATAT PENGAMBIL -->
                <?php if (!$alreadyTaken): ?>
                    <hr class="my-4">
                    <div class="form-catatan">
                        <h5><i class="bi bi-person-check"></i> Catat Pengambil Barang</h5>
                        <form method="POST" action="index.php?action=catat_pengambil" enctype="multipart/form-data" id="formCatat">
                            <input type="hidden" name="id_laporan" value="<?= $id_laporan ?>">

                            <!-- INPUT NIM -->
                            <div class="mb-3">
                                <label class="form-label">NIM Pengambil <span class="text-danger">*</span></label>
                                <input type="text"
                                    name="nim_pengambil"
                                    id="nimSearch"
                                    class="form-control"
                                    placeholder="Ketik NIM..."
                                    autocomplete="off"
                                    required>
                                <div id="suggestions" class="list-group position-absolute w-100 mt-1" style="z-index:1000; max-height:200px; overflow-y:auto; display:none;"></div>
                            </div>

                            <!-- NAMA (OTOMATIS) -->
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" id="namaDisplay" class="form-control" readonly placeholder="Akan muncul otomatis">
                            </div>

                            <!-- FOTO BUKTI -->
                            <div class="mb-3">
                                <label class="form-label">Foto Bukti Nomor Induk <span class="text-danger">*</span></label>
                                <input type="file" name="foto_bukti" class="form-control" accept="image/*" required onchange="previewBukti(event)">
                                <div id="bukti-preview" class="mt-2" style="max-height:200px; overflow:hidden; border-radius:8px; display:none;">
                                    <img id="preview-img" src="" alt="Preview" style="width:100%; height:auto;">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-submit btn-lg w-100 mt-3">
                                <i class="bi bi-check-circle"></i> Tandai Sudah Diambil
                            </button>
                        </form>
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
                                fetch(`api/search_civitas.php?nim=${encodeURIComponent(q)}`)
                                    .then(r => r.json())
                                    .then(data => {
                                        suggestions.innerHTML = '';
                                        if (data.length === 0) {
                                            suggestions.style.display = 'none';
                                            return;
                                        }
                                        data.forEach(item => {
                                            const div = document.createElement('div');
                                            div.className = 'list-group-item list-group-item-action';
                                            div.innerHTML = `<strong>${item.nomor_induk}</strong><br><small>${item.nama}</small>`;
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

                <div class="text-center mt-4">
                    <a href="index.php?action=dashboard" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>