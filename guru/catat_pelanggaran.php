
<?php
// guru/catat_pelanggaran.php

date_default_timezone_set('Asia/Jakarta');

include __DIR__ . '/../includes/cek_session.php';
include __DIR__ . '/../config/koneksi.php';
include __DIR__ . '/../includes/akses_pelanggaran.php';

// ============================================
// TANGGAL OTOMATIS
// ============================================

$daftar_hari = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
];

$tanggal_otomatis = date('Y-m-d');

function format_tanggal_indonesia($tanggal, $daftar_hari)
{
    if (empty($tanggal) || $tanggal === '0000-00-00') {
        return '-';
    }

    $timestamp = strtotime($tanggal);

    if ($timestamp === false) {
        return '-';
    }

    $nama_hari = $daftar_hari[date('l', $timestamp)]
        ?? date('l', $timestamp);

    return $nama_hari . ', ' . date('d-m-Y', $timestamp);
}

// ============================================
// KONTEKS AKSES
// ============================================

$konteks_akses = konteks_akses_pelanggaran($koneksi);
$lingkup_akses = lingkup_sql_pelanggaran($konteks_akses, 'ps');

$is_wali = !empty($konteks_akses['is_wali']);

$kelas_ids = array_map(
    'intval',
    $konteks_akses['kelas_ids'] ?? []
);

$daftar_kelas = !empty($kelas_ids)
    ? implode(',', $kelas_ids)
    : '0';

// ============================================
// DROPDOWN SISWA
// ============================================

if ($is_wali) {
    $siswa = mysqli_query($koneksi, "
        SELECT DISTINCT s.id, s.nis, s.nama
        FROM t_siswa s
        INNER JOIN t_kelas_siswa ks
            ON ks.siswa_id = s.id
        WHERE s.status_aktif = 1
        AND ks.status_aktif = 1
        AND ks.kelas_id IN ($daftar_kelas)
        ORDER BY s.nama ASC
    ");
} else {
    $siswa = mysqli_query($koneksi, "
        SELECT id, nis, nama
        FROM t_siswa
        WHERE status_aktif = 1
        ORDER BY nama ASC
    ");
}

// ============================================
// DROPDOWN TAHUN AJARAN
// ============================================

$tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY id DESC
");

// ============================================
// DROPDOWN KELAS
// ============================================

$filter_kelas = '';

if ($is_wali) {
    $filter_kelas = " AND id IN ($daftar_kelas)";
}

$kelas = mysqli_query($koneksi, "
    SELECT id, nama, tingkat, jurusan
    FROM t_kelas
    WHERE status_aktif = 1
    $filter_kelas
    ORDER BY nama ASC
");

// ============================================
// DROPDOWN PELANGGARAN
// ============================================

$pelanggaran = mysqli_query($koneksi, "
    SELECT id, nama, poin, pelanggaran_kategori_id
    FROM t_pelanggaran
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

// ============================================
// GURU LOGIN
// ============================================

$id_user = (int)($_SESSION['id_user'] ?? $_SESSION['id'] ?? 0);

$stmt_guru = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_guru
    WHERE user_id = ?
    AND status_aktif = 1
    LIMIT 1
");

if (!$stmt_guru) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt_guru, 'i', $id_user);
mysqli_stmt_execute($stmt_guru);

$guru_login = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt_guru)
) ?: [];

mysqli_stmt_close($stmt_guru);

// ============================================
// QUERY DATA CATATAN PELANGGARAN
// ============================================

if ($is_wali) {

    /*
     * BAGIAN 1:
     * Ambil semua catatan pelanggaran yang
     * kelasnya menjadi tanggung jawab wali.
     *
     * Tidak lagi bergantung pada kecocokan
     * data t_kelas_siswa.
     */

    $sql = "
        SELECT
            ps.id,
            ps.tanggal,
            ps.poin,
            ps.status,

            COALESCE(s.nama, 'Siswa Tidak Ditemukan') AS nama_siswa,
            COALESCE(s.nis, '-') AS nis,

            COALESCE(k.nama, '-') AS nama_kelas,
            COALESCE(k.jurusan, '') AS jurusan,

            COALESCE(ta.nama, '-') AS tahun_ajaran,
            COALESCE(p.nama, 'Belum ada catatan') AS nama_pelanggaran,
            COALESCE(g.nama, '-') AS nama_guru

        FROM t_pelanggaran_siswa ps

        LEFT JOIN t_siswa s
            ON ps.siswa_id = s.id

        LEFT JOIN t_kelas k
            ON ps.kelas_id = k.id

        LEFT JOIN t_tahun_ajaran ta
            ON ps.tahun_ajaran_id = ta.id

        LEFT JOIN t_pelanggaran p
            ON ps.pelanggaran_id = p.id

        LEFT JOIN t_guru g
            ON ps.guru_id = g.id

        WHERE ps.kelas_id IN ($daftar_kelas)

        UNION ALL

        /*
         * BAGIAN 2:
         * Tampilkan siswa yang belum mempunyai
         * catatan pelanggaran pada kelas dan
         * tahun ajaran tersebut.
         */

        SELECT
            NULL AS id,
            NULL AS tanggal,
            NULL AS poin,
            NULL AS status,

            s.nama AS nama_siswa,
            COALESCE(s.nis, '-') AS nis,

            COALESCE(k.nama, '-') AS nama_kelas,
            COALESCE(k.jurusan, '') AS jurusan,

            COALESCE(ta.nama, '-') AS tahun_ajaran,
            'Belum ada catatan' AS nama_pelanggaran,
            '-' AS nama_guru

        FROM t_kelas_siswa ks

        INNER JOIN t_siswa s
            ON ks.siswa_id = s.id

        LEFT JOIN t_kelas k
            ON ks.kelas_id = k.id

        LEFT JOIN t_tahun_ajaran ta
            ON ks.tahun_ajaran_id = ta.id

        WHERE ks.status_aktif = 1
        AND s.status_aktif = 1
        AND ks.kelas_id IN ($daftar_kelas)

        AND NOT EXISTS (
            SELECT 1
            FROM t_pelanggaran_siswa ps
            WHERE ps.siswa_id = ks.siswa_id
            AND ps.kelas_id = ks.kelas_id
            AND ps.tahun_ajaran_id = ks.tahun_ajaran_id
        )

        ORDER BY nama_siswa ASC, tahun_ajaran ASC, tanggal DESC
    ";

} else {

    // ========================================
    // QUERY GURU BIASA
    // ========================================

    $sql = "
        SELECT
            ps.id,
            ps.tanggal,
            ps.poin,
            ps.status,

            COALESCE(
                s.nama,
                'Siswa Tidak Ditemukan'
            ) AS nama_siswa,

            COALESCE(s.nis, '-') AS nis,

            COALESCE(k.nama, '-') AS nama_kelas,
            COALESCE(k.jurusan, '') AS jurusan,

            COALESCE(ta.nama, '-') AS tahun_ajaran,
            COALESCE(p.nama, '-') AS nama_pelanggaran,
            COALESCE(g.nama, 'Sistem / Admin') AS nama_guru

        FROM t_pelanggaran_siswa ps

        LEFT JOIN t_siswa s
            ON ps.siswa_id = s.id

        LEFT JOIN t_kelas k
            ON ps.kelas_id = k.id

        LEFT JOIN t_tahun_ajaran ta
            ON ps.tahun_ajaran_id = ta.id

        LEFT JOIN t_pelanggaran p
            ON ps.pelanggaran_id = p.id

        LEFT JOIN t_guru g
            ON ps.guru_id = g.id

        WHERE $lingkup_akses

        ORDER BY ps.id DESC
    ";
}

// ============================================
// EKSEKUSI QUERY
// ============================================

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Catat Pelanggaran Siswa - SIP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet"
          href="../layout/style.css">

    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <?php include __DIR__ . '/../layout/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 p-4 guru-content guru-violation-list">

            <!-- HEADER -->
            <header class="admin-page-header">
                <div class="admin-page-heading">
                    <div>
                        <h1>Catat Pelanggaran</h1>
                        <p>SIP · Catat pelanggaran siswa.</p>
                    </div>
                </div>
            </header>

            <!-- PESAN SUKSES -->
            <?php if (isset($_SESSION['pesan_sukses'])) { ?>

                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_sukses']) ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>

                <?php unset($_SESSION['pesan_sukses']); ?>

            <?php } ?>

            <!-- PESAN ERROR -->
            <?php if (isset($_SESSION['pesan_error'])) { ?>

                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_error']) ?>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"></button>
                </div>

                <?php unset($_SESSION['pesan_error']); ?>

            <?php } ?>

            <!-- FORM CATAT PELANGGARAN -->
            <div class="card mb-4 shadow-sm border-0">

                <div class="card-header bg-white py-3 fw-bold">
                    Form Catat Pelanggaran
                </div>

                <div class="card-body">

                    <form action="proses_simpan_pelanggaran.php" method="POST">

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Siswa</label>

                                <select name="siswa_id" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>

                                    <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>
                                        <option value="<?= (int)$s['id'] ?>">
                                            <?= htmlspecialchars($s['nis'] . ' - ' . $s['nama']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tahun Ajaran</label>

                                <select name="tahun_ajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>

                                    <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>
                                        <option value="<?= (int)$ta['id'] ?>">
                                            <?= htmlspecialchars($ta['nama']) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelas</label>

                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>

                                    <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
                                        <option value="<?= (int)$k['id'] ?>">
                                            <?= htmlspecialchars(
                                                $k['nama'] . ' - ' .
                                                $k['tingkat'] . ' - ' .
                                                $k['jurusan']
                                            ) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Pelanggaran</label>

                                <select name="pelanggaran_id"
                                        id="pelanggaran"
                                        class="form-select"
                                        required>

                                    <option value="">-- Pilih Pelanggaran --</option>

                                    <?php while ($p = mysqli_fetch_assoc($pelanggaran)) { ?>
                                        <option
                                            value="<?= (int)$p['id'] ?>"
                                            data-poin="<?= (int)$p['poin'] ?>"
                                            data-kategori="<?= (int)$p['pelanggaran_kategori_id'] ?>">

                                            <?= htmlspecialchars(
                                                $p['nama'] . ' (' . $p['poin'] . ' poin)'
                                            ) ?>

                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guru Pelapor</label>

                                <input type="text"
                                       class="form-control"
                                       value="<?= htmlspecialchars(
                                           $guru_login['nama'] ?? $_SESSION['nama'] ?? ''
                                       ) ?>"
                                       readonly>

                                <input type="hidden"
                                       name="guru_id"
                                       value="<?= (int)($guru_login['id'] ?? 0) ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>

                                <select name="status" class="form-select" required>
                                    <option value="Ditindaklanjuti">Ditindaklanjuti</option>
                                    <option value="Proses">Proses</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Keterangan</label>

                                <textarea name="keterangan"
                                          class="form-control"
                                          rows="3"></textarea>
                            </div>

                        </div>

                        <input type="hidden"
                               name="tanggal"
                               value="<?= htmlspecialchars($tanggal_otomatis) ?>">

                        <input type="hidden" name="poin" id="poin">

                        <input type="hidden"
                               name="pelanggaran_kategori_id"
                               id="kategori">

                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="fa-solid fa-save me-1"></i>
                            Simpan Pelanggaran
                        </button>

                    </form>
                </div>
            </div>

            <!-- TABEL CATATAN -->
            <div class="card shadow-sm border-0 mb-5">

                <div class="card-header bg-white py-3 fw-bold">
                    Data Catatan Pelanggaran
                </div>

                <div class="card-body p-3">

                    <div class="table-responsive">

                        <table id="tabelPelanggaran"
                               class="table table-sm table-striped table-hover align-middle mb-0 w-100 guru-violation-table">

                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Pelanggaran</th>
                                    <th>Guru</th>
                                    <th>Tanggal</th>
                                    <th>Poin</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php
                                $no = 1;

                                if ($hasil && mysqli_num_rows($hasil) > 0) {

                                    while ($row = mysqli_fetch_assoc($hasil)) {

                                        $ada_catatan = !empty($row['id']);
                                ?>

                                    <tr>

                                        <td class="text-center fw-semibold">
                                            <?= $no++ ?>
                                        </td>

                                        <td class="fw-semibold text-dark">
                                            <?= htmlspecialchars($row['nama_siswa']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                trim($row['nama_kelas'] . ' ' . $row['jurusan'])
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['tahun_ajaran']) ?>
                                        </td>

                                        <td>
                                            <?php if ($ada_catatan) { ?>

                                                <?= htmlspecialchars($row['nama_pelanggaran']) ?>

                                            <?php } else { ?>

                                                <span class="text-muted fst-italic">
                                                    Belum ada catatan
                                                </span>

                                            <?php } ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($row['nama_guru']) ?>
                                        </td>

                                        <td class="text-center text-nowrap">
                                            <?= htmlspecialchars(
                                                format_tanggal_indonesia(
                                                    $row['tanggal'] ?? '',
                                                    $daftar_hari
                                                )
                                            ) ?>
                                        </td>

                                        <td class="text-center fw-bold">
                                            <?= $ada_catatan
                                                ? htmlspecialchars($row['poin'] ?? '0')
                                                : '-' ?>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($ada_catatan) { ?>

                                                <span class="badge bg-secondary px-2 py-1">
                                                    <?= htmlspecialchars($row['status'] ?? '-') ?>
                                                </span>

                                            <?php } else { ?>

                                                <span class="badge bg-warning text-dark">
                                                    Belum Dicatat
                                                </span>

                                            <?php } ?>
                                        </td>

                                        <td class="text-center text-nowrap">
                                            <?php if ($ada_catatan) { ?>

                                                <a href="edit_catatan_pelanggaran.php?id=<?= (int)$row['id'] ?>"
                                                   class="btn btn-warning btn-sm text-white me-1"
                                                   title="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>

                                                <form action="proses_hapus_catatan_pelanggaran.php"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Yakin ingin menghapus catatan pelanggaran ini?');">

                                                    <input type="hidden"
                                                           name="id"
                                                           value="<?= (int)$row['id'] ?>">

                                                    <button type="submit"
                                                            class="btn btn-danger btn-sm"
                                                            title="Hapus">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>

                                            <?php } else { ?>

                                                <span class="text-muted">-</span>

                                            <?php } ?>
                                        </td>

                                    </tr>

                                <?php
                                    }
                                } else {
                                ?>

                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            Tidak ada data siswa.
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

<!-- JAVASCRIPT -->

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
// ============================================
// OTOMATIS POIN DAN KATEGORI
// ============================================

const pilihPelanggaran = document.getElementById('pelanggaran');
const inputPoin = document.getElementById('poin');
const inputKategori = document.getElementById('kategori');

if (pilihPelanggaran) {
    pilihPelanggaran.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];

        inputPoin.value = selected.dataset.poin || '';
        inputKategori.value = selected.dataset.kategori || '';
    });
}

// ============================================
// DATATABLES
// ============================================

$(document).ready(function () {
    $('#tabelPelanggaran').DataTable({
        autoWidth: false,

        columnDefs: [
            { targets: 0, width: "4%", orderable: false },
            { targets: 1, width: "13%" },
            { targets: 2, width: "9%" },
            { targets: 3, width: "9%" },
            { targets: 4, width: "16%" },
            { targets: 5, width: "11%" },
            { targets: 6, width: "10%" },
            { targets: 7, width: "7%" },
            { targets: 8, width: "9%" },
            { targets: 9, width: "12%", orderable: false }
        ],

        language: {
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(disaring dari total _MAX_ data)",
            search: "Cari:",

            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },

        pageLength: 10
    });
});
</script>

</body>
</html>
