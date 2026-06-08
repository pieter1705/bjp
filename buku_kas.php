<?php
$conn = mysqli_connect("localhost", "root", "", "koperasi_harian");

// 1. Proses Simpan Data
if (isset($_POST['simpan'])) {
    $tgl = $_POST['tanggal'];
    $ket = $_POST['keterangan'];
    $tipe = $_POST['tipe'];
    $nom = $_POST['nominal'];

    $insert = mysqli_query($conn, "INSERT INTO buku_kas (tanggal, keterangan, tipe, nominal) VALUES ('$tgl', '$ket', '$tipe', '$nom')");
    if ($insert) {
        echo "<script>alert('Transaksi Berhasil Disimpan!'); window.location='buku_kas.php';</script>";
    }
}

// 2. Ambil Ringkasan Saldo
$sql_res = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN tipe='masuk' THEN nominal ELSE 0 END) as masuk,
    SUM(CASE WHEN tipe='keluar' THEN nominal ELSE 0 END) as keluar 
    FROM buku_kas");
$res = mysqli_fetch_assoc($sql_res);
$total_masuk = $res['masuk'] ?? 0;
$total_keluar = $res['keluar'] ?? 0;
$saldo_akhir = $total_masuk - $total_keluar;

// 3. Ambil Data Tabel
$query_table = mysqli_query($conn, "SELECT * FROM buku_kas ORDER BY tanggal DESC, id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Kas Modern - Bhakti Jaya Prima</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            --accent-gradient: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            --glass: rgba(255, 255, 255, 0.9);
        }

        body { 
            background-color: #f1f5f9; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #1e293b; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
        }

        /* Modern Navigation Bar */
        .navbar-main {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            padding: 1rem 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .nav-link-custom {
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 10px 20px;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .btn-back { background: #fff; color: #64748b; border: 1px solid #e2e8f0; }
        .btn-back:hover { background: #f8fafc; color: #1e293b; transform: translateX(-3px); }

        .btn-dash { background: var(--accent-gradient); color: white; border: none; }
        .btn-dash:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3); color: white; }

        .brand-text { font-weight: 800; font-size: 1.25rem; letter-spacing: -0.5px; }

        /* Main Content Styling */
        .main-wrapper { flex: 1; padding: 3rem 0; }
        .card-summary { 
            border: none; 
            border-radius: 24px; 
            background: white; 
            padding: 1.5rem; 
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); 
            transition: transform 0.3s ease;
        }
        .card-summary:hover { transform: translateY(-5px); }

        .form-kas { 
            background: white; 
            border-radius: 24px; 
            padding: 2rem; 
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05); 
            margin-bottom: 2rem; 
        }

        .table-container { 
            background: white; 
            border-radius: 24px; 
            padding: 1.5rem; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); 
        }

        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            font-weight: 600;
        }

        .btn-save {
            background: #f39c12;
            color: white;
            border-radius: 12px;
            border: none;
            padding: 12px;
            font-weight: 700;
            transition: 0.3s;
        }
        .btn-save:hover { background: #e67e22; box-shadow: 0 8px 20px rgba(243, 156, 18, 0.3); }

        /* Modern Footer */
        .footer-main {
            background: #0f172a;
            color: #94a3b8;
            padding: 4rem 0 2rem;
            margin-top: auto;
            position: relative;
        }
        .footer-main::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }
    </style>
</head>
<body>

<nav class="navbar-main sticky-top">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 d-flex gap-3">
                <button onclick="history.back()" class="nav-link-custom btn-back shadow-sm">
                    <i class="fas fa-chevron-left me-2"></i>Kembali
                </button>
                <a href="dashboard.php" class="nav-link-custom btn-dash shadow-sm">
                    <i class="fas fa-grid-2 me-2"></i>Dashboard
                </a>
            </div>
            <div class="col-md-4 text-center d-none d-md-block">
                <div class="brand-text text-dark">BHAK'TI JAYA <span class="text-success">PRIMA</span></div>
            </div>
            <div class="col-md-4 text-end d-none d-md-flex justify-content-end align-items-center gap-2">
                <span class="small fw-bold text-muted">Bhakti Jaya Prima</span>
                <div class="bg-success rounded-circle" style="width: 8px; height: 8px;"></div>
            </div>
        </div>
    </div>
</nav>

<div class="main-wrapper">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold mb-2">FINANCE MODULE</span>
                <h1 class="fw-800 m-0 display-6">Buku Kas Nasional</h1>
            </div>
            <a href="export_kas.php" target="_blank" class="btn btn-dark rounded-pill px-4 py-2 fw-bold shadow">
                <i class="fas fa-file-pdf me-2 text-warning"></i>Cetak Laporan
            </a>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card-summary border-start border-primary border-5">
                    <small class="text-muted fw-bold text-uppercase small">Saldo Akhir Terkini</small>
                    <h2 class="fw-800 text-dark mt-2 mb-0">Rp <?= number_format($saldo_akhir, 0, ',', '.'); ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-summary border-start border-success border-5">
                    <small class="text-muted fw-bold text-uppercase small">Total Debit (Masuk)</small>
                    <h2 class="fw-800 text-success mt-2 mb-0">Rp <?= number_format($total_masuk, 0, ',', '.'); ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-summary border-start border-danger border-5">
                    <small class="text-muted fw-bold text-uppercase small">Total Kredit (Keluar)</small>
                    <h2 class="fw-800 text-danger mt-2 mb-0">Rp <?= number_format($total_keluar, 0, ',', '.'); ?></h2>
                </div>
            </div>
        </div>

        <div class="form-kas">
            <h5 class="fw-bold mb-4 d-flex align-items-center">
                <span class="bg-warning p-2 rounded-3 me-3 text-white"><i class="fas fa-plus"></i></span>
                Input Transaksi Baru
            </h5>
            <form action="" method="POST" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" required value="<?= date('Y-m-d'); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Keterangan Transaksi</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Pembayaran Listrik" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipe Kas</label>
                    <select name="tipe" class="form-select">
                        <option value="masuk">Masuk (Debit)</option>
                        <option value="keluar">Keluar (Kredit)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nominal</label>
                    <input type="number" name="nominal" class="form-control" placeholder="0" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" name="simpan" class="btn btn-save w-100">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">TANGGAL</th>
                        <th>KETERANGAN</th>
                        <th>KATEGORI</th>
                        <th class="text-end">NOMINAL</th>
                        <th class="text-center">OPSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($query_table)): ?>
                    <tr>
                        <td class="ps-4 fw-medium text-secondary small"><?= date('d M Y', strtotime($row['tanggal'])); ?></td>
                        <td class="fw-bold"><?= $row['keterangan']; ?></td>
                        <td>
                            <span class="badge <?= $row['tipe'] == 'masuk' ? 'bg-success' : 'bg-danger'; ?>-subtle text-<?= $row['tipe'] == 'masuk' ? 'success' : 'danger'; ?> border-0 px-3 py-2 rounded-pill fw-bold">
                                <?= strtoupper($row['tipe']); ?>
                            </span>
                        </td>
                        <td class="text-end fw-800 <?= $row['tipe'] == 'masuk' ? 'text-success' : 'text-danger'; ?>">
                            <?= $row['tipe'] == 'masuk' ? '+' : '-'; ?> Rp <?= number_format($row['nominal'], 0, ',', '.'); ?>
                        </td>
                        <td class="text-center">
                            <a href="hapus_kas.php?id=<?= $row['id']; ?>" 
                               class="btn btn-outline-danger btn-sm rounded-3 px-3" 
                               onclick="return confirm('Hapus transaksi ini?')">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="footer-main">
    <div class="container">
        <div class="row gy-4 align-items-center">
            <div class="col-md-6">
                <h4 class="fw-800 text-white mb-1">BHAK'TI JAYA PRIMA</h4>
                <p class="mb-0 small text-uppercase letter-spacing-1">Integrated Operational & Finance Management System</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex justify-content-md-end gap-3 mb-3">
                    <a href="#" class="text-white opacity-50"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-white opacity-50"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white opacity-50"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <p class="mb-0 small">&copy; 2026 Sistem KSP Harian Bhak'Ti Jaya Prima | Ambon, Maluku</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>