
<?php
// ========================================
// ABOUT ME
// ========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// BASE URL PROJECT
$base_url = '/Ukk_2026/';

// CEK LOGIN
if (
    !isset($_SESSION['status']) ||
    $_SESSION['status'] !== 'login'
) {
    header("Location: " . $base_url . "login.php");
    exit;
}

// CEK ROLE
$role = strtolower(trim($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    http_response_code(403);
    exit('Akses ditolak.');
}

// DATA PROFIL
$nama    = "Nizar Sahaidan";
$kelas   = "XII RPL";
$sekolah = "SMK Muhammadiyah Tasikmalaya";
$jurusan = "Rekayasa Perangkat Lunak";
$hobi    = "Football";

// FOTO PROFIL
$foto = $base_url . 'foto-profil.jpg';

?>

<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>About Me | SIP</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

body {
    font-family: 'Poppins', sans-serif;
    background: #f4f7fb;
    margin: 0;
    overflow-x: hidden;
}

.main-content {
    margin-left: 240px;
    width: calc(100% - 240px);
    min-height: 100vh;
    padding: 25px 30px;
    box-sizing: border-box;
}

.profile-banner {
    height: 130px;
    background: linear-gradient(135deg, #0d6efd, #6ea8fe);
    border-radius: 18px 18px 0 0;
}

.profile-card {
    border: none;
    border-radius: 18px;
    overflow: hidden;
}

.profile-img {
    width: 125px;
    height: 125px;
    object-fit: cover;
    border: 5px solid white;
    border-radius: 50%;
    margin-top: -65px;
    background: white;
}

.info-card {
    border: none;
    border-radius: 14px;
    height: 100%;
    box-shadow: 0 3px 15px rgba(0,0,0,.04);
    transition: .3s;
}

.info-card:hover {
    transform: translateY(-4px);
}

.icon-box {
    width: 48px;
    height: 48px;
    min-width: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e8f1ff;
    color: #0d6efd;
    border-radius: 12px;
    font-size: 20px;
}

.content-card {
    border: none;
    border-radius: 15px;
    height: 100%;
    box-shadow: 0 3px 15px rgba(0,0,0,.04);
}

.skill-badge {
    background: #e8f1ff;
    color: #0d6efd;
    padding: 9px 13px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
}

/* RESPONSIVE SIDEBAR */
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

<?php
require_once __DIR__ . '/layout/sidebar.php';
?>

<main class="main-content">

<div class="container-fluid p-0">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">About Me</h2>
            <p class="text-secondary mb-0">
                Mengenal lebih dekat dengan saya.
            </p>
        </div>

        <i class="fas fa-user-circle fs-1 text-primary"></i>

    </div>

    <!-- PROFIL -->
    <div class="card profile-card shadow-sm mb-4">

        <div class="profile-banner"></div>

        <div class="card-body text-center pt-0 pb-4">

            <img
                src="<?= htmlspecialchars($foto); ?>"
                class="profile-img mb-3"
                alt="Foto Profil"
                onerror="this.style.display='none';"
            >

            <h3 class="fw-bold">
                <?= htmlspecialchars($nama); ?>
            </h3>

            <span class="badge bg-primary px-3 py-2 mb-3">
                <?= htmlspecialchars($jurusan); ?>
            </span>

            <p class="text-secondary mx-auto mb-0"
               style="max-width:700px;line-height:1.8">

                Halo, saya <strong><?= htmlspecialchars($nama); ?></strong>,
                pelajar kelas <strong><?= htmlspecialchars($kelas); ?></strong>
                di <strong><?= htmlspecialchars($sekolah); ?></strong>.
                Saya tertarik pada teknologi, pemrograman,
                dan pengembangan website.

            </p>

        </div>

    </div>

    <!-- INFORMASI PERSONAL -->
    <h5 class="fw-bold mb-3">
        <i class="fas fa-id-card text-primary me-2"></i>
        Informasi Personal
    </h5>

    <div class="row g-3 mb-4">

        <?php
        $informasi = [
            ['fa-user', 'Nama Lengkap', $nama],
            ['fa-users', 'Kelas', $kelas],
            ['fa-futbol', 'Hobi', $hobi],
            ['fa-school', 'Asal Sekolah', $sekolah],
            ['fa-laptop-code', 'Jurusan', $jurusan]
        ];

        foreach ($informasi as $item):
        ?>

        <div class="col-md-6 col-xl-4">

            <div class="card info-card">

                <div class="card-body d-flex align-items-center gap-3">

                    <div class="icon-box">
                        <i class="fas <?= $item[0]; ?>"></i>
                    </div>

                    <div>
                        <small class="text-secondary">
                            <?= htmlspecialchars($item[1]); ?>
                        </small>

                        <div class="fw-semibold">
                            <?= htmlspecialchars($item[2]); ?>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <!-- DESKRIPSI DAN KEAHLIAN -->
    <div class="row g-3 mb-4">

        <div class="col-lg-7">

            <div class="card content-card">

                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-address-card text-primary me-2"></i>
                        Deskripsi Diri
                    </h5>

                    <p class="text-secondary lh-lg">
                        Saya adalah pelajar Rekayasa Perangkat Lunak
                        yang senang mempelajari teknologi, memahami
                        cara kerja website, dan mencoba hal baru
                        dalam pemrograman.
                    </p>

                    <p class="text-secondary lh-lg mb-0">
                        Saya bercita-cita menjadi Web Developer
                        yang mampu menghasilkan karya digital
                        berkualitas dan bermanfaat.
                    </p>

                </div>

            </div>

        </div>

        <div class="col-lg-5">

            <div class="card content-card">

                <div class="card-body p-4">

                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-code text-primary me-2"></i>
                        Bidang yang Dipelajari
                    </h5>

                    <div class="d-flex flex-wrap gap-2 mb-3">

                        <span class="skill-badge">HTML</span>
                        <span class="skill-badge">CSS</span>
                        <span class="skill-badge">PHP</span>
                        <span class="skill-badge">Bootstrap</span>
                        <span class="skill-badge">MySQL</span>
                        <span class="skill-badge">Web Development</span>

                    </div>

                    <hr>

                    <p class="text-secondary small mb-0">
                        Berfokus pada pengembangan website
                        dengan struktur rapi dan desain
                        yang mudah digunakan.
                    </p>

                </div>

            </div>

        </div>

    </div>

    <div class="text-center text-secondary small py-3">
        © <?= date('Y'); ?> SIP - Sistem Informasi Pelanggaran
    </div>

</div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
