<?php
require_once 'controllers/AuthController.php';
require_once 'controllers/ProfileController.php';
require_once 'controllers/LaporanController.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$authController = new AuthController();
$profileController = new ProfileController($authController->getSessionManager());
$laporanController = new LaporanController($authController->getSessionManager());

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log("POST received: " . print_r($_POST, true));
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            $result = $authController->login($email, $password);
            error_log("Login result: " . print_r($result, true));
            $success = $result['success'];
            $message = $result['message'];
            if ($success) {
                $authController->setRememberMeCookie($email, $authController->getSessionManager()->get('userId'), $remember);
                header('Location: index.php?action=home');
                return;
            }
        }
        include 'views/login.php';
        break;
    case 'profile':
        if (!$authController->getSessionManager()->get('userId')) {
            header('Location: index.php?action=login');
            return;
        }
        $result = $profileController->showProfile();
        if ($result['success']) {
            $profil = $result['profil'];
            include 'views/profile.php';
        } else {
            $message = $result['message'];
            $success = false;
            include 'views/profile.php';
        }
        break;
    case 'update_profile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = $_POST['nama'] ?? '';
            $nomorKontak = $_POST['nomorKontak'] ?? '';
            $result = $profileController->updateProfile($nama, $nomorKontak);
            $success = $result['success'];
            $message = $result['message'];
            $profil = $profileController->showProfile()['profil'];
            include 'views/profile.php';
        } else {
            header('Location: index.php?action=profile');
            return;
        }
        break;
    case 'submit_laporan':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $namaBarang = $_POST['nama_barang'] ?? '';
            $deskripsiFisik = $_POST['deskripsi_fisik'] ?? '';
            $kategori = $_POST['kategori'] ?? '';
            $lokasi = $_POST['lokasi'] ?? '';
            $waktu = $_POST['waktu'] ?? '';
            $result = $laporanController->submitLaporanHilang($namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu);
            $success = $result['success'];
            $message = $result['message'];
            include 'views/laporan_form.php';
        } else {
            header('Location: index.php?action=laporan_form');
            return;
        }
        break;
    case 'laporan_form':
        if (!$authController->getSessionManager()->get('userId')) {
            header('Location: index.php?action=login');
            return;
        }
        $nim = $authController->getSessionManager()->get('nim'); // Ambil nim dari session
        include 'views/laporan_form.php';
        break;
    case 'riwayat_laporan':
        if (!$authController->getSessionManager()->get('userId')) {
            header('Location: index.php?action=login');
            return;
        }
        $result = $laporanController->getRiwayatLaporan();
        $success = $result['success'];
        $riwayat = $result['riwayat'] ?? [];
        $message = $result['message'] ?? '';
        include 'views/riwayat_laporan.php';
        break;
    case 'home':
        include 'views/home.php';
        break;
    default:
        header('Location: index.php?action=login');
        return;
}