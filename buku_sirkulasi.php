<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// Cek Otoritas Edit & Tambah
$can_edit = ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'manager');

// 1. PENGATURAN WAKTU
$bulan = isset($_GET['bulan']) && $_GET['bulan'] != "" ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) && $_GET['tahun'] != "" ? (int)$_GET['tahun'] : (int)date('Y');

if ($bulan < 1 || $bulan > 12) $bulan = (int)date('m');
if ($tahun < 1) $tahun = (int)date('Y');

$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
$list_bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

// 2. LOGIKA SALDO AWAL
$query_saldo_awal_masuk = mysqli_query($conn, "SELECT SUM(nominal_bayar) as total FROM angsuran WHERE tanggal_bayar < '$tahun-$bulan-01'");
$row_masuk = mysqli_fetch_assoc($query_saldo_awal_masuk);
$saldo_awal = $row_masuk['total'] ?? 0;

// 3. QUERY DATA HARIAN (MASUK)
$data_harian = [];
$query_masuk = "SELECT DATE(tanggal_bayar) as tgl, SUM(nominal_bayar) as masuk, COUNT(id_angsuran) as jml 
                FROM angsuran WHERE MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ? GROUP BY DATE(tanggal_bayar)";
$stmt1 = mysqli_prepare($conn, $query_masuk);
mysqli_stmt_bind_param($stmt1, "ii", $bulan, $tahun);
mysqli_stmt_execute($stmt1);
$res_masuk = mysqli_stmt_get_result($stmt1);
while ($row = mysqli_fetch_assoc($res_masuk)) {
    $data_harian[$row['tgl']]['masuk'] = $row['masuk'];
    $data_harian[$row['tgl']]['count'] = $row['jml'];
}

// 4. QUERY DATA HARIAN (KELUAR)
$query_keluar = "SELECT DATE(tgl_cair) as tgl, SUM(plafon_pinjaman) as keluar 
                 FROM pinjaman WHERE MONTH(tgl_cair) = ? AND YEAR(tgl_cair) = ? GROUP BY DATE(tgl_cair)";
$stmt2 = mysqli_prepare($conn, $query_keluar);
if($stmt2){
    mysqli_stmt_bind_param($stmt2, "ii", $bulan, $tahun);
    mysqli_stmt_execute($stmt2);
    $res_keluar = mysqli_stmt_get_result($stmt2);
    while ($row = mysqli_fetch_assoc($res_keluar)) {
        $data_harian[$row['tgl']]['keluar'] = $row['keluar'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Sirkulasi - KSP Bhak'Ti Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2563eb; --bg: #f8fafc; }
        body { background-color: var(--bg); font-family: 'Inter', sans-serif; font-size: 12px; }
        .main-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-top: 20px; }
        .table thead th { background: #1e293b; color: white; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; vertical-align: middle; }
        .bg-sunday { background-color: #fff1f2 !important; }
        .badge-sunday { background-color: #ef4444; color: white; padding: 2px 6px; border-radius: 4px; font-size: 9px; }
        .text-nominal { font-family: 'Courier New', monospace; font-weight: bold; }
        .btn-edit { padding: 2px 8px; font-size: 10px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="main-card">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <div>
                <h4 class="fw-bold m-0 text-primary">BUKU SIRKULASI HARIAN</h4>
                <p class="text-muted m-0">Periode: <?= $list_bulan[$bulan] ?> <?= $tahun ?></p>
            </div>
            <div class="d-flex gap-2">
                <?php if($can_edit): ?>
                <div class="dropdown">
                    <button class="btn btn-sm btn-success dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-plus-circle me-1"></i> Tambah Data
                    </button>
                    <ul class="dropdown-menu shadow">
                        <li><a class="dropdown-item small" href="tambah_angsuran.php"><i class="fas fa-hand-holding-dollar me-2 text-success"></i>Angsuran (Masuk)</a></li>
                        <li><a class="dropdown-item small" href="tambah_pinjaman.php"><i class="fas fa-money-bill-transfer me-2 text-danger"></i>Pinjaman (Keluar)</a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <button onclick="window.print()" class="btn btn-sm btn-dark shadow-sm"><i class="fas fa-print me-1"></i> Cetak</button>
                <a href="index.php" class="btn btn-sm btn-outline-secondary shadow-sm">Kembali</a>
            </div>
        </div>

        <form method="GET" class="row g-2 mb-4 no-print bg-light p-3 rounded border align-items-end">
            <div class="col-md-3">
                <label class="small fw-bold text-muted">Bulan</label>
                <select name="bulan" class="form-select form-select-sm">
                    <?php foreach($list_bulan as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $k == $bulan ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small fw-bold text-muted">Tahun</label>
                <input type="number" name="tahun" class="form-control form-control-sm" value="<?= $tahun ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100 shadow-sm">Tampilkan</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="text-center">
                    <tr>
                        <th rowspan="2" width="40">NO</th>
                        <th rowspan="2">TANGGAL</th>
                        <th rowspan="2">SALDO AWAL</th>
                        <th colspan="2">ARUS KAS</th>
                        <th rowspan="2">SALDO AKHIR</th>
                        <?php if($can_edit): ?><th rowspan="2" class="no-print">AKSI</th><?php endif; ?>
                    </tr>
                    <tr>
                        <th width="150">MASUK (DEBET)</th>
                        <th width="150">KELUAR (KREDIT)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $current_saldo = $saldo_awal;
                    $total_m = 0; $total_k = 0;

                    for($i=1; $i<=$jumlah_hari; $i++): 
                        $tgl_full = "$tahun-" . str_pad($bulan, 2, "0", STR_PAD_LEFT) . "-" . str_pad($i, 2, "0", STR_PAD_LEFT);
                        $is_sun = (date('N', strtotime($tgl_full)) == 7);
                        
                        $masuk = $data_harian[$tgl_full]['masuk'] ?? 0;
                        $keluar = $data_harian[$tgl_full]['keluar'] ?? 0;
                        
                        $saldo_awal_hari_ini = $current_saldo;
                        $current_saldo = ($saldo_awal_hari_ini + $masuk) - $keluar;
                        
                        $total_m += $masuk; $total_k += $keluar;
                    ?>
                    <tr class="<?= $is_sun ? 'bg-sunday' : '' ?>">
                        <td class="text-center"><?= $i ?></td>
                        <td>
                            <?= date('d/m/y', strtotime($tgl_full)) ?>
                            <?= $is_sun ? '<span class="badge-sunday">MNG</span>' : '' ?>
                        </td>
                        <td class="text-end text-muted">Rp <?= number_format($saldo_awal_hari_ini, 0, ',', '.') ?></td>
                        <td class="text-end text-success fw-bold text-nominal">
                            <?= $masuk > 0 ? 'Rp '.number_format($masuk, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end text-danger fw-bold text-nominal">
                            <?= $keluar > 0 ? 'Rp '.number_format($keluar, 0, ',', '.') : '-' ?>
                        </td>
                        <td class="text-end fw-bold bg-light">Rp <?= number_format($current_saldo, 0, ',', '.') ?></td>
                        <?php if($can_edit): ?>
                        <td class="text-center no-print">
                            <a href="edit_sirkulasi.php?tgl=<?= $tgl_full ?>" class="btn btn-warning btn-edit text-white shadow-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endfor; ?>
                </tbody>
                <tfoot class="table-dark text-end">
                    <tr>
                        <td colspan="3" class="text-center fw-bold">TOTAL SIRKULASI BULAN INI</td>
                        <td class="text-nominal text-success">Rp <?= number_format($total_m, 0, ',', '.') ?></td>
                        <td class="text-nominal text-danger">Rp <?= number_format($total_k, 0, ',', '.') ?></td>
                        <td class="bg-primary text-white">Rp <?= number_format($current_saldo, 0, ',', '.') ?></td>
                        <?php if($can_edit): ?><td class="no-print"></td><?php endif; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>