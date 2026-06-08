<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
    header("Location: buku_sirkulasi.php");
    exit();
}

$tgl = $_GET['tgl'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Review Transaksi Tanggal <?= $tgl ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="m-0">Transaksi Tanggal: <?= date('d F Y', strtotime($tgl)) ?></h5>
        </div>
        <div class="card-body">
            <p class="text-muted small">Catatan: Untuk mengubah angka sirkulasi, silakan edit data angsuran atau pinjaman di bawah ini.</p>
            
            <h6>1. Daftar Angsuran (Masuk)</h6>
            <table class="table table-sm table-bordered mb-4">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nominal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q = mysqli_query($conn, "SELECT * FROM angsuran WHERE DATE(tanggal_bayar) = '$tgl'");
                    while($d = mysqli_fetch_assoc($q)){
                        echo "<tr>
                                <td>{$d['id_angsuran']}</td>
                                <td>Rp ".number_format($d['nominal_bayar'])."</td>
                                <td><a href='edit_angsuran.php?id={$d['id_angsuran']}' class='btn btn-xs btn-warning'>Ubah Data</a></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>

            <h6>2. Daftar Pencairan Pinjaman (Keluar)</h6>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>ID Pinjam</th>
                        <th>Plafon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q = mysqli_query($conn, "SELECT * FROM pinjaman WHERE DATE(tgl_cair) = '$tgl'");
                    while($d = mysqli_fetch_assoc($q)){
                        echo "<tr>
                                <td>{$d['id_pinjaman']}</td>
                                <td>Rp ".number_format($d['plafon_pinjaman'])."</td>
                                <td><a href='edit_pinjaman.php?id={$d['id_pinjaman']}' class='btn btn-xs btn-warning'>Ubah Data</a></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <hr>
            <a href="buku_sirkulasi.php" class="btn btn-secondary">Kembali ke Buku Sirkulasi</a>
        </div>
    </div>
</div>
</body>
</html>