<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';
$pageTitle = "Manajemen Modul";
$activePage = "modul";

// Ambil data praktikum yang akan diedit
$edit_data = null;
if (isset($_GET['edit_praktikum'])) {
    $id = intval($_GET['edit_praktikum']);
    $stmt = $conn->prepare("SELECT * FROM praktikum WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
}

// Tambah Praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_praktikum'])) {
    $nama = $_POST['nama_praktikum'];
    $deskripsi = $_POST['deskripsi'];

    $stmt = $conn->prepare("INSERT INTO praktikum (nama_praktikum, deskripsi) VALUES (?, ?)");
    $stmt->bind_param("ss", $nama, $deskripsi);
    $stmt->execute();
    header("Location: modul.php");
    exit();
}

// Update Praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_praktikum'])) {
    $id = $_POST['praktikum_id'];
    $nama = $_POST['nama_praktikum'];
    $deskripsi = $_POST['deskripsi'];

    $stmt = $conn->prepare("UPDATE praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nama, $deskripsi, $id);
    $stmt->execute();
    header("Location: modul.php");
    exit();
}

// Hapus Praktikum & modul terkait
if (isset($_GET['delete_praktikum'])) {
    $id = intval($_GET['delete_praktikum']);
    $conn->query("DELETE FROM modul WHERE praktikum_id = $id");
    $conn->query("DELETE FROM praktikum WHERE id = $id");
    header("Location: modul.php");
    exit();
}

// Hapus Modul
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM modul WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: modul.php");
    exit();
}

// Tambah Modul
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_modul'])) {
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

// Ambil semua praktikum
$praktikum = $conn->query("SELECT * FROM praktikum");

function getModul($conn, $praktikum_id) {
    $stmt = $conn->prepare("SELECT * FROM modul WHERE praktikum_id = ?");
    $stmt->bind_param("i", $praktikum_id);
    $stmt->execute();
    return $stmt->get_result();
}
?>

<?php include 'templates/header.php'; ?>

<!-- Form Tambah/Edit Praktikum -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-xl font-semibold mb-4"><?= $edit_data ? 'Edit Praktikum' : 'Tambah Praktikum Baru' ?></h2>
    <form action="" method="POST" class="space-y-4">
        <?php if ($edit_data): ?>
            <input type="hidden" name="update_praktikum" value="1">
            <input type="hidden" name="praktikum_id" value="<?= $edit_data['id'] ?>">
        <?php else: ?>
            <input type="hidden" name="tambah_praktikum" value="1">
        <?php endif; ?>

        <input type="text" name="nama_praktikum" placeholder="Nama Praktikum"
               value="<?= $edit_data ? htmlspecialchars($edit_data['nama_praktikum']) : '' ?>"
               required class="w-full p-2 border rounded">

        <button type="submit" class="bg-<?= $edit_data ? 'yellow' : 'blue' ?>-600 hover:bg-<?= $edit_data ? 'yellow' : 'blue' ?>-700 text-white px-4 py-2 rounded">
            <?= $edit_data ? 'Update' : 'Simpan Praktikum' ?>
        </button>

        <?php if ($edit_data): ?>
            <a href="modul.php" class="ml-4 text-sm text-gray-600 underline">Batal</a>
        <?php endif; ?>
    </form>
</div>

<!-- Daftar Praktikum dan Modul -->
<?php while ($p = $praktikum->fetch_assoc()): ?>
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Praktikum: <?= htmlspecialchars($p['nama_praktikum']) ?></h2>
            <div class="space-x-2 flex">
                <a href="modul.php?edit_praktikum=<?= $p['id'] ?>" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 text-sm">Edit</a>
                <a href="?delete_praktikum=<?= $p['id'] ?>" onclick="return confirm('Yakin ingin menghapus praktikum ini? Semua modul terkait akan ikut dihapus!')" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">Hapus</a>
            </div>
        </div>

        <button onclick="document.getElementById('form-<?= $p['id'] ?>').classList.toggle('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mb-4">+ Tambah Modul</button>

        <form id="form-<?= $p['id'] ?>" action="" method="post" enctype="multipart/form-data" class="space-y-4 hidden mb-6">
            <input type="hidden" name="praktikum_id" value="<?= $p['id'] ?>">
            <input type="hidden" name="tambah_modul" value="1">
            <input type="text" name="judul" placeholder="Judul Modul" required class="w-full p-2 border rounded">
            <textarea name="deskripsi" placeholder="Deskripsi" class="w-full p-2 border rounded"></textarea>
            <input type="file" name="file_materi" accept=".pdf,.doc,.docx" class="block">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Simpan</button>
        </form>

        <table class="w-full table-auto text-left">
    <thead>
        <tr class="bg-gray-100">
            <th class="px-4 py-2">Judul</th>
            <th class="px-4 py-2">Deskripsi</th>
            <th class="px-4 py-2">Materi</th>
            <th class="px-4 py-2">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $modul = getModul($conn, $p['id']); ?>
        <?php while ($m = $modul->fetch_assoc()): ?>
            <tr class="border-b">
                <td class="px-4 py-2"><?= htmlspecialchars($m['judul_modul']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($m['deskripsi']) ?></td>
                <td class="px-4 py-2">
                    <?php if ($m['file_materi']): ?>
                        <span class="text-gray-800"><?= htmlspecialchars($m['file_materi']) ?></span>
                    <?php else: ?>
                        <span class="text-gray-400 italic">Tidak ada</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-2 space-x-2">
                    <a href="edit_module.php?id=<?= $m['id'] ?>" class="text-blue-600 hover:underline">Edit</a>
                    <a href="delete_module.php?id=<?= $m['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

    </div>
<?php endwhile; ?>

<?php include 'templates/footer.php'; ?>
