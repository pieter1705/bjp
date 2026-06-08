<?php
$conn = mysqli_connect("localhost", "root", "", "koperasi_harian");

// Ambil semua data urut dari yang terlama ke terbaru untuk hitung saldo berjalan
$query = mysqli_query($conn, "SELECT * FROM buku_kas ORDER BY tanggal ASC, id ASC");

// Ringkasan footer
$sql_summary = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN tipe='masuk' THEN nominal ELSE 0 END) as masuk,
    SUM(CASE WHEN tipe='keluar' THEN nominal ELSE 0 END) as keluar 
    FROM buku_kas");
$summary = mysqli_fetch_assoc($sql_summary);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Kas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', serif; padding: 30px; font-size: 13px; }
        .header-print { border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000 !important; padding: 8px !important; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

<div class="no-print text-center mb-4">
    <button onclick="window.print()" class="btn btn-primary">Klik Cetak</button>
</div>

<div class="header-print d-flex justify-content-between">
    <div>
        <h3 class="fw-bold mb-0">BHAK'TI JAYA PRIMA</h3>
        <p class="mb-0">Laporan Buku Kas Operasional</p>
        <small>Per Tanggal: <?= date('d F Y'); ?></small>
    </div>
    <div class="text-end">
        <p class="mb-0">Dicetak oleh: Admin</p>
    </div>
</div>

<table class="table">
    <thead class="table-light">
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Keterangan</th>
            <th>Masuk (Debit)</th>
            <th>Keluar (Kredit)</th>
            <th>Saldo Akhir</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $running_balance = 0;
        while($row = mysqli_fetch_assoc($query)): 
            if($row['tipe'] == 'masuk') { $running_balance += $row['nominal']; }
            else { $running_balance -= $row['nominal']; }
        ?>
        <tr>
            <td class="text-center"><?= $no++; ?></td>
            <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
            <td><?= $row['keterangan']; ?></td>
            <td class="text-end text-success"><?= $row['tipe'] == 'masuk' ? 'Rp '.number_format($row['nominal'], 0, ',', '.') : '-'; ?></td>
            <td class="text-end text-danger"><?= $row['tipe'] == 'keluar' ? 'Rp '.number_format($row['nominal'], 0, ',', '.') : '-'; ?></td>
            <td class="text-end fw-bold">Rp <?= number_format($running_balance, 0, ',', '.'); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
    <tfoot class="fw-bold">
        <tr>
            <td colspan="3" class="text-center">TOTAL</td>
            <td class="text-end text-success">Rp <?= number_format($summary['masuk'], 0, ',', '.'); ?></td>
            <td class="text-end text-danger">Rp <?= number_format($summary['keluar'], 0, ',', '.'); ?></td>
            <td class="text-end bg-light">Rp <?= number_format($summary['masuk'] - $summary['keluar'], 0, ',', '.'); ?></td>
        </tr>
    </tfoot>
</table>

<div class="row mt-5 text-center">
    <div class="col-8"></div>
    <div class="col-4">
        <p>Ambon, <?= date('d F Y'); ?></p>
        <p class="mb-5">Mengetahui, Bendahara</p>
        <br><br>
        <strong>( Pieter Toisuta )</strong>
    </div>
</div>

</body>
</html>