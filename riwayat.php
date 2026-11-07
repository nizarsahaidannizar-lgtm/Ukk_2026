
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Admin dan guru boleh mengakses riwayat
batasi_akses(['admin', 'guru']);

$role = $_SESSION['role'] ?? '';
$sql = "";
$hasil = false;

// ======================================
// MENENTUKAN HAK AKSES RIWAYAT
// ======================================

if ($role === 'admin') {

    // ADMIN: Melihat seluruh riwayat guru
    $sql = "SELECT
                ps.id,
                ps.tanggal,
                ps.poin,
                ps.tindakan,
                ps.status,
                ps.updated_at,
                COALESCE(s.nama, ps.nama_siswa, 'Siswa Tidak Ditemukan') AS nama_siswa,
                COALESCE(k.nama, ps.nama_kelas, '-') AS nama_kelas,
                COALESCE(p.nama, ps.nama_pelanggaran, '-') AS nama_pelanggaran
            FROM t_pelanggaran_siswa ps
            LEFT JOIN t_siswa s ON ps.siswa_id = s.id
            LEFT JOIN t_kelas k ON ps.kelas_id = k.id
            LEFT JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
            ORDER BY ps.updated_at DESC, ps.id DESC";

    $hasil = mysqli_query($koneksi, $sql);

} elseif ($role === 'guru') {

    // GURU: Mengambil ID guru berdasarkan akun login
    $id_user = $_SESSION['id_user'] ?? 0;

    if (!$id_user) {
        die("ID akun guru tidak ditemukan. Silakan login kembali.");
    }

    $stmtGuru = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_guru WHERE user_id = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmtGuru, "i", $id_user);
    mysqli_stmt_execute($stmtGuru);

    $resultGuru = mysqli_stmt_get_result($stmtGuru);
    $dataGuru = mysqli_fetch_assoc($resultGuru);

    mysqli_stmt_close($stmtGuru);

    if (!$dataGuru) {
        die("Data guru tidak ditemukan.");
    }

    $guru_id = (int) $dataGuru['id'];

    // HANYA MENAMPILKAN RIWAYAT YANG DICATAT GURU TERSEBUT
    $sql = "SELECT
                ps.id,
                ps.tanggal,
                ps.poin,
                ps.tindakan,
                ps.status,
                ps.updated_at,
                COALESCE(s.nama, ps.nama_siswa, 'Siswa Tidak Ditemukan') AS nama_siswa,
                COALESCE(k.nama, ps.nama_kelas, '-') AS nama_kelas,
                COALESCE(p.nama, ps.nama_pelanggaran, '-') AS nama_pelanggaran
            FROM t_pelanggaran_siswa ps
            LEFT JOIN t_siswa s ON ps.siswa_id = s.id
            LEFT JOIN t_kelas k ON ps.kelas_id = k.id
            LEFT JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
            WHERE ps.guru_id = ?
            ORDER BY ps.updated_at DESC, ps.id DESC";

    $stmt = mysqli_prepare($koneksi, $sql);

    mysqli_stmt_bind_param($stmt, "i", $guru_id);
    mysqli_stmt_execute($stmt);

    $hasil = mysqli_stmt_get_result($stmt);

} else {
    die("Akses tidak diizinkan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Riwayat Pelanggaran - SIP</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <header class="admin-page-header mb-4">
                <h1>Riwayat Pelanggaran</h1>

                <p>
                    <?php if ($role === 'admin'): ?>
                        Menampilkan seluruh riwayat pelanggaran yang dicatat oleh semua guru.
                    <?php else: ?>
                        Menampilkan riwayat pelanggaran yang kamu catat sendiri.
                    <?php endif; ?>
                </p>
            </header>

            <!-- CARD TABEL -->
            <div class="card border-0 shadow-sm rounded-3 mb-5">

                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold text-dark">
                        <i class="fas fa-history me-2"></i>
                        Data Catatan Riwayat Pelanggaran
                    </h5>
                </div>

                <div class="card-body p-3">

                    <div class="table-responsive">

                        <table id="tabelRiwayat"
                               class="table table-striped table-hover align-middle mb-0 w-100 history-table">

                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Siswa</th>
                                    <th>Kelas</th>
                                    <th>Pelanggaran</th>
                                    <th>Poin</th>
                                    <th>Tindakan</th>
                                    <th>Status</th>
                                    <th>Terakhir Diubah</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if ($hasil && mysqli_num_rows($hasil) > 0): ?>

                                    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>

                                        <tr>

                                            <td class="text-center text-nowrap">
                                                <?= htmlspecialchars($row['tanggal'] ?? '-') ?>
                                            </td>

                                            <td class="fw-semibold text-dark">
                                                <?= htmlspecialchars($row['nama_siswa']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['nama_kelas']) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['nama_pelanggaran']) ?>
                                            </td>

                                            <td class="text-center fw-bold">
                                                <?= (int)$row['poin'] ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($row['tindakan'] ?? '-') ?>
                                            </td>

                                            <td class="text-center">
                                                <span class="badge bg-secondary px-2 py-1">
                                                    <?= htmlspecialchars($row['status'] ?? '-') ?>
                                                </span>
                                            </td>

                                            <td class="text-center small text-muted">
                                                <?= htmlspecialchars($row['updated_at'] ?? '-') ?>
                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                    <?php if ($hasil && mysqli_num_rows($hasil) == 0): ?>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Belum ada riwayat pelanggaran yang tercatat.
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </main>
    </div>
</div>

<!-- JAVASCRIPT -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {

    $('#tabelRiwayat').DataTable({

        autoWidth: false,

        columnDefs: [
            { targets: 0, width: "10%" },
            { targets: 1, width: "14%" },
            { targets: 2, width: "9%" },
            { targets: 3, width: "18%" },
            { targets: 4, width: "6%", className: "text-center" },
            { targets: 5, width: "17%" },
            { targets: 6, width: "12%", className: "text-center" },
            { targets: 7, width: "14%", className: "text-center" }
        ],

        dom:
            "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

        language: {
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Data riwayat tidak ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 data",
            infoFiltered: "(disaring dari total _MAX_ data)",
            search: "Cari:",

            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },

        pageLength: 10,
        order: []

    });

});
</script>

</body>
</html>

<?php
// Menutup statement khusus guru
if (isset($stmt) && $stmt) {
    mysqli_stmt_close($stmt);
}
?>
