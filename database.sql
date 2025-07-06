CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','asisten') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS praktikum (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_praktikum VARCHAR(100) NOT NULL,
    deskripsi TEXT
);

CREATE TABLE modul (
    id INT AUTO_INCREMENT PRIMARY KEY,
    praktikum_id INT NOT NULL,
    judul_modul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    file_materi VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (praktikum_id) REFERENCES praktikum(id) ON DELETE CASCADE
);

CREATE TABLE laporan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT,
    modul_id INT,
    file_laporan VARCHAR(255),
    tanggal_kumpul DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('terkirim', 'diperiksa') DEFAULT 'terkirim'
);

CREATE TABLE tugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mahasiswa_id INT NOT NULL,
    modul_id INT NOT NULL,
    file_tugas VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Relasi ke mahasiswa dan modul (optional, jika sudah ada tabel user dan modul)
    FOREIGN KEY (mahasiswa_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (modul_id) REFERENCES modul(id) ON DELETE CASCADE
);

