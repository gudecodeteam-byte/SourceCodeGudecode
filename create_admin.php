<?php
require 'config.php';

$username = 'admin';
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashedPassword);

if ($stmt->execute()) {
    echo "Admin berhasil dibuat!";
} else {
    echo "Gagal: " . $stmt->error;
}
?>
