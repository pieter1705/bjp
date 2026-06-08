<?php
// 1. Koneksi ke Database
$conn = mysqli_connect("localhost", "root", "", "koperasi_harian");

// 2. Query Ambil Data dari Tabel daftar_gaji
$query = mysqli_query($conn, "SELECT * FROM daftar_gaji ORDER BY id DESC");

// 3. Query Hitung Ringkasan (Statistik)
$total_gaji = mysqli_query($conn, "SELECT SUM(total_diterima) as total FROM daftar_gaji");
$row_total = mysqli_fetch_assoc($total_gaji);

$staf_dibayar = mysqli_query($conn, "SELECT COUNT(*) as jml FROM daftar_gaji WHERE status_bayar = 'Dibayar'");
$row_paid = mysqli_fetch_assoc($staf_dibayar);

$staf_pending = mysqli_query($conn, "SELECT COUNT(*) as jml FROM daftar_gaji WHERE status_bayar = 'Pending'");
$row_pending = mysqli_fetch_assoc($staf_pending);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Gaji - Ringkasan Operasional</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root { --primary-gaji: #27ae60; --bg-body: #f1f5f9; --surface: #ffffff; }
        body { background-color: var(--bg-body); font-family: 'Plus Jakarta Sans', sans-serif; color: #1e293b; }
        .container { max-width: 1200px; }
        
        /* Tombol Kembali Custom */
        .btn-back {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background: #fff;
            color: #64748b;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: 0.3s;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        .btn-back:hover {
            background: #f8fafc;
            color: #1e293b;
            transform: translateX(-5px);
        }

        .card-summary { background: var(--surface); border: none; border-radius: 20px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border-bottom: 4px solid var(--primary-gaji); }
        .gaji-container { background: var(--surface); border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid rgba(226, 232, 240, 0.8); }
        .table thead th { background-color: #f8fafc; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 16px 20px; border: none; }
        .table tbody td { padding: 20px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .status-pill { padding: 6px 14px; border-radius: 10px; font-size: 0.8rem; font-weight: 700; }
        .bg-paid { background-color: #d1fae5; color: #065f46; }
        .bg-pending { background-color: #fee2e2; color: #991b1b; }
        .btn-action { width: 35px; height: 35px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; text-decoration: none; }
    </style>
</head>
<body>

<div class="container py-5">
    
    <a href="index.php" class="btn-back">
        <i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard
    </a>

    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold mb-0">Payroll / Daftar Gaji</h2>
            <p class="text-secondary mb-0">Manajemen pengupahan staf operasional</p>
        </div>
        <a href="tambah_gaji.php" class="btn btn-success rounded-pill px-4 py-2 fw-bold text-white shadow-sm">
            <i class="fas fa-plus me-2"></i> Input Gaji Baru
        </a>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card-summary">
                <small class="text-muted fw-bold">TOTAL PENGELUARAN GAJI</small>
                <h3 class="fw-bold text-dark mt-1">Rp <?= number_format($row_total['total'], 0, ',', '.'); ?></h3>
                <span class="text-muted small">Update: <?= date('d M Y'); ?></span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-summary" style="border-bottom-color: #3498db;">
                <small class="text-muted fw-bold text-primary">STAF DIBAYAR</small>
                <h3 class="fw-bold text-dark mt-1"><?= $row_paid['jml']; ?> Orang</h3>
                <span class="text-muted small">Sudah lunas terbayar</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-summary" style="border-bottom-color: #e74c3c;">
                <small class="text-muted fw-bold text-danger">MENUNGGU PEMBAYARAN</small>
                <h3 class="fw-bold text-dark mt-1"><?= $row_pending['jml']; ?> Orang</h3>
                <span class="text-muted small">Masih dalam antrean</span>
            </div>
        </div>
    </div>

    <div class="gaji-container">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <h5 class="fw-bold m-0">Rincian Slip Gaji</h5>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Staf & Jabatan</th>
                        <th>Gaji Pokok</th>
                        <th>Bonus/Lembur</th>
                        <th>Potongan</th>
                        <th>Total Terima</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="fas fa-user-tie text-secondary"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0"><?= $row['nama_staf']; ?></h6>
                                    <small class="text-muted"><?= $row['jabatan']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td>Rp <?= number_format($row['gaji_pokok'], 0, ',', '.'); ?></td>
                        <td class="text-success">+ Rp <?= number_format($row['bonus'], 0, ',', '.'); ?></td>
                        <td class="text-danger">- Rp <?= number_format($row['potongan'], 0, ',', '.'); ?></td>
                        <td class="fw-bold">Rp <?= number_format($row['total_diterima'], 0, ',', '.'); ?></td>
                        <td class="text-center">
                            <span class="status-pill <?= ($row['status_bayar'] == 'Dibayar') ? 'bg-paid' : 'bg-pending'; ?>">
                                <?= $row['status_bayar']; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="cetak_slip.php?id=<?= $row['id']; ?>" class="btn btn-light btn-action text-primary" title="Cetak Slip"><i class="fas fa-print"></i></a>
                            <a href="hapus_gaji.php?id=<?= $row['id']; ?>" class="btn btn-light btn-action text-danger" onclick="return confirm('Hapus data ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>