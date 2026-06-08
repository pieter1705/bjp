<?php
// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "koperasi_harian");

// Ambil data staf lengkap dengan jabatannya dari tabel users/anggota
$query_staf = mysqli_query($conn, "SELECT nama_lengkap, jabatan FROM users ORDER BY nama_lengkap ASC");

// Proses Simpan Data
if (isset($_POST['simpan'])) {
    $bulan_tahun = $_POST['bulan'] . " " . $_POST['tahun'];
    $nama_staf   = $_POST['nama_staf'];
    $jabatan     = $_POST['jabatan'];
    $gaji_pokok  = $_POST['gaji_pokok'];
    $bonus       = $_POST['bonus'];
    $potongan    = $_POST['potongan'];
    $total       = ($gaji_pokok + $bonus) - $potongan;
    $status      = $_POST['status'];

    $query = "INSERT INTO daftar_gaji (bulan_tahun, nama_staf, jabatan, gaji_pokok, bonus, potongan, total_diterima, status_bayar) 
              VALUES ('$bulan_tahun', '$nama_staf', '$jabatan', '$gaji_pokok', '$bonus', '$potongan', '$total', '$status')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data gaji berhasil ditambahkan!'); window.location='daftar_gaji.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Payroll - Loka Monitor</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
    <style>
        :root {
            --primary-success: #27ae60;
            --bg-soft: #f0f4f8;
            --text-main: #1e293b;
        }

        body { background-color: var(--bg-soft); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--text-main); }

        .card-input { 
            border: none; border-radius: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.05); 
            background: #fff; padding: 45px; position: relative; overflow: hidden;
        }

        .card-input::before {
            content: ""; position: absolute; top: -50px; right: -50px; width: 150px;
            height: 150px; background: rgba(39, 174, 96, 0.05); border-radius: 50%;
        }

        .form-label { font-weight: 700; font-size: 0.8rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Select2 & Input Styling */
        .form-control, .form-select, .select2-container--bootstrap-5 .select2-selection { 
            border-radius: 14px !important; padding: 12px 18px; border: 1.5px solid #e2e8f0 !important; 
            background: #fbfbfb !important; font-weight: 600 !important; min-height: 52px;
        }

        .section-divider { height: 1px; background: linear-gradient(90deg, transparent, #e2e8f0, transparent); margin: 40px 0; }

        .total-preview { 
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); 
            border: 2px solid #27ae60; border-radius: 20px; padding: 30px; text-align: center;
        }

        .btn-save { 
            background: var(--primary-success); color: #fff; border: none; border-radius: 16px; 
            padding: 18px; font-weight: 800; width: 100%; transition: 0.3s;
        }

        .btn-save:hover { background: #219150; transform: translateY(-3px); box-shadow: 0 10px 25px rgba(39, 174, 96, 0.25); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center mb-5 gap-3">
                <a href="daftar_gaji.php" class="btn btn-white shadow-sm rounded-4 p-3 bg-white text-success"><i class="fas fa-chevron-left"></i></a>
                <div>
                    <h2 class="fw-800 mb-0 text-dark">Tambah Gaji Baru</h2>
                    <p class="text-muted mb-0 small">Periode Pembayaran Staf Loka Monitor</p>
                </div>
            </div>

            <div class="card-input">
                <form action="" method="POST">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select" required>
                                <?php
                                $bulans = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                                foreach ($bulans as $m) {
                                    $selected = (date('F') == $m) ? 'selected' : '';
                                    echo "<option value='$m' $selected>$m</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select" required>
                                <option value="2025">2025</option>
                                <option value="2026" selected>2026</option>
                                <option value="2027">2027</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Staf</label>
                            <select name="nama_staf" id="searchStaf" class="form-select" required>
                                <option value="">Cari Nama...</option>
                                <?php while($s = mysqli_fetch_assoc($query_staf)): ?>
                                    <option value="<?= $s['nama_lengkap']; ?>" data-jabatan="<?= $s['jabatan']; ?>">
                                        <?= $s['nama_lengkap']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" id="inputJabatan" class="form-control" placeholder="Otomatis terisi..." readonly required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Status Pembayaran</label>
                            <div class="d-flex gap-4 mt-1">
                                <div class="form-check border p-2 px-4 rounded-3">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="status" value="Pending" id="s1" checked>
                                    <label class="form-check-label fw-bold" for="s1">Pending</label>
                                </div>
                                <div class="form-check border p-2 px-4 rounded-3 border-success">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="status" value="Dibayar" id="s2">
                                    <label class="form-check-label fw-bold text-success" for="s2">Lunas</label>
                                </div>
                            </div>
                        </div>

                        <div class="section-divider"></div>

                        <div class="col-md-4">
                            <label class="form-label">Gaji Pokok</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                                <input type="number" name="gaji_pokok" id="gp" class="form-control border-start-0" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bonus</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                                <input type="number" name="bonus" id="bonus" class="form-control border-start-0 text-success" value="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Potongan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted">Rp</span>
                                <input type="number" name="potongan" id="pot" class="form-control border-start-0 text-danger" value="0">
                            </div>
                        </div>

                        <div class="col-12 mt-5">
                            <div class="total-preview">
                                <span class="form-label text-muted small">Total Bersih (Take Home Pay)</span>
                                <h1 class="fw-800 text-success mb-0 mt-2" id="grandTotal">Rp 0</h1>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" name="simpan" class="btn btn-save shadow">
                                <i class="fas fa-check-circle me-2"></i> Simpan Data Gaji
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // 1. Aktifkan Fitur Cari Nama
        $('#searchStaf').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari Nama Staf...'
        });

        // 2. Auto-Fill Jabatan saat Nama dipilih
        $('#searchStaf').on('change', function() {
            const selectedJabatan = $(this).find(':selected').data('jabatan');
            $('#inputJabatan').val(selectedJabatan || 'Jabatan tidak ditemukan');
        });

        // 3. Hitung Otomatis
        function calculate() {
            const gp = parseInt($('#gp').val()) || 0;
            const bonus = parseInt($('#bonus').val()) || 0;
            const pot = parseInt($('#pot').val()) || 0;
            const total = (gp + bonus) - pot;
            $('#grandTotal').text("Rp " + total.toLocaleString('id-ID'));
        }

        $('#gp, #bonus, #pot').on('input', calculate);
    });
</script>

</body>
</html>