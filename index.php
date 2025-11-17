<?php

/**
 * ==================================================================
 *  SISTEM INFORMASI PENEMUAN & PENGELOLAAN BARANG HILANG
 *  File: index.php
 *  Entry Point Aplikasi
 *  Author: Kelompok 1 RSI 2025
 *  ==================================================================
 */

date_default_timezone_set('Asia/Jakarta');
session_start();

// ==================================================================
//  REQUIRE & INISIALISASI CONTROLLERS
// ==================================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/ProfileController.php';
require_once __DIR__ . '/app/Controllers/LaporanController.php';
require_once __DIR__ . '/app/Controllers/ClaimController.php';
require_once __DIR__ . '/app/Controllers/NotifikasiController.php';

// Inisialisasi
$authController       = new AuthController();
$sessionManager       = $authController->getSessionManager();
$profileController    = new ProfileController($sessionManager);
$laporanController    = new LaporanController($sessionManager);
$claimController      = new ClaimController($sessionManager);
$notifikasiController = new NotifikasiController($sessionManager);

// ==================================================================
//  TENTUKAN ACTION
// ==================================================================
$action = $_GET['action'] ?? 'home';

// ==================================================================
//  CEK STATUS LOGIN & ROLE
// ==================================================================
$isLoggedIn = $sessionManager->get('userId') !== null;
$userRole   = $sessionManager->get('role');

// ==================================================================
//  REDIRECT LOGIC
// ==================================================================

if ($isLoggedIn) {
    if (in_array($action, ['login', 'register'])) {
        $redirect = ($userRole === 'satpam') ? 'dashboard' : 'home';
        header("Location: index.php?action=$redirect");
        exit;
    }
} else {
    $protectedActions = [
        'profile',
        'update_profile',
        'laporan',
        'laporan-form',
        'submit_laporan',
        'search',
        'laporan-detail',
        'search_civitas',
        'claim',
        'submit_claim',
        'claim_saya',
        'dashboard',
        'dashboard_claim',
        'verifikasi_claim',
        'laporanSatpam-form',
        'submit_laporan_ditemukan',
        'laporanSatpam-detail',
        'catat_pengambil',
        'mail',
        'mark_as_read'
    ];
    if (in_array($action, $protectedActions)) {
        header('Location: index.php?action=login');
        exit;
    }
}

// ==================================================================
//  ROUTING SWITCH
// ==================================================================

switch ($action) {

    // ==================================================================
    //  HALAMAN UMUM
    // ==================================================================
    case 'home':
        require_once 'app/Views/civitas/home.php';
        break;

    // ==================================================================
    //  AUTH: LOGIN
    // ==================================================================
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            $result = $authController->login($email, $password);

            if ($result['success']) {
                $authController->setRememberMeCookie($email, $sessionManager->get('userId'), $remember);
                $redirect = ($result['role'] === 'civitas') ? 'home' : 'dashboard';
                header("Location: index.php?action=$redirect");
                exit;
            } else {
                $message = $result['message'];
                $success = false;
            }
        }
        require_once 'app/Views/auth/login.php';
        break;

    // ==================================================================
    //  AUTH: REGISTER
    // ==================================================================
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email        = trim($_POST['email'] ?? '');
            $password     = $_POST['password'] ?? '';
            $nama         = trim($_POST['nama'] ?? '');
            $nomor_kontak = trim($_POST['nomor_kontak'] ?? '');
            $role         = $_POST['role'] ?? '';
            $nomor_induk  = trim($_POST['nomor_induk'] ?? '');

            if (empty($email) || empty($password) || empty($nama) || empty($nomor_kontak) || empty($role)) {
                $success = false;
                $message = 'Semua field wajib diisi';
                require_once 'app/Views/auth/register.php';
                break;
            }

            if ($role === 'civitas') {
                if (!preg_match('/@(student\.)?ub\.ac\.id$/i', $email)) {
                    $success = false;
                    $message = 'Civitas harus menggunakan email UB';
                    require_once 'app/Views/auth/register.php';
                    break;
                }
                if (empty($nomor_induk)) {
                    $success = false;
                    $message = 'NIM/NIP wajib diisi';
                    require_once 'app/Views/auth/register.php';
                    break;
                }
            }

            if ($role === 'satpam' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $success = false;
                $message = 'Format email tidak valid';
                require_once 'app/Views/auth/register.php';
                break;
            }

            $stmt = getDB()->prepare("SELECT id_akun FROM akun WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $success = false;
                $message = 'Email sudah terdaftar';
                require_once 'app/Views/auth/register.php';
                break;
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);
            try {
                getDB()->beginTransaction();
                $stmt = getDB()->prepare("INSERT INTO akun (email, password, nama, nomor_kontak, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$email, $hashed, $nama, $nomor_kontak, $role]);
                $id_akun = getDB()->lastInsertId();

                if ($role === 'civitas') {
                    $stmt = getDB()->prepare("INSERT INTO civitas (id_akun, nomor_induk) VALUES (?, ?)");
                    $stmt->execute([$id_akun, $nomor_induk]);
                } elseif ($role === 'satpam') {
                    $stmt = getDB()->prepare("INSERT INTO satpam (id_akun) VALUES (?)");
                    $stmt->execute([$id_akun]);
                }

                getDB()->commit();
                header('Location: index.php?action=login&msg=register_success');
                exit;
            } catch (Exception $e) {
                getDB()->rollBack();
                $success = false;
                $message = 'Gagal membuat akun: ' . $e->getMessage();
                require_once 'app/Views/auth/register.php';
            }
            break;
        }
        require_once 'app/Views/auth/register.php';
        break;

    // ==================================================================
    //  PROFILE
    // ==================================================================
    case 'profile':
        $result = $profileController->showProfile();
        $profil = $result['success'] ? $result['profil'] : [];
        require_once 'app/Views/civitas/profile.php';
        break;

    case 'update_profile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama        = trim($_POST['nama'] ?? '');
            $nomorKontak = trim($_POST['nomorKontak'] ?? '');
            $result      = $profileController->updateProfile($nama, $nomorKontak);
            $profil      = $profileController->showProfile()['profil'] ?? [];
            $success     = $result['success'];
            $message     = $result['message'];
            require_once 'app/Views/civitas/profile.php';
        } else {
            header('Location: index.php?action=profile');
            exit;
        }
        break;

    // ==================================================================
    //  LAPORAN: CIVITAS
    // ==================================================================
    case 'laporan':
        $laporanController->showLaporanPage();
        break;

    case 'laporan-form':
        require_once 'app/Views/civitas/laporan-form.php';
        break;

    case 'submit_laporan':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $namaBarang     = trim($_POST['nama_barang'] ?? '');
            $deskripsiFisik = trim($_POST['deskripsi_fisik'] ?? '');
            $kategori       = $_POST['kategori'] ?? '';
            $lokasi         = $_POST['lokasi'] ?? '';
            $waktu          = $_POST['waktu'] ?? '';
            $file           = $_FILES['foto'] ?? null;

            $result = $laporanController->submitLaporanHilang($namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu, $file);
            $success = $result['success'];
            $message = $result['message'];
            require_once 'app/Views/civitas/laporan-form.php';
        }
        break;

    case 'search':
        require_once 'app/Views/civitas/search.php';
        break;

    case 'laporan-detail':
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            require_once 'app/Views/error.php';
            break;
        }
        require_once 'app/Views/civitas/laporan-detail.php';
        break;

    case 'search_civitas':
        $laporanController->searchCivitas();
        break;

    // ==================================================================
    //  CLAIM: CIVITAS
    // ==================================================================
    case 'claim':
        $claimController->showForm();
        break;

    case 'submit_claim':
        $claimController->submitClaim();
        break;

    case 'claim_saya':
        $claimController->showMyClaims();
        break;

    // ==================================================================
    //  CLAIM: SATPAM
    // ==================================================================
    case 'dashboard_claim':
        if ($userRole !== 'satpam') {
            header('Location: index.php?action=home');
            exit;
        }
        $GLOBALS['sessionManager'] = $sessionManager;
        $GLOBALS['current_page'] = 'dashboard_claim';
        $claimController->showDashboard();
        break;

    case 'verifikasi_claim':
        $claimController->verifikasiClaim();
        break;

    // ==================================================================
    //  DASHBOARD & LAPORAN: SATPAM
    // ==================================================================
    case 'dashboard':
        if ($userRole !== 'satpam') {
            header('Location: index.php?action=home');
            exit;
        }
        $GLOBALS['sessionManager'] = $sessionManager;
        $GLOBALS['current_page'] = 'dashboard';
        require_once 'app/Views/admin/dashboard.php';
        break;

    case 'laporanSatpam-form':
        if ($sessionManager->get('role') !== 'satpam') {
            header('Location: index.php?action=login');
            exit;
        }
        require_once 'app/Views/admin/laporanSatpam-form.php';
        break;

    case 'submit_laporan_ditemukan':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sessionManager->get('role') === 'satpam') {
            $namaBarang     = trim($_POST['nama_barang'] ?? '');
            $deskripsiFisik = trim($_POST['deskripsi_fisik'] ?? '');
            $kategori       = $_POST['kategori'] ?? '';
            $lokasi         = $_POST['lokasi'] ?? '';
            $waktu          = $_POST['waktu'] ?? '';

            $result = $laporanController->submitLaporanDitemukan($namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu);
            $msg = $result['success'] ? 'success' : 'error';
            header("Location: index.php?action=laporanSatpam-form&msg=$msg");
            exit;
        }
        header('Location: index.php?action=laporanSatpam-form');
        break;

    case 'laporanSatpam-detail':
        if ($userRole !== 'satpam') {
            header('Location: index.php?action=login');
            exit;
        }
        require_once 'app/Views/admin/laporanSatpam-detail.php';
        break;

    case 'catat_pengambil':
        if ($userRole !== 'satpam' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=dashboard');
            exit;
        }

        $id_laporan     = (int)($_POST['id_laporan'] ?? 0);
        $nim_pengambil  = trim($_POST['nim_pengambil'] ?? '');
        $foto_bukti     = $_FILES['foto_bukti'] ?? null;
        $waktu_diambil  = date('Y-m-d H:i:s');

        if ($id_laporan <= 0 || empty($nim_pengambil) || !$foto_bukti || $foto_bukti['error'] !== UPLOAD_ERR_OK) {
            $error = "NIM, foto bukti, dan ID laporan wajib diisi.";
            header("Location: index.php?action=laporanSatpam-detail&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        $stmt = getDB()->prepare("SELECT a.nama, c.nomor_induk FROM akun a JOIN civitas c ON a.id_akun = c.id_akun WHERE c.nomor_induk = ? AND a.role = 'civitas'");
        $stmt->execute([$nim_pengambil]);
        $civitas = $stmt->fetch();
        if (!$civitas) {
            $error = "NIM tidak ditemukan di sistem civitas.";
            header("Location: index.php?action=laporanSatpam-detail&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        $ext = strtolower(pathinfo($foto_bukti['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) {
            $error = "Format foto tidak didukung. Gunakan JPG atau PNG.";
            header("Location: index.php?action=laporanSatpam-detail&id=$id_laporan&error=" . urlencode($error));
            exit;
        }
        if ($foto_bukti['size'] > 3 * 1024 * 1024) {
            $error = "Ukuran foto maksimal 3MB.";
            header("Location: index.php?action=laporanSatpam-detail&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        $safeNim   = preg_replace('/[^0-9]/', '', $nim_pengambil);
        $timestamp = time();
        $newName   = "nim_{$safeNim}_laporan_{$id_laporan}_img_{$timestamp}.{$ext}";
        $uploadDir = __DIR__ . '/public/uploads/bukti/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $destination = $uploadDir . $newName;

        if (!move_uploaded_file($foto_bukti['tmp_name'], $destination)) {
            $error = "Gagal menyimpan foto bukti.";
            header("Location: index.php?action=laporanSatpam-detail&id=$id_laporan&error=" . urlencode($error));
            exit;
        }
        $fotoPath = "uploads/bukti/{$newName}";

        $stmt = getDB()->prepare("
            UPDATE laporan 
            SET status = 'sudah_diambil', nim_pengambil = ?, foto_bukti = ?, waktu_diambil = ?
            WHERE id_laporan = ? AND status != 'sudah_diambil'
        ");
        $stmt->execute([$nim_pengambil, $fotoPath, $waktu_diambil, $id_laporan]);

        if ($stmt->rowCount() === 0) {
            $error = "Barang sudah pernah dicatat diambil.";
            header("Location: index.php?action=laporanSatpam-detail&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        header("Location: index.php?action=laporanSatpam-detail&id=$id_laporan&success=1");
        exit;
        break;

    // ==================================================================
    //  NOTIFIKASI: SATPAM
    // ==================================================================
    case 'mail':
        $GLOBALS['sessionManager'] = $sessionManager;
        $GLOBALS['current_page'] = 'mail';
        $notifikasiController->index();
        break;

    case 'mark_as_read':
        $notifikasiController->markAsRead();
        break;

    // ==================================================================
    //  LOGOUT
    // ==================================================================
    case 'logout':
        $authController->logout();
        header('Location: index.php?action=home');
        exit;

        // ==================================================================
        //  DEFAULT
        // ==================================================================
    default:
        header('Location: index.php?action=home');
        exit;
}
