<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// Ambil data statistik
$mahasiswa_id = $_SESSION['user_id'];

// 1. Hitung praktikum yang diikuti
$query_praktikum = "SELECT COUNT(*) as total FROM pendaftaran_praktikum WHERE mahasiswa_id = ?";
$stmt_praktikum = $conn->prepare($query_praktikum);
$stmt_praktikum->bind_param("i", $mahasiswa_id);
$stmt_praktikum->execute();
$result_praktikum = $stmt_praktikum->get_result()->fetch_assoc();
$jumlah_praktikum = $result_praktikum['total'];

// 2. Hitung tugas yang sudah dikumpulkan
$query_tugas_selesai = "SELECT COUNT(DISTINCT l.modul_id) as total 
                        FROM laporan l
                        JOIN modul m ON l.modul_id = m.id
                        JOIN pendaftaran_praktikum pp ON m.praktikum_id = pp.praktikum_id
                        WHERE l.mahasiswa_id = ? AND pp.mahasiswa_id = ?";
$stmt_tugas_selesai = $conn->prepare($query_tugas_selesai);
$stmt_tugas_selesai->bind_param("ii", $mahasiswa_id, $mahasiswa_id);
$stmt_tugas_selesai->execute();
$result_tugas_selesai = $stmt_tugas_selesai->get_result()->fetch_assoc();
$tugas_selesai = $result_tugas_selesai['total'];

// 3. Hitung tugas yang belum dikumpulkan
$query_tugas_menunggu = "SELECT COUNT(DISTINCT m.id) as total
                         FROM modul m
                         JOIN pendaftaran_praktikum pp ON m.praktikum_id = pp.praktikum_id
                         LEFT JOIN laporan l ON m.id = l.modul_id AND l.mahasiswa_id = ?
                         WHERE pp.mahasiswa_id = ? AND l.id IS NULL";
$stmt_tugas_menunggu = $conn->prepare($query_tugas_menunggu);
$stmt_tugas_menunggu->bind_param("ii", $mahasiswa_id, $mahasiswa_id);
$stmt_tugas_menunggu->execute();
$result_tugas_menunggu = $stmt_tugas_menunggu->get_result()->fetch_assoc();
$tugas_menunggu = $result_tugas_menunggu['total'];

require_once 'templates/header_mahasiswa.php'; 
?>

<div class="container mx-auto px-4 py-8">
    <!-- Header Welcome -->
    <div class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white p-6 rounded-xl shadow-lg mb-8">
        <h1 class="text-2xl md:text-3xl font-bold">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
        <p class="mt-2 text-sm md:text-base opacity-90">Semangat menyelesaikan praktikum Anda</p>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Praktikum Diikuti -->
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500">Praktikum Diikuti</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $jumlah_praktikum ?></p>
                </div>
            </div>
        </div>

        <!-- Tugas Selesai -->
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500">Tugas Selesai</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $tugas_selesai ?></p>
                </div>
            </div>
        </div>

        <!-- Tugas Menunggu -->
        <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-gray-500">Tugas Menunggu</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $tugas_menunggu ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'templates/footer_mahasiswa.php';
?>