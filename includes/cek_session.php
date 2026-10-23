<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login") {
    header("Location: " . (strpos($_SERVER['PHP_SELF'], '/guru/') !== false ? '../login.php' : 'login.php'));
    exit();
}

function batasi_akses(array $roles_diizinkan) {
    $role_aktif = strtolower(trim($_SESSION['role'] ?? ''));
    $roles_diizinkan = array_map('strtolower', $roles_diizinkan);

    if (!in_array($role_aktif, $roles_diizinkan, true)) {
        $_SESSION['pesan_error'] = 'Akses ditolak. Anda tidak memiliki izin ke halaman ini.';
        $prefix = strpos($_SERVER['PHP_SELF'], '/guru/') !== false ? '../' : '';
        if ($role_aktif === 'admin') {
            header('Location: ' . $prefix . 'dashboard.php');
        } elseif ($role_aktif === 'guru') {
            header('Location: ' . $prefix . 'guru/dashboard_guru.php');
        } else {
            header('Location: ' . $prefix . 'login.php');
        }
        exit();
    }
}

$halaman = basename($_SERVER['PHP_SELF']);
$self_path = $_SERVER['PHP_SELF'] ?? '';
$is_guru_subdir = strpos($self_path, '/guru/') !== false;

$hak_akses_halaman = [
    // Admin only
    'dashboard.php' => ['admin'],
    'kelola_siswa.php' => ['admin'],
    'tambah_siswa.php' => ['admin'],
    'edit_siswa.php' => ['admin'],
    'hapus_siswa.php' => ['admin'],
    'proses_tambah_siswa.php' => ['admin'],
    'proses_edit_siswa.php' => ['admin'],
    'penempatan_siswa.php' => ['admin'],
    'proses_simpan_penempatan.php' => ['admin'],
    'cetak_laporan.php' => ['guru'],

    // Guru access sesuai use case
    'dashboard_guru.php' => ['guru'],
    'catat_pelanggaran.php' => ['guru'],
    'proses_simpan_pelanggaran.php' => ['guru'],
    'tindakan_guru.php' => ['guru'],
    'proses_tindakan.php' => ['admin', 'guru'],
    'laporan.php' => ['admin'],
    'laporan_guru.php' => ['guru'],
    'riwayat.php' => ['admin'],
    'riwayat_guru.php' => ['guru'],
    'rekap_poin.php' => ['guru'],

    // Halaman lain tetap admin
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
    'kelola_pelanggaran_kategori.php' => ['admin'],
    'tambah_pelanggaran_kategori.php' => ['admin'],
    'edit_pelanggaran_kategori.php' => ['admin'],
    'hapus_pelanggaran_kategori.php' => ['admin'],
    'proses_tambah_pelanggaran_kategori.php' => ['admin'],
    'proses_edit_pelanggaran.php' => ['admin'],
    'kelola_guru.php' => ['admin'],
    'tambah_guru.php' => ['admin'],
    'edit_guru.php' => ['admin'],
    'hapus_guru.php' => ['admin'],
    'proses_tambah_guru.php' => ['admin'],
    'proses_edit_guru.php' => ['admin'],
];

$izin_halaman = $hak_akses_halaman[$halaman] ?? ['admin'];
if ($halaman === 'catat_pelanggaran.php' && $is_guru_subdir) {
    $izin_halaman = ['guru'];
}
if ($halaman === 'proses_simpan_pelanggaran.php' && $is_guru_subdir) {
    $izin_halaman = ['guru'];
}

batasi_akses($izin_halaman);

$aturan_batas_input = [
    'tambah_siswa.php' => ['nis' => ['max' => 30], 'nisn' => ['min' => 10, 'max' => 10], 'nama' => ['max' => 150], 'alamat' => ['max' => 1000], 'tanggal_lahir' => ['max' => 10]],
    'edit_siswa.php' => ['nis' => ['max' => 30], 'nisn' => ['min' => 10, 'max' => 10], 'nama' => ['max' => 150], 'alamat' => ['max' => 1000], 'tanggal_lahir' => ['max' => 10]],
    'proses_tambah_siswa.php' => ['nis' => ['max' => 30], 'nisn' => ['min' => 10, 'max' => 10], 'nama' => ['max' => 150], 'alamat' => ['max' => 1000], 'tanggal_lahir' => ['max' => 10]],
    'proses_edit_siswa.php' => ['nis' => ['max' => 30], 'nisn' => ['min' => 10, 'max' => 10], 'nama' => ['max' => 150], 'alamat' => ['max' => 1000], 'tanggal_lahir' => ['max' => 10]],
    'tambah_guru.php' => ['nip' => ['max' => 30], 'nama' => ['max' => 150], 'email' => ['max' => 150], 'password' => ['min' => 8, 'max' => 72]],
    'edit_guru.php' => ['nip' => ['max' => 30], 'nama' => ['max' => 150], 'email' => ['max' => 150]],
    'proses_tambah_guru.php' => ['nip' => ['max' => 30], 'nama' => ['max' => 150], 'email' => ['max' => 150], 'password' => ['min' => 8, 'max' => 72]],
    'proses_edit_guru.php' => ['nip' => ['max' => 30], 'nama' => ['max' => 150], 'email' => ['max' => 150]],
    'tambah_kelas.php' => ['nama' => ['max' => 100], 'tingkat' => ['max' => 20], 'jurusan' => ['max' => 100]],
    'edit_kelas.php' => ['nama' => ['max' => 100], 'tingkat' => ['max' => 20], 'jurusan' => ['max' => 100]],
    'proses_tambah_kelas.php' => ['nama' => ['max' => 100], 'tingkat' => ['max' => 20], 'jurusan' => ['max' => 100]],
    'proses_edit_kelas.php' => ['nama' => ['max' => 100], 'tingkat' => ['max' => 20], 'jurusan' => ['max' => 100]],
    'tambah_tahun_ajaran.php' => ['nama' => ['max' => 20], 'tanggal_mulai' => ['max' => 10], 'tanggal_selesai' => ['max' => 10]],
    'edit_tahun_ajaran.php' => ['nama' => ['max' => 20], 'tanggal_mulai' => ['max' => 10], 'tanggal_selesai' => ['max' => 10]],
    'proses_tambah_tahun_ajaran.php' => ['nama' => ['max' => 20], 'tanggal_mulai' => ['max' => 10], 'tanggal_selesai' => ['max' => 10]],
    'proses_edit_tahun_ajaran.php' => ['nama' => ['max' => 20], 'tanggal_mulai' => ['max' => 10], 'tanggal_selesai' => ['max' => 10]],
    'tambah_pelanggaran_kategori.php' => ['nama' => ['max' => 100], 'deskripsi' => ['max' => 1000]],
    'edit_pelanggaran_kategori.php' => ['nama' => ['max' => 100], 'deskripsi' => ['max' => 1000]],
    'proses_tambah_pelanggaran_kategori.php' => ['nama' => ['max' => 100], 'deskripsi' => ['max' => 1000]],
    'proses_edit_pelanggaran.php' => ['nama' => ['max' => 100], 'deskripsi' => ['max' => 1000]],
    'proses_tambah_kategori.php' => ['nama' => ['max' => 100], 'deskripsi' => ['max' => 1000]],
    'tambah_walikelas.php' => ['tanggal_mulai' => ['max' => 10], 'tanggal_selesai' => ['max' => 10]],
    'edit_walikelas.php' => ['tanggal_mulai' => ['max' => 10], 'tanggal_selesai' => ['max' => 10]],
    'proses_tambah_walikelas.php' => ['tanggal_mulai' => ['max' => 10], 'tanggal_selesai' => ['max' => 10]],
    'proses_edit_walikelas.php' => ['tanggal_mulai' => ['max' => 10], 'tanggal_selesai' => ['max' => 10]],
    'penempatan_siswa.php' => ['tanggal_mulai' => ['max' => 10], 'tanggal_selesai' => ['max' => 10]],
    'proses_simpan_penempatan.php' => ['tanggal_mulai' => ['max' => 10], 'tanggal_selesai' => ['max' => 10]],
    'catat_pelanggaran.php' => ['tanggal' => ['max' => 10], 'keterangan' => ['max' => 1000], 'tindakan' => ['max' => 1000], 'status' => ['max' => 30]],
    'proses_simpan_pelanggaran.php' => ['tanggal' => ['max' => 10], 'keterangan' => ['max' => 1000], 'tindakan' => ['max' => 1000], 'status' => ['max' => 30]],
    'tindakan_guru.php' => ['tindakan' => ['max' => 1000], 'status' => ['max' => 30]],
    'proses_tindakan.php' => ['tindakan' => ['max' => 1000], 'status' => ['max' => 30]],
    'laporan.php' => ['mulai' => ['max' => 10], 'sampai' => ['max' => 10], 'status' => ['max' => 30]],
    'laporan_guru.php' => ['mulai' => ['max' => 10], 'sampai' => ['max' => 10], 'status' => ['max' => 30]],
    'cetak_laporan.php' => ['mulai' => ['max' => 10], 'sampai' => ['max' => 10], 'status' => ['max' => 30]],
];

$aturan_batas_angka = [
    'tambah_siswa.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'edit_siswa.php' => ['id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_tambah_siswa.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_edit_siswa.php' => ['id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'tambah_guru.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'edit_guru.php' => ['status_aktif' => ['min' => 0, 'max' => 1], 'user_id' => ['min' => 1, 'max' => 2147483647]],
    'proses_tambah_guru.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_edit_guru.php' => ['status_aktif' => ['min' => 0, 'max' => 1], 'user_id' => ['min' => 1, 'max' => 2147483647]],
    'tambah_kelas.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'edit_kelas.php' => ['id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_tambah_kelas.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_edit_kelas.php' => ['id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'tambah_tahun_ajaran.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'edit_tahun_ajaran.php' => ['id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_tambah_tahun_ajaran.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_edit_tahun_ajaran.php' => ['id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'tambah_pelanggaran_kategori.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'edit_pelanggaran_kategori.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_tambah_pelanggaran_kategori.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_edit_pelanggaran.php' => ['id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_tambah_kategori.php' => ['status_aktif' => ['min' => 0, 'max' => 1]],
    'tambah_walikelas.php' => ['tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647], 'kelas_id' => ['min' => 1, 'max' => 2147483647], 'guru_id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'edit_walikelas.php' => ['tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647], 'kelas_id' => ['min' => 1, 'max' => 2147483647], 'guru_id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_tambah_walikelas.php' => ['tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647], 'kelas_id' => ['min' => 1, 'max' => 2147483647], 'guru_id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_edit_walikelas.php' => ['id' => ['min' => 1, 'max' => 2147483647], 'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647], 'kelas_id' => ['min' => 1, 'max' => 2147483647], 'guru_id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'penempatan_siswa.php' => ['siswa_id' => ['min' => 1, 'max' => 2147483647], 'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647], 'kelas_id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'proses_simpan_penempatan.php' => ['siswa_id' => ['min' => 1, 'max' => 2147483647], 'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647], 'kelas_id' => ['min' => 1, 'max' => 2147483647], 'status_aktif' => ['min' => 0, 'max' => 1]],
    'catat_pelanggaran.php' => ['siswa_id' => ['min' => 1, 'max' => 2147483647], 'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647], 'kelas_id' => ['min' => 1, 'max' => 2147483647], 'pelanggaran_id' => ['min' => 1, 'max' => 2147483647], 'pelanggaran_kategori_id' => ['min' => 1, 'max' => 2147483647], 'poin' => ['min' => 0, 'max' => 2147483647]],
    'proses_simpan_pelanggaran.php' => ['siswa_id' => ['min' => 1, 'max' => 2147483647], 'tahun_ajaran_id' => ['min' => 1, 'max' => 2147483647], 'kelas_id' => ['min' => 1, 'max' => 2147483647], 'pelanggaran_id' => ['min' => 1, 'max' => 2147483647], 'pelanggaran_kategori_id' => ['min' => 1, 'max' => 2147483647], 'poin' => ['min' => 0, 'max' => 2147483647]],
    'tindakan_guru.php' => [],
    'proses_tindakan.php' => ['id' => ['min' => 1, 'max' => 2147483647]],
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $batas_halaman = $aturan_batas_input[$halaman] ?? [];
    $batas_angka_halaman = $aturan_batas_angka[$halaman] ?? [];
    $pesan_batas = '';

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
            : (preg_match_all('/./us', $value, $matches) ?: strlen($value));

        if (isset($rule['min']) && $character_count < $rule['min']) {
            $pesan_batas = 'Kolom ' . $field . ' minimal ' . $rule['min'] . ' karakter.';
            break;
        }
        if (isset($rule['max']) && $character_count > $rule['max']) {
            $pesan_batas = 'Kolom ' . $field . ' maksimal ' . $rule['max'] . ' karakter.';
            break;
        }
    }

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
            if ($value === false || $value < $rule['min'] || $value > $rule['max']) {
                $pesan_batas = 'Nilai ' . $field . ' harus antara ' . $rule['min'] . ' dan ' . $rule['max'] . '.';
                break;
            }
        }
    }

    if ($pesan_batas !== '') {
        http_response_code(422);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html lang="id"><meta charset="UTF-8"><title>Input tidak valid</title>';
        echo '<body style="font-family:sans-serif;padding:2rem"><h1>Input tidak valid</h1><p>';
        echo htmlspecialchars($pesan_batas, ENT_QUOTES, 'UTF-8');
        echo '</p><button type="button" onclick="history.back()">Kembali ke formulir</button></body></html>';
        exit;
    }
}
?>