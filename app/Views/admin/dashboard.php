<?php
// app/Views/satpam/dashboard.php

header("Cache-Control: no-store, no-cache, must-revalidate,max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once dirname(__DIR__, 2) . '../../config/db.php';
require_once dirname(__DIR__, 2) . '../../app/Controllers/AuthController.php';

$auth = new AuthController();
$sessionManager = $auth->getSessionManager();

if ($sessionManager->get('role') !== 'satpam') {
    header('Location: index.php?action=login');
    exit;
}

$pdo = getDB();

// FILTER & PAGINATION
$filter_hilang = $_GET['filter_hilang'] ?? 'semua';
$filter_ditemukan = $_GET['filter_ditemukan'] ?? 'semua';

$perPage = (int)($_GET['show'] ?? 10);
if ($perPage <= 0) $perPage = 10;

$page_hilang = max(1, (int)($_GET['page_hilang'] ?? 1));
$page_ditemukan = max(1, (int)($_GET['page_ditemukan'] ?? 1));

$offset_hilang = ($page_hilang - 1) * $perPage;     // <-- DIPERBAIKI: HAPUS MINUS GANDA
$offset_ditemukan = ($page_ditemukan - 1) * $perPage;

// DATA BARANG HILANG
$where = "WHERE l.tipe_laporan = 'hilang'";
$params = [];
if ($filter_hilang !== 'semua') {
    $where .= " AND l.status = ?";
    $params[] = $filter_hilang;
}
$sql = "SELECT SQL_CALC_FOUND_ROWS l.id_laporan, l.nama_barang, l.kategori, l.lokasi, l.waktu, l.status,
        a.nama AS nama_pembuat, c.nomor_induk
        FROM laporan l
        JOIN akun a ON l.id_akun = a.id_akun
        LEFT JOIN civitas c ON a.id_akun = c.id_akun
        $where
        ORDER BY l.created_at DESC
        LIMIT $offset_hilang, $perPage";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan_hilang = $stmt->fetchAll();

$total_hilang = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPages_hilang = ceil($total_hilang / $perPage);

// DATA BARANG DITEMUKAN
$where = "WHERE l.tipe_laporan = 'ditemukan'";
$params = [];
if ($filter_ditemukan !== 'semua') {
    $where .= " AND l.status = ?";
    $params[] = $filter_ditemukan;
}
$sql = "SELECT SQL_CALC_FOUND_ROWS l.id_laporan, l.nama_barang, l.kategori, l.lokasi, l.waktu, l.status,
        a.nama AS nama_pembuat, c.nomor_induk
        FROM laporan l
        JOIN akun a ON l.id_akun = a.id_akun
        LEFT JOIN civitas c ON a.id_akun = c.id_akun
        $where
        ORDER BY l.created_at DESC
        LIMIT $offset_ditemukan, $perPage";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan_ditemukan = $stmt->fetchAll();

$total_ditemukan = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPages_ditemukan = ceil($total_ditemukan / $perPage);

$GLOBALS['current_page'] = 'dashboard';
$title = 'Dashboard Satpam - Lost & Found FILKOM';
include 'app/Views/layouts/sidebar.php';
?>


<link rel="stylesheet" href="/public/assets/css/dashboard.css">

<div class="dashboard-container">

    <!-- TOMBOL BUAT LAPORAN -->
    <div class="lapor-button-section">
        <a href="index.php?action=laporanSatpam-form" class="btn-lapor-solid">
            <div class="lapor-icon-box">
                <span class="material-symbols-outlined">add</span>
            </div>
            <div class="lapor-title">Buat Laporan Penemuan</div>
        </a>
    </div>
    <br>
    <!-- BARANG HILANG -->
    <div class="section-card mb-5">
        <div class="section-header bg-warning">
            <h5 class="section-title">Barang Hilang</h5>
            <div class="filter-buttons">
                <a href="index.php?action=dashboard&filter_hilang=semua" class="filter-btn <?= $filter_hilang === 'semua' ? 'active' : '' ?>">Semua</a>
                <a href="index.php?action=dashboard&filter_hilang=belum_ditemukan" class="filter-btn <?= $filter_hilang === 'belum_ditemukan' ? 'active' : '' ?>">Belum Ditemukan</a>
                <a href="index.php?action=dashboard&filter_hilang=sudah_diambil" class="filter-btn <?= $filter_hilang === 'sudah_diambil' ? 'active' : '' ?>">Sudah Diambil</a>
            </div>
        </div>
        <div class="section-body">
            <?php if (empty($laporan_hilang)): ?>
                <p class="text-center text-muted py-5">Tidak ada laporan barang hilang.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Barang</th>
                                <th>Kategori</th>
                                <th>Lokasi</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Pembuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan_hilang as $i => $l): ?>
                                <tr class="table-row" data-href="index.php?action=laporanSatpam-detail&id=<?= $l['id_laporan'] ?>">
                                    <td><?= ($page_hilang - 1) * $perPage + $i + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($l['nama_barang']) ?></strong></td>
                                    <td><span class="badge-cat"><?= ucfirst($l['kategori']) ?></span></td>
                                    <td><?= htmlspecialchars($l['lokasi']) ?></td>
                                    <td class="small-text"><?= date('d M Y H:i', strtotime($l['waktu'])) ?></td>
                                    <td>
                                        <span class="status-badge <?= $l['status'] === 'belum_ditemukan' ? 'status-missing' : 'status-claimed' ?>">
                                            <?= $l['status'] === 'belum_ditemukan' ? 'Belum Ditemukan' : 'Sudah Diambil' ?>
                                        </span>
                                    </td>
                                    <td class="pembuat-info">
                                        <strong><?= htmlspecialchars($l['nama_pembuat']) ?></strong>
                                        <?php if ($l['nomor_induk']): ?>
                                            <div class="nim-text">NIM: <?= htmlspecialchars($l['nomor_induk']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION PERSIS GAMBAR -->
                <div class="table-footer">
                    <div class="show-entries">
                        <label>Show</label>
                        <select onchange="window.location='index.php?action=dashboard&show='+this.value+'&filter_hilang=<?= $filter_hilang ?>&filter_ditemukan=<?= $filter_ditemukan ?>'">
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                        <span>entries</span>
                    </div>

                    <div class="pagination">
                        <?php if ($page_hilang > 1): ?>
                            <a href="index.php?action=dashboard&filter_hilang=<?= $filter_hilang ?>&page_hilang=<?= $page_hilang - 1 ?>&show=<?= $perPage ?>" class="pagination-arrow">«</a>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $page_hilang - 2);
                        $endPage = min($totalPages_hilang, $page_hilang + 2);
                        for ($p = $startPage; $p <= $endPage; $p++): ?>
                            <?php if ($p == $page_hilang): ?>
                                <span class="pagination-number active"><?= $p ?></span>
                            <?php else: ?>
                                <a href="index.php?action=dashboard&filter_hilang=<?= $filter_hilang ?>&page_hilang=<?= $p ?>&show=<?= $perPage ?>" class="pagination-number"><?= $p ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page_hilang < $totalPages_hilang): ?>
                            <a href="index.php?action=dashboard&filter_hilang=<?= $filter_hilang ?>&page_hilang=<?= $page_hilang + 1 ?>&show=<?= $perPage ?>" class="pagination-arrow">»</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BARANG DITEMUKAN (SAMA DENGAN HILANG + PAGINATION SENDIRI) -->
    <div class="section-card">
        <div class="section-header bg-success">
            <h5 class="section-title">Barang Ditemukan</h5>
            <div class="filter-buttons">
                <a href="index.php?action=dashboard&filter_ditemukan=semua" class="filter-btn <?= $filter_ditemukan === 'semua' ? 'active' : '' ?>">Semua</a>
                <a href="index.php?action=dashboard&filter_ditemukan=menunggu_klaim" class="filter-btn <?= $filter_ditemukan === 'menunggu_klaim' ? 'active' : '' ?>">Menunggu Klaim</a>
                <a href="index.php?action=dashboard&filter_ditemukan=sudah_diambil" class="filter-btn <?= $filter_ditemukan === 'sudah_diambil' ? 'active' : '' ?>">Sudah Diambil</a>
            </div>
        </div>
        <div class="section-body">
            <?php if (empty($laporan_ditemukan)): ?>
                <p class="text-center text-muted py-5">Tidak ada laporan barang ditemukan.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Barang</th>
                                <th>Kategori</th>
                                <th>Lokasi</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <th>Pembuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan_ditemukan as $i => $l): ?>
                                <tr class="table-row" data-href="index.php?action=laporanSatpam-detail&id=<?= $l['id_laporan'] ?>">
                                    <td><?= ($page_ditemukan - 1) * $perPage + $i + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($l['nama_barang']) ?></strong></td>
                                    <td><span class="badge-cat"><?= ucfirst($l['kategori']) ?></span></td>
                                    <td><?= htmlspecialchars($l['lokasi']) ?></td>
                                    <td class="small-text"><?= date('d M Y H:i', strtotime($l['waktu'])) ?></td>
                                    <td>
                                        <span class="status-badge <?= $l['status'] === 'menunggu_klaim' ? 'status-waiting' : 'status-claimed' ?>">
                                            <?= $l['status'] === 'menunggu_klaim' ? 'Menunggu Klaim' : 'Sudah Diambil' ?>
                                        </span>
                                    </td>
                                    <td class="pembuat-info">
                                        <strong><?= htmlspecialchars($l['nama_pembuat']) ?></strong>
                                        <?php if ($l['nomor_induk']): ?>
                                            <div class="nim-text">NIM: <?= htmlspecialchars($l['nomor_induk']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION DITEMUKAN -->
                <div class="table-footer">
                    <div class="show-entries">
                        <label>Show</label>
                        <select onchange="window.location='index.php?action=dashboard&show='+this.value+'&filter_ditemukan=<?= $filter_ditemukan ?>'">
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                        <span>entries</span>
                    </div>

                    <div class="pagination">
                        <?php if ($page_ditemukan > 1): ?>
                            <a href="index.php?action=dashboard&filter_ditemukan=<?= $filter_ditemukan ?>&page_ditemukan=<?= $page_ditemukan - 1 ?>&show=<?= $perPage ?>" class="pagination-arrow">«</a>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $page_ditemukan - 2);
                        $endPage = min($totalPages_ditemukan, $page_ditemukan + 2);
                        for ($p = $startPage; $p <= $endPage; $p++): ?>
                            <?php if ($p == $page_ditemukan): ?>
                                <span class="pagination-number active"><?= $p ?></span>
                            <?php else: ?>
                                <a href="index.php?action=dashboard&filter_ditemukan=<?= $filter_ditemukan ?>&page_ditemukan=<?= $p ?>&show=<?= $perPage ?>" class="pagination-number"><?= $p ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page_ditemukan < $totalPages_ditemukan): ?>
                            <a href="index.php?action=dashboard&filter_ditemukan=<?= $filter_ditemukan ?>&page_ditemukan=<?= $page_ditemukan + 1 ?>&show=<?= $perPage ?>" class="pagination-arrow">»</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
    document.querySelectorAll('.table-row').forEach(row => {
        row.style.cursor = 'pointer';
        row.addEventListener('click', () => {
            window.location = row.dataset.href;
        });
    });
</script>

</div> <!-- end .page-container -->
</main>
</body>

</html>