<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';
$pageTitle = "Daftar Mata Praktikum";
$activePage = "courses";

// Flash message
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$mahasiswa_id = $_SESSION['user_id'];

// Handle pendaftaran praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar_praktikum'])) {
    $praktikum_id = intval($_POST['praktikum_id']);

    // Cek apakah sudah mendaftar
    $cek = $conn->prepare("SELECT * FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?");
    $cek->bind_param("ii", $mahasiswa_id, $praktikum_id);
    $cek->execute();
    $res = $cek->get_result();
    
    if ($res->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);
        
        if ($stmt->execute()) {
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Berhasil mendaftar praktikum!'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal mendaftar praktikum!'];
        }
    }
    header("Location: courses.php");
    exit();
}

// Handle batal pendaftaran praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batal_praktikum'])) {
    $praktikum_id = intval($_POST['praktikum_id']);
    
    $stmt = $conn->prepare("DELETE FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?");
    $stmt->bind_param("ii", $mahasiswa_id, $praktikum_id);
    
    if ($stmt->execute()) {
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Berhasil membatalkan pendaftaran praktikum!'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal membatalkan pendaftaran!'];
    }
    
    header("Location: courses.php");
    exit();
}

// Pencarian
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT * FROM praktikum WHERE nama_praktikum LIKE ?");
    $like = "%$search%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $praktikum = $stmt->get_result();
} else {
    $praktikum = $conn->query("SELECT * FROM praktikum");
}
?>

<?php include 'templates/header_mahasiswa.php'; ?>

<div class="container mx-auto px-4 py-6">
    <?php if (isset($flash)): ?>
        <div class="mb-4 p-4 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= $flash['message'] ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold mb-4">Cari Mata Praktikum</h2>

        <form method="get" class="mb-4 flex gap-2">
            <input type="text" name="search" placeholder="Cari Mata Praktikum..." 
                   value="<?= htmlspecialchars($search) ?>" 
                   class="border p-2 rounded w-full">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Cari</button>
        </form>

        <?php if ($praktikum->num_rows > 0): ?>
            <?php while ($p = $praktikum->fetch_assoc()): ?>
                <div class="mb-6 border-b pb-4">
                    <h3 class="text-lg font-bold"><?= htmlspecialchars($p['nama_praktikum']) ?></h3>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($p['deskripsi']) ?></p>

                    <?php
                        // Jumlah modul
                        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM modul WHERE praktikum_id = ?");
                        $stmt->bind_param("i", $p['id']);
                        $stmt->execute();
                        $result = $stmt->get_result()->fetch_assoc();
                        $jumlah_modul = $result['total'];

                        // Cek apakah sudah daftar
                        $stmt = $conn->prepare("SELECT * FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?");
                        $stmt->bind_param("ii", $mahasiswa_id, $p['id']);
                        $stmt->execute();
                        $sudah_daftar = $stmt->get_result()->num_rows > 0;
                    ?>
                    <p class="mt-1 text-sm">Jumlah Modul: <strong><?= $jumlah_modul ?></strong></p>

                    <div class="mt-2 flex items-center gap-2">
                        <?php if ($sudah_daftar): ?>
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded text-sm font-semibold">
                                Sudah Terdaftar
                            </span>
                            <form method="post">
                                <input type="hidden" name="praktikum_id" value="<?= $p['id'] ?>">
                                <button type="submit" name="batal_praktikum" 
                                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">
                                    Batal Daftar
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="post">
                                <input type="hidden" name="praktikum_id" value="<?= $p['id'] ?>">
                                <button type="submit" name="daftar_praktikum" 
                                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                                    Daftar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-600">Tidak ada praktikum ditemukan.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer_mahasiswa.php'; ?>