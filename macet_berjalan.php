<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// 1. Pengaturan Waktu
$bulan = isset($_GET['bulan']) && $_GET['bulan'] != "" ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) && $_GET['tahun'] != "" ? (int)$_GET['tahun'] : (int)date('Y');

if ($bulan < 1 || $bulan > 12) $bulan = (int)date('m');
if ($tahun < 1) $tahun = (int)date('Y');

$list_bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

// Logika Hari Kerja (S/D Hari ini)
$hari_ini = (int)date('d');
if ($bulan != date('m') || $tahun != date('Y')) {
    $hari_ini = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
}

// 2. Query Utama
$query = "SELECT 
            p.id_pinjaman, 
            a.nama, 
            p.tgl_cair, 
            p.nominal_bayar as target_harian, 
            p.total_tagihan,
            IFNULL(SUM(ang.nominal_bayar), 0) as total_bayar_bulan_ini
          FROM pinjaman p
          JOIN anggota a ON p.id_anggota = a.id_anggota
          LEFT JOIN angsuran ang ON p.id_pinjaman = ang.id_pinjaman 
            AND MONTH(ang.tanggal_bayar) = $bulan 
            AND YEAR(ang.tanggal_bayar) = $tahun
          WHERE p.status = 'aktif'
          GROUP BY p.id_pinjaman
          HAVING total_bayar_bulan_ini < (target_harian * $hari_ini)";

$result = mysqli_query($conn, $query);
$count_macet = mysqli_num_rows($result);
$total_tunggakan_global = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Macet Berjalan Dashboard | KSP Bhak'Ti Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.95);
            --primary-gradient: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            --danger-rose: #f43f5e;
            --soft-rose: #fff1f2;
            --text-main: #1e293b;
        }

        body { 
            background: #f8fafc;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--text-main);
        }

        .navbar-brand-custom {
            font-weight: 800;
            letter-spacing: -1px;
            color: #1e293b;
            font-size: 1.5rem;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            padding: 2rem;
        }

        .stat-card {
            border: none;
            border-radius: 20px;
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); }

        .icon-shape {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .progress { height: 8px; border-radius: 10px; background: #e2e8f0; }
        .progress-bar { border-radius: 10px; background-color: var(--danger-rose); }

        .table thead th {
            background: #f1f5f9;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            padding: 1.2rem;
        }

        .table tbody tr { border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .table tbody tr:last-child { border: none; }

        .avatar-circle {
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 14px;
        }

        .btn-filter {
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
        }

        .btn-filter:hover { color: #f8fafc; opacity: 0.9; }

        @media print {
            .no-print { display: none; }
            body { background: white; }
            .glass-card { box-shadow: none; border: 1px solid #eee; }
        }
    </style>
</head>
<body class="py-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-5 no-print">
        <div class="navbar-brand-custom">KSP <span class="text-danger">Bhak'Ti</span> Jaya</div>
        <div class="d-flex gap-3">
            <button onclick="window.print()" class="btn btn-white shadow-sm rounded-pill px-4 fw-bold">
                <i class="fa fa-print me-2"></i>Export PDF
            </button>
            <a href="index.php" class="btn btn-dark shadow rounded-pill px-4 fw-bold">
                <i class="fa fa-home"></i>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-4">
            <div class="card stat-card shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-danger text-white me-3 shadow-danger">
                        <i class="fa fa-users-slash fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-bold">Anggota Macet</p>
                        <h2 class="fw-bold mb-0"><?= $count_macet ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card stat-card shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-primary text-white me-3">
                        <i class="fa fa-calendar-day fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-bold">Hari Berjalan</p>
                        <h2 class="fw-bold mb-0"><?= $hari_ini ?> <span class="fs-6 text-muted">/ <?= cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun) ?></span></h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card stat-card shadow-sm p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-warning text-white me-3">
                        <i class="fa fa-chart-pie fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0 fw-bold">Periode Analisa</p>
                        <h2 class="fw-bold mb-0 fs-4"><?= strtoupper($list_bulan[$bulan]) ?> <?= $tahun ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <div class="no-print">
            <form method="GET" class="row g-3 mb-5 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-secondary">Pilih Bulan</label>
                    <select name="bulan" class="form-select form-select-lg border-0 bg-light rounded-4">
                        <?php foreach($list_bulan as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $k == $bulan ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-secondary">Tahun</label>
                    <input type="number" name="tahun" class="form-select-lg form-control border-0 bg-light rounded-4" value="<?= $tahun ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-filter btn-lg w-100 shadow">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Anggota</th>
                        <th class="text-center">Drop</th>
                        <th class="text-end">Target Harian</th>
                        <th class="text-end">Realisasi / Target</th>
                        <th class="text-end pe-4">Nominal Macet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                            $seharusnya = $row['target_harian'] * $hari_ini;
                            $tunggakan = $seharusnya - $row['total_bayar_bulan_ini'];
                            $percent = ($row['total_bayar_bulan_ini'] / $seharusnya) * 100;
                            $total_tunggakan_global += $tunggakan;
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3">
                                    <?= substr($row['nama'], 0, 1) ?>
                                </div>
                                <div>
                                    <div class="fw-bold"><?= strtoupper($row['nama']) ?></div>
                                    <span class="text-muted small">ID: #<?= $row['id_pinjaman'] ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="text-center text-secondary small"><?= date('d/m/y', strtotime($row['tgl_cair'])) ?></td>
                        <td class="text-end fw-semibold">Rp<?= number_format($row['target_harian'], 0, ',', '.') ?></td>
                        <td class="text-end" style="min-width: 200px;">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="fw-bold">Rp<?= number_format($row['total_bayar_bulan_ini'], 0, ',', '.') ?></span>
                                <span class="text-muted">Rp<?= number_format($seharusnya, 0, ',', '.') ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $percent ?>%"></div>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <span class="text-danger fw-800 fs-6">
                                Rp<?= number_format($tunggakan, 0, ',', '.') ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center py-5">Semua lancar! Tidak ada kemacetan periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($count_macet > 0): ?>
        <div class="mt-5 p-4 rounded-4" style="background: var(--soft-rose);">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-danger text-uppercase letter-spacing-1">Estimasi Total Kerugian Berjalan</span>
                <h3 class="fw-800 text-danger mb-0">Rp<?= number_format($total_tunggakan_global, 0, ',', '.') ?></h3>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<footer class="text-center py-5 text-muted small">
    &copy; 2026 KSP Bhak'Ti Jaya Prima - Integrated Financial Analytics
</footer>

</body>
</html>