<?php
session_start();
include 'koneksi.php';

// 1. SETTING PERIODE
$bulan_aktif_num = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun_aktif = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$list_bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$bulan_aktif = $list_bulan[$bulan_aktif_num];
$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan_aktif_num, $tahun_aktif);

// --- PERBAIKAN NAMA KOLOM ---
// Sesuaikan nominal_pinjam dan tgl_pinjam dengan nama asli di tabel pinjaman Anda
$col_nominal = "plafon_pinjaman"; // Ubah jika nama kolomnya berbeda (misal: jumlah_pinjam)
$col_tanggal = "tgl_cair";       // Ubah jika nama kolomnya berbeda (misal: tanggal_pinjam)

// 2. QUERY STATISTIK UTAMA
$q_storting = mysqli_query($conn, "SELECT SUM(nominal_bayar) as total FROM angsuran WHERE MONTH(tanggal_bayar) = '$bulan_aktif_num' AND YEAR(tanggal_bayar) = '$tahun_aktif'");
$data_storting = mysqli_fetch_assoc($q_storting);
$total_storting_bulan = $data_storting['total'] ?? 0;

// Proteksi Query Droping agar tidak Fatal Error jika kolom salah
$q_droping = mysqli_query($conn, "SELECT SUM($col_nominal) as total FROM pinjaman WHERE MONTH($col_tanggal) = '$bulan_aktif_num' AND YEAR($col_tanggal) = '$tahun_aktif'");
if (!$q_droping) {
    // Jika masih error, gunakan nilai 0 sementara agar halaman tetap tampil
    $total_droping_bulan = 0;
    $error_msg = "Error: Kolom $col_nominal atau $col_tanggal tidak ditemukan di tabel pinjaman.";
} else {
    $data_droping = mysqli_fetch_assoc($q_droping);
    $total_droping_bulan = $data_droping['total'] ?? 0;
}

// 3. AMBIL DATA HARIAN UNTUK CHART & TABEL (Gunakan kolom yang sudah diperbaiki)
$data_harian = [];
$query_harian = "SELECT 
                    tgl, 
                    SUM(storting) as tot_storting, 
                    SUM(droping) as tot_droping 
                 FROM (
                    SELECT tanggal_bayar as tgl, nominal_bayar as storting, 0 as droping FROM angsuran 
                    UNION ALL 
                    SELECT $col_tanggal as tgl, 0 as storting, $col_nominal as droping FROM pinjaman
                 ) as gabungan 
                 WHERE MONTH(tgl) = '$bulan_aktif_num' AND YEAR(tgl) = '$tahun_aktif'
                 GROUP BY DATE(tgl)";

$res_harian = mysqli_query($conn, $query_harian);
if ($res_harian) {
    while($row = mysqli_fetch_assoc($res_harian)) {
        $data_harian[date('Y-m-d', strtotime($row['tgl']))] = [
            'storting' => $row['tot_storting'],
            'droping' => $row['tot_droping']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Analytics - Premium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #4f46e5; --warning: #f59e0b; --danger: #ef4444; --success: #10b981; --bg-body: #f8fafc; }
        body { background-color: var(--bg-body); font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; padding-bottom: 50px; }
        .page-header { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 80px 0 120px; color: white; margin-bottom: -60px; position: relative; }
        .btn-back { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); color: white; border: 1px solid rgba(255, 255, 255, 0.2); padding: 10px 20px; border-radius: 14px; text-decoration: none; font-weight: 600; position: absolute; left: 20px; top: 20px; }
        .card { border: none; border-radius: 24px; box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05); background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); margin-bottom: 24px; }
        .stat-card { border-left: 6px solid; padding: 25px; }
        .stat-label { font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; }
        .stat-value { font-size: 1.75rem; font-weight: 800; margin-top: 5px; }
        .table thead th { background-color: #f1f5f9; text-transform: uppercase; font-size: 0.65rem; font-weight: 800; color: #475569; padding: 20px; }
        .sunday-row { background-color: #fff1f2 !important; }
        .badge-modern { padding: 8px 16px; border-radius: 12px; font-weight: 700; font-size: 0.7rem; }
        .bg-success-subtle { background-color: #d1fae5; color: #065f46; }
        .bg-warning-subtle { background-color: #fef3c7; color: #92400e; }
    </style>
</head>
<body>

<header class="page-header text-center">
    <a href="index.php" class="btn-back"><i class="fas fa-chevron-left"></i> Kembali</a>
    <div class="container">
        <h1 class="fw-800 mb-2">Analisis Rekapitulasi</h1>
        <p class="opacity-75">Ringkasan performa finansial periode <?= $bulan_aktif ?> <?= $tahun_aktif ?></p>
        <?php if(isset($error_msg)): ?>
            <div class="alert alert-danger d-inline-block mt-3 py-1 px-3 small"><?= $error_msg ?></div>
        <?php endif; ?>
    </div>
</header>

<div class="container">
    <div class="row g-4 mb-2">
        <div class="col-lg-8">
            <div class="card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0"><i class="fas fa-chart-line text-primary me-2"></i>Arus Kas Mingguan</h5>
                    <span class="badge bg-light text-dark rounded-pill px-3"><?= $bulan_aktif ?></span>
                </div>
                <div style="height: 350px;"><canvas id="rekapChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="row g-3">
                <div class="col-12">
                    <div class="card stat-card" style="border-color: var(--warning);">
                        <span class="stat-label">Total Droping</span>
                        <h3 class="stat-value text-dark">Rp <?= number_format($total_droping_bulan, 0, ',', '.') ?></h3>
                        <div class="d-flex align-items-center mt-2">
                            <i class="fas fa-arrow-up text-success me-2"></i>
                            <span class="text-success fw-bold small">Otomatis</span>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card stat-card" style="border-color: var(--primary);">
                        <span class="stat-label">Target Tagihan</span>
                        <h3 class="stat-value text-dark">Rp <?= number_format($total_storting_bulan * 1.1, 0, ',', '.') ?></h3>
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: 65%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="card-header bg-white p-4 border-0"><h5 class="fw-bold m-0">Performa Bulanan</h5></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Storting (Setoran)</th>
                        <th>Droping (Pinjaman)</th>
                        <th class="text-center">Efektivitas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold"><?= $bulan_aktif ?> <?= $tahun_aktif ?></td>
                        <td class="text-success fw-800">Rp <?= number_format($total_storting_bulan, 0, ',', '.') ?></td>
                        <td class="fw-600 text-dark">Rp <?= number_format($total_droping_bulan, 0, ',', '.') ?></td>
                        <td class="text-center">
                            <?php 
                                $status = ($total_storting_bulan > $total_droping_bulan) ? "Sangat Baik" : "Perlu Evaluasi";
                                $color = ($total_storting_bulan > $total_droping_bulan) ? "bg-success-subtle" : "bg-warning-subtle";
                            ?>
                            <span class="badge-modern <?= $color ?>"><?= $status ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold m-0">Rincian Performa Harian</h5>
            <span class="small text-muted fw-bold"><?= $bulan_aktif ?></span>
        </div>
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="sticky-top shadow-sm">
                    <tr>
                        <th class="text-center">Tgl</th>
                        <th>Storting</th>
                        <th>Droping</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($i = 1; $i <= $jumlah_hari; $i++) {
                        $tgl_key = "$tahun_aktif-" . str_pad($bulan_aktif_num, 2, '0', STR_PAD_LEFT) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
                        $timestamp = strtotime($tgl_key);
                        $is_minggu = (date('N', $timestamp) == 7);
                        
                        $st = isset($data_harian[$tgl_key]) ? $data_harian[$tgl_key]['storting'] : 0;
                        $dr = isset($data_harian[$tgl_key]) ? $data_harian[$tgl_key]['droping'] : 0;
                    ?>
                    <tr class="<?= $is_minggu ? 'sunday-row' : '' ?>">
                        <td class="text-center fw-bold <?= $is_minggu ? 'text-danger' : '' ?>"><?= $i ?></td>
                        <td class="text-success fw-bold"><?= $st > 0 ? 'Rp '.number_format($st,0,',','.') : '-' ?></td>
                        <td class="text-warning fw-bold"><?= $dr > 0 ? 'Rp '.number_format($dr,0,',','.') : '-' ?></td>
                        <td class="text-center">
                            <?php if($is_minggu): ?> <i class="fas fa-moon text-muted"></i>
                            <?php elseif($st > $dr): ?> <i class="fas fa-arrow-trend-up text-success"></i>
                            <?php else: ?> <i class="fas fa-arrow-trend-down text-danger"></i>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('rekapChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mgg 1', 'Mgg 2', 'Mgg 3', 'Mgg 4'],
            datasets: [{
                label: 'Setoran',
                data: [3500000, 4200000, 3800000, 5200000], 
                borderColor: '#4f46e5',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(79, 70, 229, 0.1)'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
</body>
</html>