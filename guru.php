<?php
session_start();
include 'database.php';

// Cek apakah pengguna sudah login dan perannya guru
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'guru') {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];

// Tambah tugas jika form dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    if (!empty($title) && !empty($description) && !empty($due_date)) {
        $query = "INSERT INTO tasks (teacher_username, title, description, due_date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $title, $description, $due_date);
        $stmt->execute();
    }
}

// Ambil semua tugas yang dibuat oleh guru ini
$query = "SELECT * FROM tasks WHERE teacher_username = ? ORDER BY due_date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Halaman Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body class="container mt-5">
    <h2 class="text-center">Selamat datang, <?php echo htmlspecialchars($username); ?>!</h2>
    <div class="card p-4 mt-3">
        <h3>Buat Tugas Baru:</h3>
        <form method="POST">
            <div class="mb-3">
                <input type="text" name="title" class="form-control" placeholder="Judul Tugas" required>
            </div>
            <div class="mb-3">
                <textarea name="description" class="form-control" placeholder="Deskripsi Tugas" required></textarea>
            </div>
            <div class="mb-3">
                <input type="date" name="due_date" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Tambahkan Tugas</button>
        </form>
    </div>
    <h3 class="mt-4">Daftar Tugas:</h3>
    <table class="table table-bordered">
        <tr class="table-primary">
            <th>Judul</th>
            <th>Deskripsi</th>
            <th>Batas Waktu</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
        </tr>
        <?php } ?>
    </table>
    <br>
    <a href="index.php" class="btn btn-danger">Logout</a>
</body>
</html>
