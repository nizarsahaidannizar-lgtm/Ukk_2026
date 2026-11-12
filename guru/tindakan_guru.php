
<?php
// ============================================
// TINDAKAN GURU DAN WALI KELAS
// File: guru/tindakan_guru.php
// ============================================

include '../includes/cek_session.php';
include '../config/koneksi.php';

// Guru dan wali kelas boleh mengakses
batasi_akses(['guru', 'guru_wali_kelas']);

// ============================================
// AMBIL DATA PELANGGARAN
// ============================================

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

        ORDER BY ps.tanggal DESC, ps.id DESC";

$hasil = mysqli_query($koneksi, $sql);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Tindakan Pelanggaran - SIP</title>

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- DATATABLES -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    <!-- STYLE PROJECT -->
    <link rel="stylesheet" href="../layout/style.css">

    <style>
        body {
            background: #f5f7fb;
        }

        .guru-content {
            margin-left: 240px !important;
            width: calc(100% - 240px) !important;
            min-height: 100vh;
            padding: 25px 30px !important;
        }

        /* HEADER DISESUAIKAN */
        .page-title {
            color: #212529;
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .card {
            border-radius: 12px;
        }

        .table thead th {
            white-space: nowrap;
            font-size: 13px;
        }

        .table tbody td {
            font-size: 13px;
        }

        @media(max-width: 768px) {
            .guru-content {
                margin-left: 200px !important;
                width: calc(100% - 200px) !important;
                padding: 15px !important;
            }
        }
    </style>

</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <?php include '../layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="guru-content">

            <!-- HEADER -->
            <div class="mb-4">

                <h4 class="page-title">
                    Tindakan Pelanggaran
                </h4>

                <p class="text-muted mb-0">
                    SIP · Kelola dan berikan tindak lanjut pelanggaran siswa.
                </p>

            </div>

            <!-- NOTIFIKASI SUKSES -->
            <?php if (isset($_SESSION['pesan_sukses'])): ?>

                <div class="alert alert-success alert-dismissible fade show shadow-sm">

                    <i class="fa-solid fa-circle-check me-2"></i>

                    <?= htmlspecialchars($_SESSION['pesan_sukses']) ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

                <?php unset($_SESSION['pesan_sukses']); ?>

            <?php endif; ?>


            <!-- NOTIFIKASI ERROR -->
            <?php if (isset($_SESSION['pesan_error'])): ?>

                <div class="alert alert-danger alert-dismissible fade show shadow-sm">

                    <i class="fa-solid fa-triangle-exclamation me-2"></i>

                    <?= htmlspecialchars($_SESSION['pesan_error']) ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert">
                    </button>

                </div>

                <?php unset($_SESSION['pesan_error']); ?>

            <?php endif; ?>


            <!-- ERROR QUERY -->
            <?php if (!$hasil): ?>

                <div class="alert alert-danger">

                    <strong>Gagal mengambil data!</strong>

                    <br>

                    <?= htmlspecialchars(mysqli_error($koneksi)) ?>

                </div>

            <?php else: ?>


            <!-- CARD DATA -->
            <div class="card border-0 shadow-sm mb-5">

                <div class="card-header bg-white py-3">

                    <h5 class="mb-0 fw-bold">

                        <i class="fa-solid fa-clipboard-list me-2 text-primary"></i>

                        Daftar Catatan Pelanggaran Siswa

                    </h5>

                </div>


                <div class="card-body p-3">

                    <div class="table-responsive">

                        <table id="tabelTindakLanjut"
                               class="table table-striped table-hover align-middle w-100">

                            <thead class="table-dark">

                                <tr>

                                    <th>Siswa</th>

                                    <th>Kelas</th>

                                    <th>Pelanggaran</th>

                                    <th class="text-center">Tanggal</th>

                                    <th class="text-center">Poin</th>

                                    <th style="min-width:280px;">
                                        Tindakan & Status
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php while ($row = mysqli_fetch_assoc($hasil)): ?>

                                    <tr>

                                        <!-- NAMA SISWA -->
                                        <td class="fw-semibold">

                                            <?= htmlspecialchars($row['nama_siswa']) ?>

                                        </td>


                                        <!-- KELAS -->
                                        <td>

                                            <?= htmlspecialchars($row['nama_kelas']) ?>

                                        </td>


                                        <!-- PELANGGARAN -->
                                        <td>

                                            <?= htmlspecialchars($row['nama_pelanggaran']) ?>

                                        </td>


                                        <!-- TANGGAL -->
                                        <td class="text-center text-nowrap">

                                            <?= htmlspecialchars($row['tanggal'] ?? '-') ?>

                                        </td>


                                        <!-- POIN -->
                                        <td class="text-center">

                                            <span class="badge bg-danger">

                                                <?= (int)$row['poin'] ?>

                                            </span>

                                        </td>


                                        <!-- FORM TINDAKAN -->
                                        <td>

                                            <form action="proses_tindakan.php"
                                                  method="POST"
                                                  class="d-flex flex-column gap-2">

                                                <input type="hidden"
                                                       name="id"
                                                       value="<?= (int)$row['id'] ?>">


                                                <textarea
                                                    name="tindakan"
                                                    class="form-control form-control-sm"
                                                    rows="2"
                                                    placeholder="Tuliskan tindakan yang diberikan..."
                                                    required><?= htmlspecialchars($row['tindakan'] ?? '') ?></textarea>


                                                <div class="d-flex gap-2">

                                                    <select
                                                        name="status"
                                                        class="form-select form-select-sm"
                                                        required>

                                                        <?php

                                                        $daftar_status = [
                                                            'Ditindaklanjuti',
                                                            'Proses',
                                                            'Selesai'
                                                        ];

                                                        $status_sekarang = $row['status'] ?? '';

                                                        ?>

                                                        <?php foreach ($daftar_status as $st): ?>

                                                            <option
                                                                value="<?= htmlspecialchars($st) ?>"
                                                                <?= $status_sekarang === $st ? 'selected' : '' ?>>

                                                                <?= htmlspecialchars($st) ?>

                                                            </option>

                                                        <?php endforeach; ?>

                                                    </select>


                                                    <button
                                                        type="submit"
                                                        class="btn btn-primary btn-sm px-3 text-nowrap">

                                                        <i class="fa-solid fa-floppy-disk me-1"></i>

                                                        Simpan

                                                    </button>

                                                </div>

                                            </form>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                    <?php if (mysqli_num_rows($hasil) === 0): ?>

                        <div class="alert alert-info mb-0">

                            <i class="fa-solid fa-circle-info me-2"></i>

                            Belum ada catatan pelanggaran siswa.

                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <?php endif; ?>

        </main>

    </div>
</div>


<!-- JAVASCRIPT -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>


<script>

$(document).ready(function() {

    $('#tabelTindakLanjut').DataTable({

        autoWidth: false,

        pageLength: 10,

        ordering: false,

        language: {

            lengthMenu: "Tampilkan _MENU_ data",

            zeroRecords: "Data tidak ditemukan",

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

        }

    });

});

</script>

</body>
</html>
