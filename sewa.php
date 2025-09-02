<?php
session_start();
require 'config.php'; // koneksi database

// Validasi koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Ambil nama motor dari POST atau GET
$motor = isset($_POST['motor']) ? $_POST['motor'] : (isset($_GET['motor']) ? $_GET['motor'] : '');
$status = '';

// Proses saat tombol Kirim ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $nomor_hp = $_POST['nomor_hp'];
    $tanggal = $_POST['tanggal'];
    $durasi = $_POST['durasi'];

    // Validasi input tidak boleh kosong
    if (!empty($motor) && !empty($nama) && !empty($email) && !empty($nomor_hp) && !empty($tanggal) && !empty($durasi)) {
        $stmt = $conn->prepare("INSERT INTO booking (motor, nama, email, nomor_hp, tanggal, durasi) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $motor, $nama, $email, $nomor_hp, $tanggal, $durasi);

        if ($stmt->execute()) {
            $status = '<div class="alert success">✅ Pemesanan berhasil! Silakan cek email Anda untuk konfirmasi.</div>';
        } else {
            $status = '<div class="alert error">❌ Gagal menyimpan data ke database.</div>';
        }
    } else {
        $status = '<div class="alert warning">⚠️ Semua kolom wajib diisi.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Sewa Motor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: url('background.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .wrapper {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px 20px;
            border-radius: 12px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 320px;
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
        }

        .logo {
            max-width: 80px;
            margin-bottom: 10px;
        }

        h2 {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        form {
            margin-top: 10px;
            text-align: left;
        }

        label {
            margin-top: 12px;
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="number"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 13px;
        }

        button {
            margin-top: 18px;
            padding: 10px;
            width: 100%;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        .alert {
            padding: 10px;
            margin-top: 15px;
            border-radius: 6px;
            font-size: 13px;
            text-align: left;
        }

        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .warning { background-color: #fff3cd; color: #856404; }

        .btn {
            display: inline-block;
            padding: 6px 10px;
            margin-top: 12px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            transition: 0.3s ease;
        }

        .btn-warning {
            background-color: #ffc107;
            color: black;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <img src="goodride.png" alt="GoodRide Logo" class="logo">
    <h2>Form Sewa Motor: <?= htmlspecialchars($motor) ?></h2>

    <?= $status ?>

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
        <input type="hidden" name="motor" value="<?= htmlspecialchars($motor) ?>">

        <label for="nama">Nama:</label>
        <input type="text" id="nama" name="nama" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="nomor_hp">Nomor HP:</label>
        <input type="text" id="nomor_hp" name="nomor_hp" required>

        <label for="tanggal">Tanggal Sewa:</label>
        <input type="date" id="tanggal" name="tanggal" required>

        <label for="durasi">Durasi (hari):</label>
        <input type="number" id="durasi" name="durasi" min="1" required>

        <button type="submit">Kirim</button>
    </form>

    <!-- Tombol Login Admin -->
    <div style="margin-top: 20px;">
        <a href="dashboard.php" class="btn btn-warning">🔐 Login Admin</a>
    </div>
</div>

</body>
</html>
