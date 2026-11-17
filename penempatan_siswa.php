
<?php
// penempatan_siswa.php

include 'includes/cek_session.php';
include 'config/koneksi.php';

batasi_akses(['admin']);

// ========================================
// AMBIL NOTIFIKASI
// ========================================

$pesan_sukses = $_SESSION['pesan_sukses'] ?? '';
$pesan_error = $_SESSION['pesan_error'] ?? '';

if (isset($_SESSION['pesan'])) {
    if (($_SESSION['tipe'] ?? '') === 'success') {
        $pesan_sukses = $_SESSION['pesan'];
    } else {
        $pesan_error = $_SESSION['pesan'];
    }

    unset($_SESSION['pesan'], $_SESSION['tipe']);
}

unset($_SESSION['pesan_sukses'], $_SESSION['pesan_error']);

// ========================================
// AMBIL TAHUN AJARAN AKTIF
// ========================================

$query_tahun_aktif = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY tanggal_mulai DESC, id DESC
    LIMIT 1
");

if (!$query_tahun_aktif) {
    die('Gagal mengambil tahun ajaran: ' . mysqli_error($koneksi));
}

$tahun_aktif = mysqli_fetch_assoc($query_tahun_aktif);

$tahun_ajaran_aktif_id = (int)($tahun_aktif['id'] ?? 0);
$nama_tahun_aktif = $tahun_aktif['nama'] ?? 'Belum ada tahun ajaran aktif';

// ========================================
// AMBIL SEMUA DAFTAR SISWA
// ========================================

$siswa = mysqli_query($koneksi, "
    SELECT id, nis, nama
    FROM t_siswa
    ORDER BY nama ASC
");

if (!$siswa) {
    die('Gagal mengambil data siswa: ' . mysqli_error($koneksi));
}

// ========================================
// AMBIL DAFTAR KELAS
// ========================================

$kelas = mysqli_query($koneksi, "
    SELECT id, nama, tingkat, jurusan
    FROM t_kelas
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

if (!$kelas) {
    die('Gagal mengambil data kelas: ' . mysqli_error($koneksi));
}

// ========================================
// AMBIL SEMUA SISWA DAN PENEMPATANNYA
// BERDASARKAN TAHUN AJARAN AKTIF
// ========================================

$hasil = false;

if ($tahun_ajaran_aktif_id > 0) {

    $sql = "
        SELECT
            s.id AS siswa_id,
            s.nis,
            s.nama AS nama_siswa,
            s.status_aktif AS status_siswa,

            ks.id AS id_penempatan,
            ks.tanggal_mulai,
            ks.tanggal_selesai,
            ks.status_aktif AS status_penempatan,

            ta.nama AS tahun_ajaran,
            k.nama AS nama_kelas,
            k.tingkat,
            k.jurusan

        FROM t_siswa s

        LEFT JOIN t_kelas_siswa ks
            ON s.id = ks.siswa_id
            AND ks.tahun_ajaran_id = $tahun_ajaran_aktif_id

        LEFT JOIN t_tahun_ajaran ta
            ON ks.tahun_ajaran_id = ta.id

        LEFT JOIN t_kelas k
            ON ks.kelas_id = k.id

        ORDER BY s.nama ASC
    ";

    $hasil = mysqli_query($koneksi, $sql);

    if (!$hasil) {
        die('Query penempatan gagal: ' . mysqli_error($koneksi));
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Penempatan Siswa - SIP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <link rel="stylesheet" href="layout/style.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">

            <header class="mb-4">
                <h2 class="fw-normal">Penempatan Siswa</h2>
                <p class="text-muted">
                    Tetapkan siswa ke kelas sesuai tahun ajaran aktif.
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

                                <select name="siswa_id" id="pilihSiswa" class="form-select" required>
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

                            <!-- TAHUN AJARAN AKTIF -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tahun Ajaran</label>

                                <input type="text"
                                       class="form-control"
                                       value="<?= htmlspecialchars($nama_tahun_aktif); ?> (Aktif)"
                                       readonly>

                                <input type="hidden"
                                       name="tahun_ajaran_id"
                                       value="<?= $tahun_ajaran_aktif_id; ?>">

                                <small class="text-muted">
                                    Tahun ajaran otomatis mengikuti tahun ajaran aktif.
                                </small>
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
                                <button type="submit"
                                        class="btn btn-primary px-4"
                                        <?= $tahun_ajaran_aktif_id <= 0 ? 'disabled' : ''; ?>>
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

                    <small class="text-muted">
                        Tahun Ajaran: <?= htmlspecialchars($nama_tahun_aktif); ?>
                    </small>
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

                                if ($hasil && mysqli_num_rows($hasil) > 0):
                                    while ($row = mysqli_fetch_assoc($hasil)):
                                ?>

                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>

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

                                            <?php if (empty($row['id_penempatan'])): ?>

                                                <span class="badge bg-warning text-dark">
                                                    Belum ditempatkan
                                                </span>

                                            <?php elseif (
                                                (int)$row['status_siswa'] === 1 &&
                                                (int)$row['status_penempatan'] === 1
                                            ): ?>

                                                <span class="badge bg-success">
                                                    Aktif
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-danger">
                                                    Tidak Aktif
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td class="text-center text-nowrap">

                                            <?php if (!empty($row['id_penempatan'])): ?>

                                                <a href="edit_penempatan.php?id=<?= (int)$row['id_penempatan']; ?>"
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
                                                           value="<?= (int)$row['id_penempatan']; ?>">

                                                    <button type="submit"
                                                            class="btn btn-danger btn-sm"
                                                            title="Hapus">
                                                        <i class="fas fa-trash text-white"></i>
                                                    </button>

                                                </form>

                                            <?php else: ?>

                                                <span class="text-muted">
                                                    Belum ada penempatan
                                                </span>

                                            <?php endif; ?>

                                        </td>
                                    </tr>

                                <?php
                                    endwhile;
                                else:
                                ?>

                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <?php if ($tahun_ajaran_aktif_id <= 0): ?>
                                                Belum ada tahun ajaran aktif.
                                            <?php else: ?>
                                                Belum ada data siswa.
                                            <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {

    $('#pilihSiswa').select2({
        width: '100%',
        placeholder: '-- Cari nama atau NIS siswa --',
        allowClear: true
    });

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
