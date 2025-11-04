<?php

namespace Models;

require_once __DIR__ . '/../config/db.php';

class LaporanModel
{
    private $database;

    public function __construct()
    {
        $this->database = getDB();
    }

    public function simpanLaporanHilang($idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu, $fotoPath = null)
    {
        try {
            $validLokasi = [
                'Smart Class Gedung F',
                'Junction',
                'Gedung Kreativitas Mahasiswa (GKM)',
                'kantin',
                'Ruang Baca',
                'Laboratorium Pembelajaran',
                'Ruang Ujian',
                'ruang tunggu',
                'Gazebo lantai 4',
                'Area Parkir',
                'EduTech',
                'Mushola Ulul Al-Baab',
                'auditorium algoritma'
            ];
            $lokasi = in_array($lokasi, $validLokasi) ? $lokasi : null;

            $stmt = $this->database->prepare("
            INSERT INTO laporan 
            (id_akun, tipe_laporan, nama_barang, deskripsi_fisik, kategori, lokasi, waktu, status, foto)
            VALUES (:id_akun, 'hilang', :nama_barang, :deskripsi_fisik, :kategori, :lokasi, :waktu, 'belum_ditemukan', :foto)
        ");
            $stmt->execute([
                'id_akun' => $idAkun,
                'nama_barang' => $namaBarang,
                'deskripsi_fisik' => $deskripsiFisik,
                'kategori' => $kategori,
                'lokasi' => $lokasi,
                'waktu' => $waktu,
                'foto' => $fotoPath
            ]);
            return ['success' => true, 'message' => 'Laporan berhasil disimpan'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Gagal menyimpan laporan: ' . $e->getMessage()];
        }
    }

    public function getRiwayatLaporan($idAkun)
    {
        try {
            $stmt = $this->database->prepare("
            SELECT id_laporan, nama_barang, kategori, lokasi, waktu, status, foto, created_at
            FROM laporan
            WHERE id_akun = :id_akun AND tipe_laporan = 'hilang'
            ORDER BY created_at DESC
        ");
            $stmt->execute(['id_akun' => $idAkun]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error fetching riwayat: " . $e->getMessage());
            return [];
        }
    }

    public function getAllLaporanHilang()
    {
        try {
            $stmt = $this->database->prepare("
                SELECT  id_laporan,
                        nama_barang,
                        deskripsi_fisik,
                        kategori,
                        lokasi,
                        waktu,
                        status,
                        foto
                FROM laporan
                WHERE tipe_laporan = 'hilang'
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log('LaporanModel::getAllLaporanHilang – ' . $e->getMessage());
            return [];
        }
    }

    public function simpanLaporanDitemukan($idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu)
    {
        try {
            $stmt = $this->database->prepare("
            INSERT INTO laporan 
            (id_akun, tipe_laporan, nama_barang, deskripsi_fisik, kategori, lokasi, waktu, status)
            VALUES (:id_akun, 'ditemukan', :nama_barang, :deskripsi_fisik, :kategori, :lokasi, :waktu, 'ditemukan')
        ");
            $stmt->execute([
                'id_akun' => $idAkun,
                'nama_barang' => $namaBarang,
                'deskripsi_fisik' => $deskripsiFisik,
                'kategori' => $kategori,
                'lokasi' => $lokasi,
                'waktu' => $waktu
            ]);
            return ['success' => true, 'message' => 'Laporan barang ditemukan berhasil disimpan'];
        } catch (\PDOException $e) {
            error_log("Error simpan laporan ditemukan: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyimpan laporan'];
        }
    }
}
