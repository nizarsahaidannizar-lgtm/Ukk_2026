<?php
include 'includes/cek_session.php';
include "config/koneksi.php";

$nama = "Sahaidan Nizzar";
$kelas = "XII RPL";
$sekolah = "SMK Muhammadiyah Tasikmalaya";
$jurusan = "Rekayasa Perangkat Lunak";
$hobi = "FootBall";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Me | Nama Kamu </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="layout/style.css">
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <?php include 'layout/sidebar.php'; ?>

        <!-- KONTEN -->
        <main class="col-md-9 col-lg-10 main-content">

            <!-- HEADER -->
            <div class="page-header">
                <h1 class="page-title">Tentang Saya</h1>
                <p class="text-secondary small mb-0">
                    Mengenal lebih dekat dengan saya.
                </p>
            </div>

            <!-- PROFIL DI TENGAH -->
            <section class="profile-card">

                <div class="profile-photo-wrapper">
                    <img
                        src="foto-profil.jpg"
                        alt="Foto "
                        class="profile-photo"
                        onerror="this.onerror=null;this.src='foto-profil.png';"
                    >
                </div>

                <h2 class="profile-name">
                    <?php echo htmlspecialchars($nama); ?>
                </h2>

                <div class="profile-role">
                    <?php echo htmlspecialchars($jurusan); ?>
                </div>

                <p class="profile-description">
                    Halo, saya <?php echo htmlspecialchars($nama); ?>,
                    seorang pelajar kelas
                    <?php echo htmlspecialchars($kelas); ?> di
                    <?php echo htmlspecialchars($sekolah); ?>.
                    Saya memiliki ketertarikan pada teknologi,
                    pengembangan website, dan desain antarmuka.
                    Saya terus belajar untuk meningkatkan kemampuan
                    serta menciptakan website yang bermanfaat,
                    responsif, dan mudah digunakan.
                </p>

                <hr class="profile-divider">

                <a href="dashboard.php" class="back-button">
                    Kembali ke Dashboard
                </a>

            </section>

            <!-- INFORMASI PERSONAL -->
            <section class="mb-4">

                <h3 class="section-title">Informasi Personal</h3>

                <div class="row g-3">

                    <div class="col-sm-6 col-xl-4">
                        <div class="info-card">
                            <div class="info-label">NAMA LENGKAP</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($nama); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-4">
                        <div class="info-card">
                            <div class="info-label">KELAS</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($kelas); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-4">
                        <div class="info-card">
                            <div class="info-label">HOBI</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($hobi); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-6">
                        <div class="info-card">
                            <div class="info-label">ASAL SEKOLAH</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($sekolah); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-6">
                        <div class="info-card">
                            <div class="info-label">JURUSAN</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($jurusan); ?>
                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- DESKRIPSI DAN KEAHLIAN -->
            <section class="row g-3">

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
                        <h3 class="section-title">Bidang yang Dipelajari</h3>

                        <span class="skill-item">HTML</span>
                        <span class="skill-item">CSS</span>
                        <span class="skill-item">PHP</span>
                        <span class="skill-item">Bootstrap</span>
                        <span class="skill-item">MySQL</span>
                        <span class="skill-item">Web Development</span>

                        <hr style="border-color: #e8ecf4; margin: 16px 0;">

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

</body>
</html>
