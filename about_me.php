<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$nama = "Sahaidan Nizzar";
$kelas = "XII RPL";
$sekolah = "SMK Muhammadiyah Tasikmalaya";
$jurusan = "Rekayasa Perangkat Lunak";
$hobi = "Football";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Saya | SIP</title>

    <!-- Bootstrap 5 CSS & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="layout/style.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            margin: 0;
        }

        .main-content {
            min-height: 100vh;
            padding: 25px 24px;
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-title {
            font-size: 36px;
            font-weight: 500;
            color: #172b4d;
            margin-bottom: 5px;
        }

        .profile-card {
            background: white;
            border-radius: 10px;
            padding: 35px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
        }

        .profile-photo-wrapper {
            width: 145px;
            height: 145px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 5px solid #e8efff;
        }

        .profile-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            font-size: 25px;
            font-weight: 700;
            color: #172b4d;
            margin-bottom: 8px;
        }

        .profile-role {
            color: #0d6efd;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .profile-description {
            color: #667085;
            font-size: 14px;
            line-height: 1.9;
            max-width: 750px;
            margin: auto;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #202b3c;
            margin-bottom: 18px;
        }

        .info-card,
        .description-card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            height: 100%;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
        }

        .info-label {
            font-size: 11px;
            color: #8993a4;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .info-value {
            color: #263449;
            font-size: 15px;
            font-weight: 600;
        }

        .skill-item {
            display: inline-block;
            background: #eaf2ff;
            color: #0d6efd;
            padding: 8px 14px;
            border-radius: 6px;
            margin: 4px 3px;
            font-size: 13px;
            font-weight: 500;
        }

        .description-card p {
            font-size: 14px;
            line-height: 1.8;
            color: #667085;
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 20px 14px;
            }

            .page-title {
                font-size: 27px;
            }

            .profile-card {
                padding: 25px 18px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 main-content">

            <div class="page-header">
                <h1 class="page-title">Tentang Saya</h1>
                <p class="text-secondary mb-0">
                    Mengenal lebih dekat dengan saya.
                </p>
            </div>

            <!-- PROFIL -->
            <section class="profile-card">

                <div class="profile-photo-wrapper">
                    <img
                        src="foto-profil.jpg"
                        alt="Foto Profil"
                        class="profile-photo"
                        onerror="this.onerror=null;this.src='foto-profil.png';"
                    >
                </div>

                <h2 class="profile-name">
                    <?= htmlspecialchars($nama); ?>
                </h2>

                <div class="profile-role">
                    <?= htmlspecialchars($jurusan); ?>
                </div>

                <p class="profile-description">
                    Halo, saya <?= htmlspecialchars($nama); ?>,
                    seorang pelajar kelas
                    <?= htmlspecialchars($kelas); ?> di
                    <?= htmlspecialchars($sekolah); ?>.
                    Saya memiliki ketertarikan pada teknologi,
                    pengembangan website, dan desain antarmuka.
                    Saya terus belajar untuk meningkatkan kemampuan
                    serta menciptakan website yang bermanfaat,
                    responsif, dan mudah digunakan.
                </p>

            </section>

            <!-- INFORMASI PERSONAL -->
            <section class="mb-4">

                <h3 class="section-title">Informasi Personal</h3>

                <div class="row g-3">

                    <div class="col-sm-6 col-xl-4">
                        <div class="info-card">
                            <div class="info-label">NAMA LENGKAP</div>
                            <div class="info-value">
                                <?= htmlspecialchars($nama); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-4">
                        <div class="info-card">
                            <div class="info-label">KELAS</div>
                            <div class="info-value">
                                <?= htmlspecialchars($kelas); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-4">
                        <div class="info-card">
                            <div class="info-label">HOBI</div>
                            <div class="info-value">
                                <?= htmlspecialchars($hobi); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-6">
                        <div class="info-card">
                            <div class="info-label">ASAL SEKOLAH</div>
                            <div class="info-value">
                                <?= htmlspecialchars($sekolah); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-6">
                        <div class="info-card">
                            <div class="info-label">JURUSAN</div>
                            <div class="info-value">
                                <?= htmlspecialchars($jurusan); ?>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- DESKRIPSI DAN KEAHLIAN -->
            <section class="row g-3 mb-4">

                <div class="col-lg-7">
                    <div class="description-card">
                        <h3 class="section-title">Deskripsi Diri</h3>

                        <p>
                            Saya adalah seorang pelajar yang sedang
                            mendalami Rekayasa Perangkat Lunak. Saya
                            senang mempelajari teknologi, memahami
                            cara kerja website, dan mencoba hal-hal
                            baru dalam pemrograman.
                        </p>

                        <p class="mt-3">
                            Saya ingin terus mengembangkan kemampuan
                            di bidang web development dan bercita-cita
                            menjadi seorang Web Developer yang mampu
                            menghasilkan karya digital berkualitas.
                        </p>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="description-card">
                        <h3 class="section-title">
                            Bidang yang Dipelajari
                        </h3>

                        <span class="skill-item">HTML</span>
                        <span class="skill-item">CSS</span>
                        <span class="skill-item">PHP</span>
                        <span class="skill-item">Bootstrap</span>
                        <span class="skill-item">MySQL</span>
                        <span class="skill-item">Web Development</span>

                        <hr>

                        <p>
                            Berfokus pada pengembangan website dengan
                            struktur yang rapi, desain yang konsisten,
                            dan pengalaman pengguna yang baik.
                        </p>
                    </div>
                </div>

            </section>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>