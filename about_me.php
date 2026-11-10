<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Inisialisasi data profil agar tidak muncul error Undefined variable
$nama    = "Nizar Sahaidan";
$kelas   = "XII RPL";
$sekolah = "SMK Muhammadiyah Tasikmalaya";
$jurusan = "Rekayasa Perangkat Lunak";
$hobi    = "Football";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Saya | SIP</title>

    <!-- Bootstrap 5 CSS & FontAwesome CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            overflow-x: hidden;
        }

        /* Penggeser Konten Kanan Presisi 240px dari Sidebar Fixed */
        .main-content {
            margin-left: 240px !important;
            width: calc(100% - 240px) !important;
            min-height: 100vh;
            padding: 25px 30px;
            box-sizing: border-box;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- 1. Panggil Sidebar Fixed -->
    <?php include 'layout/sidebar.php'; ?>

    <!-- 2. Konten Utama -->
    <main class="main-content">
        <div class="container-fluid p-0">

            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Tentang Saya</h2>
                    <p class="text-secondary mb-0">Mengenal lebih dekat dengan saya.</p>
                </div>
                <i class="fas fa-user-circle fs-1 text-primary"></i>
            </div>

            <!-- PROFIL UTAMA -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body text-center p-4">
                    <img src="foto-profil.jpg"
                         class="rounded-circle border border-4 border-primary mb-3"
                         width="130"
                         height="130"
                         style="object-fit:cover"
                         alt="Foto Profil"
                         onerror="this.onerror=null;this.src='foto-profil.png';">

                    <h3 class="fw-bold text-dark mb-2">
                        <?= htmlspecialchars($nama); ?>
                    </h3>

                    <span class="badge bg-primary px-3 py-2 mb-3 fs-6">
                        <?= htmlspecialchars($jurusan); ?>
                    </span>

                    <p class="text-secondary mx-auto mb-0" style="max-width:700px; line-height:1.8">
                        Halo, saya <strong><?= htmlspecialchars($nama); ?></strong>,
                        pelajar kelas <strong><?= htmlspecialchars($kelas); ?></strong>
                        di <strong><?= htmlspecialchars($sekolah); ?></strong>.
                        Saya tertarik pada teknologi, pemrograman, dan pengembangan website.
                    </p>
                </div>
            </div>

            <!-- INFORMASI PERSONAL -->
            <h5 class="fw-bold mb-3 text-dark">Informasi Personal</h5>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <i class="fas fa-user text-primary fs-3"></i>
                            <div>
                                <small class="text-secondary d-block">Nama Lengkap</small>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($nama); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <i class="fas fa-users text-primary fs-3"></i>
                            <div>
                                <small class="text-secondary d-block">Kelas</small>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($kelas); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <i class="fas fa-futbol text-primary fs-3"></i>
                            <div>
                                <small class="text-secondary d-block">Hobi</small>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($hobi); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <i class="fas fa-school text-primary fs-3"></i>
                            <div>
                                <small class="text-secondary d-block">Asal Sekolah</small>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($sekolah); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <i class="fas fa-laptop-code text-primary fs-3"></i>
                            <div>
                                <small class="text-secondary d-block">Jurusan</small>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($jurusan); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DESKRIPSI DAN KEAHLIAN -->
            <div class="row g-3 mb-4">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3 text-dark">
                                <i class="fas fa-address-card text-primary me-2"></i>Deskripsi Diri
                            </h5>
                            <p class="text-secondary lh-lg mb-3">
                                Saya adalah pelajar Rekayasa Perangkat Lunak yang senang mempelajari teknologi, memahami cara kerja website, dan mencoba hal baru dalam pemrograman.
                            </p>
                            <p class="text-secondary lh-lg mb-0">
                                Saya bercita-cita menjadi Web Developer yang mampu menghasilkan karya digital berkualitas dan bermanfaat.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3 text-dark">
                                <i class="fas fa-code text-primary me-2"></i>Bidang yang Dipelajari
                            </h5>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-primary-subtle text-primary p-2">HTML</span>
                                <span class="badge bg-primary-subtle text-primary p-2">CSS</span>
                                <span class="badge bg-primary-subtle text-primary p-2">PHP</span>
                                <span class="badge bg-primary-subtle text-primary p-2">Bootstrap</span>
                                <span class="badge bg-primary-subtle text-primary p-2">MySQL</span>
                                <span class="badge bg-primary-subtle text-primary p-2">Web Development</span>
                            </div>
                            <hr>
                            <p class="text-secondary small mb-0">
                                Berfokus pada pengembangan website dengan struktur rapi dan desain yang mudah digunakan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>