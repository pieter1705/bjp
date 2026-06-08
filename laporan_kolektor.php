<?php
session_start();
include 'koneksi.php';

// Proteksi login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$nama_user_login = $_SESSION['nama']; 
$tgl_sekarang = date('d/m/Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setoran Kolektor - KSP Bhak'Ti Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .header-section { background: white; padding: 20px 0; margin-bottom: 30px; border-bottom: 1px solid #dee2e6; }
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .table-thead { background-color: #f8f9fa; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        .avatar-circle { width: 35px; height: 35px; background: #e7f1ff; color: #0d6efd; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
        .badge-status { font-weight: 500; padding: 6px 12px; border-radius: 20px; }
        .btn-dashboard { border-radius: 10px; font-weight: 600; }
        .total-box { background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 10px; padding: 15px; }
    </style>
</head>
<body>

<div class="header-section">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold mb-0 text-primary"><i class="fas fa-wallet me-2"></i>Setoran Kolektor</h4>
            <small class="text-muted">Manajemen rekapitulasi harian kas masuk</small>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-dashboard">
            <i class="fas fa-th-large me-2"></i>Dashboard
        </a>
    </div>
</div>

<div class="container">
    <div class="card card-custom mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label class="small fw-bold text-muted mb-2">PERIODE LAPORAN</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-muted"></i></span>
                        <input type="text" class="form-control" value="<?= $tgl_sekarang; ?>" readonly>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="d-none d-md-block mb-2">&nbsp;</label>
                    <button class="btn btn-primary w-100 fw-bold">Tampilkan</button>
                </div>
                <div class="col-md-6 text-md-end">
                    <label class="d-none d-md-block mb-2">&nbsp;</label>
                    <span class="badge bg-info-subtle text-info p-2 px-3">Data Tanggal: <?= $tgl_sekarang; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom">
        <div class="card-header bg-dark p-3 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
            <div>
                <h6 class="text-white mb-0 fw-bold">Daftar Kolektor Aktif</h6>
                <small class="text-white-50">Verifikasi setoran tunai harian kolektor</small>
            </div>
            <button class="btn btn-light btn-sm fw-bold"><i class="fas fa-print me-2"></i>Cetak Laporan</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-thead text-muted">
                        <tr>
                            <th class="ps-4">Nama Kolektor</th>
                            <th>Anggota Terbayar</th>
                            <th>Total Setoran</th>
                            <th>Status Rekon</th>
                            <th class="text-center">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-3"><i class="fas fa-user"></i></div>
                                    <div>
                                        <div class="fw-bold"><?= $nama_user_login; ?> <span class="badge bg-primary-subtle text-primary small ms-1" style="font-size: 10px;">Anda</span></div>
                                        <small class="text-muted text-uppercase" style="font-size: 10px;"><?= $role; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">0 Orang</span></td>
                            <td class="fw-bold text-primary">Rp 0</td>
                            <td><span class="badge bg-secondary-subtle text-secondary badge-status">Kosong</span></td>
                            <td class="text-center"><span class="text-muted small">N/A</span></td>
                        </tr>

                        <?php 
                        // Jika Admin/Manager, tampilkan kolektor/petugas lainnya
                        if($role == 'admin' || $role == 'manager') {
                            $query = mysqli_query($conn, "SELECT nama_lengkap, role FROM users WHERE nama_lengkap != '$nama_user_login'");
                            while($row = mysqli_fetch_assoc($query)) { ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3 bg-light text-muted"><i class="fas fa-user"></i></div>
                                        <div>
                                            <div class="fw-bold"><?= $row['nama_lengkap']; ?></div>
                                            <small class="text-muted text-uppercase" style="font-size: 10px;"><?= $row['role']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">0 Orang</span></td>
                                <td class="fw-bold text-primary">Rp 0</td>
                                <td><span class="badge bg-secondary-subtle text-secondary badge-status">Kosong</span></td>
                                <td class="text-center"><span class="text-muted small">N/A</span></td>
                            </tr>
                        <?php } 
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white p-4">
            <div class="row justify-content-between align-items-center">
                <div class="col-md-6">
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Status otomatis berubah <strong>"Verifikasi"</strong> setelah tombol verif ditekan.</small>
                </div>
                <div class="col-md-4">
                    <div class="total-box text-center">
                        <small class="text-muted fw-bold d-block mb-1">TOTAL KAS MASUK</small>
                        <h3 class="fw-bold text-primary mb-0">Rp 0</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="text-center mt-5 mb-4 text-muted small">
    &copy; 2026 KSP Bhak'Ti Jaya - Ambon, Maluku
</footer>

</body>
</html> 