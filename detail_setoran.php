<?php
include 'koneksi.php';

// Validasi input untuk keamanan sederhana
$id_kolektor = isset($_GET['id_kolektor']) ? mysqli_real_escape_string($conn, $_GET['id_kolektor']) : '';
$tgl = isset($_GET['tgl']) ? mysqli_real_escape_string($conn, $_GET['tgl']) : date('Y-m-d');

// Ambil nama kolektor untuk header
$q_kolektor = mysqli_query($conn, "SELECT nama_kolektor FROM user_kolektor WHERE id_user = '$id_kolektor'");
$data_k = mysqli_fetch_assoc($q_kolektor);
$nama_kolektor = $data_k['nama_kolektor'] ?? 'Tidak Diketahui';

$query = "SELECT 
            a.id_angsuran,
            n.nama_anggota, 
            a.nominal_bayar, 
            a.tgl_bayar
          FROM angsuran a
          JOIN anggota n ON a.id_anggota = n.id_anggota 
          WHERE a.id_kolektor = '$id_kolektor' AND DATE(a.tgl_bayar) = '$tgl'";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("<div class='alert alert-danger m-5'>Kesalahan Database: " . mysqli_error($conn) . "</div>");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Setoran - <?= htmlspecialchars($nama_kolektor); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
        }
        .container { max-width: 900px; }
        .main-card {
            background: #ffffff;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .header-gradient {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            padding: 2rem;
            color: white;
            border-bottom: none;
        }
        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 12px;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }
        .info-pill {
            background: #f1f5f9;
            border-radius: 15px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }
        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            display: block;
        }
        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #0f172a;
        }
        .table thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            font-weight: 700;
            border-top: none;
            padding: 1rem;
        }
        .table tbody td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .amount-text {
            color: #10b981;
            font-weight: 700;
        }
        .footer-summary {
            background-color: #f8fafc;
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 2rem;
        }
        .grand-total-label {
            font-weight: 600;
            color: #64748b;
        }
        .grand-total-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container mt-5 mb-5">
        <div class="main-card">
            <div class="header-gradient d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 fw-bold">Rincian Pembayaran</h4>
                    <p class="mb-0 opacity-75 small">Detail transaksi angsuran per anggota</p>
                </div>
                <a href="laporan_setoran.php?tgl=<?= $tgl; ?>" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
            </div>
            
            <div class="card-body p-4 p-md-5">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="info-pill">
                            <span class="info-label">Kolektor Bertugas</span>
                            <span class="info-value"><?= htmlspecialchars($nama_kolektor); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <div class="info-pill text-sm-end">
                            <span class="info-label">Tanggal Laporan</span>
                            <span class="info-value text-primary"><i class="far fa-calendar-alt me-2"></i><?= date('d M Y', strtotime($tgl)); ?></span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th class="text-center" width="80">No</th>
                                <th>Nama Anggota</th>
                                <th class="text-center">Waktu</th>
                                <th class="text-end">Nominal Setoran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            $total = 0;
                            if(mysqli_num_rows($result) > 0):
                                while($row = mysqli_fetch_assoc($result)): 
                                    $total += $row['nominal_bayar'];
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-medium"><?= $no++; ?></td>
                                <td>
                                    <span class="fw-bold d-block text-dark"><?= htmlspecialchars($row['nama_anggota']); ?></span>
                                    <span class="text-muted small">ID Transaksi: #<?= $row['id_angsuran']; ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark fw-medium border"><?= date('H:i', strtotime($row['tgl_bayar'])); ?> WIB</span>
                                </td>
                                <td class="text-end amount-text">
                                    Rp <?= number_format($row['nominal_bayar'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <img src="https://illustrations.popsy.co/gray/data-report.svg" alt="no-data" style="width: 150px; opacity: 0.5;">
                                    <p class="mt-3 text-muted">Belum ada transaksi pembayaran untuk hari ini.</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="footer-summary d-flex justify-content-between align-items-center">
                    <span class="grand-total-label text-uppercase small">Total Penerimaan Kas</span>
                    <span class="grand-total-value">Rp <?= number_format($total, 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>