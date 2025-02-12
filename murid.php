<?php
session_start();
include 'database.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'murid') {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];

// Ambil semua tugas dari database
$query = "SELECT * FROM tasks ORDER BY due_date ASC";
$result = $conn->query($query);

// Proses pengumpulan tugas
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_task'])) {
    $task_id = $_POST['task_id'] ?? '';
    
    if (!empty($task_id) && isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_name = $_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        $upload_dir = "uploads/";
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_path = $upload_dir . basename($file_name);
        move_uploaded_file($file_tmp, $file_path);
        
        // Hapus tugas setelah dikumpulkan
        $delete_query = "DELETE FROM tasks WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
    }
    header("Location: murid.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Halaman Murid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body class="container">
    <h2 class="mt-5">Selamat datang, <?php echo htmlspecialchars($username); ?>!</h2>
    <h3>Daftar Tugas:</h3>
    <table class="table table-bordered">
        <tr>
            <th>Judul</th>
            <th>Deskripsi</th>
            <th>Batas Waktu</th>
            <th>Upload Tugas</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
            <td>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="task_id" value="<?php echo $row['id']; ?>">
                    <input type="file" name="file" required>
                    <button type="submit" name="submit_task" class="btn btn-success">Submit</button>
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
    <br>
    <a href="index.php" class="btn btn-danger">Logout</a>
</body>
</html>
