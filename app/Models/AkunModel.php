<?php
// app/Models/AkunModel.php

namespace Models;

require_once __DIR__ . '/../../config/db.php';

class AkunModel
{
    private $database;

    public function __construct()
    {
        $this->database = getDB();
    }

    /**
     * Login user berdasarkan email
     */
    public function getUserByEmail(string $email)
    {
        $stmt = $this->database->prepare("SELECT * FROM akun WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Ambil profil lengkap user
     */
    public function getProfil(int $idAkun)
    {
        $stmt = $this->database->prepare("
            SELECT a.*, c.nomor_induk, s.id_satpam 
            FROM akun a 
            LEFT JOIN civitas c ON a.id_akun = c.id_akun 
            LEFT JOIN satpam s ON a.id_akun = s.id_akun 
            WHERE a.id_akun = :id
        ");
        $stmt->execute(['id' => $idAkun]);
        return $stmt->fetch();
    }

    /**
     * Update profil user
     */
    public function updateProfil(int $idAkun, string $nama, string $nomorKontak): bool
    {
        $stmt = $this->database->prepare("
            UPDATE akun 
            SET nama = :nama, nomor_kontak = :nomorKontak 
            WHERE id_akun = :id
        ");
        return $stmt->execute([
            'nama' => $nama,
            'nomorKontak' => $nomorKontak,
            'id' => $idAkun
        ]);
    }

    /**
     * Ambil user berdasarkan ID (untuk remember me)
     */
    public function getUserById(int $idAkun)
    {
        $stmt = $this->database->prepare("SELECT * FROM akun WHERE id_akun = ?");
        $stmt->execute([$idAkun]);
        return $stmt->fetch();
    }
}
