<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $praktikum_id = $_POST['praktikum_id'];
    $file_materi = null;

    if (!empty($_FILES['file_materi']['name'])) {
        $targetDir = '../uploads/';
        $file_materi = basename($_FILES['file_materi']['name']);
        move_uploaded_file($_FILES['file_materi']['tmp_name'], $targetDir . $file_materi);
    }

    $stmt = $conn->prepare("INSERT INTO modul (praktikum_id, judul_modul, deskripsi, file_materi) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $praktikum_id, $judul, $deskripsi, $file_materi);
    $stmt->execute();

    header("Location: modul.php");
    exit();
}

$praktikum = $conn->query("SELECT * FROM praktikum");

$pageTitle = "Tambah Modul";
$activePage = "modul";
include 'templates/header.php';
?>

<div class="max-w-xl mx-auto bg-white p-6 mt-10 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Tambah Modul</h1>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block font-medium">Judul Modul</label>
            <input type="text" name="judul" required class="w-full p-2 border rounded">
        </div>
        <div>
            <label class="block font-medium">Deskripsi</label>
            <textarea name="deskripsi" class="w-full p-2 border rounded"></textarea>
        </div>
        <div>
            <label class="block font-medium">File Materi</label>
            <input type="file" name="file_materi" accept=".pdf,.doc,.docx" class="block">
        </div>
        <div>
            <label class="block font-medium">Pilih Praktikum</label>
            <select name="praktikum_id" required class="w-full p-2 border rounded">
                <option value="">-- Pilih --</option>
                <?php while ($p = $praktikum->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_praktikum']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="flex justify-between">
            <a href="modul.php" class="bg-gray-400 hover:bg-gray-500 text-white py-2 px-4 rounded">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">Tambah</button>
        </div>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
