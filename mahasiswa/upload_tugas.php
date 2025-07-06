<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_tugas'])) {
    // Validasi file
    $allowedExtensions = ['pdf', 'doc', 'docx'];
    $fileExtension = strtolower(pathinfo($_FILES['file_tugas']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedExtensions)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Hanya file PDF/DOC/DOCX yang diizinkan!'];
        header("Location: practice.php");
        exit();
    }

    // Cek ukuran file (max 5MB)
    if ($_FILES['file_tugas']['size'] > 5 * 1024 * 1024) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Ukuran file maksimal 5MB!'];
        header("Location: practice.php");
        exit();
    }

    $modul_id = intval($_POST['modul_id']);
    $mahasiswa_id = $_SESSION['user_id'];
    $targetDir = "../uploads/tugas/";
    
    // Buat folder jika belum ada
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Generate nama file unik
    $fileName = "tugas_" . $modul_id . "_" . $mahasiswa_id . "_" . time() . "." . $fileExtension;
    $targetFilePath = $targetDir . $fileName;

    // Debugging - tampilkan info path
    error_log("Attempting to upload to: " . $targetFilePath);

    if (move_uploaded_file($_FILES['file_tugas']['tmp_name'], $targetFilePath)) {
        try {
            // Cek apakah sudah pernah upload
            $check = $conn->prepare("SELECT id FROM laporan WHERE modul_id = ? AND mahasiswa_id = ?");
            $check->bind_param("ii", $modul_id, $mahasiswa_id);
            $check->execute();
            $result = $check->get_result();
            
            if ($result->num_rows > 0) {
                // Update record yang ada
                $stmt = $conn->prepare("UPDATE laporan SET file_laporan = ? WHERE modul_id = ? AND mahasiswa_id = ?");
                $stmt->bind_param("sii", $fileName, $modul_id, $mahasiswa_id);
            } else {
                // Buat record baru
                $stmt = $conn->prepare("INSERT INTO laporan (modul_id, mahasiswa_id, file_laporan) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $modul_id, $mahasiswa_id, $fileName);
            }
            
            if ($stmt->execute()) {
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Tugas berhasil diupload!'];
            } else {
                // Hapus file yang sudah diupload jika gagal menyimpan ke database
                if (file_exists($targetFilePath)) {
                    unlink($targetFilePath);
                }
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'Gagal menyimpan data tugas! Error: ' . $conn->error];
            }
        } catch (Exception $e) {
            // Hapus file jika terjadi exception
            if (file_exists($targetFilePath)) {
                unlink($targetFilePath);
            }
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()];
        }
    } else {
        $errorMsg = "Gagal mengupload file. Error: ";
        switch ($_FILES['file_tugas']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg .= "Ukuran file terlalu besar!";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg .= "File hanya terupload sebagian!";
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg .= "Tidak ada file yang diupload!";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMsg .= "Folder temporary tidak ada!";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $errorMsg .= "Gagal menulis file ke disk!";
                break;
            case UPLOAD_ERR_EXTENSION:
                $errorMsg .= "Upload dihentikan oleh ekstensi PHP!";
                break;
            default:
                $errorMsg .= "Unknown error (" . $_FILES['file_tugas']['error'] . ")";
        }
        $_SESSION['flash'] = ['type' => 'error', 'message' => $errorMsg];
    }
}

header("Location: practice.php");
exit();
?>