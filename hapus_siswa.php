<?php
// hapus_siswa.php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // 1. Ambil nama siswa
    $stmt_cek = mysqli_prepare($koneksi, "SELECT nama FROM t_siswa WHERE id = ?");
    mysqli_stmt_bind_param($stmt_cek, "i", $id);
    mysqli_stmt_execute($stmt_cek);
    $res_cek = mysqli_stmt_get_result($stmt_cek);

    if ($data = mysqli_fetch_assoc($res_cek)) {
        $nama_siswa = $data['nama'];

        // 2. Hapus dulu data di tabel penempatan kelas (t_kelas_siswa) jika ada
        $stmt_relasi = mysqli_prepare($koneksi, "DELETE FROM t_kelas_siswa WHERE siswa_id = ?");
        mysqli_stmt_bind_param($stmt_relasi, "i", $id);
        mysqli_stmt_execute($stmt_relasi);
        mysqli_stmt_close($stmt_relasi);

        // 3. Hapus data utama siswa
        $stmt_del = mysqli_prepare($koneksi, "DELETE FROM t_siswa WHERE id = ?");
        mysqli_stmt_bind_param($stmt_del, "i", $id);

        if (mysqli_stmt_execute($stmt_del)) {
            $_SESSION['pesan_sukses'] = "Data siswa <strong>" . htmlspecialchars($nama_siswa) . "</strong> beserta riwayat kelasnya berhasil dihapus!";
        } else {
            $_SESSION['pesan_error'] = "Gagal menghapus data siswa!";
        }

        mysqli_stmt_close($stmt_del);
    } else {
        $_SESSION['pesan_error'] = "Data siswa tidak ditemukan!";
    }

    mysqli_stmt_close($stmt_cek);
}

header('Location: kelola_siswa.php');
exit();
?>