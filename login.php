<?php
session_start();
require 'config.php';

// Jika sudah login, arahkan langsung ke dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password_input = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM admin WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $data = $result->fetch_assoc();
            if (password_verify($password_input, $data['password'])) {
                // Login sukses
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $username; // Simpan username jika perlu
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Password salah.';
            }
        } else {
            $error = 'Username tidak ditemukan.';
        }
        $stmt->close(); // Tutup statement
    } else {
        $error = 'Terjadi kesalahan saat koneksi ke server.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - GoodRide</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('assets/background.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.92);
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25);
            width: 350px;
        }

        .logo {
            width: 120px;
            margin-bottom: 20px;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 90%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        button {
            width: 95%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #218838;
        }

        .error {
            margin-top: 15px;
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="login-box">
    <img src="goodride.png" alt="Logo GoodRide" class="logo">
    <h2>Login Admin</h2>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required autofocus>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Masuk</button>
    </form>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</div>
</body>
</html>
