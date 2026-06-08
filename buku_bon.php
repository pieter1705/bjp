<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

/**
 * 1. QUERY UTAMA: BUKU BON
 * Menggunakan Subquery agar terhindar dari error ONLY_FULL_GROUP_BY 
 * dan memastikan perhitungan saldo akurat.
 */
$query = "SELECT 
            b.id_bon, 
            a.nama, 
            b.tgl_bon, 
            b.nominal_bon,
            b.keterangan,
            (SELECT IFNULL(SUM(nominal_bayar), 0) 
             FROM bayar_bon 
             WHERE id_bon = b.id_bon) as total_bayar
          FROM bon b
          JOIN anggota a ON b.id_anggota = a.id_anggota
          HAVING total_bayar < b.nominal_bon
          ORDER BY b.tgl_bon DESC";

$result = mysqli_query($conn, $query);
$count_bon = ($result) ? mysqli_num_rows($result) : 0;
$grand_total_bon = 0; // Akan dihitung di dalam loop
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Bon Nasabah | KSP Bhak'Ti Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0f172a;
            --accent: #6366f1;
            --success: #10b981;
            --bg: #f8fafc;
        }

        body { 
            background: var(--bg);
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #1e293b;
        }

        .hero-bon {
            background: linear-gradient(135deg, #4f46e5 0%, #0f172a 100%);
            padding: 70px 0 130px;
            color: white;
            border-radius: 0 0 50px 50px;
        }

        .summary-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 24px;
            padding: 25px;
            margin-top: -90px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05);
        }

        .search-container {
            background: white;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
            padding: 8px 15px;
        }

        .search-container input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 14px;
            margin-left: 10px;
        }

        .glass-panel {
            background: white;
            border-radius: 30px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            margin-top: 30px;
        }

        .table thead th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #64748b;
            padding: 20px;
            border: none;
        }

        .user-icon {
            width: 40px;
            height: 40px;
            background: #f1f5f9;
            color: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .sisa-tagihan {
            color: #ef4444;
            background: #fff1f2;
            padding: 5px 12px;
            border-radius: 10px;
            font-weight: 700;
        }

        .status-badge {
            font-size: 10px;
            padding: 4px 10px;
            border-radius: 6px;
            background: #e0e7ff;
            color: #4338ca;
            font-weight: 700;
        }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="hero-bon no-print">
    <div class="container text-center">
        <h1 class="fw-800 mb-2">BUKU BON NASABAH</h1>
        <p class="opacity-75">Rekapitulasi pinjaman sementara & dana talangan KSP Bhak'Ti Jaya</p>
    </div>
</div>

<div class="container">
    <div class="summary-card">
        <div class="row align-items-center g-4">
            <div class="col-md-3 border-end">
                <p class="text-muted small fw-bold mb-1">JUMLAH BON</p>
                <h3 class="fw-800 mb-0"><?= $count_bon ?> <small class="fs-6 text-muted">Baris</small></h3>
            </div>
            <div class="col-md-5 border-end">
                <p class="text-muted small fw-bold mb-1">TOTAL SALDO BON</p>
                <h3 class="fw-800 text-primary mb-0" id="totalBonDisplay text-danger">Rp 0</h3>
            </div>
            <div class="col-md-4">
                <div class="search-container d-flex align-items-center">
                    <i class="fa fa-search text-muted"></i>
                    <input type="text" id="searchBon" placeholder="Cari nama nasabah...">
                </div>
            </div>
        </div>
    </div>

    <div class="glass-panel mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Rincian Bon Berjalan</h5>
            <div class="d-flex gap-2 no-print">
                <button type="button" class="btn btn-primary btn-sm rounded-3 px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="fa fa-plus me-1"></i> Tambah Bon
                </button>
                <button onclick="window.print()" class="btn btn-light btn-sm rounded-3 px-3">Cetak</button>
                <a href="index.php" class="btn btn-dark btn-sm rounded-3 px-3">Dashboard</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>NASABAH</th>
                        <th>TANGGAL</th>
                        <th>KETERANGAN</th>
                        <th class="text-end">NOMINAL</th>
                        <th class="text-end">SISA BON</th>
                    </tr>
                </thead>
                <tbody id="bonTableBody">
                    <?php 
                    if($result && mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                            $sisa = $row['nominal_bon'] - $row['total_bayar'];
                            $grand_total_bon += $sisa;
                    ?>
                    <tr class="bon-row">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-icon me-3"><?= substr($row['nama'], 0, 1) ?></div>
                                <div>
                                    <div class="fw-bold row-nama"><?= strtoupper($row['nama']) ?></div>
                                    <span class="status-badge">BON-<?= $row['id_bon'] ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted small"><?= date('d M Y', strtotime($row['tgl_bon'])) ?></td>
                        <td class="small"><?= $row['keterangan'] ?></td>
                        <td class="text-end fw-bold">Rp<?= number_format($row['nominal_bon'], 0, ',', '.') ?></td>
                        <td class="text-end">
                            <span class="sisa-tagihan">Rp<?= number_format($sisa, 0, ',', '.') ?></span>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">Semua lunas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Input Bon Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses_bon.php" method="POST">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Anggota</label>
                        <select name="id_anggota" class="form-select border-0 bg-light rounded-3" required>
                            <option value="">-- Pilih Anggota --</option>
                            <?php
                            $q_ang = mysqli_query($conn, "SELECT id_anggota, nama FROM anggota ORDER BY nama ASC");
                            while($a = mysqli_fetch_assoc($q_ang)) echo "<option value='".$a['id_anggota']."'>".strtoupper($a['nama'])."</option>";
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nominal (Rp)</label>
                        <input type="number" name="nominal_bon" class="form-control border-0 bg-light rounded-3" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Keterangan</label>
                        <textarea name="keterangan" class="form-control border-0 bg-light rounded-3" rows="3"></textarea>
                    </div>
                    <input type="hidden" name="tgl_bon" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="simpan_bon" class="btn btn-primary w-100 rounded-3 fw-bold">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Update total di header setelah PHP selesai menghitung di loop
    document.getElementById('totalBonDisplay').innerText = 'Rp <?= number_format($grand_total_bon, 0, ',', '.') ?>';

    // Search Filter Instan
    document.getElementById('searchBon').addEventListener('keyup', function() {
        let val = this.value.toUpperCase();
        let rows = document.querySelectorAll('.bon-row');
        rows.forEach(row => {
            let nama = row.querySelector('.row-nama').innerText;
            row.style.display = nama.toUpperCase().includes(val) ? "" : "none";
        });
    });
</script>

</body>
</html>