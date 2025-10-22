<?php
namespace Models;

require_once __DIR__ . '/../config/db.php';

class AkunModel {
    private $database;

    public function __construct() {
        $this->database = getDB();
    }

    public function login($email, $password) {
        error_log("Login attempt: email=$email");
        $stmt = $this->database->prepare("SELECT * FROM akun WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        error_log("User fetched: " . print_r($user, true));

        if ($user && password_verify($password, $user['password'])) {
            error_log("Login success for email=$email");
            return $user;
        }
        error_log("Login failed: email or password wrong for email=$email");
        return false;
    }

    public function getProfil($idAkun) {
        $stmt = $this->database->prepare("SELECT a.*, c.nomor_induk FROM akun a LEFT JOIN civitas c ON a.id_akun = c.id_akun WHERE a.id_akun = :id");
        $stmt->execute(['id' => $idAkun]);
        $profil = $stmt->fetch();
        error_log("Profile fetched for idAkun=$idAkun: " . print_r($profil, true));
        return $profil;
    }

    public function updateProfil($idAkun, $nama, $nomorKontak) {
        $stmt = $this->database->prepare("UPDATE akun SET nama = :nama, nomor_kontak = :nomorKontak WHERE id_akun = :id");
        return $stmt->execute([
            'nama' => $nama,
            'nomorKontak' => $nomorKontak,
            'id' => $idAkun
        ]);
    }
}