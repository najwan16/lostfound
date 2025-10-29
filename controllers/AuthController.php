<?php
require_once __DIR__ . '/../models/AkunModel.php';
use Models\AkunModel;

class SessionManager
{
    public function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    public function set($key, $value) { $_SESSION[$key] = $value; }
    public function get($key) { return $_SESSION[$key] ?? null; }
    public function destroy() { session_unset(); session_destroy(); }
}

class AuthController
{
    private $model;
    private $session;

    public function __construct()
    {
        $this->model = new AkunModel();
        $this->session = new SessionManager();
        $this->session->start();
    }

    public function getSessionManager() { return $this->session; }

    // PERBAIKAN UTAMA: Login + Return Role
    public function login($email, $password)
    {
        $user = $this->model->getUserByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            // Simpan session
            $this->session->set('userId', $user['id_akun']);
            $this->session->set('nama', $user['nama']);
            $this->session->set('role', $user['role']);

            // Jika civitas, ambil NIM
            if ($user['role'] === 'civitas') {
                $profil = $this->model->getProfil($user['id_akun']);
                $this->session->set('nim', $profil['nomor_induk'] ?? '');
            }

            return [
                'success' => true,
                'role' => $user['role'],
                'message' => 'Login berhasil'
            ];
        }

        return [
            'success' => false,
            'message' => 'Email atau password salah'
        ];
    }

    public function setRememberMeCookie($email, $userId, $remember)
    {
        if ($remember) {
            setcookie('user_email', $email, time() + (7 * 24 * 3600), "/");
            setcookie('user_id', $userId, time() + (7 * 24 * 3600), "/");
        }
    }

    public function logout()
    {
        $this->session->destroy();
        setcookie('user_email', '', time() - 3600, "/");
        setcookie('user_id', '', time() - 3600, "/");
        header('Location: index.php');
        exit;
    }
}