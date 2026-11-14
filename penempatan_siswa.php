
<?php
// penempatan_siswa.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

// AMBIL NOTIFIKASI
$pesan_sukses = $_SESSION['pesan_sukses'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';

// NOTIFIKASI DARI PROSES EDIT
if (isset($_SESSION['pesan'])) {

    if (($_SESSION['tipe'] ?? '') === 'success') {
        $pesan_sukses = $_SESSION['pesan'];
    } else {
        $pesan_error = $_SESSION['pesan'];
    }

    unset($_SESSION['pesan']);
    unset($_SESSION['tipe']);
}

unset($_SESSION['pesan_sukses']);
unset($_SESSION['pesan_error']);

// AMBIL DAFTAR SISWA
$siswa = mysqli_query($koneksi, "
    SELECT id, nis, nama
    FROM t_siswa
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

// AMBIL TAHUN AJARAN
$tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    ORDER BY nama ASC
");

// AMBIL KELAS
$kelas = mysqli_query($koneksi, "
    SELECT id, nama, tingkat, jurusan
    FROM t_kelas
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

// AMBIL DATA PENEMPATAN
$sql = "
    SELECT
        ks.id,
        ks.siswa_id,
        ks.tahun_ajaran_id,
        ks.kelas_id,
        ks.tanggal_mulai,
        ks.tanggal_selesai,
        s.nis,
        s.nama AS nama_siswa,
        ta.nama AS tahun_ajaran,
        k.nama AS nama_kelas,
        k.tingkat,
        k.jurusan,
        ks.status_aktif
    FROM t_kelas_siswa ks
    LEFT JOIN t_siswa s
        ON ks.siswa_id = s.id
    LEFT JOIN t_tahun_ajaran ta
        ON ks.tahun_ajaran_id = ta.id
    LEFT JOIN t_kelas k
        ON ks.kelas_id = k.id
    ORDER BY ks.id DESC
";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Query penempatan gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Penempatan Siswa - SIP</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DataTables Bootstrap 5 -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <link rel="stylesheet" href="layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <!-- HEADER -->
            <header class="mb-4">
                <h2 class="fw-normal">Penempatan Siswa</h2>
                <p class="text-muted">
                    Tetapkan siswa ke kelas sesuai tahun ajaran.
                </p>
            </header>

            <!-- NOTIFIKASI SUKSES -->
            <?php if ($pesan_sukses !== ''): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($pesan_sukses); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- NOTIFIKASI ERROR -->
            <?php if ($pesan_error !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($pesan_error); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- FORM PENEMPATAN -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">

                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-user-plus me-2"></i>
                        Form Penempatan Siswa
                    </h5>
                </div>

                <div class="card-body p-4">

                    <form action="proses_simpan_penempatan.php" method="POST">

                        <div class="row g-3">

                            <!-- SISWA -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Siswa</label>

                                <select name="siswa_id" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>

                                    <?php if ($siswa): ?>
                                        <?php while ($s = mysqli_fetch_assoc($siswa)): ?>
                                            <option value="<?= (int)$s['id']; ?>">
                                                <?= htmlspecialchars($s['nis'] . ' - ' . $s['nama']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>

                                </select>
                            </div>

                            <!-- TAHUN AJARAN -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Ajaran</label>

                                <select name="tahun_ajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>

                                    <?php if ($tahun && mysqli_num_rows($tahun) > 0): ?>
                                        <?php while ($ta = mysqli_fetch_assoc($tahun)): ?>
                                            <option value="<?= (int)$ta['id']; ?>">
                                                <?= htmlspecialchars($ta['nama']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <option value="" disabled>
                                            Data tahun ajaran belum tersedia
                                        </option>
                                    <?php endif; ?>

                                </select>
                            </div>

                            <!-- KELAS -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kelas</label>

                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>

                                    <?php if ($kelas): ?>
                                        <?php while ($k = mysqli_fetch_assoc($kelas)): ?>
                                            <option value="<?= (int)$k['id']; ?>">
                                                <?= htmlspecialchars(
                                                    $k['nama'] . ' - Tingkat ' .
                                                    $k['tingkat'] . ' - ' .
                                                    $k['jurusan']
                                                ); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>

                                </select>
                            </div>

                            <!-- STATUS -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Status</label>

                                <select name="status_aktif" class="form-select" required>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>

                            <!-- TANGGAL MULAI -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Mulai</label>

                                <input type="date"
                                       name="tanggal_mulai"
                                       class="form-control"
                                       required>
                            </div>

                            <!-- TANGGAL SELESAI -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tanggal Selesai</label>

                                <input type="date"
                                       name="tanggal_selesai"
                                       class="form-control">
                            </div>

                            <!-- SIMPAN -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-1"></i>
                                    Simpan Penempatan
                                </button>
                            </div>

                        </div>

                    </form>

                </div>
            </div>

            <!-- TABEL PENEMPATAN -->
            <div class="card shadow-sm border-0 rounded-3">

                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-table me-2"></i>
                        Data Penempatan Siswa
                    </h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table id="tabelPenempatan"
                               class="table table-striped table-bordered table-hover align-middle w-100">

                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No.</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Kelas</th>
                                    <th>Jurusan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php
                                $no = 1;

                                if (mysqli_num_rows($hasil) > 0):
                                    while ($row = mysqli_fetch_assoc($hasil)):
                                ?>

                                    <tr>

                                        <td class="text-center">
                                            <?= $no++; ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nis'] ?? '-'); ?>
                                        </td>

                                        <td class="fw-semibold">
                                            <?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?>
                                        </td>

                                        <td class="text-center">
                                            <?= htmlspecialchars($row['tahun_ajaran'] ?? '-'); ?>
                                        </td>

                                        <td class="text-center">
                                            <?= htmlspecialchars($row['nama_kelas'] ?? '-'); ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['jurusan'] ?? '-'); ?>
                                        </td>

                                        

                                        <td class="text-center">


                                            <?php if ((int)$row['status_aktif'] === 1): ?>
                                                <span class="badge bg-success px-2 py-1 rounded">
                                                    Aktif
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger px-2 py-1 rounded">
                                                    Tidak Aktif
                                                </span>
                                            <?php endif; ?>

                                        </td>

                                        <!-- AKSI -->
                                        <td class="text-center text-nowrap">

                                            <a href="edit_penempatan.php?id=<?= (int)$row['id']; ?>"
                                               class="btn btn-warning btn-sm"
                                               title="Edit">
                                                <i class="fas fa-edit text-white"></i>
                                            </a>

                                            <form action="proses_hapus_penempatan.php"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus data penempatan siswa ini?');">

                                                <input type="hidden"
                                                       name="id"
                                                       value="<?= (int)$row['id']; ?>">

                                                <button type="submit"
                                                        class="btn btn-danger btn-sm"
                                                        title="Hapus">
                                                    <i class="fas fa-trash text-white"></i>
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                <?php
                                    endwhile;
                                else:
                                ?>

                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            Belum ada data penempatan siswa.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- JAVASCRIPT -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {

    $('#tabelPenempatan').DataTable({

        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [[0, 'asc']],
        autoWidth: false,

        columnDefs: [
            {
                targets: [0, 7],
                orderable: false,
                searchable: false
            }
        ],

        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data penempatan",

            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        }

    });

});
</script>

</body>
</html>
