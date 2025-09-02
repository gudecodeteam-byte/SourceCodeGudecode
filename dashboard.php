<?php
session_start();

require 'config.php';
require_once 'dompdf/vendor/autoload.php'; // autoload dompdf

use Dompdf\Dompdf;

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// === CETAK PDF ===
if (isset($_GET['cetak_pdf']) && is_numeric($_GET['cetak_pdf'])) {
    $id = (int) $_GET['cetak_pdf'];
    $data = $conn->query("SELECT * FROM booking WHERE id = $id")->fetch_assoc();

    if ($data) {
        $kode = $data['kode_booking'];
        $nama = $data['nama'];
        $email = $data['email'];
        $hp = $data['nomor_hp'];
        $motor = $data['motor'];
        $tanggal = $data['tanggal'];
        $durasi = $data['durasi'];
        $bukti = $data['bukti_file'] ? "uploads/" . $data['bukti_file'] : 'Belum ada bukti';

        $html = "
        <h2 style='text-align:center;'>Bukti Transaksi Pemesanan</h2>
        <hr>
        <table style='width:100%; font-size:14px;'>
            <tr><td><strong>Kode Booking</strong></td><td>:</td><td>$kode</td></tr>
            <tr><td><strong>Nama</strong></td><td>:</td><td>$nama</td></tr>
            <tr><td><strong>Email</strong></td><td>:</td><td>$email</td></tr>
            <tr><td><strong>No. HP</strong></td><td>:</td><td>$hp</td></tr>
            <tr><td><strong>Motor</strong></td><td>:</td><td>$motor</td></tr>
            <tr><td><strong>Tanggal Sewa</strong></td><td>:</td><td>$tanggal</td></tr>
            <tr><td><strong>Durasi</strong></td><td>:</td><td>$durasi hari</td></tr>
            <tr><td><strong>Bukti</strong></td><td>:</td><td>$bukti</td></tr>
        </table>
        <br><br>
        <p style='text-align:right;'>Admin GoodRide</p>
        ";

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("bukti_transaksi_$kode.pdf", ["Attachment" => true]);
        exit;
    } else {
        echo "Data tidak ditemukan.";
        exit;
    }
}

// === Statistik ===
$totalBooking = $conn->query("SELECT COUNT(*) as total FROM booking")->fetch_assoc()['total'];
$bookingToday = $conn->query("SELECT COUNT(*) as total FROM booking WHERE DATE(tanggal) = CURDATE()")->fetch_assoc()['total'];
$totalGuests = $conn->query("SELECT COUNT(*) as total FROM guest_messages")->fetch_assoc()['total'];
$totalVerified = $conn->query("SELECT COUNT(*) as total FROM booking WHERE bukti_file IS NOT NULL AND bukti_file != ''")->fetch_assoc()['total'];

// === Filter ===
$filterNamaBooking = isset($_GET['nama_booking']) ? $_GET['nama_booking'] : '';
$filterNamaGuest = isset($_GET['nama_guest']) ? $_GET['nama_guest'] : '';

$bookingSql = "SELECT * FROM booking";
if ($filterNamaBooking !== '') {
    $bookingSql .= " WHERE nama LIKE '%" . $conn->real_escape_string($filterNamaBooking) . "%'";
}
$bookingSql .= " ORDER BY id DESC";
$bookingResult = $conn->query($bookingSql);

$guestSql = "SELECT * FROM guest_messages";
if ($filterNamaGuest !== '') {
    $guestSql .= " WHERE name LIKE '%" . $conn->real_escape_string($filterNamaGuest) . "%'";
}
$guestSql .= " ORDER BY created_at DESC";
$guestResult = $conn->query($guestSql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - GoodRide</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
    .table thead { background-color: #007bff; color: white; }
    .table tbody tr:hover { background-color: #f1f1f1; }
    .container { margin-top: 30px; }
    .section-title { margin-top: 40px; margin-bottom: 20px; }
    .stat-card { border-radius: 10px; }
  </style>
</head>
<body>

<header>
  <h1>Dashboard Admin - GoodRide</h1>
  <p>Kelola Data Pemesanan & Buku Tamu</p>
</header>

<div class="container">
  <!-- Statistik Ringkas -->
  <div class="row text-center mb-4">
    <div class="col-md-3">
      <div class="bg-primary text-white p-3 stat-card">
        <h5>Total Booking</h5>
        <p class="fs-4"><?= $totalBooking ?></p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="bg-success text-white p-3 stat-card">
        <h5>Booking Hari Ini</h5>
        <p class="fs-4"><?= $bookingToday ?></p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="bg-info text-white p-3 stat-card">
        <h5>Total Pesan Tamu</h5>
        <p class="fs-4"><?= $totalGuests ?></p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="bg-warning text-dark p-3 stat-card">
        <h5>Bukti Terverifikasi</h5>
        <p class="fs-4"><?= $totalVerified ?></p>
      </div>
    </div>
  </div>

  <!-- Filter Booking -->
  <form class="row g-3 mb-4" method="GET">
    <div class="col-md-4">
      <label for="nama_booking" class="form-label">Cari Nama Pemesan</label>
      <input type="text" class="form-control" name="nama_booking" id="nama_booking" value="<?= htmlspecialchars($filterNamaBooking) ?>" placeholder="Nama pemesan...">
    </div>
    <div class="col-md-4 align-self-end">
      <button type="submit" class="btn btn-primary">Terapkan Filter</button>
      <a href="dashboard.php" class="btn btn-secondary">Reset</a>
    </div>
  </form>

  <!-- Data Booking -->
  <h4 class="section-title">Data Pemesanan</h4>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>No</th>
        <th>Kode Booking</th>
        <th>Nama</th>
        <th>Email</th>
        <th>No HP</th>
        <th>Motor</th>
        <th>Tanggal</th>
        <th>Durasi</th>
        <th>Bukti</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $no = 1;
      if ($bookingResult && $bookingResult->num_rows > 0) {
        while ($row = $bookingResult->fetch_assoc()) {
          if (empty($row['kode_booking'])) {
            $kodeBaru = 'GD-' . strtoupper(substr(md5($row['id'] . $row['nama']), 0, 6));
            $updateKode = $conn->prepare("UPDATE booking SET kode_booking = ? WHERE id = ?");
            $updateKode->bind_param("si", $kodeBaru, $row['id']);
            $updateKode->execute();
            $row['kode_booking'] = $kodeBaru;
          }

          // Tampilkan hanya tombol Cetak PDF
          $bukti = "<a href='?cetak_pdf={$row['id']}' class='btn btn-sm btn-outline-success'>Cetak PDF</a>";

          echo "<tr>
                  <td>{$no}</td>
                  <td>{$row['kode_booking']}</td>
                  <td>{$row['nama']}</td>
                  <td>{$row['email']}</td>
                  <td>{$row['nomor_hp']}</td>
                  <td>{$row['motor']}</td>
                  <td>{$row['tanggal']}</td>
                  <td>{$row['durasi']} hari</td>
                  <td>$bukti</td>
                  <td>
                    <a href='delete.php?type=booking&id={$row['id']}' onclick=\"return confirm('Hapus data ini?')\" class='btn btn-danger btn-sm'>Hapus</a>
                  </td>
                </tr>";
          $no++;
        }
      } else {
        echo "<tr><td colspan='10' class='text-center'>Tidak ada data pemesanan.</td></tr>";
      }
      ?>
    </tbody>
  </table>

  <!-- Filter Buku Tamu -->
  <form class="row g-3 mb-4" method="GET">
    <div class="col-md-4">
      <label for="nama_guest" class="form-label">Cari Nama Tamu</label>
      <input type="text" class="form-control" name="nama_guest" id="nama_guest" value="<?= htmlspecialchars($filterNamaGuest) ?>" placeholder="Nama tamu...">
    </div>
    <div class="col-md-4 align-self-end">
      <button type="submit" class="btn btn-primary">Terapkan Filter</button>
      <a href="dashboard.php" class="btn btn-secondary">Reset</a>
    </div>
  </form>

  <!-- Data Buku Tamu -->
  <h4 class="section-title">Data Buku Tamu</h4>
  <table class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Email</th>
        <th>No HP</th>
        <th>Pesan</th>
        <th>Tanggal</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $no = 1;
      if ($guestResult && $guestResult->num_rows > 0) {
        while ($row = $guestResult->fetch_assoc()) {
          echo "<tr>
                  <td>{$no}</td>
                  <td>{$row['name']}</td>
                  <td>{$row['email']}</td>
                  <td>{$row['phone']}</td>
                  <td>{$row['message']}</td>
                  <td>{$row['created_at']}</td>
                  <td>
                    <a href='delete.php?type=guest&id={$row['id']}' onclick=\"return confirm('Hapus pesan ini?')\" class='btn btn-danger btn-sm'>Hapus</a>
                  </td>
                </tr>";
          $no++;
        }
      } else {
        echo "<tr><td colspan='7' class='text-center'>Tidak ada pesan tamu.</td></tr>";
      }
      ?>
    </tbody>
  </table>

  <div class="mt-4 d-flex justify-content-between">
    <a href="index.html" class="btn btn-secondary">← Kembali ke Halaman Utama</a>
    <a href="?logout=true" class="btn btn-light">Logout</a>
  </div>
</div>

</body>
</html>
