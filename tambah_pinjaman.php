<?php
include 'koneksi.php';

// Ambil data anggota untuk dropdown
$anggota_query = mysqli_query($conn, "SELECT id_anggota, nama FROM anggota ORDER BY nama ASC");

if(isset($_POST['submit'])){
    $id_anggota = mysqli_real_escape_string($conn, $_POST['id_anggota']);
    $plafon = mysqli_real_escape_string($conn, $_POST['plafon']);
    $bunga = mysqli_real_escape_string($conn, $_POST['bunga']); 
    $lama = mysqli_real_escape_string($conn, $_POST['lama']); 
    $tgl_drop = mysqli_real_escape_string($conn, $_POST['tgl_drop']); // Mengambil input tanggal baru

    // Perhitungan di sisi server untuk keamanan
    $total_tagihan = $plafon + ($plafon * ($bunga / 100));
    
    // Query disesuaikan untuk menggunakan $tgl_drop
    $query = "INSERT INTO pinjaman (id_anggota, plafon_pinjaman, lama_hari, bunga_persen, total_tagihan, tgl_cair) 
              VALUES ('$id_anggota', '$plafon', '$lama', '$bunga', '$total_tagihan', '$tgl_drop')";
    
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Pinjaman Berhasil Diinput!'); window.location='index.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop Pinjaman - KSP Bhakti Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; color: #334155; }
        .card { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .card-header { background: #fbbf24 !important; border: none; padding: 20px; letter-spacing: 0.5px; }
        .form-label { font-weight: 600; font-size: 0.85rem; color: #475569; margin-bottom: 8px; }
        .form-control, .form-select { padding: 12px 15px; border-radius: 10px; border: 1px solid #e2e8f0; background-color: #f8fafc; transition: all 0.2s; }
        .form-control:focus, .form-select:focus { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); border-color: #3b82f6; background-color: #fff; }
        .btn-process { background: #2563eb; border: none; padding: 14px; border-radius: 10px; font-weight: 600; transition: all 0.3s; color: white; }
        .btn-process:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }
        .btn-cancel { background: #64748b; color: white; padding: 14px; border-radius: 10px; font-weight: 600; text-decoration: none; text-align: center; display: block; }
        .summary-box { background: #f1f5f9; border-radius: 12px; padding: 15px; margin-top: 10px; border-left: 4px solid #3b82f6; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-lg">
                    <div class="card-header text-center">
                        <h5 class="mb-0 fw-bold text-dark text-uppercase">Form Drop Pinjaman Baru</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" id="loanForm">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Drop (Pencairan)</label>
                                <input type="date" name="tgl_drop" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pilih Anggota / Nasabah</label>
                                <select name="id_anggota" class="form-select" required>
                                    <option value="">-- Pilih Anggota --</option>
                                    <?php while($a = mysqli_fetch_assoc($anggota_query)): ?>
                                        <option value="<?= $a['id_anggota']; ?>"><?= htmlspecialchars($a['nama']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nominal Pinjaman (Plafon)</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light">Rp</span>
                                    <input type="number" name="plafon" id="plafon" class="form-control fw-bold" placeholder="Contoh: 1000000" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Bunga (%)</label>
                                    <input type="number" name="bunga" id="bunga" class="form-control" value="20" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Tenor (Hari)</label>
                                    <input type="number" name="lama" id="tenor" class="form-control" value="20" required>
                                </div>
                            </div>

                            <div class="summary-box mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Total Pengembalian:</small>
                                    <strong class="text-primary" id="displayTotal">Rp 0</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Tagihan / Hari:</small>
                                    <strong class="text-success" id="displayPerDay">Rp 0</strong>
                                </div>
                            </div>

                            <div class="d-grid gap-3">
                                <button type="submit" name="submit" class="btn btn-primary btn-process">
                                    <i class="fas fa-check-circle me-2"></i>Konfirmasi & Simpan
                                </button>
                                <a href="index.php" class="btn-cancel">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <p class="text-center mt-4 text-muted small">&copy; 2026 KSP Bhakti Jaya - Sistem Manajemen Pinjaman</p>
            </div>
        </div>
    </div>

    <script>
        const plafonInput = document.getElementById('plafon');
        const bungaInput = document.getElementById('bunga');
        const tenorInput = document.getElementById('tenor');
        const displayTotal = document.getElementById('displayTotal');
        const displayPerDay = document.getElementById('displayPerDay');

        function calculate() {
            const plafon = parseFloat(plafonInput.value) || 0;
            const bunga = parseFloat(bungaInput.value) || 0;
            const tenor = parseFloat(tenorInput.value) || 1;

            const total = plafon + (plafon * (bunga / 100));
            const perDay = total / tenor;

            displayTotal.innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(total);
            displayPerDay.innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(perDay);
        }

        [plafonInput, bungaInput, tenorInput].forEach(input => {
            input.addEventListener('input', calculate);
        });
    </script>
</body>
</html>