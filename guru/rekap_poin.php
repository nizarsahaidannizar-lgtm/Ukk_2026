
<?php
// guru/rekap_poin.php

// ======================================
// INCLUDE FILE
// ======================================

include __DIR__ . '/../includes/cek_session.php';
include __DIR__ . '/../config/koneksi.php';

// ======================================
// NORMALISASI ROLE
// ======================================

$role = normalisasi_role($_SESSION['role'] ?? '');

// ======================================
// CEK HAK AKSES
// ======================================

batasi_akses(['guru', 'guru_wali_kelas']);

// ======================================
// QUERY REKAP SELURUH PELANGGARAN
// ======================================

$sql = "SELECT
            ps.siswa_id,
            COALESCE(s.nama, ps.nama_siswa, 'Siswa Tidak Ditemukan') AS nama_siswa,
            COALESCE(k.nama, ps.nama_kelas, '-') AS nama_kelas,
            COUNT(ps.id) AS jumlah_pelanggaran,
            COALESCE(SUM(ps.poin), 0) AS total_poin,
            MAX(ps.tanggal) AS terakhir

        FROM t_pelanggaran_siswa ps

        LEFT JOIN t_siswa s
            ON ps.siswa_id = s.id

        LEFT JOIN t_kelas k
            ON ps.kelas_id = k.id

        GROUP BY
            ps.siswa_id,
            s.nama,
            ps.nama_siswa,
            ps.kelas_id,
            k.nama,
            ps.nama_kelas

        ORDER BY
            total_poin DESC,
            nama_siswa ASC";

$hasil = mysqli_query($koneksi, $sql);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Rekap Poin Siswa - SIP</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DATATABLES -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- CSS UTAMA -->
    <link rel="stylesheet" href="../layout/style.css">

</head>

<body class="bg-light">

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 guru-content">

            <!-- HEADER -->
            <header class="admin-page-header mb-4">

                <div class="admin-page-heading">

                    <div>

                        <h1>Rekap Poin Siswa</h1>

                        <p class="text-muted">
                            SIP · Ringkasan akumulasi poin dari seluruh pelanggaran yang dicatat oleh semua guru.
                        </p>

                    </div>

                </div>

            </header>

            <!-- CARD REKAP -->
            <div class="card border-0 shadow-sm rounded-3 mb-5">

                <div class="card-header bg-white py-3">

                    <h5 class="card-title m-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar me-2"></i>
                        Daftar Akumulasi Poin Pelanggaran Siswa
                    </h5>

                </div>

                <div class="card-body p-3">

                    <div class="table-responsive">

                        <table
                            id="tabelRekapPoin"
                            class="table table-striped table-hover align-middle mb-0 w-100"
                        >

                            <thead class="table-dark">

                                <tr>

                                    <th class="text-center py-3" style="width:60px;">
                                        No.
                                    </th>

                                    <th class="py-3">
                                        Nama Siswa
                                    </th>

                                    <th class="py-3">
                                        Kelas
                                    </th>

                                    <th class="text-center py-3">
                                        Jumlah Pelanggaran
                                    </th>

                                    <th class="text-center py-3">
                                        Total Poin
                                    </th>

                                    <th class="text-center py-3">
                                        Terakhir Tercatat
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php
                                $no = 1;

                                if ($hasil && mysqli_num_rows($hasil) > 0):

                                    while ($row = mysqli_fetch_assoc($hasil)):
                                ?>

                                    <tr>

                                        <td class="text-center fw-semibold">
                                            <?= $no++ ?>
                                        </td>

                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars($row['nama_siswa']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_kelas']) ?>
                                        </td>

                                        <td class="text-center">
                                            <?= (int)$row['jumlah_pelanggaran'] ?> Kali
                                        </td>

                                        <td class="text-center fw-bold text-danger">
                                            <?= number_format((int)$row['total_poin']) ?> Poin
                                        </td>

                                        <td class="text-center small text-muted">
                                            <?= htmlspecialchars($row['terakhir'] ?? '-') ?>
                                        </td>

                                    </tr>

                                <?php
                                    endwhile;

                                endif;
                                ?>

                            </tbody>

                        </table>

                    </div>

                    <!-- PESAN KOSONG -->
                    <?php if ($hasil && mysqli_num_rows($hasil) == 0): ?>

                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Belum ada data rekap poin siswa.
                        </div>

                    <?php endif; ?>

                    <!-- PESAN ERROR -->
                    <?php if (!$hasil): ?>

                        <div class="alert alert-danger mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Gagal mengambil data rekap:
                            <?= htmlspecialchars(mysqli_error($koneksi)) ?>
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

<!-- DATATABLES -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {

    $('#tabelRekapPoin').DataTable({

        autoWidth: false,

        language: {

            lengthMenu: "Tampilkan _MENU_ data",

            zeroRecords: "Belum ada data rekap poin siswa",

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
