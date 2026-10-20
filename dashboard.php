<?php
// dashboard.php
include 'includes/cek_session.php';
include 'config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIP</title>

    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

            <!-- ALERT WELCOME / NOTIFIKASI -->
            <div class="alert alert-primary alert-dismissible fade show mb-4 shadow-sm" role="alert">
                Selamat Datang di <strong>SIP (Sistem Informasi Pelanggaran)</strong>, Anda login sebagai <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- SUMMARY CARDS / RINGKASAN DATA -->
            <div class="row g-3 mb-4">
                <!-- Card Total Siswa -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3 me-3">
                                <i class="fa-solid fa-user-graduate fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small m-0">Total Siswa</h6>
                                <h4 class="fw-bold m-0">
                                    <?php
                                    $q_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM t_siswa");
                                    echo mysqli_fetch_assoc($q_siswa)['total'] ?? 0;
                                    ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Total Guru -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-3 me-3">
                                <i class="fa-solid fa-chalkboard-user fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small m-0">Total Guru</h6>
                                <h4 class="fw-bold m-0">
                                    <?php
                                    $q_guru = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM t_guru");
                                    echo mysqli_fetch_assoc($q_guru)['total'] ?? 0;
                                    ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Kategori Pelanggaran -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-3 me-3">
                                <i class="fa-solid fa-list-check fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small m-0">Kategori Pelanggaran</h6>
                                <h4 class="fw-bold m-0">
                                    <?php
                                    $q_kat = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM t_pelanggaran_kategori");
                                    echo mysqli_fetch_assoc($q_kat)['total'] ?? 0;
                                    ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Wali Kelas -->
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body d-flex align-items-center p-3">
                            <div class="bg-info bg-opacity-10 text-info p-3 rounded-3 me-3">
                                <i class="fa-solid fa-user-shield fs-3"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small m-0">Wali Kelas</h6>
                                <h4 class="fw-bold m-0">
                                    <?php
                                    $q_wk = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM t_wali_kelas");
                                    echo mysqli_fetch_assoc($q_wk)['total'] ?? 0;
                                    ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/bootstrap.bundle.min.js"></script>

</body>
</html>