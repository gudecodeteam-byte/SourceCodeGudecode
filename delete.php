<?php
require 'config.php';

$showLoading = false; // Untuk mengontrol apakah loading screen ditampilkan
$redirectURL = 'dashboard.php?msg=error&info=Terjadi kesalahan.';
$redirectMsg = 'Menghapus data...';

if (isset($_GET['id']) && isset($_GET['type'])) {
    $id = intval($_GET['id']);
    $type = $_GET['type'];

    // Tentukan jenis data yang akan dihapus
    if ($type === 'booking') {
        $stmt = $conn->prepare("DELETE FROM booking WHERE id = ?");
        $stmt->bind_param("i", $id);
        $redirectMsg = "Booking berhasil dihapus!";
        $redirectURL = "dashboard.php?msg=success&info=" . urlencode($redirectMsg);
    } elseif ($type === 'guest') {
        $stmt = $conn->prepare("DELETE FROM guest_messages WHERE id = ?");
        $stmt->bind_param("i", $id);
        $redirectMsg = "Pesan tamu berhasil dihapus!";
        $redirectURL = "dashboard.php?msg=success&info=" . urlencode($redirectMsg);
    } else {
        $redirectURL = "dashboard.php?msg=error&info=" . urlencode("Jenis data tidak dikenali.");
    }

    // Eksekusi
    if ($stmt->execute()) {
        $showLoading = true;
    } else {
        $redirectURL = "dashboard.php?msg=error&info=" . urlencode("Gagal menghapus data dari database.");
    }
} else {
    $redirectURL = "dashboard.php?msg=error&info=" . urlencode("Parameter tidak lengkap.");
}
?>

<?php if ($showLoading): ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Menghapus...</title>
  <meta http-equiv="refresh" content="2;url=<?= $redirectURL ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #e0f7fa, #80deea);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
    .loading-box {
      text-align: center;
      background: #ffffffdd;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 0 15px rgba(0,0,0,0.2);
    }
    .loading-box h4 {
      margin-top: 20px;
      color: #007bff;
    }
  </style>
</head>
<body>
  <div class="loading-box">
    <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
      <span class="visually-hidden">Loading...</span>
    </div>
    <h4><?= htmlspecialchars($redirectMsg) ?></h4>
    <p class="text-muted">Anda akan diarahkan dalam beberapa detik...</p>
  </div>
</body>
</html>
<?php else: ?>
<?php header("Location: $redirectURL"); exit(); ?>
<?php endif; ?>
