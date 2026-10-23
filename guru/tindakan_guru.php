<?php
include '../includes/cek_session.php';
include '../config/koneksi.php';
include '../includes/akses_pelanggaran.php';

$konteks = konteks_akses_pelanggaran($koneksi);
$lingkup = lingkup_sql_pelanggaran($konteks, 'ps');

// Gunakan WHERE 1=1 jika $lingkup kosong
$where_clause = (!empty($lingkup) && trim($lingkup) != '') ? $lingkup : "1=1";

// Query SQL dengan JOIN untuk mengambil data relasi lengkap
$sql = "SELECT 
            ps.id, 
            ps.tanggal, 
            ps.poin, 
            ps.tindakan, 
            ps.status,
            COALESCE(s.nama, ps.nama_siswa, 'Siswa Tidak Ditemukan') AS nama_siswa,
            COALESCE(k.nama, ps.nama_kelas, '-') AS nama_kelas,
            COALESCE(p.nama, ps.nama_pelanggaran, '-') AS nama_pelanggaran
        FROM t_pelanggaran_siswa ps
        LEFT JOIN t_siswa s ON ps.siswa_id = s.id
        LEFT JOIN t_kelas k ON ps.kelas_id = k.id
        LEFT JOIN t_pelanggaran p ON ps.pelanggaran_id = p.id
        WHERE $where_clause
        ORDER BY ps.tanggal DESC, ps.id DESC";

$hasil = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tindak Lanjut Pelanggaran - SIP</title>
    
    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../layout/style.css">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        
        <!-- SIDEBAR NAVIGASI -->
        <?php include '../layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4">
            
            <?php include '../layout/brand_header.php'; ?>

            <h3 class="fw-bold text-dark mb-4">Tindak Lanjut Pelanggaran</h3>

            <!-- ALERT NOTIFIKASI -->
            <?php if (isset($_SESSION['pesan_sukses'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($_SESSION['pesan_sukses']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['pesan_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['pesan_error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php endif; ?>

            <!-- CARD TABLE DATA -->
            <div class="card border-0 shadow-sm rounded-3 mb-5">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title m-0 fw-bold">Daftar Catatan Pelanggaran Siswa</h5>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="tabelTindakLanjut" class="table table-striped table-hover align-middle mb-0 w-100">
                            <thead class="table-dark">
                                <tr>
                                    <th class="py-3">Siswa</th>
                                    <th class="py-3">Kelas</th>
                                    <th class="py-3">Pelanggaran</th>
                                    <th class="text-center py-3">Tanggal</th>
                                    <th class="text-center py-3">Poin</th>
                                    <th class="py-3" style="min-width: 280px;">Tindakan & Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($hasil && mysqli_num_rows($hasil) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                                    <tr>
                                        <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                                        <td><?= htmlspecialchars($row['nama_pelanggaran']) ?></td>
                                        <td class="text-center small"><?= htmlspecialchars($row['tanggal'] ?? '-') ?></td>
                                        <td class="text-center fw-bold text-danger"><?= (int)$row['poin'] ?></td>
                                        <td>
                                            <form action="../proses_tindakan.php" method="post" class="d-flex flex-column gap-2 py-1">
                                                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                                <textarea name="tindakan" class="form-control form-control-sm" rows="2" placeholder="Tuliskan tindakan yang diberikan..." required><?= htmlspecialchars($row['tindakan'] ?? '') ?></textarea>
                                                
                                                <div class="d-flex gap-2">
                                                    <select name="status" class="form-select form-select-sm" required>
                                                        <?php foreach (['Ditindaklanjuti', 'Proses', 'Selesai'] as $st): ?>
                                                            <option value="<?= $st ?>" <?= ($row['status'] === $st) ? 'selected' : '' ?>><?= $st ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button class="btn btn-primary btn-sm px-3 fw-semibold text-nowrap" type="submit">
                                                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- jQuery & Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS & Bootstrap 5 Integration -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabelTindakLanjut').DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_ data",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(disaring dari total _MAX_ data)",
                "search": "Cari:",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            "pageLength": 10,
            "order": [] // Menjaga urutan asli dari query SQL
        });
    });
</script>
</body>
</html>