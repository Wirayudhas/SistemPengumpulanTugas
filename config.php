<?php
// Pengaturan Database
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'Arthur'); 
define('DB_PASSWORD', '123'); 
define('DB_NAME', 'simparak');

// Membuat koneksi ke database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>