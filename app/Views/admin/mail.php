<?php
// app/Views/admin/mail.php
// VARIABEL SUDAH DIKIRIM DARI CONTROLLER: $hariIni, $mingguIni, $totalUnread, $page_title, $current_page
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link href="public/assets/css/mail.css" rel="stylesheet">
    <link href="public/assets/css/admin.css" rel="stylesheet">
</head>

<body>

    <div class="d-flex">
        <?php include realpath(dirname(__DIR__) . '/layouts/sidebar.php'); ?>

        <main class="main-content">
            <div class="container-fluid">

                <!-- Header + Badge -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">Kotak Masuk</h2>
                    <?php if ($totalUnread > 0): ?>
                        <span class="badge bg-danger fs-6"><?= $totalUnread ?> Baru</span>
                    <?php endif; ?>
                </div>

                <!-- Hari Ini -->
                <section class="mb-5">
                    <h2 class="section-title">Hari Ini</h2>
                    <?php if (empty($hariIni)): ?>
                        <div class="text-center py-5">
                            <p class="text-muted fs-5">Tidak ada notifikasi hari ini.</p>
                        </div>
                    <?php else: ?>
                        <div class="laporan-list">
                            <?php foreach ($hariIni as $notifikasi): ?>
                                <?= renderNotifCard($notifikasi) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Minggu Ini -->
                <section>
                    <h2 class="section-title">Minggu Ini</h2>
                    <?php if (empty($mingguIni)): ?>
                        <div class="text-center py-5">
                            <p class="text-muted fs-5">Tidak ada notifikasi minggu ini.</p>
                        </div>
                    <?php else: ?>
                        <div class="laporan-list">
                            <?php foreach ($mingguIni as $notifikasi): ?>
                                <?= renderNotifCard($notifikasi) ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            </div>
        </main>
    </div>

    <script>
        function markAsRead(idPemberitahuan) {
            fetch('index.php?action=mark_as_read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'id_pemberitahuan=' + idPemberitahuan
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const card = document.getElementById('notif-' + idPemberitahuan);
                        card.classList.remove('unread');
                        updateBadge();
                    }
                });
        }

        function updateBadge() {
            const badge = document.querySelector('.badge.bg-danger');
            if (badge) {
                let count = parseInt(badge.textContent) || 0;
                if (count > 1) badge.textContent = count - 1;
                else badge.remove();
            }
        }
    </script>
</body>

</html>

<?php
function renderNotifCard(array $notifikasi): string
{
    $unreadClass = $notifikasi['dibaca'] == 0 ? 'unread' : '';
    $badge = $notifikasi['dibaca'] == 0 ? '<span class="badge bg-primary">Baru</span>' : '';
    $waktu = date('d M Y - H:i', strtotime($notifikasi['waktu_kirim'])) . ' WIB';

    return "
    <div class=\"laporan-card $unreadClass\" id=\"notif-{$notifikasi['id_pemberitahuan']}\" onclick=\"markAsRead({$notifikasi['id_pemberitahuan']})\">
        <div class=\"laporan-info\">
            <h6>" . htmlspecialchars($notifikasi['nama_barang']) . " $badge</h6>
            <div class=\"laporan-meta\">
                <span class=\"meta-item\"><span class=\"material-symbols-outlined\">location_on</span> " . htmlspecialchars($notifikasi['lokasi']) . "</span>
                <span class=\"meta-item\"><span class=\"material-symbols-outlined\">category</span> " . ucfirst($notifikasi['kategori']) . "</span>
            </div>
        </div>
        <div class=\"laporan-time\">$waktu</div>
        <div class=\"laporan-action\">
            <a href=\"index.php?action=laporanSatpam-detail&id={$notifikasi['id_laporan']}\" class=\"btn-cocok\">Lihat Detail</a>
        </div>
    </div>";
}
?>