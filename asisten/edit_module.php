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
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $file_materi = $modul['file_materi'];

    if (!empty($_FILES['file_materi']['name'])) {
        $targetDir = '../uploads/';
        $file_materi = basename($_FILES['file_materi']['name']);
        move_uploaded_file($_FILES['file_materi']['tmp_name'], $targetDir . $file_materi);
    }

    $stmt = $conn->prepare("UPDATE modul SET judul_modul = ?, deskripsi = ?, file_materi = ? WHERE id = ?");
    $stmt->bind_param("sssi", $judul, $deskripsi, $file_materi, $id);
    $stmt->execute();

    header("Location: modul.php");
    exit();
}

$pageTitle = "Edit Modul";
$activePage = "modul";
include 'templates/header.php';
?>

<div class="max-w-xl mx-auto bg-white p-6 mt-10 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Edit Modul</h1>
    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block font-medium">Judul Modul</label>
            <input type="text" name="judul" value="<?= htmlspecialchars($modul['judul_modul']) ?>" required class="w-full p-2 border rounded">
        </div>
        <div>
            <label class="block font-medium">Deskripsi</label>
            <textarea name="deskripsi" class="w-full p-2 border rounded"><?= htmlspecialchars($modul['deskripsi']) ?></textarea>
        </div>
        <div>
            <label class="block font-medium">File Materi (Opsional)</label>
            <input type="file" name="file_materi" accept=".pdf,.doc,.docx" class="block">
            <?php if ($modul['file_materi']): ?>
            <?php endif; ?>
        </div>
        <div class="flex justify-between">
            <a href="modul.php" class="bg-gray-400 hover:bg-gray-500 text-white py-2 px-4 rounded">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded">Simpan</button>
        </div>
    </form>
</div>

<?php include 'templates/footer.php'; ?>
