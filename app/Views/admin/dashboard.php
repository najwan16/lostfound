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
$perPage = $perPage <= 0 ? 10 : $perPage;

$page_hilang = max(1, (int)($_GET['page_hilang'] ?? 1));
$page_ditemukan = max(1, (int)($_GET['page_ditemukan'] ?? 1));

$offset_hilang = ($page_hilang - 1) * $perPage;
$offset_ditemukan = ($page_ditemukan - 1) * $perPage;

// DATA BARANG HILANG (FIXED)
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
$laporan_hilang = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_hilang = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPages_hilang = ceil($total_hilang / $perPage);

// DATA BARANG DITEMUKAN (FIXED)
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
$laporan_ditemukan = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_ditemukan = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPages_ditemukan = ceil($total_ditemukan / $perPage);

$GLOBALS['current_page'] = 'dashboard';
$title = 'Dashboard Satpam - Lost & Found FILKOM';
include 'app/Views/layouts/sidebar.php';
?>

<link rel="stylesheet" href="/public/assets/css/dashboard.css">

<div class="dashboard-container">

    <div class="lapor-button-section">
        <a href="index.php?action=laporanSatpam-form" class="btn-lapor-solid">
            <div class="lapor-icon-box"><span class="material-symbols-outlined">add</span></div>
            <div class="lapor-title">Buat Laporan Penemuan</div>
        </a>
    </div>
    <br>

    <!-- BARANG HILANG -->
    <div class="section-card mb-5">
        <div class="section-header bg-warning">
            <h5 class="section-title">Barang Hilang</h5>
            <div class="filter-buttons">
                <a href="?action=dashboard&filter_hilang=semua" class="filter-btn <?= $filter_hilang === 'semua' ? 'active' : '' ?>">Semua</a>
                <a href="?action=dashboard&filter_hilang=belum_ditemukan" class="filter-btn <?= $filter_hilang === 'belum_ditemukan' ? 'active' : '' ?>">Belum Ditemukan</a>
                <a href="?action=dashboard&filter_hilang=sudah_diambil" class="filter-btn <?= $filter_hilang === 'sudah_diambil' ? 'active' : '' ?>">Sudah Diambil</a>
            </div>
        </div>
        <div class="section-body">

            <div class="search-container">
                <span class="material-symbols-outlined search-icon">search</span>
                <input type="text" id="searchHilang" class="search-box" placeholder="Cari nama barang...">
            </div>

            <?php if (empty($laporan_hilang)): ?>
                <p class="text-center text-muted py-5">Tidak ada laporan barang hilang.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table" id="tableHilang">
                        <thead>
                            <tr>
                                <!-- <th style="width:60px;">No.</th> -->
                                <th class="sortable" data-sort="barang">Barang</th>
                                <th class="sortable" data-sort="kategori">Kategori</th>
                                <th class="sortable" data-sort="lokasi">Lokasi</th>
                                <th class="sortable" data-sort="waktu">Waktu</th>
                                <th class="sortable" data-sort="status">Status</th>
                                <th class="sortable" data-sort="pembuat">Pembuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan_hilang as $i => $l): ?>
                                <!-- <?php $nomor = ($page_hilang - 1) * $perPage + $i + 1; ?> -->
                                <tr class="table-row" data-href="index.php?action=laporanSatpam-detail&id=<?= $l['id_laporan'] ?>">
                                    <!-- <td class="fixed-no" data-original-no="<?= $nomor ?>"><?= $nomor ?></td> -->
                                    <td data-barang="<?= strtolower(htmlspecialchars($l['nama_barang'])) ?>"><strong><?= htmlspecialchars($l['nama_barang']) ?></strong></td>
                                    <td data-kategori="<?= $l['kategori'] ?>"><span class="badge-cat"><?= ucfirst($l['kategori']) ?></span></td>
                                    <td data-lokasi="<?= htmlspecialchars($l['lokasi']) ?>"><?= htmlspecialchars($l['lokasi']) ?></td>
                                    <td data-waktu="<?= strtotime($l['waktu']) ?>"><?= date('d M Y H:i', strtotime($l['waktu'])) ?></td>
                                    <td data-status="<?= $l['status'] ?>">
                                        <span class="status-badge <?= $l['status'] === 'belum_ditemukan' ? 'status-missing' : 'status-claimed' ?>">
                                            <?= $l['status'] === 'belum_ditemukan' ? 'Belum Ditemukan' : 'Sudah Diambil' ?>
                                        </span>
                                    </td>
                                    <td data-pembuat="<?= htmlspecialchars($l['nama_pembuat']) . ' ' . ($l['nomor_induk'] ?? '') ?>">
                                        <strong><?= htmlspecialchars($l['nama_pembuat']) ?></strong>
                                        <?php if ($l['nomor_induk']): ?><div class="nim-text">NIM: <?= htmlspecialchars($l['nomor_induk']) ?></div><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination tetap sama -->
                <div class="table-footer">
                    <div class="show-entries">
                        <label>Show</label>
                        <select onchange="window.location='index.php?action=dashboard&show='+this.value+'&filter_hilang=<?= $filter_hilang ?>&filter_ditemukan=<?= $filter_ditemukan ?>&page_hilang=<?= $page_hilang ?>&page_ditemukan=<?= $page_ditemukan ?>'">
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                        <span>entries</span>
                    </div>
                    <div class="pagination">
                        <?php if ($page_hilang > 1): ?>
                            <a href="index.php?action=dashboard&filter_hilang=<?= $filter_hilang ?>&page_hilang=<?= $page_hilang - 1 ?>&show=<?= $perPage ?>&filter_ditemukan=<?= $filter_ditemukan ?>&page_ditemukan=<?= $page_ditemukan ?>" class="pagination-arrow">«</a>
                        <?php endif; ?>
                        <?php for ($p = max(1, $page_hilang - 2); $p <= min($totalPages_hilang, $page_hilang + 2); $p++): ?>
                            <a href="index.php?action=dashboard&filter_hilang=<?= $filter_hilang ?>&page_hilang=<?= $p ?>&show=<?= $perPage ?>&filter_ditemukan=<?= $filter_ditemukan ?>&page_ditemukan=<?= $page_ditemukan ?>" class="pagination-number <?= $p == $page_hilang ? 'active' : '' ?>"><?= $p ?></a>
                        <?php endfor; ?>
                        <?php if ($page_hilang < $totalPages_hilang): ?>
                            <a href="index.php?action=dashboard&filter_hilang=<?= $filter_hilang ?>&page_hilang=<?= $page_hilang + 1 ?>&show=<?= $perPage ?>&filter_ditemukan=<?= $filter_ditemukan ?>&page_ditemukan=<?= $page_ditemukan ?>" class="pagination-arrow">»</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- BARANG DITEMUKAN (sama persis, hanya ganti variabel) -->
    <div class="section-card">
        <div class="section-header bg-success">
            <h5 class="section-title">Barang Ditemukan</h5>
            <div class="filter-buttons">
                <a href="?action=dashboard&filter_ditemukan=semua" class="filter-btn <?= $filter_ditemukan === 'semua' ? 'active' : '' ?>">Semua</a>
                <a href="?action=dashboard&filter_ditemukan=menunggu_klaim" class="filter-btn <?= $filter_ditemukan === 'menunggu_klaim' ? 'active' : '' ?>">Menunggu Klaim</a>
                <a href="?action=dashboard&filter_ditemukan=sudah_diambil" class="filter-btn <?= $filter_ditemukan === 'sudah_diambil' ? 'active' : '' ?>">Sudah Diambil</a>
            </div>
        </div>
        <div class="section-body">

            <div class="search-container">
                <span class="material-symbols-outlined search-icon">search</span>
                <input type="text" id="searchDitemukan" class="search-box" placeholder="Cari nama barang...">
            </div>

            <?php if (empty($laporan_ditemukan)): ?>
                <p class="text-center text-muted py-5">Tidak ada laporan barang ditemukan.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table" id="tableDitemukan">
                        <thead>
                            <tr>
                                <th class="sortable" data-sort="barang">Barang</th>
                                <th class="sortable" data-sort="kategori">Kategori</th>
                                <th class="sortable" data-sort="lokasi">Lokasi</th>
                                <th class="sortable" data-sort="waktu">Waktu</th>
                                <th class="sortable" data-sort="status">Status</th>
                                <th class="sortable" data-sort="pembuat">Pembuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan_ditemukan as $i => $l): ?>
                                <?php $nomor = ($page_ditemukan - 1) * $perPage + $i + 1; ?>
                                <tr class="table-row" data-href="index.php?action=laporanSatpam-detail&id=<?= $l['id_laporan'] ?>">
                                    <td data-barang="<?= strtolower(htmlspecialchars($l['nama_barang'])) ?>"><strong><?= htmlspecialchars($l['nama_barang']) ?></strong></td>
                                    <td data-kategori="<?= $l['kategori'] ?>"><span class="badge-cat"><?= ucfirst($l['kategori']) ?></span></td>
                                    <td data-lokasi="<?= htmlspecialchars($l['lokasi']) ?>"><?= htmlspecialchars($l['lokasi']) ?></td>
                                    <td data-waktu="<?= strtotime($l['waktu']) ?>"><?= date('d M Y H:i', strtotime($l['waktu'])) ?></td>
                                    <td data-status="<?= $l['status'] ?>">
                                        <span class="status-badge <?= $l['status'] === 'menunggu_klaim' ? 'status-waiting' : 'status-claimed' ?>">
                                            <?= $l['status'] === 'menunggu_klaim' ? 'Menunggu Klaim' : 'Sudah Diambil' ?>
                                        </span>
                                    </td>
                                    <td data-pembuat="<?= htmlspecialchars($l['nama_pembuat']) . ' ' . ($l['nomor_induk'] ?? '') ?>">
                                        <strong><?= htmlspecialchars($l['nama_pembuat']) ?></strong>
                                        <?php if ($l['nomor_induk']): ?><div class="nim-text">NIM: <?= htmlspecialchars($l['nomor_induk']) ?></div><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="show-entries">
                        <label>Show</label>
                        <select onchange="window.location='index.php?action=dashboard&show='+this.value+'&filter_hilang=<?= $filter_hilang ?>&filter_ditemukan=<?= $filter_ditemukan ?>&page_hilang=<?= $page_hilang ?>&page_ditemukan=<?= $page_ditemukan ?>'">
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                        </select>
                        <span>entries</span>
                    </div>
                    <div class="pagination">
                        <?php if ($page_ditemukan > 1): ?>
                            <a href="index.php?action=dashboard&filter_ditemukan=<?= $filter_ditemukan ?>&page_ditemukan=<?= $page_ditemukan - 1 ?>&show=<?= $perPage ?>&filter_hilang=<?= $filter_hilang ?>&page_hilang=<?= $page_hilang ?>" class="pagination-arrow">«</a>
                        <?php endif; ?>
                        <?php for ($p = max(1, $page_ditemukan - 2); $p <= min($totalPages_ditemukan, $page_ditemukan + 2); $p++): ?>
                            <a href="index.php?action=dashboard&filter_ditemukan=<?= $filter_ditemukan ?>&page_ditemukan=<?= $p ?>&show=<?= $perPage ?>&filter_hilang=<?= $filter_hilang ?>&page_hilang=<?= $page_hilang ?>" class="pagination-number <?= $p == $page_ditemukan ? 'active' : '' ?>"><?= $p ?></a>
                        <?php endfor; ?>
                        <?php if ($page_ditemukan < $totalPages_ditemukan): ?>
                            <a href="index.php?action=dashboard&filter_ditemukan=<?= $filter_ditemukan ?>&page_ditemukan=<?= $page_ditemukan + 1 ?>&show=<?= $perPage ?>&filter_hilang=<?= $filter_hilang ?>&page_hilang=<?= $page_hilang ?>" class="pagination-arrow">»</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Klik row
    document.querySelectorAll('.table-row').forEach(r => {
        r.style.cursor = 'pointer';
        r.addEventListener('click', () => window.location = r.dataset.href);
    });

    // Search hanya nama barang
    ['searchHilang', 'searchDitemukan'].forEach(id => {
        document.getElementById(id)?.addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            const tableId = id === 'searchHilang' ? '#tableHilang' : '#tableDitemukan';
            document.querySelectorAll(`${tableId} tbody tr`).forEach(row => {
                const nama = row.querySelector('td[data-barang]')?.getAttribute('data-barang') || '';
                row.style.display = nama.includes(term) ? '' : 'none';
            });
        });
    });

    // === SORTING + NOMOR TETAP URUT ===
    document.querySelectorAll('th.sortable').forEach(th => {
        th.style.cursor = 'pointer';
        th.innerHTML += ' <span style="font-size:0.8em;opacity:0.6;">↕</span>';

        th.addEventListener('click', () => {
            const table = th.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const idx = Array.from(th.parentNode.children).indexOf(th);
            const key = th.getAttribute('data-sort');

            // Toggle asc/desc
            const asc = !th.classList.contains('asc');
            document.querySelectorAll('th.sortable').forEach(h => h.classList.remove('asc', 'desc'));
            th.classList.toggle('asc', asc);
            th.classList.toggle('desc', !asc);

            // Sort rows
            rows.sort((a, b) => {
                let aVal = a.cells[idx].dataset[key] || a.cells[idx].textContent.trim();
                let bVal = b.cells[idx].dataset[key] || b.cells[idx].textContent.trim();

                if (key === 'waktu') {
                    aVal = parseInt(a.cells[idx].dataset.waktu || 0);
                    bVal = parseInt(b.cells[idx].dataset.waktu || 0);
                } else {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                return (aVal > bVal ? 1 : -1) * (asc ? 1 : -1);
            });

            // Kosongkan tbody
            rows.forEach(r => tbody.appendChild(r));

            // *** PERBAIKAN NOMOR URUT ***
            tbody.querySelectorAll('tr').forEach((row, i) => {
                const noCell = row.querySelector('.fixed-no');
                const originalNo = noCell.getAttribute('data-original-no');
                noCell.textContent = originalNo; // Kembalikan nomor asli sesuai pagination
            });
        });
    });
</script>

</div>
</main>
</body>

</html>