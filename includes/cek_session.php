
<?php
// includes/cek_session.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// BASE URL PROJECT
// ============================================

$base_url_path = '/Ukk_2026/';

// ============================================
// CEK LOGIN
// ============================================

if (
    !isset($_SESSION['status']) ||
    $_SESSION['status'] !== 'login'
) {
    header("Location: " . $base_url_path . "login.php");
    exit();
}

// ============================================
// FUNGSI BATASI AKSES
// ============================================

function batasi_akses(array $roles_diizinkan)
{
    global $base_url_path;

    $role_aktif = strtolower(trim($_SESSION['role'] ?? ''));

    $roles_diizinkan = array_map(
        function ($role) {
            return strtolower(trim($role));
        },
        $roles_diizinkan
    );

    if (!in_array($role_aktif, $roles_diizinkan, true)) {

        $_SESSION['pesan_error'] =
            'Akses ditolak. Anda tidak memiliki izin ke halaman ini.';

        if ($role_aktif === 'admin') {

            header('Location: ' . $base_url_path . 'dashboard.php');

        } elseif (
            $role_aktif === 'guru' ||
            $role_aktif === 'guru_wali_kelas'
        ) {

            header('Location: ' . $base_url_path . 'guru/dashboard_guru.php');

        } else {

            header('Location: ' . $base_url_path . 'login.php');
        }

        exit();
    }
}

// ============================================
// INFORMASI HALAMAN
// ============================================

$halaman = basename($_SERVER['PHP_SELF'] ?? '');
$self_path = $_SERVER['PHP_SELF'] ?? '';

$is_guru_subdir = strpos($self_path, '/guru/') !== false;

// ============================================
// DAFTAR HAK AKSES HALAMAN
// ============================================

$hak_akses_halaman = [

    // ========================================
    // ADMIN
    // ========================================

    'dashboard.php' => ['admin'],

    'kelola_siswa.php' => ['admin'],
    'tambah_siswa.php' => ['admin'],
    'edit_siswa.php' => ['admin'],
    'hapus_siswa.php' => ['admin'],
    'proses_tambah_siswa.php' => ['admin'],
    'proses_edit_siswa.php' => ['admin'],

    'penempatan_siswa.php' => ['admin'],
    'proses_simpan_penempatan.php' => ['admin'],

    'kelola_kelas.php' => ['admin'],
    'tambah_kelas.php' => ['admin'],
    'edit_kelas.php' => ['admin'],
    'hapus_kelas.php' => ['admin'],
    'proses_tambah_kelas.php' => ['admin'],
    'proses_edit_kelas.php' => ['admin'],

    'kelola_tahun_ajaran.php' => ['admin'],
    'tambah_tahun_ajaran.php' => ['admin'],
    'edit_tahun_ajaran.php' => ['admin'],
    'hapus_tahun_ajaran.php' => ['admin'],
    'proses_tambah_tahun_ajaran.php' => ['admin'],
    'proses_edit_tahun_ajaran.php' => ['admin'],

    'kelola_walikelas.php' => ['admin'],
    'tambah_walikelas.php' => ['admin'],
    'edit_walikelas.php' => ['admin'],
    'hapus_walikelas.php' => ['admin'],
    'proses_tambah_walikelas.php' => ['admin'],
    'proses_edit_walikelas.php' => ['admin'],

    'kelola_guru.php' => ['admin'],
    'tambah_guru.php' => ['admin'],
    'edit_guru.php' => ['admin'],
    'hapus_guru.php' => ['admin'],
    'proses_tambah_guru.php' => ['admin'],
    'proses_edit_guru.php' => ['admin'],

    'kelola_pelanggaran_kategori.php' => ['admin'],
    'tambah_pelanggaran_kategori.php' => ['admin'],
    'edit_pelanggaran_kategori.php' => ['admin'],
    'hapus_pelanggaran_kategori.php' => ['admin'],
    'proses_tambah_pelanggaran_kategori.php' => ['admin'],
    'proses_edit_pelanggaran.php' => ['admin'],

    'kelola_jenis_pelanggaran.php' => ['admin'],
    'tambah_jenis_pelanggaran.php' => ['admin'],
    'proses_tambah_jenis_pelanggaran.php' => ['admin'],
    'edit_jenis_pelanggaran.php' => ['admin'],
    'proses_edit_jenis_pelanggaran.php' => ['admin'],
    'hapus_jenis_pelanggaran.php' => ['admin'],

    // ========================================
    // GURU DAN WALI KELAS
    // ========================================

    'dashboard_guru.php' => ['guru', 'guru_wali_kelas'],

    'catat_pelanggaran.php' => ['guru', 'guru_wali_kelas'],
    'proses_simpan_pelanggaran.php' => ['guru', 'guru_wali_kelas'],

    'edit_catatan_pelanggaran.php' => ['guru', 'guru_wali_kelas'],
    'proses_edit_catatan_pelanggaran.php' => ['guru', 'guru_wali_kelas'],
    'proses_hapus_catatan_pelanggaran.php' => ['guru', 'guru_wali_kelas'],

    'tindakan_guru.php' => ['guru', 'guru_wali_kelas'],

    // ========================================
    // RIWAYAT ADMIN, GURU, DAN WALI KELAS
    // ========================================

    'riwayat_guru.php' => ['admin', 'guru', 'guru_wali_kelas'],

    'rekap_poin.php' => ['guru', 'guru_wali_kelas'],

    // ========================================
    // KHUSUS GURU BIASA
    // ========================================

    'laporan_guru.php' => ['guru'],

    // ========================================
    // KHUSUS ADMIN
    // ========================================

    'laporan.php' => ['admin'],
    'riwayat.php' => ['admin'],

    // ========================================
    // ADMIN, GURU, DAN WALI KELAS
    // ========================================

    'cetak_laporan.php' => ['admin', 'guru', 'guru_wali_kelas'],
    'proses_tindakan.php' => ['admin', 'guru', 'guru_wali_kelas']

];

// ============================================
// MENENTUKAN IZIN HALAMAN
// ============================================

$izin_halaman = $hak_akses_halaman[$halaman] ?? [];

// ============================================
// PEMERIKSAAN HAK AKSES
// ============================================

batasi_akses($izin_halaman);

// ============================================
// BATAS INPUT TEKS
// ============================================

$aturan_batas_input = [];

// ============================================
// BATAS INPUT ANGKA
// ============================================

$aturan_batas_angka = [

    'tambah_siswa.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'edit_siswa.php' => [
        'id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_tambah_siswa.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_edit_siswa.php' => [
        'id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'tambah_guru.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'edit_guru.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1],
        'user_id' => ['min' => 1, 'max' => 2147483647]
    ],

    'proses_tambah_guru.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_edit_guru.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1],
        'user_id' => ['min' => 1, 'max' => 2147483647]
    ],

    'tambah_kelas.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'edit_kelas.php' => [
        'id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_tambah_kelas.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_edit_kelas.php' => [
        'id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'tambah_tahun_ajaran.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'edit_tahun_ajaran.php' => [
        'id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_tambah_tahun_ajaran.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_edit_tahun_ajaran.php' => [
        'id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'tambah_pelanggaran_kategori.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'edit_pelanggaran_kategori.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_tambah_pelanggaran_kategori.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_edit_pelanggaran.php' => [
        'id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_tambah_kategori.php' => [
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'edit_jenis_pelanggaran.php' => [
        'id' => ['min' => 1, 'max' => 2147483647]
    ],

    'proses_tambah_jenis_pelanggaran.php' => [
        'pelanggaran_kategori_id' => ['min' => 1, 'max' => 2147483647],
        'poin' => ['min' => 0, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_edit_jenis_pelanggaran.php' => [
        'id' => ['min' => 1, 'max' => 2147483647],
        'pelanggaran_kategori_id' => ['min' => 1, 'max' => 2147483647],
        'poin' => ['min' => 0, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'hapus_jenis_pelanggaran.php' => [
        'id' => ['min' => 1, 'max' => 2147483647]
    ],

    'tambah_walikelas.php' => [
        'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647],
        'kelas_id' => ['min' => 1, 'max' => 2147483647],
        'guru_id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'edit_walikelas.php' => [
        'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647],
        'kelas_id' => ['min' => 1, 'max' => 2147483647],
        'guru_id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_tambah_walikelas.php' => [
        'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647],
        'kelas_id' => ['min' => 1, 'max' => 2147483647],
        'guru_id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_edit_walikelas.php' => [
        'id' => ['min' => 1, 'max' => 2147483647],
        'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647],
        'kelas_id' => ['min' => 1, 'max' => 2147483647],
        'guru_id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'penempatan_siswa.php' => [
        'siswa_id' => ['min' => 1, 'max' => 2147483647],
        'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647],
        'kelas_id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'proses_simpan_penempatan.php' => [
        'siswa_id' => ['min' => 1, 'max' => 2147483647],
        'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647],
        'kelas_id' => ['min' => 1, 'max' => 2147483647],
        'status_aktif' => ['min' => 0, 'max' => 1]
    ],

    'catat_pelanggaran.php' => [
        'siswa_id' => ['min' => 1, 'max' => 2147483647],
        'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647],
        'kelas_id' => ['min' => 1, 'max' => 2147483647],
        'pelanggaran_id' => ['min' => 1, 'max' => 2147483647],
        'pelanggaran_kategori_id' => ['min' => 1, 'max' => 2147483647],
        'poin' => ['min' => 0, 'max' => 2147483647]
    ],

    'proses_simpan_pelanggaran.php' => [
        'siswa_id' => ['min' => 1, 'max' => 2147483647],
        'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647],
        'kelas_id' => ['min' => 1, 'max' => 2147483647],
        'pelanggaran_id' => ['min' => 1, 'max' => 2147483647],
        'pelanggaran_kategori_id' => ['min' => 1, 'max' => 2147483647],
        'poin' => ['min' => 0, 'max' => 2147483647]
    ],

    'edit_catatan_pelanggaran.php' => [
        'id' => ['min' => 1, 'max' => 2147483647]
    ],

    'proses_edit_catatan_pelanggaran.php' => [
        'id' => ['min' => 1, 'max' => 2147483647],
        'siswa_id' => ['min' => 1, 'max' => 2147483647],
        'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647],
        'kelas_id' => ['min' => 1, 'max' => 2147483647],
        'pelanggaran_id' => ['min' => 1, 'max' => 2147483647]
    ],

    'proses_hapus_catatan_pelanggaran.php' => [
        'id' => ['min' => 1, 'max' => 2147483647]
    ],

    'proses_tindakan.php' => [
        'id' => ['min' => 1, 'max' => 2147483647]
    ]
];

// ============================================
// VALIDASI INPUT POST
// ============================================

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $batas_halaman = $aturan_batas_input[$halaman] ?? [];
    $batas_angka_halaman = $aturan_batas_angka[$halaman] ?? [];

    $pesan_batas = '';

    // VALIDASI TEKS
    foreach ($batas_halaman as $field => $rule) {

        if (!array_key_exists($field, $_POST)) {
            continue;
        }

        if (!is_string($_POST[$field])) {
            $pesan_batas = 'Format kolom ' . $field . ' tidak valid.';
            break;
        }

        $value = $_POST[$field];

        $character_count = function_exists('mb_strlen')
            ? mb_strlen($value, 'UTF-8')
            : strlen($value);

        if (isset($rule['min']) && $character_count < $rule['min']) {
            $pesan_batas = 'Kolom ' . $field . ' minimal ' . $rule['min'] . ' karakter.';
            break;
        }

        if (isset($rule['max']) && $character_count > $rule['max']) {
            $pesan_batas = 'Kolom ' . $field . ' maksimal ' . $rule['max'] . ' karakter.';
            break;
        }

        if (!empty($rule['digits']) && $value !== '' && !preg_match('/\A[0-9]+\z/', $value)) {
            $pesan_batas = 'Kolom ' . $field . ' hanya boleh berisi angka.';
            break;
        }

        if (!empty($rule['no_digits']) && preg_match('/[0-9]/', $value)) {
            $pesan_batas = 'Nama siswa tidak boleh mengandung angka.';
            break;
        }

        if (!empty($rule['academic_year']) && !preg_match('/\A[0-9]{4}\/[0-9]{4}\z/', $value)) {
            $pesan_batas = 'Nama tahun ajaran harus berformat angka seperti 2026/2027.';
            break;
        }
    }

    // VALIDASI ANGKA
    if ($pesan_batas === '') {

        foreach ($batas_angka_halaman as $field => $rule) {

            if (!array_key_exists($field, $_POST)) {
                continue;
            }

            if (!is_string($_POST[$field]) && !is_int($_POST[$field])) {
                $pesan_batas = 'Format angka ' . $field . ' tidak valid.';
                break;
            }

            $value = filter_var($_POST[$field], FILTER_VALIDATE_INT);

            if (
                $value === false ||
                $value < $rule['min'] ||
                $value > $rule['max']
            ) {
                $pesan_batas = 'Nilai ' . $field . ' harus antara ' .
                    $rule['min'] . ' dan ' . $rule['max'] . '.';
                break;
            }
        }
    }

    // TAMPILKAN PESAN ERROR
    if ($pesan_batas !== '') {

        http_response_code(422);
        header('Content-Type: text/html; charset=UTF-8');

        echo '<!DOCTYPE html>';
        echo '<html lang="id">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<title>Input tidak valid</title>';
        echo '</head>';

        echo '<body style="font-family:sans-serif;padding:2rem">';
        echo '<h1>Input tidak valid</h1>';

        echo '<p>' .
            htmlspecialchars($pesan_batas, ENT_QUOTES, 'UTF-8') .
            '</p>';

        echo '<button type="button" onclick="history.back()">Kembali ke formulir</button>';

        echo '</body>';
        echo '</html>';

        exit();
    }
}
?>
