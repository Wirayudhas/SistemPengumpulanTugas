<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';
$pageTitle = "Penilaian Tugas";
$activePage = "report";

// Pastikan tabel memiliki kolom nilai
$conn->query("ALTER TABLE laporan ADD COLUMN IF NOT EXISTS nilai DECIMAL(5,2) DEFAULT NULL");

// Proses penilaian
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['beri_nilai'])) {
    $laporan_id = intval($_POST['laporan_id']);
    $nilai = floatval($_POST['nilai']);
    $catatan = $conn->real_escape_string($_POST['catatan']);
    
    $stmt = $conn->prepare("UPDATE laporan SET nilai = ?, catatan = ?, status = 'dinilai' WHERE id = ?");
    $stmt->bind_param("dsi", $nilai, $catatan, $laporan_id);
    $stmt->execute();
    
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Nilai berhasil disimpan!'];
    header("Location: report.php");
    exit();
}

// Query yang aman
$laporan = $conn->query("
    SELECT 
        l.id,
        l.mahasiswa_id,
        l.modul_id,
        l.file_laporan,
        l.status,
        IFNULL(l.nilai, 0) as nilai,
        IFNULL(l.catatan, '-') as catatan,
        u.nama as nama_mahasiswa, 
        m.judul_modul 
    FROM laporan l
    JOIN users u ON l.mahasiswa_id = u.id
    JOIN modul m ON l.modul_id = m.id
    ORDER BY l.id DESC
");

include 'templates/header.php';
?>

<div class="container mx-auto p-4">
    
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-300 text-left">
                    <th class="py-3 px-6 font-semibold text-gray-800">Mahasiswa</th>
                    <th class="py-3 px-6 font-semibold text-gray-800">Modul</th>
                    <th class="py-3 px-6 font-semibold text-gray-700">File</th>
                    <th class="py-3 px-6 font-semibold text-gray-800">Nilai</th>
                    <th class="py-3 px-6 font-semibold text-gray-800">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php while($row = $laporan->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="py-4 px-6 text-gray-800"><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                    <td class="py-4 px-6 text-gray-800"><?= htmlspecialchars($row['judul_modul']) ?></td>
                    <td class="py-4 px-6 text-gray-700">
                        <a href="../uploads/<?= htmlspecialchars($row['file_laporan']) ?>" 
                           class="text-blue-600 hover:text-blue-800 hover:underline" target="_blank">
                            Lihat
                        </a>
                    </td>
                    <td class="py-4 px-6 font-medium text-gray-800">
                        <?= $row['nilai'] > 0 ? $row['nilai'] : '<span class="text-gray-500">Belum dinilai</span>' ?>
                    </td>
                    <td class="py-4 px-6 text-gray-800">
                        <button onclick="showNilaiModal(<?= $row['id'] ?>, <?= $row['nilai'] ?>)"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm transition duration-200">
                            <?= $row['nilai'] > 0 ? 'Ubah Nilai' : 'Beri Nilai' ?>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Input Nilai -->
<div id="nilaiModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-96">
        <h3 class="text-xl font-bold mb-4">Form Penilaian</h3>
        <form method="POST">
            <input type="hidden" name="laporan_id" id="modalLaporanId">
            
            <div class="mb-4">
                <label class="block mb-1">Nilai (0-100)</label>
                <input type="number" name="nilai" id="inputNilai" 
                       min="0" max="100" step="0.1" 
                       class="w-full p-2 border rounded" required>
            </div>
            
            
            <div class="flex justify-end gap-2">
                <button type="button" onclick="hideNilaiModal()" 
                        class="bg-gray-500 text-white px-4 py-2 rounded">
                    Batal
                </button>
                <button type="submit" name="beri_nilai" 
                        class="bg-green-500 text-white px-4 py-2 rounded">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showNilaiModal(id, nilai = 0) {
    document.getElementById('modalLaporanId').value = id;
    document.getElementById('inputNilai').value = nilai > 0 ? nilai : '';
    document.getElementById('nilaiModal').classList.remove('hidden');
}

function hideNilaiModal() {
    document.getElementById('nilaiModal').classList.add('hidden');
}
</script>

<?php include 'templates/footer.php'; ?>