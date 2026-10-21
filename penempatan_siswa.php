<?php
// penempatan_siswa.php
include 'includes/cek_session.php'; // Pakai '../includes/cek_session.php' jika di dalam folder guru/
include 'config/koneksi.php';

// HANYA ROLE ADMIN YANG BISA AKSES
batasi_akses(['admin']);

// Ambil daftar siswa
$siswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama
     FROM t_siswa
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

// Ambil daftar tahun ajaran
$tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     WHERE status_aktif = 1
     ORDER BY id DESC"
);

// Ambil daftar kelas
$kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan
     FROM t_kelas
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

// Ambil data penempatan siswa
$sql = "SELECT
            ks.id,
            s.nis,
            s.nama AS nama_siswa,
            ta.nama AS tahun_ajaran,
            k.nama AS nama_kelas,
            k.tingkat,
            k.jurusan,
            ks.tanggal_mulai,
            ks.tanggal_selesai,
            ks.status_aktif
        FROM t_kelas_siswa ks
        LEFT JOIN t_siswa s ON ks.siswa_id = s.id
        LEFT JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
        LEFT JOIN t_kelas k ON ks.kelas_id = k.id
        ORDER BY ks.id DESC";

$hasil = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penempatan Siswa - SIP</title>
    
    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/style.css">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <?php include 'layout/sidebar.php'; ?>

            <!-- KONTEN UTAMA -->
            <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
                
                <!-- HEADER BRANDING SIP & LOGO SEKOLAH -->
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="bg-white p-3 rounded-3 shadow-sm me-3 border">
                        <i class="fa-solid fa-school fs-2 text-dark"></i>
                    </div>
                    <div>
                        <h1 class="h2 fw-bold text-dark m-0">SIP</h1>
                        <p class="text-muted m-0 small">Sistem Informasi Pelanggaran Siswa</p>
                    </div>
                </div>

                <!-- TOMBOL KEMBALI KE DASHBOARD -->
                <div class="mb-3">
                    <a href="dashboard.php" class="btn btn-secondary fw-semibold">
                        Kembali ke Dashboard
                    </a>
                </div>

                <!-- ALERT NOTIFIKASI SUKSES / ERROR -->
                <?php if (isset($_SESSION['pesan_sukses'])) { ?>
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <?php
                        echo htmlspecialchars($_SESSION['pesan_sukses']);
                        unset($_SESSION['pesan_sukses']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } ?>

                <?php if (isset($_SESSION['pesan_error'])) { ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <?php
                        echo htmlspecialchars($_SESSION['pesan_error']);
                        unset($_SESSION['pesan_error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php } ?>

                <!-- CARD FORM PENEMPATAN SISWA -->
                <div class="card shadow-sm border-0 rounded-3 mb-5">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title m-0 fw-bold">Form Penempatan Siswa</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="proses_simpan_penempatan.php" method="POST">
                            <div class="row g-3">
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Siswa</label>
                                    <select name="siswa_id" class="form-select" required>
                                        <option value="">-- Pilih Siswa --</option>
                                        <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>
                                            <option value="<?php echo $s['id']; ?>">
                                                <?php echo htmlspecialchars($s['nis'] . ' - ' . $s['nama']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tahun Ajaran</label>
                                    <select name="tahun_ajaran_id" class="form-select" required>
                                        <option value="">-- Pilih Tahun Ajaran --</option>
                                        <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>
                                            <option value="<?php echo $ta['id']; ?>">
                                                <?php echo htmlspecialchars($ta['nama']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Kelas</label>
                                    <select name="kelas_id" class="form-select" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
                                            <option value="<?php echo $k['id']; ?>">
                                                <?php echo htmlspecialchars($k['nama'] . ' - Tingkat ' . $k['tingkat'] . ' - ' . $k['jurusan']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select name="status_aktif" class="form-select" required>
                                        <option value="1">Aktif</option>
                                        <option value="0">Tidak Aktif</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tanggal Mulai</label>
                                    <input type="date" name="tanggal_mulai" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Tanggal Selesai</label>
                                    <input type="date" name="tanggal_selesai" class="form-control" required>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                        Simpan Penempatan
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                <!-- CARD TABEL DATA PENEMPATAN SISWA -->
                <div class="card shadow-sm border-0 rounded-3 mb-5">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title m-0 fw-bold">Data Penempatan Siswa</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table id="tabelPenempatan" class="table table-striped table-bordered table-hover align-middle w-100">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No.</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Kelas</th>
                                        <th>Tingkat</th>
                                        <th>Jurusan</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($hasil)) {
                                    ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($row['nis'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_siswa'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['tahun_ajaran'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_kelas'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['tingkat'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['jurusan'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['tanggal_mulai'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['tanggal_selesai'] ?? '-'); ?></td>
                                            <td>
                                                <?php if ($row['status_aktif'] == 1) { ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php } else { ?>
                                                    <span class="badge bg-danger">Tidak Aktif</span>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>

    <!-- DataTables Bootstrap 5 JS -->
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function () {
        $('#tabelPenempatan').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[2, 'asc']], // Urutkan berdasarkan Nama Siswa
            scrollX: true,
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data penempatan",
                infoEmpty: "Tidak ada data penempatan",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Data penempatan tidak ditemukan",
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