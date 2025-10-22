<?php
require_once __DIR__ . '/../models/AkunModel.php';
use Models\AkunModel;

class ProfileController {
    private $model;
    private $session;

    public function __construct($sessionManager) {
        $this->model = new AkunModel();
        $this->session = $sessionManager;
    }

    public function showProfile() {
        $userId = $this->session->get('userId');
        if (!$userId) {
            header('Location: index.php?action=login');
            return;
        }

        $profil = $this->model->getProfil($userId);
        if (!$profil) {
            error_log("Profile fetch failed for userId=$userId");
            return ['success' => false, 'message' => 'Gagal mengambil data profil'];
        }

        return ['success' => true, 'profil' => $profil];
    }

    public function updateProfile($nama, $nomorKontak) {
        $userId = $this->session->get('userId');
        if (!$userId) {
            header('Location: index.php?action=login');
            return;
        }

        if (empty($nama) || empty($nomorKontak)) {
            return ['success' => false, 'message' => 'Nama dan nomor kontak wajib diisi'];
        }

        $success = $this->model->updateProfil($userId, $nama, $nomorKontak);
        if ($success) {
            $this->session->set('nama', $nama); // Update session
            error_log("Profile updated for userId=$userId");
            return ['success' => true, 'message' => 'Profil berhasil diperbarui'];
        }
        error_log("Profile update failed for userId=$userId");
        return ['success' => false, 'message' => 'Gagal memperbarui profil'];
    }
}