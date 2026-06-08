<?php
session_start();
include 'koneksi.php'; 

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

// --- QUERY DATA ---
$query_anggota = mysqli_query($conn, "SELECT COUNT(*) as total FROM anggota");
$total_anggota = ($query_anggota) ? mysqli_fetch_assoc($query_anggota)['total'] : 0;

$tgl_sekarang = date('Y-m-d');
$query_setoran = mysqli_query($conn, "SELECT SUM(nominal_bayar) as total_masuk FROM angsuran WHERE tanggal_bayar = '$tgl_sekarang'");
$data_setoran = mysqli_fetch_assoc($query_setoran);
$total_setoran_hari_ini = $data_setoran['total_masuk'] ?? 0;

$query_belum_bayar = mysqli_query($conn, "SELECT COUNT(p.id_pinjaman) as total 
    FROM pinjaman p 
    WHERE p.status = 'aktif' 
    AND p.id_pinjaman NOT IN (
        SELECT id_pinjaman FROM angsuran WHERE tanggal_bayar = '$tgl_sekarang'
    )");
$res_belum_bayar = mysqli_fetch_assoc($query_belum_bayar);
$nasabah_belum_bayar = $res_belum_bayar['total'] ?? 0;

$query_finance = mysqli_query($conn, "SELECT SUM(plafon_pinjaman) as total_drop, SUM(total_tagihan) as total_tagihan FROM pinjaman WHERE status = 'aktif'");
$data_finance = mysqli_fetch_assoc($query_finance);

$total_droping_aktif = $data_finance['total_drop'] ?? 0;
$total_seluruh_tagihan = $data_finance['total_tagihan'] ?? 0;
$total_tagihan_hari_ini = $total_seluruh_tagihan / 20;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard KSP Bhak'Ti Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; }
        .card-stats { border: none; border-radius: 12px; transition: 0.3s; }
        .card-menu { transition: transform 0.3s; cursor: pointer; border: none; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); height: 100%; }
        .card-menu:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .icon-box { font-size: 2rem; margin-bottom: 10px; color: #0d6efd; }
        .text-label { font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 1.15rem; font-weight: 800; }
        h5.section-title { border-left: 4px solid #0d6efd; padding-left: 15px; margin-bottom: 20px; font-weight: 700; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <img src="logo.jpeg" alt="Logo" width="35" height="35" class="me-2 rounded-circle">KSP BHAK'TI JAYA PRIMA
        </a>
        <div class="navbar-nav ms-auto">
            <span class="nav-link text-white small">Halo, <?= ucfirst($role); ?> | <a href="logout.php" class="text-white fw-bold">Logout</a></span>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold">Ringkasan Operasional</h3>
            <p class="text-muted">Selamat datang, Anda login sebagai <strong><?= strtoupper($role); ?></strong>.</p>
        </div>
    </div>

    <div class="row g-3 mb-5">
        <div class="col-md-2-5 col-md-4 col-6">
            <div class="card card-stats bg-white p-3 shadow-sm h-100 border-start border-success border-4">
                <div class="text-label">Setoran Masuk</div>
                <div class="stat-value text-success">Rp <?= number_format($total_setoran_hari_ini, 0, ',', '.'); ?></div>
            </div>
        </div>
        <div class="col-md-2-5 col-md-4 col-6">
            <div class="card card-stats bg-white p-3 shadow-sm h-100 border-start border-danger border-4">
                <div class="text-label">Belum Bayar</div>
                <div class="stat-value text-danger"><?= $nasabah_belum_bayar; ?> <small class="fw-normal">Orang</small></div>
            </div>
        </div>
        <div class="col-md-2-5 col-md-4 col-6">
            <div class="card card-stats bg-white p-3 shadow-sm h-100 border-start border-dark border-4">
                <div class="text-label">Total Anggota</div>
                <div class="stat-value text-dark"><?= number_format($total_anggota, 0, ',', '.'); ?></div>
            </div>
        </div>
        <?php if ($role != 'petugas') : ?>
        <div class="col-md-2-5 col-md-6 col-6">
            <div class="card card-stats bg-white p-3 shadow-sm h-100 border-start border-warning border-4">
                <div class="text-label">Total Droping</div>
                <div class="stat-value text-warning">Rp <?= number_format($total_droping_aktif, 0, ',', '.'); ?></div>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-md-2-5 col-md-6 col-6">
            <div class="card card-stats bg-white p-3 shadow-sm h-100 border-start border-primary border-4">
                <div class="text-label">Target Tagihan</div>
                <div class="stat-value text-primary">Rp <?= number_format($total_tagihan_hari_ini, 0, ',', '.'); ?></div>
            </div>
        </div>
    </div>

    <h5 class="section-title">Menu Utama</h5>
    <div class="row g-4 mb-5 text-center">
        <div class="col-6 col-md-3">
            <a href="anggota.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-4">
                    <div class="icon-box"><i class="fas fa-users"></i></div>
                    <h6 class="fw-bold small">Data Anggota</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="tambah_pinjaman.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-4">
                    <div class="icon-box text-warning"><i class="fas fa-hand-holding-usd"></i></div>
                    <h6 class="fw-bold small">Drop Pinjaman</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="monitoring.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-4">
                    <div class="icon-box text-success"><i class="fas fa-calendar-check"></i></div>
                    <h6 class="fw-bold small">Tagihan Harian</h6>
                </div>
            </a>
        </div>
        <?php if ($role != 'petugas') : ?>
        <div class="col-6 col-md-3">
            <a href="laporan_kolektor.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-4"> 
                    <div class="icon-box text-danger"><i class="fas fa-chart-line"></i></div>
                    <h6 class="fw-bold small">Lihat Laporan</h6>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($role != 'petugas') : ?>
    <h5 class="section-title">Pembukuan & Administrasi</h5>
    <div class="row g-3 text-center">
        <div class="col-6 col-md-2">
            <a href="buku_angsuran.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-primary"><i class="fas fa-book"></i></div>
                    <h6 class="fw-bold small">Buku Angsuran</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="buku_storting.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-info"><i class="fas fa-file-invoice-dollar"></i></div>
                    <h6 class="fw-bold small">Buku Storting</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="buku_target.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-success"><i class="fas fa-bullseye"></i></div>
                    <h6 class="fw-bold small">Buku Target</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="buku_rekapitulasi.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-secondary"><i class="fas fa-list-alt"></i></div>
                    <h6 class="fw-bold small">Rekapitulasi</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="buku_kas.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-warning"><i class="fas fa-vault"></i></div>
                    <h6 class="fw-bold small">Buku Kas</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="buku_pinjaman.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-primary"><i class="fas fa-address-book"></i></div>
                    <h6 class="fw-bold small">Buku Pinjaman</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="buku_sirkulasi.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-info"><i class="fas fa-sync"></i></div>
                    <h6 class="fw-bold small">Buku Sirkulasi</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="macet_berjalan.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-warning"><i class="fas fa-exclamation-triangle"></i></div>
                    <h6 class="fw-bold small">Buku 2 (Macet)</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="buku_macet_total.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-danger"><i class="fas fa-skull-crossbones"></i></div>
                    <h6 class="fw-bold small">Buku 3 (Macet T)</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="buku_bon.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-danger"><i class="fas fa-receipt"></i></div>
                    <h6 class="fw-bold small">Buku Bon</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="buku_transaksi.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-dark"><i class="fas fa-exchange-alt"></i></div>
                    <h6 class="fw-bold small">Buku Transaksi</h6>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="daftar_gaji.php" class="text-decoration-none text-dark">
                <div class="card card-menu p-3">
                    <div class="icon-box text-success"><i class="fas fa-money-check-alt"></i></div>
                    <h6 class="fw-bold small">Daftar Gaji</h6>
                </div>
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<footer class="mt-5 py-4 text-center text-muted">
    <hr class="container mb-4">
    <small>&copy; 2026 Sistem KSP Harian Bhak'Ti Jaya Prima | Ambon, Maluku</small>
</footer>

</body>
</html>