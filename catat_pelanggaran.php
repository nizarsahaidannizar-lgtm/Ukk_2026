<?php
// catat_pelanggaran.php
include 'includes/cek_session.php';
include 'config/koneksi.php';
include 'includes/akses_pelanggaran.php';

$konteks_akses = konteks_akses_pelanggaran($koneksi);
$lingkup_akses = lingkup_sql_pelanggaran($konteks_akses, 'ps');

// Daftar siswa
$siswa = mysqli_query($koneksi, "
    SELECT id, nis, nama
    FROM t_siswa
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

// Daftar tahun ajaran
$tahun = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY id DESC
");

// Daftar kelas
$kelas = mysqli_query($koneksi, "
    SELECT id, nama, tingkat, jurusan
    FROM t_kelas
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

// Daftar pelanggaran
$pelanggaran = mysqli_query($koneksi, "
    SELECT id, nama, poin, pelanggaran_kategori_id
    FROM t_pelanggaran
    WHERE status_aktif = 1
    ORDER BY nama ASC
");

// Data guru yang login
$stmt_guru = mysqli_prepare($koneksi, "
    SELECT id, nama
    FROM t_guru
    WHERE user_id = ? AND status_aktif = 1
    LIMIT 1
");

$id_user = (int)($_SESSION['id_user'] ?? 0);

mysqli_stmt_bind_param($stmt_guru, 'i', $id_user);
mysqli_stmt_execute($stmt_guru);

$guru_login = mysqli_fetch_assoc(
    mysqli_stmt_get_result($stmt_guru)
) ?: [];

// Data catatan pelanggaran
$sql = "
    SELECT
        ps.*,
        ta.nama AS tahun_ajaran,
        s.nis,
        k.tingkat,
        k.jurusan
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_tahun_ajaran ta
        ON ps.tahun_ajaran_id = ta.id
    LEFT JOIN t_siswa s
        ON ps.siswa_id = s.id
    LEFT JOIN t_kelas k
        ON ps.kelas_id = k.id
    WHERE $lingkup_akses
    ORDER BY ps.id DESC
";

$hasil = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Catat Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="layout/style.css">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 p-4">

            <!-- HEADER UTAMA -->
            <div class="mb-4">
                <h2 class="fw-normal text-dark mb-1">Tindakan Pelanggaran</h2>
                <p class="text-muted mb-0">SIP &middot; Semua guru dapat melihat dan memberikan tindak lanjut pelanggaran siswa.</p>
            </div>

            <!-- PESAN SUKSES -->
            <?php if (isset($_SESSION['pesan_sukses'])) { ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_sukses']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php } ?>

            <!-- PESAN ERROR -->
            <?php if (isset($_SESSION['pesan_error'])) { ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['pesan_error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php } ?>

            <!-- FORM CATAT PELANGGARAN -->
            <div class="card shadow-sm mb-4">

                <!-- HEADER KARTU DIUBAH JADI HITAM (bg-dark) -->
                <div class="card-header bg-dark text-white">
                    <strong>
                        <i class="fas fa-edit me-2"></i>
                        Form Catat Pelanggaran
                    </strong>
                </div>

                <div class="card-body">

                    <form action="proses_simpan_pelanggaran.php" method="POST">

                        <div class="row">

                            <!-- SISWA -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Siswa</label>

                                <select name="siswa_id" class="form-select" required>
                                    <option value="">-- Pilih Siswa --</option>

                                    <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>
                                        <option value="<?= (int)$s['id']; ?>">
                                            <?= htmlspecialchars($s['nis'] . ' - ' . $s['nama']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- TAHUN AJARAN -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tahun Ajaran</label>

                                <select name="tahun_ajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Tahun Ajaran --</option>

                                    <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>
                                        <option value="<?= (int)$ta['id']; ?>">
                                            <?= htmlspecialchars($ta['nama']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- KELAS -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelas</label>

                                <select name="kelas_id" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>

                                    <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
                                        <option value="<?= (int)$k['id']; ?>">
                                            <?= htmlspecialchars(
                                                $k['nama'] . ' - ' .
                                                $k['tingkat'] . ' - ' .
                                                $k['jurusan']
                                            ); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <!-- JENIS PELANGGARAN -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenis Pelanggaran</label>

                                <select name="pelanggaran_id"
                                        id="pelanggaran"
                                        class="form-select"
                                        required>

                                    <option value="">-- Pilih Pelanggaran --</option>

                                    <?php while ($p = mysqli_fetch_assoc($pelanggaran)) { ?>
                                        <option
                                            value="<?= (int)$p['id']; ?>"
                                            data-poin="<?= (int)$p['poin']; ?>"
                                            data-kategori="<?= (int)$p['pelanggaran_kategori_id']; ?>">

                                            <?= htmlspecialchars($p['nama'] . ' (' . $p['poin'] . ' poin)'); ?>

                                        </option>
                                    <?php } ?>
                                </select>

                                <input type="hidden"
                                       name="pelanggaran_kategori_id"
                                       id="kategori">
                            </div>

                            <!-- GURU PELAPOR -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guru Pelapor</label>

                                <input type="text"
                                       class="form-control"
                                       value="<?= htmlspecialchars($guru_login['nama'] ?? $_SESSION['nama'] ?? ''); ?>"
                                       readonly>

                                <input type="hidden"
                                       name="guru_id"
                                       value="<?= (int)($guru_login['id'] ?? 0); ?>">
                            </div>

                            <!-- TANGGAL -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal</label>

                                <input type="date"
                                       name="tanggal"
                                       class="form-control"
                                       value="<?= date('Y-m-d'); ?>"
                                       required>
                            </div>

                            <!-- POIN -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Poin Pelanggaran</label>

                                <input type="number"
                                       name="poin"
                                       id="poin"
                                       class="form-control"
                                       min="0"
                                       placeholder="Poin otomatis"
                                       readonly
                                       required>

                                <small class="text-muted">
                                    Poin terisi otomatis sesuai jenis pelanggaran.
                                </small>
                            </div>

                            <!-- STATUS -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>

                                <select name="status" class="form-select" required>
                                    <option value="Ditindaklanjuti">Ditindaklanjuti</option>
                                    <option value="Proses">Proses</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>

                            <!-- KETERANGAN -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Keterangan</label>

                                <textarea name="keterangan"
                                          class="form-control"
                                          rows="3"></textarea>
                            </div>

                            <!-- TINDAKAN -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tindakan</label>

                                <textarea name="tindakan"
                                          class="form-control"
                                          rows="3"></textarea>
                            </div>

                        </div>

                        <!-- TOMBOL DIUBAH JADI HITAM (btn-dark) -->
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-save me-1"></i>
                            Simpan Pelanggaran
                        </button>

                    </form>

                </div>
            </div>

            <!-- DATA CATATAN PELANGGARAN -->
            <div class="card shadow-sm">

                <!-- HEADER KARTU DIUBAH JADI HITAM (bg-dark) -->
                <div class="card-header bg-dark text-white">
                    <strong>
                        <i class="fas fa-table me-2"></i>
                        Data Catatan Pelanggaran
                    </strong>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped table-hover align-middle">

                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Tahun Ajaran</th>
                                    <th>Pelanggaran</th>
                                    <th>Guru</th>
                                    <th>Tanggal</th>
                                    <th>Poin</th>
                                    <th>Tindakan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php
                                $no = 1;

                                if ($hasil && mysqli_num_rows($hasil) > 0) {
                                    while ($row = mysqli_fetch_assoc($hasil)) {
                                ?>

                                    <tr>
                                        <td><?= $no++; ?></td>

                                        <td><?= htmlspecialchars($row['nama_siswa'] ?? '-'); ?></td>

                                        <td><?= htmlspecialchars($row['nama_kelas'] ?? '-'); ?></td>

                                        <td><?= htmlspecialchars($row['tahun_ajaran'] ?? '-'); ?></td>

                                        <td><?= htmlspecialchars($row['nama_pelanggaran'] ?? '-'); ?></td>

                                        <td><?= htmlspecialchars($row['nama_guru'] ?? '-'); ?></td>

                                        <td><?= htmlspecialchars($row['tanggal'] ?? '-'); ?></td>

                                        <td>
                                            <span class="badge bg-danger">
                                                <?= (int)$row['poin']; ?> Poin
                                            </span>
                                        </td>

                                        <td><?= htmlspecialchars($row['tindakan'] ?? '-'); ?></td>

                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?= htmlspecialchars($row['status'] ?? '-'); ?>
                                            </span>
                                        </td>
                                    </tr>

                                <?php
                                    }
                                } else {
                                ?>

                                    <tr>
                                        <td colspan="10" class="text-center text-muted">
                                            Belum ada data catatan pelanggaran.
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

<!-- BOOTSTRAP JAVASCRIPT -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- POIN OTOMATIS -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const pilihanPelanggaran = document.getElementById('pelanggaran');
    const inputPoin = document.getElementById('poin');
    const inputKategori = document.getElementById('kategori');

    function tampilkanPoin() {

        const opsi = pilihanPelanggaran.options[
            pilihanPelanggaran.selectedIndex
        ];

        if (pilihanPelanggaran.value !== '') {
            inputPoin.value = opsi.dataset.poin || '';
            inputKategori.value = opsi.dataset.kategori || '';
        } else {
            inputPoin.value = '';
            inputKategori.value = '';
        }
    }

    pilihanPelanggaran.addEventListener('change', tampilkanPoin);

    tampilkanPoin();

});
</script>

</body>
</html>