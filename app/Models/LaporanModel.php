<?php
// app/Models/LaporanModel.php

namespace Models;

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // PHPMailer
require_once __DIR__ . '/../../config/Mail.php'; // Class Mail

use Mail;

use PHPMailer\PHPMailer\PHPMailer;

class LaporanModel
{
    private $database;

    public function __construct()
    {
        $this->database = getDB();
    }

    // ==================================================================
    //  LAPORAN HILANG
    // ==================================================================
    public function simpanLaporanHilang($idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu, $fotoPath = null)
    {
        try {
            $validLokasi = [
                'Smart Class Gedung F', 'Junction', 'Gedung Kreativitas Mahasiswa (GKM)', 'kantin',
                'Ruang Baca', 'Laboratorium Pembelajaran', 'Ruang Ujian', 'ruang tunggu',
                'Gazebo lantai 4', 'Area Parkir', 'EduTech', 'Mushola Ulul Al-Baab', 'auditorium algoritma'
            ];
            $lokasi = in_array($lokasi, $validLokasi) ? $lokasi : null;

            $stmt = $this->database->prepare("
                INSERT INTO laporan 
                (id_akun, tipe_laporan, nama_barang, deskripsi_fisik, kategori, lokasi, waktu, status, foto)
                VALUES (?, 'hilang', ?, ?, ?, ?, ?, 'belum_ditemukan', ?)
            ");
            $stmt->execute([$idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu, $fotoPath]);
            return ['success' => true, 'message' => 'Laporan berhasil disimpan'];
        } catch (\PDOException $e) {
            error_log("Error simpan laporan hilang: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyimpan laporan'];
        }
    }

    public function getRiwayatLaporan($idAkun)
    {
        try {
            $stmt = $this->database->prepare("
                SELECT id_laporan, nama_barang, kategori, lokasi, waktu, status, foto, created_at
                FROM laporan
                WHERE id_akun = ? AND tipe_laporan = 'hilang'
                ORDER BY created_at DESC
            ");
            $stmt->execute([$idAkun]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error get riwayat: " . $e->getMessage());
            return [];
        }
    }

    public function getAllLaporanHilang()
    {
        try {
            $stmt = $this->database->prepare("
                SELECT id_laporan, nama_barang, deskripsi_fisik, kategori, lokasi, waktu, status, foto
                FROM laporan
                WHERE tipe_laporan = 'hilang'
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            error_log("Error get all hilang: " . $e->getMessage());
            return [];
        }
    }

    // ==================================================================
    //  LAPORAN DITEMUKAN
    // ==================================================================
    public function simpanLaporanDitemukan($idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu)
    {
        try {
            $stmt = $this->database->prepare("
                INSERT INTO laporan 
                (id_akun, tipe_laporan, nama_barang, deskripsi_fisik, kategori, lokasi, waktu, status)
                VALUES (?, 'ditemukan', ?, ?, ?, ?, ?, 'ditemukan')
            ");
            $stmt->execute([$idAkun, $namaBarang, $deskripsiFisik, $kategori, $lokasi, $waktu]);
            return ['success' => true, 'message' => 'Laporan ditemukan berhasil disimpan'];
        } catch (\PDOException $e) {
            error_log("Error simpan ditemukan: " . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyimpan laporan'];
        }
    }

    // ==================================================================
    //  NOTIFIKASI OTOMATIS: EMAIL (CIVITAS) + DASHBOARD (SATPAM)
    // ==================================================================
    public function kirimNotifikasiPencocokan(int $idLaporanDitemukan): void
    {
        $pdo = getDB();

        // Ambil data laporan ditemukan
        $stmt = $pdo->prepare("
            SELECT l.nama_barang, l.deskripsi_fisik, l.kategori, l.lokasi
            FROM laporan l
            WHERE l.id_laporan = ? AND l.tipe_laporan = 'ditemukan'
        ");
        $stmt->execute([$idLaporanDitemukan]);
        $ditemukan = $stmt->fetch();

        if (!$ditemukan) return;

        // Cari laporan hilang yang cocok
        $stmt = $pdo->prepare("
            SELECT 
                l.id_laporan, 
                l.id_akun, 
                a.email, 
                a.nama, 
                l.nama_barang AS nama_hilang, 
                l.deskripsi_fisik AS deskripsi_hilang,
                l.kategori,
                l.lokasi
            FROM laporan l
            JOIN akun a ON l.id_akun = a.id_akun
            WHERE l.tipe_laporan = 'hilang' AND l.status = 'belum_ditemukan'
        ");
        $stmt->execute();
        $laporanHilang = $stmt->fetchAll();

        foreach ($laporanHilang as $hilang) {
            $similarNama = similar_text(strtolower($ditemukan['nama_barang']), strtolower($hilang['nama_hilang']), $persenNama);
            $similarDeskripsi = similar_text(strtolower($ditemukan['deskripsi_fisik']), strtolower($hilang['deskripsi_hilang']), $persenDeskripsi);
            $persenKesamaan = max($persenNama, $persenDeskripsi);

            if (
                $persenKesamaan >= 70 &&
                $ditemukan['kategori'] === $hilang['kategori'] &&
                $ditemukan['lokasi'] === $hilang['lokasi']
            ) {
                // === EMAIL KE CIVITAS (SUNGGUHAN + HTML) ===
                if (preg_match('/@(student\.)?ub\.ac\.id$/i', $hilang['email'])) {
                    $html = $this->getEmailTemplate($hilang, $ditemukan, $hilang['id_laporan']);
                    $text = strip_tags($html);

                    // Kirim email sungguhan
                    $sent = Mail::send($hilang['email'], 'Barang Anda Mungkin Ditemukan!', $html, $text);

                    // Simpan ke DB untuk log
                    $status = $sent ? 'Terkirim' : 'Gagal kirim';
                    $this->simpanNotifikasi($hilang['id_akun'], $idLaporanDitemukan, "Email: $status", 'email');
                }

                // === DASHBOARD KE SEMUA SATPAM ===
                $pesanSatpam = "Barang \"{$ditemukan['nama_barang']}\" cocok dengan laporan hilang ID #{$hilang['id_laporan']}";
                $stmtSatpam = $pdo->prepare("SELECT id_akun FROM akun WHERE role = 'satpam'");
                $stmtSatpam->execute();
                foreach ($stmtSatpam->fetchAll() as $satpam) {
                    $this->simpanNotifikasi($satpam['id_akun'], $idLaporanDitemukan, $pesanSatpam, 'dashboard');
                }
            }
        }
    }

    // ==================================================================
    //  TEMPLATE EMAIL HTML
    // ==================================================================
    private function getEmailTemplate($hilang, $ditemukan, $idLaporan): string
    {
        $url = "https://sipan.filkom.ub.ac.id/index.php?action=laporan-detail&id={$idLaporan}";

        return "
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Barang Anda Ditemukan!</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 30px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: white; padding: 30px 20px; text-align: center; }
                .header h1 { margin: 0; font-size: 24px; }
                .header p { margin: 8px 0 0; font-size: 14px; opacity: 0.9; }
                .content { padding: 30px 25px; color: #333; }
                .content h2 { color: #0d6efd; margin-top: 0; }
                .item { background: #f8f9ff; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #0d6efd; }
                .item strong { color: #0d6efd; }
                .btn { display: inline-block; background: #0d6efd; color: white; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: 600; margin: 20px 0; }
                .btn:hover { background: #0b5ed7; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 12px; }
                .footer a { color: #0d6efd; text-decoration: none; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Lost & Found FILKOM UB</h1>
                </div>
                <div class='content'>
                    <h2>Halo {$hilang['nama']},</h2>
                    <p>Kami informasikan bahwa barang Anda kemungkinan telah <strong>ditemukan</strong>!</p>
                    
                    <div class='item'>
                        <p><strong>Barang Anda:</strong> {$hilang['nama_hilang']}</p>
                        <p><strong>Lokasi Hilang:</strong> {$hilang['lokasi']}</p>
                    </div>
                    
                    <div class='item'>
                        <p><strong>Barang Ditemukan:</strong> {$ditemukan['nama_barang']}</p>
                        <p><strong>Lokasi Ditemukan:</strong> {$ditemukan['lokasi']}</p>
                        <p><strong>Kategori:</strong> " . ucfirst($ditemukan['kategori']) . "</p>
                    </div>
                    
                    <p>Silakan segera ke <strong>pos satpam FILKOM</strong> untuk verifikasi dan pengambilan.</p>
                    
                    <div style='text-align: center;'>
                        <a href='{$url}' class='btn'>Lihat Detail Laporan</a>
                    </div>
                    
                    <p style='font-size: 13px; color: #777; margin-top: 30px;'>
                        Email ini dikirim otomatis oleh sistem. Jangan balas email ini.
                    </p>
                </div>
                <div class='footer'>
                    <p>&copy; 2025 Lost & Found- Fakultas Ilmu Komputer UB</p>
                    <p><a href='https://sipan.filkom.ub.ac.id'>sipan.filkom.ub.ac.id</a></p>
                </div>
            </div>
        </body>
        </html>";
    }

    // ==================================================================
    //  SIMPAN NOTIFIKASI KE DB (ANTI-DUPLIKAT)
    // ==================================================================
    private function simpanNotifikasi(int $idAkun, int $idLaporan, string $pesan, string $tipe): void
    {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            INSERT INTO pemberitahuan_sistem 
            (id_akun, id_laporan, pesan, tipe_notif, waktu_kirim, dibaca)
            VALUES (?, ?, ?, ?, NOW(), 0)
            ON DUPLICATE KEY UPDATE 
                pesan = VALUES(pesan), 
                waktu_kirim = NOW()
        ");
        $stmt->execute([$idAkun, $idLaporan, $pesan, $tipe]);
    }
}