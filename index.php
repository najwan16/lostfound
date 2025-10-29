<?php
session_start();
require_once 'config/db.php'; // Pastikan ini ada dan berisi getDB()
require_once 'controllers/AuthController.php';
require_once 'controllers/ProfileController.php';
require_once 'controllers/LaporanController.php';

// Inisialisasi controller
$authController = new AuthController();
$sessionManager = $authController->getSessionManager();
$profileController = new ProfileController($sessionManager);
$laporanController = new LaporanController($sessionManager);

// Tentukan action default: home
$action = $_GET['action'] ?? 'home';

// Cek apakah user sudah login
$isLoggedIn = $sessionManager->get('userId') !== null;
$userRole = $sessionManager->get('role');

// === LOGIKA REDIRECT BERDASARKAN STATUS LOGIN & ROLE ===
if ($isLoggedIn) {
    // Jika sudah login, tapi buka login/register → redirect ke role masing-masing
    if (in_array($action, ['login', 'register'])) {
        if ($userRole === 'satpam') {
            header('Location: index.php?action=dashboard');
        } else {
            header('Location: index.php?action=home');
        }
        exit;
    }
} else {
    // Jika belum login, blokir akses ke halaman terproteksi
    $protectedActions = ['profile', 'update_profile', 'laporan_form', 'submit_laporan', 'search_page', 'dashboard'];
    if (in_array($action, $protectedActions)) {
        header('Location: index.php?action=login');
        exit;
    }
}

switch ($action) {

    // ==================================================================
    // HALAMAN PERTAMA: HOME (TANPA LOGIN)
    // ==================================================================
    case 'home':
        // Tampilkan home untuk semua (belum login bisa lihat, sudah login juga)
        include 'views/home_page.php';
        break;

    // ==================================================================
    // LOGIN
    // ==================================================================
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            $result = $authController->login($email, $password);

            if ($result['success']) {
                $authController->setRememberMeCookie($email, $sessionManager->get('userId'), $remember);

                // Redirect berdasarkan role
                if ($result['role'] === 'civitas') {
                    header('Location: index.php?action=home');
                } elseif ($result['role'] === 'satpam') {
                    header('Location: index.php?action=dashboard');
                }
                exit;
            } else {
                $message = $result['message'];
                $success = false;
            }
        }
        include 'views/login.php';
        break;

    // ==================================================================
    // REGISTER
    // ==================================================================
    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $nama = trim($_POST['nama'] ?? '');
            $nomor_kontak = trim($_POST['nomor_kontak'] ?? '');
            $role = $_POST['role'] ?? '';
            $nomor_induk = trim($_POST['nomor_induk'] ?? '');

            // === VALIDASI UMUM ===
            if (empty($email) || empty($password) || empty($nama) || empty($nomor_kontak) || empty($role)) {
                $success = false;
                $message = 'Semua field wajib diisi';
                include 'views/register.php';
                break;
            }

            // === VALIDASI KHUSUS CIVITAS ===
            if ($role === 'civitas') {
                if (!preg_match('/@(student\.)?ub\.ac\.id$/i', $email)) {
                    $success = false;
                    $message = 'Civitas harus menggunakan email UB (@ub.ac.id atau @student.ub.ac.id)';
                    include 'views/register.php';
                    break;
                }
                if (empty($nomor_induk)) {
                    $success = false;
                    $message = 'NIM/NIP wajib diisi untuk Civitas';
                    include 'views/register.php';
                    break;
                }
            }

            // === VALIDASI KHUSUS SATPAM ===
            if ($role === 'satpam') {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $success = false;
                    $message = 'Format email tidak valid';
                    include 'views/register.php';
                    break;
                }
            }

            // === CEK EMAIL SUDAH TERDAFTAR ===
            $stmt = getDB()->prepare("SELECT id_akun FROM akun WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $success = false;
                $message = 'Email sudah terdaftar';
                include 'views/register.php';
                break;
            }

            // === SIMPAN KE DATABASE ===
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            try {
                getDB()->beginTransaction();

                $stmt = getDB()->prepare("
                    INSERT INTO akun (email, password, nama, nomor_kontak, role)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$email, $hashed, $nama, $nomor_kontak, $role]);
                $id_akun = getDB()->lastInsertId();

                if ($role === 'civitas') {
                    $stmt = getDB()->prepare("INSERT INTO civitas (id_akun, nomor_induk) VALUES (?, ?)");
                    $stmt->execute([$id_akun, $nomor_induk]);
                }

                getDB()->commit();
                $success = true;
                $message = 'Akun berhasil dibuat! Silakan login.';
                header('Location: index.php?action=login&msg=register_success');
                exit;
            } catch (Exception $e) {
                getDB()->rollBack();
                $success = false;
                $message = 'Gagal membuat akun: ' . $e->getMessage();
            }
        }
        include 'views/register.php';
        break;

    // ==================================================================
    // PROFILE
    // ==================================================================
    case 'profile':
        $result = $profileController->showProfile();
        if ($result['success']) {
            $profil = $result['profil'];
        } else {
            $message = $result['message'];
            $success = false;
        }
        include 'views/profile_page.php';
        break;

    case 'update_profile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nama = $_POST['nama'] ?? '';
            $nomorKontak = $_POST['nomorKontak'] ?? '';
            $result = $profileController->updateProfile($nama, $nomorKontak);
            $success = $result['success'];
            $message = $result['message'];
            $profil = $profileController->showProfile()['profil'] ?? [];
            include 'views/profile_page.php';
        } else {
            header('Location: index.php?action=profile');
            exit;
        }
        break;

    // ==================================================================
    // LAPORAN
    // ==================================================================
    case 'laporan_form':
        $nim = $sessionManager->get('nim');
        include 'views/laporan_form.php';
        break;

    case 'submit_laporan':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $namaBarang = trim($_POST['nama_barang'] ?? '');
            $deskripsiFisik = trim($_POST['deskripsi_fisik'] ?? '');
            $kategori = trim($_POST['kategori'] ?? '');
            $lokasi = trim($_POST['lokasi'] ?? '');
            $waktu = trim($_POST['waktu'] ?? '');

            if (empty($namaBarang) || empty($deskripsiFisik) || empty($kategori) || empty($lokasi) || empty($waktu)) {
                $success = false;
                $message = 'Semua field wajib diisi';
                include 'views/laporan_form.php';
                break;
            }

            $result = $laporanController->submitLaporanHilang($namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu);
            $success = $result['success'];
            $message = $result['message'];
            include 'views/laporan_form.php';
        } else {
            header('Location: index.php?action=laporan_form');
            exit;
        }
        break;

    case 'search_page':
        $result = $laporanController->getRiwayatLaporan();
        $riwayat = $result['riwayat'] ?? [];
        $message = $result['message'] ?? '';
        $success = $result['success'] ?? false;
        include 'views/search_page.php';
        break;

    // ==================================================================
    // DASHBOARD SATPAM
    // ==================================================================
    case 'dashboard':
        if ($userRole !== 'satpam') {
            header('Location: index.php?action=home');
            exit;
        }
        include 'views/admin/dashboard.php';
        break;

    // ==================================================================
    // LOGOUT (opsional, tambahkan link di view)
    // ==================================================================
    case 'logout':
        $authController->logout();
        header('Location: index.php?action=home');
        exit;

    // ==================================================================
    // DEFAULT: ke home
    // ==================================================================
    default:
        header('Location: index.php?action=home');
        exit;
}