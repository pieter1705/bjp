<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// 1. Pengaturan Filter Tanggal
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

/**
 * 2. QUERY GABUNGAN (UNION)
 * Menggabungkan data Pinjaman (Uang Keluar) dan Angsuran (Uang Masuk)
 * agar tampil dalam satu urutan waktu (kronologis).
 */
$query = "(SELECT 
            tgl_cair as tanggal, 
            'Pencairan Pinjaman' as tipe, 
            a.nama as nasabah, 
            plafon_pinjaman as nominal, 
            'keluar' as arus
          FROM pinjaman p
          JOIN anggota a ON p.id_anggota = a.id_anggota
          WHERE tgl_cair BETWEEN '$tgl_awal' AND '$tgl_akhir')
          
          UNION ALL
          
          (SELECT 
            tanggal_bayar as tanggal, 
            'Setoran Angsuran' as tipe, 
            a.nama as nasabah, 
            ang.nominal_bayar as nominal, 
            'masuk' as arus
          FROM angsuran ang
          JOIN pinjaman p ON ang.id_pinjaman = p.id_pinjaman
          JOIN anggota a ON p.id_anggota = a.id_anggota
          WHERE tanggal_bayar BETWEEN '$tgl_awal' AND '$tgl_akhir')
          
          ORDER BY tanggal DESC";

$result = mysqli_query($conn, $query);

// Variabel Rekapitulasi
$total_masuk = 0;
$total_keluar = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Transaksi | KSP Bhak'Ti Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0f172a;
            --success: #10b981;
            --danger: #ef4444;
            --bg: #f8fafc;
        }

        body { 
            background: var(--bg);
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #1e293b;
        }

        .header-gradient {
            background: linear-gradient(135deg, #3bcf5b 0%, #1e3b2a 100%);
            padding: 40px 0 100px;
            color: white;
            border-radius: 0 0 40px 40px;
        }

        .card-summary {
            background: white;
            border: none;
            border-radius: 20px;
            padding: 20px;
            margin-top: -60px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .card-summary:hover { transform: translateY(-5px); }

        .filter-panel {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            margin-top: 25px;
        }

        .table-panel {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-top: 25px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .badge-masuk { background: #d1fae5; color: #059669; font-weight: 700; padding: 6px 12px; border-radius: 10px; }
        .badge-keluar { background: #fee2e2; color: #dc2626; font-weight: 700; padding: 6px 12px; border-radius: 10px; }
        
        .table thead th {
            background: #f1f5f9;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            padding: 15px;
        }

        .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media print { .no-print { display: none; } body { background: white; } }
    </style>
</head>
<body>

<div class="header-gradient no-print text-center">
    <div class="container">
        <h2 class="fw-800 mb-1">BUKU TRANSAKSI</h2>
        <p class="opacity-75">Riwayat Arus Kas Masuk & Keluar KSP Bhak'Ti Jaya</p>
    </div>
</div>

<div class="container">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card-summary border-start border-4 border-success">
                <p class="text-muted small fw-bold mb-1">TOTAL MASUK</p>
                <h3 class="fw-800 text-success mb-0" id="txtMasuk">Rp 0</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-summary border-start border-4 border-danger">
                <p class="text-muted small fw-bold mb-1">TOTAL KELUAR</p>
                <h3 class="fw-800 text-danger mb-0" id="txtKeluar">Rp 0</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-summary border-start border-4 border-primary">
                <p class="text-muted small fw-bold mb-1">SALDO PERIODE</p>
                <h3 class="fw-800 text-primary mb-0" id="txtSaldo">Rp 0</h3>
            </div>
        </div>
    </div>

    <div class="filter-panel no-print">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Dari Tanggal</label>
                <input type="date" name="tgl_awal" class="form-control rounded-3" value="<?= $tgl_awal ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Sampai Tanggal</label>
                <input type="date" name="tgl_akhir" class="form-control rounded-3" value="<?= $tgl_akhir ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-bold"><i class="fa fa-filter me-2"></i>Filter</button>
            </div>
            <div class="col-md-2">
            <a href="index.php" class="btn btn-dark btn-sm rounded-3 px-3">Dashboard</a>
            </div>
            <!-- <div class="col-md-2">
                <button onclick="window.print()" class="btn btn-light w-100 rounded-3 fw-bold"><i class="fa fa-print me-2"></i>Cetak</button>
            </div> -->
            
        </form>
    </div>

    <div class="table-panel mb-5">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>TANGGAL</th>
                        <th>JENIS TRANSAKSI</th>
                        <th>NASABAH</th>
                        <th class="text-end">NOMINAL</th>
                        <th class="text-center">ARUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                            if($row['arus'] == 'masuk') $total_masuk += $row['nominal'];
                            else $total_keluar += $row['nominal'];
                    ?>
                    <tr>
                        <td class="fw-bold small"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="icon-box me-2 <?= $row['arus'] == 'masuk' ? 'bg-light text-success' : 'bg-light text-danger' ?>">
                                    <i class="fa <?= $row['arus'] == 'masuk' ? 'fa-arrow-down' : 'fa-arrow-up' ?>"></i>
                                </div>
                                <span class="small fw-bold"><?= $row['tipe'] ?></span>
                            </div>
                        </td>
                        <td class="text-uppercase small fw-bold"><?= $row['nasabah'] ?></td>
                        <td class="text-end fw-800">Rp<?= number_format($row['nominal'], 0, ',', '.') ?></td>
                        <td class="text-center">
                            <span class="<?= $row['arus'] == 'masuk' ? 'badge-masuk' : 'badge-keluar' ?>">
                                <?= strtoupper($row['arus']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">Tidak ada transaksi pada periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Update summary text
    document.getElementById('txtMasuk').innerText = 'Rp <?= number_format($total_masuk, 0, ',', '.') ?>';
    document.getElementById('txtKeluar').innerText = 'Rp <?= number_format($total_keluar, 0, ',', '.') ?>';
    document.getElementById('txtSaldo').innerText = 'Rp <?= number_format($total_masuk - $total_keluar, 0, ',', '.') ?>';
</script>

</body>
</html>