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

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public function destroy()
    {
        session_unset();
        session_destroy();
    }
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

    public function getSessionManager()
    {
        return $this->session;
    }

    public function login($email, $password)
    {
        // Validasi email UB
        if (!str_ends_with($email, '@ub.ac.id') && !str_ends_with($email, '@student.ub.ac.id')) {
            return ['success' => false, 'message' => 'Gunakan email UB (@ub.ac.id atau @student.ub.ac.id)'];
        }

        // Cek login via model
        $user = $this->model->login($email, $password);
        if ($user) {
            // Set session via wrapper
            $this->session->set('userId', $user['id_akun']);
            $this->session->set('nama', $user['nama']);
            $this->session->set('role', $user['role']);

            return ['success' => true, 'message' => 'Login berhasil'];
        }
        return ['success' => false, 'message' => 'Email atau password salah'];
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
        // Hapus session dan cookie
        $this->session->destroy();
        setcookie('user_email', '', time() - 3600, "/");
        setcookie('user_id', '', time() - 3600, "/");

        // Redirect tanpa exit
        header('Location: ../index.php');
        return;
    }
}
