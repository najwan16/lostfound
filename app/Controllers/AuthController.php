<?php
// app/Controllers/AuthController.php

require_once __DIR__ . '/../Models/AkunModel.php';

use Models\AkunModel;

/**
 * Session Manager - Singleton Pattern
 */
if (!class_exists('SessionManager')) {
    class SessionManager
    {
        private static $instance = null;

        private function __construct()
        {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }

        public static function getInstance(): self
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function set(string $key, $value): void
        {
            $_SESSION[$key] = $value;
        }

        public function get(string $key)
        {
            return $_SESSION[$key] ?? null;
        }

        public function destroy(): void
        {
            session_unset();
            session_destroy();
        }

        public function has(string $key): bool
        {
            return isset($_SESSION[$key]);
        }
    }
}

class AuthController
{
    private $model;
    private $session;

    public function __construct()
    {
        $this->model = new AkunModel();
        $this->session = SessionManager::getInstance();
    }

    /**
     * @return SessionManager
     */
    public function getSessionManager(): SessionManager
    {
        return $this->session;
    }

    /**
     * Login user dan simpan session
     */
    // app/Controllers/AuthController.php

    public function login(string $email, string $password): array
    {
        $email = trim($email);
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email dan password wajib diisi'];
        }

        $user = $this->model->getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Email atau password salah'];
        }

        // Simpan session
        $this->session->set('userId', $user['id_akun']);
        $this->session->set('nama', $user['nama']);
        $this->session->set('role', $user['role']);

        // Ambil NIM jika civitas
        if ($user['role'] === 'civitas') {
            $profil = $this->model->getProfil($user['id_akun']);
            $this->session->set('nim', $profil['nomor_induk'] ?? '');
        }

        // === CEK NOMOR KONTAK ===
        $nomorKontak = trim($user['nomor_kontak'] ?? '');
        if (empty($nomorKontak)) {
            // Tandai bahwa user perlu isi nomor kontak
            $this->session->set('require_contact', true);
            return [
                'success' => true,
                'require_contact' => true,
                'role' => $user['role'],
                'message' => 'Silakan lengkapi nomor telepon'
            ];
        }

        return [
            'success' => true,
            'require_contact' => false,
            'role' => $user['role'],
            'message' => 'Login berhasil'
        ];
    }

    /**
     * Set cookie remember me
     */
    public function setRememberMeCookie(string $email, int $userId, bool $remember): void
    {
        if ($remember) {
            $expire = time() + (7 * 24 * 3600); // 7 hari
            setcookie('user_email', $email, $expire, "/", "", false, true);
            setcookie('user_id', $userId, $expire, "/", "", false, true);
        } else {
            setcookie('user_email', '', time() - 3600, "/");
            setcookie('user_id', '', time() - 3600, "/");
        }
    }

    /**
     * Logout dan bersihkan session + cookie
     */
    public function logout(): void
    {
        $this->session->destroy();
        $this->setRememberMeCookie('', 0, false);
        header('Location: index.php?action=login');
        exit;
    }

    /**
     * Cek apakah user sudah login
     */
    public function isLoggedIn(): bool
    {
        return $this->session->has('userId');
    }

    /**
     * Cek role user
     */
    public function getRole(): ?string
    {
        return $this->session->get('role');
    }

    public function updateKontak(string $nomor): array
    {
        $userId = $this->session->get('userId');
        if (!$userId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        // Normalisasi nomor
        $nomor = preg_replace('/[^0-9]/', '', $nomor); // hapus non-digit

        if (substr($nomor, 0, 1) === '0') {
            $nomor = '62' . substr($nomor, 1); // 081 → 6281
        } elseif (substr($nomor, 0, 2) !== '62') {
            $nomor = '62' . $nomor; // 81 → 6281
        }

        if (!preg_match('/^62[0-9]{9,13}$/', $nomor)) {
            return ['success' => false, 'message' => 'Nomor tidak valid. Gunakan format Indonesia (contoh: 081234567890 atau 81234567890)'];
        }

        $success = $this->model->updateProfil($userId, $this->session->get('nama'), $nomor);

        if ($success) {
            // Hapus flag require_contact
            $this->session->set('require_contact', false);
            return ['success' => true, 'message' => 'Nomor telepon berhasil disimpan'];
        }

        return ['success' => false, 'message' => 'Gagal menyimpan nomor'];
    }
}
