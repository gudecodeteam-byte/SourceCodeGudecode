<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = htmlspecialchars(trim($_POST['name']));
    $email   = htmlspecialchars(trim($_POST['email']));
    $phone   = htmlspecialchars(trim($_POST['phone']));
    $message = htmlspecialchars(trim($_POST['message']));

    $stmt = $conn->prepare("INSERT INTO guest_messages (name, email, phone, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $name, $email, $phone, $message);

    if ($stmt->execute()) {
        echo "<script>alert('Pesan berhasil dikirim!'); window.location.href='index.html';</script>";
    } else {
        echo "Gagal menyimpan pesan: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
