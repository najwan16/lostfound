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
                } elseif ($role === 'satpam') {
                    $stmt = getDB()->prepare("INSERT INTO satpam (id_akun) VALUES (?)");
                    $stmt->execute([$id_akun]);
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
    case 'laporan':
        $nim = $sessionManager->get('nim');
        include 'views/laporan_page.php';
        break;

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
            $file = $_FILES['foto'] ?? null;

            $result = $laporanController->submitLaporanHilang(
                $namaBarang,
                $deskripsiFisik,
                $kategori,
                $lokasi,
                $waktu,
                $file
            );

            $success = $result['success'];
            $message = $result['message'];
            include 'views/laporan_form.php';
        }
        break;

    case 'search_page':
        $result = $laporanController->getRiwayatLaporan();
        $riwayat = $result['riwayat'] ?? [];
        $message = $result['message'] ?? '';
        $success = $result['success'] ?? false;
        include 'views/search_page.php';
        break;

    case 'detail_laporan':
        $id = $_GET['id'] ?? 0;
        if (!$id || !is_numeric($id)) {
            include 'views/error.php';
            break;
        }
        include 'views/detail_laporan.php';
        break;

    case 'claim':
        $id = $_GET['id'] ?? 0;
        if (!$id || !is_numeric($id)) {
            include 'views/error.php';
            break;
        }
        // Kirim ID ke claim.php
        $laporanId = $id;
        include 'views/claim.php';
        break;

    // ==================================================================
    // KLAIM
    // ==============================================================
    case 'submit_klaim':
        // === 1. CEK SESSION & ROLE ===
        if (!isset($_SESSION['userId']) || $_SESSION['role'] !== 'civitas') {
            header('Location: index.php?action=home');
            exit;
        }

        $userId = $_SESSION['userId']; // AMAN: PASTIKAN ADA
        $id_laporan = (int)($_POST['id_laporan'] ?? 0);
        $deskripsi_ciri = trim($_POST['deskripsi_ciri'] ?? '');
        $bukti_file = $_FILES['bukti_kepemilikan'] ?? null;

        // === 2. VALIDASI INPUT ===
        if ($id_laporan <= 0 || empty($deskripsi_ciri) || !$bukti_file || $bukti_file['error'] !== UPLOAD_ERR_OK) {
            $error = "Semua field wajib diisi dan file harus valid.";
            header("Location: index.php?action=claim&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        // === 3. CEK LAPORAN VALID & SUDAH DIAMBIL ===
        $stmt = getDB()->prepare("SELECT 1 FROM laporan WHERE id_laporan = ? AND status = 'sudah_diambil'");
        $stmt->execute([$id_laporan]);
        if (!$stmt->fetch()) {
            $error = "Laporan tidak valid atau belum diambil.";
            header("Location: index.php?action=claim&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        // === 4. CEK SUDAH KLAIM ===
        $stmt = getDB()->prepare("SELECT 1 FROM klaim WHERE id_laporan = ? AND id_akun = ?");
        $stmt->execute([$id_laporan, $userId]);
        if ($stmt->fetch()) {
            $error = "Anda sudah mengajukan klaim.";
            header("Location: index.php?action=claim&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        // === 5. UPLOAD BUKTI ===
        $ext = strtolower(pathinfo($bukti_file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed) || $bukti_file['size'] > 3 * 1024 * 1024) {
            $error = "File harus JPG/PNG dan <3MB.";
            header("Location: index.php?action=claim&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        $uploadDir = __DIR__ . '/uploads/bukti_klaim/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $newName = "bukti_klaim_{$id_laporan}_{$userId}_" . time() . ".{$ext}";
        $dest = $uploadDir . $newName;

        if (!move_uploaded_file($bukti_file['tmp_name'], $dest)) {
            $error = "Gagal upload bukti.";
            header("Location: index.php?action=claim&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        $buktiPath = "uploads/bukti_klaim/{$newName}";

        // === 6. SIMPAN KLAIM KE DB ===
        $stmt = getDB()->prepare("
    INSERT INTO klaim 
    (id_laporan, id_akun, bukti_kepemilikan, deskripsi_ciri, status_klaim, created_at, updated_at)
    VALUES (?, ?, ?, ?, 'diajukan', NOW(), NOW())
");
        $stmt->execute([$id_laporan, $userId, $buktiPath, $deskripsi_ciri]);

        // === 7. SUKSES ===
        header("Location: index.php?action=detail_laporan&id=$id_laporan&success=klaim_diajukan");
        exit;
        break;


    // ==================================================================
    // DASHBOARD SATPAM
    // ==================================================================
    case 'dashboard':
        if ($userRole !== 'satpam') {
            header('Location: index.php?action=home');
            exit;
        }
        $current_page = 'dashboard';
        include 'views/admin/dashboard.php';
        break;

    case 'laporan_ditemukan_form':
        if ($sessionManager->get('role') !== 'satpam') {
            header('Location: index.php?action=login');
            exit;
        }
        include 'views/admin/laporan_ditemukan_form.php';
        break;

    case 'submit_laporan_ditemukan':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sessionManager->get('role') === 'satpam') {
            $namaBarang = trim($_POST['nama_barang'] ?? '');
            $deskripsiFisik = trim($_POST['deskripsi_fisik'] ?? '');
            $kategori = $_POST['kategori'] ?? '';
            $lokasi = $_POST['lokasi'] ?? '';
            $waktu = $_POST['waktu'] ?? '';

            $result = $laporanController->submitLaporanDitemukan(
                $namaBarang,
                $deskripsiFisik,
                $kategori,
                $lokasi,
                $wakt
            );

            // Redirect dengan pesan
            $msg = $result['success'] ? 'success' : 'error';
            header("Location: index.php?action=laporan_ditemukan_form&msg=$msg");
            exit;
        }
        // Jika bukan POST, redirect ke form
        header('Location: index.php?action=laporan_ditemukan_form');
        exit;
        break;

    // Di switch($action)
    case 'detail_laporan_satpam':
        if ($userRole !== 'satpam') {
            header('Location: index.php?action=login');
            exit;
        }
        include 'views/admin/detail_laporan_satpam.php';
        break;

    case 'catat_pengambil':
        // === CEK AKSES & METODE ===
        if ($userRole !== 'satpam' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=dashboard');
            exit;
        }

        $id_laporan = (int)($_POST['id_laporan'] ?? 0);
        $nim_pengambil = trim($_POST['nim_pengambil'] ?? '');
        $foto_bukti = $_FILES['foto_bukti'] ?? null;
        $waktu_diambil = date('Y-m-d H:i:s');

        // === VALIDASI INPUT DASAR ===
        if ($id_laporan <= 0 || empty($nim_pengambil) || !$foto_bukti || $foto_bukti['error'] !== UPLOAD_ERR_OK) {
            $error = "NIM, foto bukti, dan ID laporan wajib diisi.";
            error_log("Catat Pengambil Error: Input tidak lengkap - ID: $id_laporan, NIM: $nim_pengambil");
            header("Location: index.php?action=detail_laporan_satpam&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        // === VALIDASI NIM DI DATABASE ===
        $stmt = getDB()->prepare("
        SELECT a.nama, c.nomor_induk 
        FROM akun a 
        JOIN civitas c ON a.id_akun = c.id_akun 
        WHERE c.nomor_induk = ? AND a.role = 'civitas'
    ");
        $stmt->execute([$nim_pengambil]);
        $civitas = $stmt->fetch();

        if (!$civitas) {
            $error = "NIM tidak ditemukan di sistem civitas.";
            error_log("Catat Pengambil Error: NIM tidak valid - $nim_pengambil");
            header("Location: index.php?action=detail_laporan_satpam&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        // === VALIDASI FILE ===
        $ext = strtolower(pathinfo($foto_bukti['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed)) {
            $error = "Format foto tidak didukung. Gunakan JPG atau PNG.";
            header("Location: index.php?action=detail_laporan_satpam&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        if ($foto_bukti['size'] > 3 * 1024 * 1024) {
            $error = "Ukuran foto maksimal 3MB.";
            header("Location: index.php?action=detail_laporan_satpam&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        // === BUAT NAMA FILE: nim_{NIM}_laporan_{ID}_img_{TIMESTAMP}.ext ===
        $safeNim = preg_replace('/[^0-9]/', '', $nim_pengambil); // Hanya angka
        $timestamp = time();
        $newName = "nim_{$safeNim}_laporan_{$id_laporan}_img_{$timestamp}.{$ext}";

        $uploadDir = __DIR__ . '/uploads/bukti/';

        // === BUAT FOLDER JIKA BELUM ADA ===
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $error = "Gagal membuat folder upload.";
                error_log("Catat Pengambil Error: mkdir failed - $uploadDir");
                header("Location: index.php?action=detail_laporan_satpam&id=$id_laporan&error=" . urlencode($error));
                exit;
            }
        }

        $destination = $uploadDir . $newName;

        // === PINDAHKAN FILE ===
        if (!move_uploaded_file($foto_bukti['tmp_name'], $destination)) {
            $error = "Gagal menyimpan foto bukti ke server.";
            error_log("Catat Pengambil Error: move_uploaded_file failed - $destination");
            header("Location: index.php?action=detail_laporan_satpam&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        $fotoPath = "uploads/bukti/{$newName}";

        // === CEK & UPDATE STATUS (HINDARI DOUBLE SUBMIT) ===
        $stmt = getDB()->prepare("
        UPDATE laporan 
        SET status = 'sudah_diambil', 
            nim_pengambil = ?, 
            foto_bukti = ?, 
            waktu_diambil = ?
        WHERE id_laporan = ? 
          AND status != 'sudah_diambil'
    ");
        $affected = $stmt->execute([$nim_pengambil, $fotoPath, $waktu_diambil, $id_laporan]);

        if ($stmt->rowCount() === 0) {
            // Jika tidak ada baris yang diupdate → sudah diambil sebelumnya
            $error = "Barang sudah pernah dicatat diambil.";
            header("Location: index.php?action=detail_laporan_satpam&id=$id_laporan&error=" . urlencode($error));
            exit;
        }

        // === SUCCESS LOG ===
        error_log("Catat Pengambil Sukses: Laporan #$id_laporan diambil oleh NIM $nim_pengambil");

        // === REDIRECT ===
        header("Location: index.php?action=detail_laporan_satpam&id=$id_laporan&success=1");
        exit;
        break;

    case 'verifikasi_klaim':
        if ($userRole !== 'satpam' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=dashboard');
            exit;
        }

        $id_klaim = (int)($_POST['id_klaim'] ?? 0);
        $id_laporan = (int)($_POST['id_laporan'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($id_klaim <= 0 || !in_array($status, ['diverifikasi', 'ditolak'])) {
            header('Location: index.php?action=dashboard_klaim');
            exit;
        }

        $stmt = getDB()->prepare("
        UPDATE klaim 
        SET status_klaim = ?, updated_at = NOW() 
        WHERE id_klaim = ?
    ");
        $stmt->execute([$status, $id_klaim]);

        if ($status === 'diverifikasi') {
            $stmt = getDB()->prepare("UPDATE laporan SET status = 'sudah_diambil' WHERE id_laporan = ?");
            $stmt->execute([$id_laporan]);
        }

        // TAMBAHKAN refresh=1
        header('Location: index.php?action=dashboard_klaim&tab=diverifikasi&nocache=' . time());
        exit;
        break;

    case 'dashboard_kotak_masuk':
        if ($userRole !== 'satpam') {
            header('Location: index.php?action=home');
            exit;
        }
        $current_page = 'dashboard_kotak_masuk';
        include 'views/admin/dashboard_kotak_masuk.php';
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


    case 'dashboard_klaim':
        if ($userRole !== 'satpam') {
            header('Location: index.php?action=home');
            exit;
        }
        $current_page = 'dashboard_klaim';
        include 'views/admin/dashboard_klaim.php';
        break;


    case 'klaim_saya':
        include 'views/widgets/claim_widget.php';
        break;
}
