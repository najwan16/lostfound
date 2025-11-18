<?php
// app/Views/satpam/dashboard_claim.php

require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__, 2) . '../../app/Controllers/AuthController.php';

$auth = new AuthController();
$sessionManager = $auth->getSessionManager();

if ($sessionManager->get('role') !== 'satpam') {
    header('Location: index.php?action=login');
    exit;
}

$pdo = getDB();

// TAB & COUNTS
$tab = $_GET['tab'] ?? 'masuk';
$counts = [
    'diajukan' => $pdo->query("SELECT COUNT(*) FROM claim WHERE status_claim = 'diajukan'")->fetchColumn(),
    'diverifikasi' => $pdo->query("SELECT COUNT(*) FROM claim WHERE status_claim = 'diverifikasi'")->fetchColumn(),
    'ditolak' => $pdo->query("SELECT COUNT(*) FROM claim WHERE status_claim = 'ditolak'")->fetchColumn(),
];

// AMBIL DATA SESUAI TAB
$status_map = [
    'masuk' => 'diajukan',
    'diverifikasi' => 'diverifikasi',
    'ditolak' => 'ditolak'
];
$status = $status_map[$tab] ?? 'diajukan';

$stmt = $pdo->prepare("
    SELECT c.*, l.nama_barang, l.lokasi, l.kategori, l.foto AS foto_laporan,
           a.nama AS nama_pengaju, a.nomor_kontak, civ.nomor_induk AS nim_pengaju
    FROM claim c
    JOIN laporan l ON c.id_laporan = l.id_laporan
    JOIN akun a ON c.id_akun = a.id_akun
    LEFT JOIN civitas civ ON a.id_akun = civ.id_akun
    WHERE c.status_claim = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$status]);
$claimList = $stmt->fetchAll();

$GLOBALS['current_page'] = 'dashboard_claim';
$title = 'Manajemen Klaim';
include 'app/Views/layouts/sidebar.php';
?>

<link rel="stylesheet" href="/public/assets/css/dashboard_claim.css">

<div class="claim-wrapper">

    <div class="container-fluid">

        <!-- TAB COUNTER -->
        <div class="tab-counter mb-5">
            <a href="index.php?action=dashboard_claim&tab=masuk" class="counter-item <?= $tab === 'masuk' ? 'active' : '' ?>">
                <span class="count masuk"><?= $counts['diajukan'] ?></span>
                Masuk
            </a>
            <a href="index.php?action=dashboard_claim&tab=diverifikasi" class="counter-item <?= $tab === 'diverifikasi' ? 'active' : '' ?>">
                <span class="count diverifikasi"><?= $counts['diverifikasi'] ?></span>
                Disetujui
            </a>
            <a href="index.php?action=dashboard_claim&tab=ditolak" class="counter-item <?= $tab === 'ditolak' ? 'active' : '' ?>">
                <span class="count ditolak"><?= $counts['ditolak'] ?></span>
                Ditolak
            </a>
        </div>

        <!-- LIST KLAIM -->
        <?php if (empty($claimList)): ?>
            <div class="empty-state">
                <span class="material-symbols-outlined">inbox</span>
                <p>Tidak ada klaim <?= $tab === 'masuk' ? 'baru' : ($tab === 'diverifikasi' ? 'disetujui' : 'ditolak') ?>.</p>
            </div>
        <?php else: ?>
            <div class="claim-grid">
                <?php foreach ($claimList as $c):
                    $fotoLaporan = $c['foto_laporan'] ? "/public/uploads/laporan/" . basename($c['foto_laporan']) : 'https://via.placeholder.com/400x300/eee/999?text=No+Image';
                    $fotoBukti = $c['bukti_kepemilikan'] ? "/public/uploads/bukti_claim/" . basename($c['bukti_kepemilikan']) : null;

                    // WA Link
                    $waText = "Halo {$c['nama_pengaju']}, klaim Anda untuk barang *{$c['nama_barang']}* sedang kami proses. Silakan hubungi pos satpam untuk pengambilan.";
                    $waLink = $c['nomor_kontak'] ? "https://wa.me/" . preg_replace('/\D/', '', $c['nomor_kontak']) . "?text=" . urlencode($waText) : '#';
                ?>
                    <div class="claim-card" onclick="openClaimModal(<?= $c['id_claim'] ?>)">
                        <div class="claim-image">
                            <img src="<?= $fotoLaporan ?>" alt="Foto Barang">
                        </div>
                        <div class="claim-info">
                            <h5><?= htmlspecialchars($c['nama_barang']) ?></h5>
                            <div class="claim-meta">
                                <span><span class="material-symbols-outlined">location_on</span> <?= htmlspecialchars($c['lokasi']) ?></span>
                                <span><span class="material-symbols-outlined">category</span> <?= ucfirst($c['kategori']) ?></span>
                            </div>
                            <?php if ($tab === 'masuk'): ?>
                                <div class="claim-actions">
                                    <button class="btn-setuju" onclick="verifikasiClaim(<?= $c['id_claim'] ?>, <?= $c['id_laporan'] ?>, 'diverifikasi', event)">Setuju</button>
                                    <button class="btn-tolak" onclick="verifikasiClaim(<?= $c['id_claim'] ?>, <?= $c['id_laporan'] ?>, 'ditolak', event)">Tolak</button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- MODAL DETAIL KLAIM -->
                    <div id="modal-<?= $c['id_claim'] ?>" class="claim-modal">
                        <div class="modal-content">
                            <span class="modal-close" onclick="closeModal(<?= $c['id_claim'] ?>)">&times;</span>
                            <div class="modal-body">
                                <div class="modal-left">
                                    <img src="<?= $fotoLaporan ?>" alt="Foto Laporan" class="modal-image">
                                    <h5><?= htmlspecialchars($c['nama_barang']) ?></h5>
                                    <p><span class="material-symbols-outlined">location_on</span> <?= htmlspecialchars($c['lokasi']) ?></p>
                                    <p><span class="material-symbols-outlined">category</span> <?= ucfirst($c['kategori']) ?></p>

                                    <h6 class="civitas-title">Profil Pengambil Barang</h6>
                                    <a class="civitas-name"><?= htmlspecialchars($c['nama_pengaju']) ?></a>
                                    <a class="civitas-nim">NIM: <?= htmlspecialchars($c['nim_pengaju'] ?? 'Tidak ada NIM') ?></a>
                                    <a class="civitas-contact">Kontak: <?= htmlspecialchars($c['nomor_kontak']) ?></a>

                                    <a href="<?= $waLink ?>" target="_blank" class="btn-wa-modal">
                                        <span class="material-symbols-outlined">chat</span>
                                        Hubungi Pengambil
                                    </a>
                                </div>
                                <div class="modal-right">
                                    <h5>Pengaju</h5>
                                    <p class="pengaju-name"><?= htmlspecialchars($c['nama_pengaju']) ?></p>
                                    <p class="pengaju-nim"><?= htmlspecialchars($c['nim_pengaju']) ?></p>

                                    <h6 class="mt-4">Bukti Kepemilikan</h6>
                                    <?php if ($fotoBukti): ?>
                                        <img src="<?= $fotoBukti ?>" alt="Bukti" class="bukti-image">
                                    <?php else: ?>
                                        <p class="text-muted">Tidak ada bukti</p>
                                    <?php endif; ?>

                                    <h6 class="mt-4">Deskripsi Ciri</h6>
                                    <p class="deskripsi-text"><?= nl2br(htmlspecialchars($c['deskripsi_ciri'] ?: 'Tidak ada deskripsi')) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    function openClaimModal(id) {
        document.getElementById('modal-' + id).style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById('modal-' + id).style.display = 'none';
    }

    function verifikasiClaim(id_claim, id_laporan, status, event) {
        event.stopPropagation();
        if (!confirm(status === 'diverifikasi' ? 'Setujui klaim ini?' : 'Tolak klaim ini?')) return;

        const form = new FormData();
        form.append('id_claim', id_claim);
        form.append('id_laporan', id_laporan);
        form.append('status', status);

        fetch('index.php?action=verifikasi_claim', {
                method: 'POST',
                body: form
            }).then(r => r.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert('Gagal memproses klaim');
                }
            });
    }

    // Close modal when clicking outside
    window.onclick = function(e) {
        if (e.target.classList.contains('claim-modal')) {
            e.target.style.display = 'none';
        }
    }
</script>

</div> <!-- end .page-container -->
</main>
</body>

</html>