<?php

$GLOBALS['current_page'] = 'mail';
$current_page = 'mail';
$title = 'Kotak Masuk - Lost & Found FILKOM';
include 'app/Views/layouts/sidebar.php';
?>

<div class="mail-wrapper">

    <!-- Hari Ini -->
    <section class="mb-5">
        <h2 class="fw-bold text-dark mb-4">Hari Ini</h2>
        <br>
        <?php if (empty($hariIni)): ?>
            <div class="text-center py-5 bg-white rounded shadow-sm border">
                <p class="text-muted mb-0">Tidak ada notifikasi hari ini.</p>
            </div>
        <?php else: ?>
            <div class="notif-list">
                <?php foreach ($hariIni as $n):
                    $unread = $n['dibaca'] == 0;
                    $detailUrl = "index.php?action=laporanSatpam-detail&id=" . $n['id_laporan'];
                ?>
                    <a href="<?= $detailUrl ?>" class="notif-link d-block">
                        <div class="notif-card <?= $unread ? 'unread' : 'read' ?>"
                            id="notif-<?= $n['id_pemberitahuan'] ?>"
                            onclick="markAsRead(<?= $n['id_pemberitahuan'] ?>, event, '<?= $detailUrl ?>')">

                            <!-- Kiri -->
                            <div class="notif-left">
                                <h6 class="<?= $unread ? 'fw-bold' : '' ?>">
                                    <?= htmlspecialchars($n['nama_barang']) ?>
                                    <?php if ($unread): ?>
                                        <span class="badge-new">Baru</span>
                                    <?php endif; ?>
                                </h6>
                                <div class="notif-meta">
                                    <span class="meta-item">
                                        <span class="material-symbols-outlined">location_on</span>
                                        <?= htmlspecialchars($n['lokasi']) ?>
                                    </span>
                                    <span class="meta-item">
                                        <span class="material-symbols-outlined">category</span>
                                        <?= ucfirst($n['kategori']) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Kanan -->
                            <div class="notif-right">
                                <div class="notif-time">
                                    <?= date('d M Y - H:i', strtotime($n['waktu_kirim'])) ?> WIB
                                </div>
                                <div class="notif-action">
                                    <span class="btn-detail-outline">Lihat Detail</span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <br><br>
    <!-- Minggu Ini -->
    <section>
        <h2 class="fw-bold text-dark mb-4">Minggu Ini</h2>
        <br>
        <?php if (empty($mingguIni)): ?>
            <div class="text-center py-5 bg-white rounded shadow-sm border">
                <p class="text-muted mb-0">Tidak ada notifikasi minggu ini.</p>
            </div>
        <?php else: ?>
            <div class="notif-list">
                <?php foreach ($mingguIni as $n):
                    $unread = $n['dibaca'] == 0;
                    $detailUrl = "index.php?action=laporanSatpam-detail&id=" . $n['id_laporan'];
                ?>
                    <a href="<?= $detailUrl ?>" class="notif-link d-block">
                        <div class="notif-card <?= $unread ? 'unread' : 'read' ?>"
                            id="notif-<?= $n['id_pemberitahuan'] ?>"
                            onclick="markAsRead(<?= $n['id_pemberitahuan'] ?>, event, '<?= $detailUrl ?>')">

                            <div class="notif-left">
                                <h6 class="<?= $unread ? 'fw-bold' : '' ?>">
                                    <?= htmlspecialchars($n['nama_barang']) ?>
                                    <?php if ($unread): ?>
                                        <span class="badge-new">Baru</span>
                                    <?php endif; ?>
                                </h6>
                                <div class="notif-meta">
                                    <span class="meta-item">
                                        <span class="material-symbols-outlined">location_on</span>
                                        <?= htmlspecialchars($n['lokasi']) ?>
                                    </span>
                                    <span class="meta-item">
                                        <span class="material-symbols-outlined">category</span>
                                        <?= ucfirst($n['kategori']) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="notif-right">
                                <div class="notif-time">
                                    <?= date('d M Y - H:i', strtotime($n['waktu_kirim'])) ?> WIB
                                </div>
                                <div class="notif-action">
                                    <span class="btn-detail-outline">Lihat Detail</span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

</div>

<script>
    function markAsRead(id, event, detailUrl) {
        // Jika klik tepat di tombol "Lihat Detail", langsung ke detail tanpa tandai dibaca
        if (event.target.closest('.btn-detail-outline')) {
            return; // biarkan link <a> jalan normal
        }

        // Jika klik di luar tombol → tandai dibaca dulu, lalu pindah ke detail
        event.preventDefault();

        fetch('index.php?action=mark_as_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'id_pemberitahuan=' + id
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    const card = document.getElementById('notif-' + id);
                    card.classList.remove('unread');
                    card.classList.add('read');
                    card.querySelector('.badge-new')?.remove();

                    // Update badge
                    const badge = document.querySelector('.badge.bg-danger');
                    if (badge) {
                        let count = parseInt(badge.textContent);
                        if (--count <= 0) badge.remove();
                        else badge.textContent = count + ' Belum Dibaca';
                    }

                    // Setelah berhasil tandai dibaca → langsung ke detail
                    window.location.href = detailUrl;
                }
            })
            .catch(() => {
                // Jika gagal, tetap pindah ke detail
                window.location.href = detailUrl;
            });
    }
</script>

</div> <!-- end .page-container -->
</main>
</body>

</html>