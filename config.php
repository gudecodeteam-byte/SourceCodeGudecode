<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'rental_motor';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
