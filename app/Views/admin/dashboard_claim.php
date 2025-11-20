<?php
// app/Views/satpam/dashboard_claim.php
require_once dirname(__DIR__, 3) . '../config/db.php';
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
    'diajukan'     => $pdo->query("SELECT COUNT(*) FROM claim WHERE status_claim = 'diajukan'")->fetchColumn(),
    'diverifikasi' => $pdo->query("SELECT COUNT(*) FROM claim WHERE status_claim = 'diverifikasi'")->fetchColumn(),
    'ditolak'      => $pdo->query("SELECT COUNT(*) FROM claim WHERE status_claim = 'ditolak'")->fetchColumn(),
];

$status_map = [
    'masuk'        => 'diajukan',
    'diverifikasi' => 'diverifikasi',
    'ditolak'      => 'ditolak'
];
$status = $status_map[$tab] ?? 'diajukan';

// Query yang benar (ambil data pelapor & pengaju klaim)
$stmt = $pdo->prepare("
    SELECT 
        c.*, 
        l.nama_barang, l.lokasi, l.kategori, l.foto AS foto_laporan,
        pelapor.nama AS nama_pelapor,
        pelapor.nomor_kontak AS kontak_pelapor,
        pengaju.nama AS nama_pengaju,
        pengaju.nomor_kontak AS kontak_pengaju,
        civ.nomor_induk AS nim_pengaju
    FROM claim c
    JOIN laporan l ON c.id_laporan = l.id_laporan
    JOIN akun pelapor ON l.id_akun = pelapor.id_akun
    JOIN akun pengaju ON c.id_akun = pengaju.id_akun
    LEFT JOIN civitas civ ON pengaju.id_akun = civ.id_akun
    WHERE c.status_claim = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$status]);
$claimList = $stmt->fetchAll();

$GLOBALS['current_page'] = 'dashboard_claim';
$title = 'Manajemen Klaim - Lost & Found FILKOM';
include 'app/Views/layouts/sidebar.php';
?>

<link rel="stylesheet" href="/public/assets/css/dashboard_claim.css">

<div class="claim-wrapper">
    <div class="container-fluid">

        <!-- STATUS TABS -->
        <div class="status-tabs">
            <a href="?action=dashboard_claim&tab=masuk" class="status-tab masuk <?= $tab === 'masuk' ? 'active' : '' ?>">
                <span class="status-count"><?= $counts['diajukan'] ?></span>
                <span class="status-label">Masuk</span>
            </a>
            <a href="?action=dashboard_claim&tab=diverifikasi" class="status-tab diverifikasi <?= $tab === 'diverifikasi' ? 'active' : '' ?>">
                <span class="status-count"><?= $counts['diverifikasi'] ?></span>
                <span class="status-label">Disetujui</span>
            </a>
            <a href="?action=dashboard_claim&tab=ditolak" class="status-tab ditolak <?= $tab === 'ditolak' ? 'active' : '' ?>">
                <span class="status-count"><?= $counts['ditolak'] ?></span>
                <span class="status-label">Ditolak</span>
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
                    $fotoBukti   = $c['bukti_kepemilikan'] ? "/public/uploads/bukti_claim/" . basename($c['bukti_kepemilikan']) : null;

                    // WhatsApp ke PELAPOR (yang menemukan barang)
                    $waTextPelapor = "Halo {$c['nama_pelapor']}, ada yang mengklaim barang *{$c['nama_barang']}* yang Anda laporkan. Silakan hubungi pengaju klaim.";
                    $waLinkPelapor = $c['kontak_pelapor'] ? "https://wa.me/" . preg_replace('/\D/', '', $c['kontak_pelapor']) . "?text=" . urlencode($waTextPelapor) : '#';
                ?>

                    <!-- CARD -->
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

                    <!-- MODAL DETAIL -->
                    <div id="modal-<?= $c['id_claim'] ?>" class="claim-modal">
                        <div class="modal-content">
                            <span class="modal-close" onclick="closeModal(<?= $c['id_claim'] ?>)">&times;</span>
                            <div class="modal-body">

                                <!-- KIRI: Detail Barang + Profil Pelapor + Tombol WA -->
                                <div class="modal-left">
                                    <img src="<?= $fotoLaporan ?>" alt="Foto Barang" class="modal-image">
                                    <h5 class="mt-4"><?= htmlspecialchars($c['nama_barang']) ?></h5>
                                    <p><span class="material-symbols-outlined">location_on</span> <?= htmlspecialchars($c['lokasi']) ?></p>
                                    <p><span class="material-symbols-outlined">category</span> <?= ucfirst($c['kategori']) ?></p>

                                    <!-- PROFIL PELAPOR (yang menemukan barang) -->
                                    <div class="pelapor-section mt-4">
                                        <h6 class="text-success fw-bold">Ditemukan & Dilaporkan Oleh</h6>
                                        <p class="fw-bold"><?= htmlspecialchars($c['nama_pelapor']) ?></p>
                                        <p class="small text-muted">Kontak: <?= htmlspecialchars($c['kontak_pelapor'] ?? 'Tidak tersedia') ?></p>
                                    </div>

                                    <!-- Tombol Hubungi Pelapor -->
                                    <a href="<?= $waLinkPelapor ?>" target="_blank" class="btn-hubungi-pengambil mt-4">
                                        <span class="material-symbols-outlined">chat</span>
                                        Hubungi Pengambil Barang
                                    </a>
                                </div>

                                <!-- KANAN: Pengaju Klaim + Bukti + Deskripsi -->
                                <div class="modal-right">
                                    <div class="pengaju-section">
                                        <h6>Pengaju Klaim</h6>
                                        <p class="fw-bold text-primary"><?= htmlspecialchars($c['nama_pengaju']) ?></p>
                                        <p>NIM: <?= htmlspecialchars($c['nim_pengaju'] ?? 'Tidak ada NIM') ?></p>
                                        <p>Kontak: <?= htmlspecialchars($c['kontak_pengaju']) ?></p>
                                    </div>

                                    <h6 class="mt-4">Bukti Kepemilikan</h6>
                                    <?php if ($fotoBukti): ?>
                                        <img src="<?= $fotoBukti ?>" alt="Bukti" class="bukti-image">
                                    <?php else: ?>
                                        <p class="text-muted">Tidak ada bukti kepemilikan</p>
                                    <?php endif; ?>

                                    <h6 class="mt-4">Deskripsi Ciri-ciri</h6>
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

<!-- Script tetap sama -->
<script>
    function openClaimModal(id) {
        document.querySelectorAll('.claim-modal').forEach(m => m.style.display = 'none');
        const modal = document.getElementById('modal-' + id);
        if (modal) modal.style.display = 'flex';
    }

    function closeModal(id) {
        const modal = document.getElementById('modal-' + id);
        if (modal) modal.style.display = 'none';
    }
    window.addEventListener('click', e => {
        if (e.target.classList.contains('claim-modal')) e.target.style.display = 'none';
    });

    function verifikasiClaim(id_claim, id_laporan, status, event) {
        event.stopPropagation();
        if (!confirm(status === 'diverifikasi' ? 'Setujui klaim ini?' : 'Tolak klaim ini?')) return;

        const btn = event.target;
        const textAwal = btn.innerText;
        btn.innerText = 'Memproses...';
        btn.disabled = true;

        const form = new FormData();
        form.append('id_claim', id_claim);
        form.append('id_laporan', id_laporan);
        form.append('status', status);

        fetch('index.php?action=verifikasi_claim', {
                method: 'POST',
                body: form
            })
            .then(r => r.ok ? r.text() : Promise.reject('Error'))
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch {
                    return {
                        success: true
                    };
                }
            })
            .then(res => {
                if (res.success) location.reload();
            })
            .catch(() => alert('Kesalahan jaringan'))
            .finally(() => {
                btn.innerText = textAwal;
                btn.disabled = false;
            });
    }
</script>

</div>
</main>
</body>

</html>