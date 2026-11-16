-- Jalankan satu kali melalui phpMyAdmin pada database db_ukk_2026_nizar.
-- Indeks ini membantu filter tanggal dashboard dan pengurutan data berdasarkan nama.
ALTER TABLE t_pelanggaran_siswa
    ADD INDEX idx_pelanggaran_tanggal (tanggal);

ALTER TABLE t_guru
    ADD INDEX idx_guru_nama (nama);

ALTER TABLE t_siswa
    ADD INDEX idx_siswa_nama (nama);

ALTER TABLE t_kelas
    ADD INDEX idx_kelas_nama (nama);
