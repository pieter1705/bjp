<?php
session_start();
include 'koneksi.php';

// 1. PROSES SIMPAN DATA (Jika Form di-submit)
if (isset($_POST['simpan'])) {
    $tanggal = $_POST['tanggal'];
    $id_pinjaman = $_POST['id_pinjaman']; 
    $jumlah = $_POST['jumlah'];
    
    $query_insert = "INSERT INTO angsuran (id_pinjaman, nominal_bayar, tanggal_bayar, status_verifikasi) 
                    VALUES ('$id_pinjaman', '$jumlah', '$tanggal', '1')";
    
    if (mysqli_query($conn, $query_insert)) {
        echo "<script>alert('Data Berhasil Disimpan!'); window.location='buku_storting.php';</script>";
    } else {
        echo "<script>alert('Gagal Simpan: " . mysqli_error($conn) . "');</script>";
    }
}

// 2. QUERY AMBIL DATA PINJAMAN UNTUK DROPDOWN (PILIHAN)
// Kita hanya mengambil pinjaman yang statusnya mungkin masih aktif/disetujui
$query_pilihan = "SELECT p.id_pinjaman, ag.nama 
                  FROM pinjaman p 
                  JOIN anggota ag ON p.id_anggota = ag.id_anggota 
                  ORDER BY ag.nama ASC";
$res_pilihan = mysqli_query($conn, $query_pilihan);

// 3. QUERY AMBIL DATA TABEL HISTORI
$query_ambil = "SELECT a.*, p.id_anggota, ag.nama 
                FROM angsuran a 
                LEFT JOIN pinjaman p ON a.id_pinjaman = p.id_pinjaman 
                LEFT JOIN anggota ag ON p.id_anggota = ag.id_anggota 
                ORDER BY a.tanggal_bayar DESC, a.id_angsuran DESC";
$result = mysqli_query($conn, $query_ambil);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Storting - Ringkasan Operasional</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #00bcd4;
            --primary-dark: #00acc1;
            --secondary: #636e72;
            --bg-body: #f8fafc;
            --surface: #ffffff;
            --text-main: #1e293b;
            --success-bg: #f0fdf4;
            --success-text: #16a34a;
        }

        body { 
            background-color: var(--bg-body); 
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            letter-spacing: -0.01em;
        }

        .container { max-width: 1000px; }

        .header-section {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .header-icon {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            width: 56px; height: 56px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 16px; color: white;
            box-shadow: 0 8px 16px rgba(0, 188, 212, 0.25);
        }

        .card { border-radius: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: var(--surface); border: 1px solid rgba(226, 232, 240, 0.8); }
        .card-header-custom { padding: 24px 30px 0; font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 12px; }
        .form-label { font-weight: 600; font-size: 0.85rem; color: var(--secondary); }
        .form-control, .form-select { border-radius: 14px; padding: 12px 16px; border: 1px solid #e2e8f0; }
        .btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border: none; border-radius: 14px; padding: 14px; font-weight: 700; }

        .table-responsive { border-radius: 24px; }
        .table thead th { background-color: #f8fafc; font-size: 0.7rem; font-weight: 700; padding: 20px 25px; color: #64748b; text-transform: uppercase; }
        .table tbody td { padding: 22px 25px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .badge-id { background: #f1f5f9; color: #475569; font-weight: 600; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; }
        .text-amount { color: var(--success-text); font-weight: 700; font-family: 'Courier New', Courier, monospace; }
        .status-pill { background-color: var(--success-bg); color: var(--success-text); padding: 8px 16px; border-radius: 50px; font-weight: 700; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 6px; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="header-section">
        <div class="header-icon"><i class="fas fa-wallet fa-lg"></i></div>
        <div>
            <h2 class="mb-0 fw-bold">Buku Storting</h2>
            <p class="text-secondary mb-0">Manajemen setoran harian operasional</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4 fw-bold ms-auto">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>
    
    <div class="card mb-4">
        <div class="card-header-custom"><i class="fas fa-circle-plus text-primary"></i> Input Transaksi Baru</div>
        <div class="card-body p-4 p-lg-5">
            <form action="" method="POST" class="row g-4">
                <div class="col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Pilih Nasabah (ID Pinjam)</label>
                    <select name="id_pinjaman" class="form-select" required>
                        <option value="">-- Pilih Nasabah --</option>
                        <?php while($p = mysqli_fetch_assoc($res_pilihan)): ?>
                            <option value="<?= $p['id_pinjaman'] ?>">
                                <?= $p['nama'] ?> (ID: <?= $p['id_pinjaman'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nominal Setoran</label>
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-transparent fw-bold">Rp</span>
                        <input type="number" name="jumlah" class="form-control" placeholder="0" required>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" name="simpan" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-2"></i> Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Waktu Transaksi</th>
                            <th>ID Pinjam</th>
                            <th>Nama Anggota</th>
                            <th>Total Setoran</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td class="text-secondary fw-medium">
                                <?= date('d M Y', strtotime($row['tanggal_bayar'])) ?>
                            </td>
                            <td><span class="badge-id"><?= $row['id_pinjaman'] ?></span></td>
                            <td class="fw-bold"><?= $row['nama'] ?? 'Nasabah #' . $row['id_anggota'] ?></td>
                            <td class="text-amount">Rp <?= number_format($row['nominal_bayar'], 0, ',', '.') ?></td>
                            <td class="text-center">
                                <div class="status-pill">
                                    <i class="fas fa-check-double"></i> 
                                    <?= ($row['status_verifikasi'] == '1') ? 'Verified' : 'Pending' ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>