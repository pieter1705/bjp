<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

/**
 * QUERY UTAMA: MACET TOTAL
 * Menggunakan Subquery untuk menghitung total terbayar agar lebih akurat
 */
$query = "SELECT 
            p.id_pinjaman, 
            a.nama, 
            p.tgl_cair, 
            p.plafon_pinjaman,
            p.total_tagihan,
            p.nominal_bayar as target_harian,
            (SELECT IFNULL(SUM(nominal_bayar), 0) 
             FROM angsuran 
             WHERE id_pinjaman = p.id_pinjaman) as total_terbayar
          FROM pinjaman p
          JOIN anggota a ON p.id_anggota = a.id_anggota
          WHERE p.status = 'aktif'
          HAVING total_terbayar < p.total_tagihan
          ORDER BY (p.total_tagihan - total_terbayar) DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Kesalahan Query: " . mysqli_error($conn));
}

$count_macet = mysqli_num_rows($result);
$grand_total_piutang = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Macet Total | KSP Bhak'Ti Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #080908;
            --danger: #ef4444;
            --warning: #f59e0b;
            --success: #10b981;
            --glass: rgba(255, 255, 255, 0.95);
        }

        body { 
            background: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #060706;
        }

        .hero-section {
            background: linear-gradient(135deg, #35fd96 0%, #1e293b 100%);
            padding: 60px 0 120px;
            color: white;
            border-radius: 0 0 50px 50px;
        }

        .summary-card {
            background: var(--glass);
            border: none;
            border-radius: 24px;
            padding: 24px;
            margin-top: -80px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05);
            backdrop-filter: blur(10px);
        }

        .search-box {
            background: white;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
            padding: 10px 20px;
            transition: all 0.3s;
        }

        .search-box:focus-within {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(38, 234, 142, 0.1);
        }

        .search-box input {
            border: none;
            outline: none;
            width: 100%;
            margin-left: 10px;
            font-weight: 500;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: #f1f5f9;
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .table-panel {
            background: var(--glass);
            border-radius: 30px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            margin-top: 30px;
        }

        .status-pill {
            font-size: 10px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 8px;
            text-transform: uppercase;
        }

        .pill-danger { background: #fee2e2; color: #ef4444; }
        .pill-warning { background: #fef3c7; color: #d97706; }
        .pill-success { background: #d1fae5; color: #059669; }

        .progress-custom {
            height: 6px;
            background: #f1f5f9;
            border-radius: 10px;
            margin-top: 5px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }

        .amount-badge {
            background: #fff1f2;
            color: #e11d48;
            font-weight: 800;
            padding: 8px 14px;
            border-radius: 12px;
            font-size: 14px;
        }

        tr { transition: transform 0.2s; }
        tr:hover { transform: scale(1.01); background: rgba(248, 250, 252, 0.8); }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="hero-section no-print text-center">
    <div class="container">
        <h1 class="fw-800 mb-2">ANALISA KEMACETAN TOTAL</h1>
        <p class="opacity-75">Sistem Pemantauan Saldo Piutang Tertahan KSP Bhak'Ti Jaya</p>
    </div>
</div>

<div class="container">
    <div class="summary-card">
        <div class="row align-items-center g-4">
            <div class="col-md-3 border-end">
                <div class="text-muted small fw-bold">TOTAL NASABAH</div>
                <div class="fs-2 fw-800 text-primary"><?= $count_macet ?> <small class="fs-6">Nasabah</small></div>
            </div>
            <div class="col-md-5 border-end">
                <div class="text-muted small fw-bold text-danger">TOTAL OUTSTANDING MACET</div>
                <div class="fs-2 fw-800 text-danger" id="grandTotal">Rp 0</div>
            </div>
            <div class="col-md-4">
                <div class="search-box d-flex align-items-center">
                    <i class="fa fa-search text-muted"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama nasabah...">
                </div>
            </div>
        </div>
    </div>

    <div class="table-panel mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Rincian Saldo Piutang</h5>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-light btn-sm rounded-3"><i class="fa fa-print me-1"></i> Cetak</button>
                <a href="index.php" class="btn btn-dark btn-sm rounded-3 px-3">Dashboard</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle" id="macetTable">
                <thead>
                    <tr class="text-muted small">
                        <th>NASABAH</th>
                        <th class="text-center">STATUS</th>
                        <th class="text-end">TAGIHAN AWAL</th>
                        <th class="text-center">PROGRESS</th>
                        <th class="text-end">SISA MACET</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if($count_macet > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                            $saldo_macet = $row['total_tagihan'] - $row['total_terbayar'];
                            $grand_total_piutang += $saldo_macet;
                            $persen = ($row['total_terbayar'] / $row['total_tagihan']) * 100;
                            
                            // Logika Kategori
                            if($persen < 30) { $pill = "pill-danger"; $status = "Berat"; $color = "#ef4444"; }
                            elseif($persen < 70) { $pill = "pill-warning"; $status = "Sedang"; $color = "#f59e0b"; }
                            else { $pill = "pill-success"; $status = "Ringan"; $color = "#10b981"; }
                    ?>
                    <tr class="nasabah-row">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3 text-uppercase"><?= substr($row['nama'], 0, 1) ?></div>
                                <div>
                                    <div class="fw-bold text-dark row-nama"><?= strtoupper($row['nama']) ?></div>
                                    <div class="text-muted small">ID: #<?= $row['id_pinjaman'] ?> • Drop: <?= date('d/m/y', strtotime($row['tgl_cair'])) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="status-pill <?= $pill ?>"><?= $status ?></span>
                        </td>
                        <td class="text-end">
                            <div class="text-muted small" style="font-size: 10px;">Plafon: Rp<?= number_format($row['plafon_pinjaman'], 0, ',', '.') ?></div>
                            <div class="fw-bold">Rp<?= number_format($row['total_tagihan'], 0, ',', '.') ?></div>
                        </td>
                        <td style="min-width: 180px;">
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span><?= number_format($persen, 1) ?>%</span>
                                <span class="text-muted">Masuk: Rp<?= number_format($row['total_terbayar'], 0, ',', '.') ?></span>
                            </div>
                            <div class="progress-custom">
                                <div class="progress-fill" style="width: <?= $persen ?>%; background: <?= $color ?>;"></div>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="amount-badge">Rp<?= number_format($saldo_macet, 0, ',', '.') ?></div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center py-5">Semua Piutang Bersih.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Update Total di Header
    document.getElementById('grandTotal').innerText = 'Rp <?= number_format($grand_total_piutang, 0, ',', '.') ?>';

    // Search Instan (Tanpa Refresh)
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toUpperCase();
        let rows = document.querySelectorAll('.nasabah-row');
        
        rows.forEach(row => {
            let nama = row.querySelector('.row-nama').innerText;
            if (nama.toUpperCase().indexOf(filter) > -1) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
</script>

</body>
</html>