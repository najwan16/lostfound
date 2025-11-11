<?php
session_start();

// --- REQUIRE SEMUA YANG DIPERLUKAN ---
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/LaporanController.php';
// --- AKHIR ---

// Inisialisasi
$authController = new AuthController();
$sessionManager = $authController->getSessionManager();
$laporanController = new LaporanController($sessionManager);

// Ambil data user
$result = $laporanController->getLaporanUser();
$laporanList = $result['success'] ? $result['laporan'] : [];

// Filter
$filter = $_GET['filter'] ?? 'semua';
$validFilters = ['semua', 'belum_ditemukan', 'sudah_diambil'];
if (!in_array($filter, $validFilters)) $filter = 'semua';

// Include widget
ob_start();
include __DIR__ . '/../views/widgets/laporan_widget.php';
echo ob_get_clean();