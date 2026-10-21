<?php
// guru/kelola_guru.php
include '../includes/cek_session.php';
include '../config/koneksi.php';

// HANYA ROLE ADMIN YANG BISA MEMBUKA HALAMAN INI
batasi_akses(['admin']);

// Query & Kode Halaman Berikutnya...
?>