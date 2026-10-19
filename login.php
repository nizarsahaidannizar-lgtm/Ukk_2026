<?php
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pelanggaran Siswa</title>

    <!-- Bootstrap 5 CSS & FontAwesome Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-dark d-flex align-items-center justify-content-center min-vh-100 p-3">

    <!-- Container Utama Tema Abu-Abu -->
    <div class="bg-secondary rounded-3 w-100 min-vh-100 d-flex flex-column justify-content-center align-items-center p-4">

        <!-- Card Login -->
        <div class="card border-0 shadow-lg rounded-4 p-4 text-center bg-white" style="max-width: 380px; width: 100%;">

            <div class="card-body p-2">

                <!-- Logo Ikon Timbangan -->
                <div class="mb-4">
                    <i class="fa-solid fa-scale-balanced display-3 text-dark"></i>
                </div>

                <!-- Pesan Error Session -->
                <?php if (isset($_SESSION['pesan_error'])): ?>
                    <div class="alert alert-danger text-center py-2 mb-3" style="font-size: 14px;" role="alert">
                        <?= htmlspecialchars($_SESSION['pesan_error']) ?>
                    </div>
                    <?php unset($_SESSION['pesan_error']); ?>
                <?php endif; ?>

                <!-- Form Login -->
                <form action="proses_login.php" method="POST">

                    <div class="mb-3 text-start">
                        <label for="email" class="form-label text-muted small fw-semibold mb-1 ms-1">Email</label>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control bg-light border-0 rounded-3 py-2 px-3"
                               placeholder="admin@gmail.com"
                               required>
                    </div>

                    <div class="mb-4 text-start">
                        <label for="password" class="form-label text-muted small fw-semibold mb-1 ms-1">Password</label>
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control bg-light border-0 rounded-3 py-2 px-3"
                               placeholder="••••••••••••"
                               required>
                    </div>

                    <button type="submit"
                            name="login"
                            class="btn btn-secondary w-100 rounded-3 py-2 fw-bold text-white shadow-sm">
                        LOGIN
                    </button>

                </form>

            </div>

        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>