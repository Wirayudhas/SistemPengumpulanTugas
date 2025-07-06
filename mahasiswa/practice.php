<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';
$pageTitle = "Praktikum Saya";
$activePage = "practice";

// Flash message
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$user_id = $_SESSION['user_id'];

$query = "SELECT 
    p.id AS praktikum_id, 
    p.nama_praktikum, 
    p.deskripsi,
    m.id AS modul_id, 
    m.judul_modul AS judul_modul, 
    m.file_materi
FROM pendaftaran_praktikum pp
JOIN praktikum p ON pp.praktikum_id = p.id
LEFT JOIN modul m ON m.praktikum_id = p.id
WHERE pp.mahasiswa_id = ?
ORDER BY p.nama_praktikum, m.id";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include 'templates/header_mahasiswa.php';
?>

<div class="container mx-auto px-4 py-6">
    <?php if (isset($flash)): ?>
        <div class="mb-4 p-4 rounded <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= $flash['message'] ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md w-full min-h-[50vh]">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Praktikum Saya</h1>

            <?php
            $current_praktikum = null;
            $has_content = false;

            if ($result->num_rows === 0) {
                echo '<p class="text-red-500 py-4">Anda belum mendaftar praktikum apapun.</p>';
            }

            while ($row = $result->fetch_assoc()):
                $has_content = true;
                if ($current_praktikum !== $row['praktikum_id']):
                    if ($current_praktikum !== null) echo '</div></div>'; // Tutup div sebelumnya
                    $current_praktikum = $row['praktikum_id'];
            ?>
            <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($row['nama_praktikum']) ?></h2>
                    <p class="text-sm text-gray-600"><?= htmlspecialchars($row['deskripsi']) ?></p>
                </div>
                <div class="p-4 space-y-4">
            <?php endif; ?>

            <?php if ($row['modul_id']): 
                // Query untuk mendapatkan data tugas dan nilai
                $stmt_tugas = $conn->prepare("SELECT file_laporan, nilai, status FROM laporan WHERE modul_id = ? AND mahasiswa_id = ?");
                $stmt_tugas->bind_param("ii", $row['modul_id'], $user_id);
                $stmt_tugas->execute();
                $tugas = $stmt_tugas->get_result()->fetch_assoc();
                $sudah_upload = $tugas !== null;
            ?>
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 py-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-gray-800"><?= htmlspecialchars($row['judul_modul']) ?></p>
                            <?php if ($sudah_upload && $tugas['nilai'] !== null): ?>
                                <span class="text-sm px-2 py-1 rounded-full 
                                    <?= $tugas['status'] == 'Lulus' ? 'bg-green-100 text-green-800' : 
                                       ($tugas['status'] == 'Tidak Lulus' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') ?>">
                                    <?= $tugas['nilai'] ?> 
                                    (<?= $tugas['status'] ?? 'Belum Dinilai' ?>)
                                </span>
                            <?php elseif ($sudah_upload): ?>
                                <span class="text-sm px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">
                                    Menunggu Penilaian
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php if ($row['file_materi']): ?>
                            <a href="../uploads/<?= $row['file_materi'] ?>" 
                               target="_blank" 
                               class="text-sm text-blue-600 hover:underline inline-block mt-1">
                               Download Materi
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($sudah_upload): ?>
                        <div class="flex items-center gap-2">
                            <a href="../uploads/tugas/<?= $tugas['file_laporan'] ?>" 
                               target="_blank"
                               class="px-3 py-1 bg-blue-50 text-blue-600 rounded-md text-sm hover:bg-blue-100 transition-colors">
                               Lihat File
                            </a>
                            <?php if ($tugas['nilai'] !== null): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 <?= $tugas['status'] == 'Lulus' ? 'text-green-500' : 'text-red-500' ?> shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <form action="upload_tugas.php" method="post" enctype="multipart/form-data" class="w-full sm:w-auto flex flex-col sm:flex-row gap-2">
                            <input type="hidden" name="modul_id" value="<?= $row['modul_id'] ?>">
                            <input type="file" name="file_tugas" required 
                                   class="text-sm text-gray-600 border border-gray-300 rounded px-3 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500 w-full">
                            <button type="submit" 
                                    class="px-3 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 shrink-0">
                                Upload
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php endwhile; ?>
            
            <?php if ($has_content) echo '</div></div>'; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer_mahasiswa.php'; ?>