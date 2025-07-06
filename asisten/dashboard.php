<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';
$pageTitle = 'Dashboard Asisten';
$activePage = 'dashboard';

// Ambil data statistik
$query_mahasiswa = "SELECT COUNT(*) as total FROM users WHERE role = 'mahasiswa'";
$result_mahasiswa = $conn->query($query_mahasiswa);
$total_mahasiswa = $result_mahasiswa->fetch_assoc()['total'];

$query_modul = "SELECT COUNT(*) as total FROM modul";
$result_modul = $conn->query($query_modul);
$total_modul = $result_modul->fetch_assoc()['total'];

$query_laporan = "SELECT COUNT(*) as total FROM laporan";
$result_laporan = $conn->query($query_laporan);
$total_laporan = $result_laporan->fetch_assoc()['total'];

// Ambil daftar mahasiswa terdaftar
$query_daftar_mahasiswa = "SELECT id, nama, email FROM users WHERE role = 'mahasiswa' LIMIT 5";
$daftar_mahasiswa = $conn->query($query_daftar_mahasiswa);

require_once 'templates/header.php';
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card Mahasiswa Terdaftar -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Mahasiswa Terdaftar</p>
            <p class="text-2xl font-bold text-gray-800"><?= $total_mahasiswa ?></p>
        </div>
    </div>

    <!-- Card Modul Tersedia -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Modul Tersedia</p>
            <p class="text-2xl font-bold text-gray-800"><?= $total_modul ?></p>
        </div>
    </div>

    <!-- Card Laporan Masuk -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800"><?= $total_laporan ?></p>
        </div>
    </div>
</div>

<!-- Daftar Mahasiswa Terdaftar -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Mahasiswa Terdaftar</h3>
    <div class="overflow-x-auto">
        <table class="w-full table-auto">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">Nama</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($mahasiswa = $daftar_mahasiswa->fetch_assoc()): ?>
                <tr class="border-b">
                    <td class="px-4 py-2"><?= htmlspecialchars($mahasiswa['nama']) ?></td>
                    <td class="px-4 py-2"><?= htmlspecialchars($mahasiswa['email']) ?></td>
                    <td class="px-4 py-2">
                        <a href="report.php?mahasiswa_id=<?= $mahasiswa['id'] ?>" class="text-blue-600 hover:underline">Lihat Laporan</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4 text-right">
        <a href="report.php" class="text-blue-600 hover:underline">Lihat semua mahasiswa →</a>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>