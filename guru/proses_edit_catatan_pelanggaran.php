<?php
include '../includes/cek_session.php';
include '../config/koneksi.php';
include '../includes/akses_pelanggaran.php';

$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks, 'ps');
$kembali = static function (int $id = 0): void {
    header('Location: ' . ($id > 0 ? 'edit_catatan_pelanggaran.php?id=' . $id : 'catat_pelanggaran.php'));
    exit;
};

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $kembali();
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$siswa_id = filter_input(INPUT_POST, 'siswa_id', FILTER_VALIDATE_INT);
$tahun_id = filter_input(INPUT_POST, 'tahun_ajaran_id', FILTER_VALIDATE_INT);
$kelas_id = filter_input(INPUT_POST, 'kelas_id', FILTER_VALIDATE_INT);
$pelanggaran_id = filter_input(INPUT_POST, 'pelanggaran_id', FILTER_VALIDATE_INT);
$tanggal = $_POST['tanggal'] ?? '';
$keterangan = trim($_POST['keterangan'] ?? '');
$tindakan = trim($_POST['tindakan'] ?? '');
$status = $_POST['status'] ?? '';
$status_diizinkan = ['tercatat', 'Ditindaklanjuti', 'Proses', 'Selesai'];

if (
    !$id || !$siswa_id || !$tahun_id || !$kelas_id || !$pelanggaran_id ||
    !is_string($tanggal) || !in_array($status, $status_diizinkan, true)
) {
    $_SESSION['pesan_error'] = 'Data catatan pelanggaran tidak valid.';
    $kembali($id ?: 0);
}

if ($konteks['is_wali'] && !in_array((int)$kelas_id, $konteks['kelas_ids'], true)) {
    $_SESSION['pesan_error'] = 'Kelas yang dipilih bukan kelas wali yang menjadi tanggung jawab Anda.';
    $kembali($id);
}

$format_tanggal = DateTime::createFromFormat('!Y-m-d', $tanggal);
if (!$format_tanggal || $format_tanggal->format('Y-m-d') !== $tanggal) {
    $_SESSION['pesan_error'] = 'Tanggal pelanggaran tidak valid.';
    $kembali($id);
}

$stmt = mysqli_prepare($koneksi, "SELECT ps.id, ps.siswa_id, ps.tahun_ajaran_id, ps.kelas_id, ps.pelanggaran_id FROM t_pelanggaran_siswa ps WHERE ps.id = ? AND $lingkup");
if (!$stmt) {
    error_log('Gagal menyiapkan pemeriksaan akses edit catatan: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Catatan pelanggaran gagal diperbarui.';
    $kembali();
}
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$boleh_edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$boleh_edit) {
    $_SESSION['pesan_error'] = 'Catatan tidak ditemukan atau Anda tidak memiliki akses.';
    $kembali();
}

$pilihan = [];
$query_pilihan = [
    'siswa' => ['SELECT nama FROM t_siswa WHERE id = ? AND (status_aktif = 1 OR id = ?)', $siswa_id, (int)$boleh_edit['siswa_id']],
    'tahun' => ['SELECT nama FROM t_tahun_ajaran WHERE id = ? AND (status_aktif = 1 OR id = ?)', $tahun_id, (int)$boleh_edit['tahun_ajaran_id']],
    'kelas' => ['SELECT nama FROM t_kelas WHERE id = ? AND (status_aktif = 1 OR id = ?)', $kelas_id, (int)$boleh_edit['kelas_id']],
    'pelanggaran' => ['SELECT nama, poin, pelanggaran_kategori_id FROM t_pelanggaran WHERE id = ? AND (status_aktif = 1 OR id = ?)', $pelanggaran_id, (int)$boleh_edit['pelanggaran_id']],
];

foreach ($query_pilihan as $nama => [$sql, $nilai, $nilai_asal]) {
    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) {
        error_log('Gagal menyiapkan data pilihan edit catatan: ' . mysqli_error($koneksi));
        $_SESSION['pesan_error'] = 'Data pilihan tidak dapat diperiksa.';
        $kembali($id);
    }
    mysqli_stmt_bind_param($stmt, 'ii', $nilai, $nilai_asal);
    mysqli_stmt_execute($stmt);
    $pilihan[$nama] = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}

if (!$pilihan['siswa'] || !$pilihan['tahun'] || !$pilihan['kelas'] || !$pilihan['pelanggaran']) {
    $_SESSION['pesan_error'] = 'Pilihan data tidak ditemukan atau sudah tidak aktif.';
    $kembali($id);
}

$nama_siswa = $pilihan['siswa']['nama'];
$nama_kelas = $pilihan['kelas']['nama'];
$nama_pelanggaran = $pilihan['pelanggaran']['nama'];
$kategori_id = (int)$pilihan['pelanggaran']['pelanggaran_kategori_id'];
$poin = (int)$pilihan['pelanggaran']['poin'];

$sql = "UPDATE t_pelanggaran_siswa ps
        SET ps.tahun_ajaran_id = ?, ps.siswa_id = ?, ps.nama_siswa = ?,
            ps.kelas_id = ?, ps.nama_kelas = ?, ps.pelanggaran_id = ?,
            ps.nama_pelanggaran = ?, ps.pelanggaran_kategori_id = ?,
            ps.tanggal = ?, ps.keterangan = ?, ps.poin = ?, ps.tindakan = ?,
            ps.status = ?, ps.updated_at = CURRENT_TIMESTAMP
        WHERE ps.id = ? AND $lingkup";
$stmt = mysqli_prepare($koneksi, $sql);
if (!$stmt) {
    error_log('Gagal menyiapkan pembaruan catatan pelanggaran: ' . mysqli_error($koneksi));
    $_SESSION['pesan_error'] = 'Catatan pelanggaran gagal diperbarui.';
    $kembali($id);
}
mysqli_stmt_bind_param(
    $stmt,
    'iisisisississi',
    $tahun_id,
    $siswa_id,
    $nama_siswa,
    $kelas_id,
    $nama_kelas,
    $pelanggaran_id,
    $nama_pelanggaran,
    $kategori_id,
    $tanggal,
    $keterangan,
    $poin,
    $tindakan,
    $status,
    $id
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['pesan_sukses'] = 'Catatan pelanggaran berhasil diperbarui.';
    mysqli_stmt_close($stmt);
    $kembali();
}

error_log('Gagal memperbarui catatan pelanggaran: ' . mysqli_stmt_error($stmt));
mysqli_stmt_close($stmt);
$_SESSION['pesan_error'] = 'Catatan pelanggaran gagal diperbarui.';
$kembali($id);
