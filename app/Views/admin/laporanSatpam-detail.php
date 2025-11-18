<?php
// app/Views/satpam/laporan-detail.php

require_once dirname(__DIR__, 2) . '../../config/db.php';
require_once dirname(__DIR__, 2) . '../../app/Controllers/AuthController.php';

$auth = new AuthController();
$sessionManager = $auth->getSessionManager();

if ($sessionManager->get('role') !== 'satpam') {
    header('Location: index.php?action=login');
    exit;
}

$id_laporan = $_GET['id'] ?? 0;
if (!$id_laporan || !is_numeric($id_laporan)) {
    die('<div class="text-center mt-5"><div class="alert alert-danger">Laporan tidak ditemukan.</div></div>');
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
    die('<div class="text-center mt-5"><div class="alert alert-danger">Laporan tidak ditemukan.</div></div>');
}

$alreadyTaken = $laporan['status'] === 'sudah_diambil';

// PATH GAMBAR
$baseUpload = 'public/uploads';
$imgSrc = $laporan['foto']
    ? "/{$baseUpload}/laporan/" . basename($laporan['foto'])
    : 'https://www.svgrepo.com/show/508699/landscape-placeholder.svg';

$fotoBukti = $laporan['foto_bukti']
    ? "/{$baseUpload}/bukti/" . basename($laporan['foto_bukti'])
    : null;

// Untuk sidebar
$GLOBALS['current_page'] = 'dashboard';
$title = 'Detail Laporan #' . $id_laporan;
include 'app/Views/layouts/sidebar.php';
?>

<link rel="stylesheet" href="/public/assets/css/laporanSatpam-detail.css">

<div class="detail-main-wrapper">

    <!-- KONTEN 1: DETAIL LAPORAN -->
    <div class="detail-section">
        <div class="detail-content">
            <!-- KIRI: GAMBAR -->
            <div class="detail-image-side">
                <img src="<?= $imgSrc ?>" alt="Foto Barang" class="detail-barang-img">
            </div>

            <!-- KANAN: INFORMASI -->
            <div class="detail-info-side">
                <!-- BADGE STATUS -->
                <div class="status-badge-large <?= $alreadyTaken ? 'status-taken' : 'status-missing' ?>">
                    <?= $alreadyTaken ? 'Sudah Diambil' : 'Belum Ditemukan' ?>
                </div>

                <!-- NAMA BARANG -->
                <h1 class="barang-title"><?= htmlspecialchars($laporan['nama_barang']) ?></h1>

                <!-- LOKASI & KATEGORI -->
                <div class="meta-row">
                    <div class="meta-box">
                        <span class="material-symbols-outlined">location_on</span>
                        <?= htmlspecialchars($laporan['lokasi']) ?>
                    </div>
                    <div class="meta-box">
                        <span class="material-symbols-outlined">category</span>
                        <?= ucfirst($laporan['kategori']) ?>
                    </div>
                </div>

                <hr class="separator">

                <!-- PELAPOR + DESKRIPSI -->
                <div class="info-grid">
                    <div class="pelapor-card">
                        <div class="pelapor-header">Pelapor</div>
                        <div class="pelapor-name"><?= htmlspecialchars($laporan['nama_pembuat']) ?></div>
                        <div class="pelapor-contact"><?= htmlspecialchars($laporan['nomor_kontak']) ?></div>
                        <?php if ($laporan['nomor_induk']): ?>
                            <div class="pelapor-nim">NIM: <?= htmlspecialchars($laporan['nomor_induk']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="deskripsi-card">
                        <div class="deskripsi-header">Deskripsi Ciri</div>
                        <div class="deskripsi-text">
                            <?= nl2br(htmlspecialchars($laporan['deskripsi_fisik'] ?: 'Tidak ada deskripsi')) ?>
                        </div>
                    </div>
                </div>

                <!-- TOMBOL WA -->
                <?php if ($laporan['tipe_laporan'] === 'hilang' && !$alreadyTaken): ?>
                    <?php
                    $template = "Halo {$laporan['nama_pembuat']},\n\nKami dari pos satpam FILKOM UB ingin menginformasikan bahwa barang Anda:\n• *Nama Barang:* {$laporan['nama_barang']}\n• *Lokasi Ditemukan:* {$laporan['lokasi']}\n\nSilakan segera hubungi kami untuk verifikasi dan pengambilan.\n\nTerima kasih,\nTim Lost & Found FILKOM UB";
                    $waLink = $laporan['nomor_kontak'] ? "https://wa.me/" . preg_replace('/\D/', '', $laporan['nomor_kontak']) . "?text=" . urlencode($template) : '#';
                    ?>
                    <div class="wa-button-box">
                        <a href="<?= $waLink ?>" target="_blank" class="btn-wa-large">
                            <span class="material-symbols-outlined">chat</span>
                            Kirim Pesan WhatsApp
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- KONTEN 2: CATAT PENGAMBIL BARANG -->
    <div class="claim-section">
        <div class="claim-header <?= $alreadyTaken ? 'claimed' : '' ?>">
            <?= $alreadyTaken ? 'Barang Sudah Diambil' : 'Catat Pengambil Barang' ?>
        </div>

        <div class="claim-content">
            <?php if ($alreadyTaken): ?>
                <!-- SUDAH DIAMBIL -->
                <div class="claimed-info">
                    <div class="claimed-grid">
                        <div class="claimed-field">
                            <div class="field-label">NIM Pengambil</div>
                            <div class="field-value"><?= htmlspecialchars($laporan['nim_pengambil']) ?></div>
                        </div>
                        <div class="claimed-field">
                            <div class="field-label">Waktu Diambil</div>
                            <div class="field-value"><?= date('d M Y, H:i', strtotime($laporan['waktu_diambil'])) ?> WIB</div>
                        </div>
                    </div>
                    <?php if ($fotoBukti): ?>
                        <div class="bukti-field">
                            <div class="field-label">Bukti Nomor Induk</div>
                            <a href="<?= $fotoBukti ?>" target="_blank">
                                <img src="<?= $fotoBukti ?>" alt="Bukti" class="bukti-image">
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- FORM CATAT PENGAMBIL -->
                <form method="POST" action="index.php?action=catat_pengambil" enctype="multipart/form-data" class="claim-form">
                    <input type="hidden" name="id_laporan" value="<?= $id_laporan ?>">

                    <div class="input-row">
                        <div class="input-group">
                            <label>NIM Pengambil <span class="required">*</span></label>
                            <input type="text" name="nim_pengambil" id="nimSearch" placeholder="Masukkan NIM" required>
                            <div id="suggestions" class="suggestions-list"></div>
                        </div>
                        <div class="input-group">
                            <label>Nama Lengkap <span class="required">*</span></label>
                            <input type="text" id="namaDisplay" readonly placeholder="Akan muncul otomatis">
                        </div>
                    </div>
                    <!-- Foto Bukti Nomor Induk -->
                    <div class="input-full">
                        <label>Foto Bukti Nomor Induk <span class="required">*</span></label>
                        <div class="upload-area" onclick="document.getElementById('fotoInput').click()">
                            <span class="material-symbols-outlined">add_photo_alternate</span>
                            <p>Klik atau drag langsung!</p>
                            <!-- INPUT FILE DISEMBUNYIKAN TOTAL -->
                            <input type="file" id="fotoInput" name="foto_bukti" accept="image/*" required onchange="previewBukti(event)" style="display:none;">
                        </div>
                        <div id="bukti-preview" class="preview-area" style="display:none;">
                            <img id="preview-img" src="" alt="Preview">
                        </div>
                    </div>


                    <div class="submit-area">
                        <button type="submit" class="btn-submit-claim">
                            Tandai Sudah Diambil
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    // Autocomplete NIM
    const nimInput = document.getElementById('nimSearch');
    const namaDisplay = document.getElementById('namaDisplay');
    const suggestions = document.getElementById('suggestions');
    let timeout;

    nimInput?.addEventListener('input', function() {
        clearTimeout(timeout);
        const q = this.value.trim();
        if (q.length < 3) {
            suggestions.innerHTML = '';
            suggestions.style.display = 'none';
            return;
        }
        timeout = setTimeout(() => {
            fetch(`index.php?action=search_civitas&nim=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    suggestions.innerHTML = '';
                    if (data.length === 0) {
                        suggestions.style.display = 'none';
                        return;
                    }
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.className = 'suggestion-item';
                        div.innerHTML = `<strong>${item.nomor_induk}</strong> - ${item.nama}`;
                        div.onclick = () => {
                            nimInput.value = item.nomor_induk;
                            namaDisplay.value = item.nama;
                            suggestions.innerHTML = '';
                            suggestions.style.display = 'none';
                        };
                        suggestions.appendChild(div);
                    });
                    suggestions.style.display = 'block';
                });
        }, 300);
    });

    // Preview bukti
    function previewBukti(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('bukti-preview');
        const img = document.getElementById('preview-img');
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                img.src = e.target.result;
                preview.style.display = 'block';
                document.querySelector('.upload-area').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    }

    // Hide suggestions on outside click
    document.addEventListener('click', e => {
        if (nimInput && !nimInput.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.style.display = 'none';
        }
    });
</script>

</div> <!-- end .page-container -->
</main>
</body>

</html>