<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

if (!isset($_GET['id'])) {
    header("Location: modul.php");
    exit();
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM modul WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$modul = $result->fetch_assoc();

if (!$modul) {
    echo "Modul tidak ditemukan.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("DELETE FROM modul WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: modul.php");
    exit();
}

$pageTitle = "Hapus Modul";
$activePage = "modul";
include 'templates/header.php';
?>

<div class="max-w-xl mx-auto bg-white p-6 mt-10 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Konfirmasi Hapus Modul</h1>
    <p class="mb-6">Apakah Anda yakin ingin menghapus modul <strong><?= htmlspecialchars($modul['judul_modul']) ?></strong>?</p>
    <form method="POST" class="flex justify-between">
        <a href="modul.php" class="bg-gray-400 hover:bg-gray-500 text-white py-2 px-4 rounded">Batal</a>
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded">Hapus</button>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
