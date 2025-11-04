<?php
require_once __DIR__ . '/../models/LaporanModel.php';

$filter = $_GET['filter'] ?? 'semua';
$validFilters = ['semua', 'belum_ditemukan', 'selesai'];
if (!in_array($filter, $validFilters)) $filter = 'semua';

$model = new \Models\LaporanModel();
$laporanList = $model->getAllLaporanHilang();

ob_start();
include __DIR__ . '/../views/widgets/laporan_widget.php';
echo ob_get_clean();
