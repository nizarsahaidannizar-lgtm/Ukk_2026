<?php
// hapus_walikelas.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: kelola_walikelas.php');
    exit();
}

// 1. Ambil data wali kelas sebelum dihapus (menggunakan t_wali_kelas)
$sql = "SELECT
            w.id,
            w.kelas_id,
            k.nama AS nama_kelas
        FROM t_wali_kelas w
        LEFT JOIN t_kelas k ON w.kelas_id = k.id
        WHERE w.id = ?";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) {
    $_SESSION['pesan_error'] = "Data wali kelas tidak ditemukan!";
    header('Location: kelola_walikelas.php');
    exit();
}

// 2. Hapus data wali kelas
$nama_kelas = $data['nama_kelas'] ?? 'Tidak diketahui';

$hapus = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_wali_kelas WHERE id = ?"
);
mysqli_stmt_bind_param($hapus, "i", $id);

try {
    if (mysqli_stmt_execute($hapus)) {
        $_SESSION['pesan_sukses'] = "Data wali kelas untuk kelas <strong>" . htmlspecialchars($nama_kelas) . "</strong> berhasil dihapus!";
    } else {
        $_SESSION['pesan_error'] = "Gagal menghapus data wali kelas!";
    }
} catch (mysqli_sql_exception $e) {
    $_SESSION['pesan_error'] = "Data wali kelas untuk kelas <strong>" . htmlspecialchars($nama_kelas) . "</strong> tidak dapat dihapus karena masih terikat dengan data lain!";
}

mysqli_stmt_close($hapus);

// Redirect kembali ke kelola_walikelas.php
header('Location: kelola_walikelas.php');
exit();
?>