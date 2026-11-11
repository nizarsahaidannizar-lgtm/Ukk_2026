
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

// Pastikan menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: penempatan_siswa.php");
    exit;
}

// Ambil ID penempatan
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// Validasi ID
if (!$id) {
    $_SESSION['pesan_error'] = "ID penempatan siswa tidak valid!";
    header("Location: penempatan_siswa.php");
    exit;
}

// Siapkan query hapus
$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM t_kelas_siswa WHERE id = ?"
);

if (!$stmt) {
    $_SESSION['pesan_error'] = "Gagal menyiapkan proses hapus!";
    header("Location: penempatan_siswa.php");
    exit;
}

// Jalankan proses hapus
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $_SESSION['pesan_sukses'] =
            "Data penempatan siswa berhasil dihapus!";
    } else {
        $_SESSION['pesan_error'] =
            "Data penempatan siswa tidak ditemukan!";
    }

} else {
    $_SESSION['pesan_error'] =
        "Data gagal dihapus: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);

header("Location: penempatan_siswa.php");
exit;
?>
