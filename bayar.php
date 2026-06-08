<?php
include 'koneksi.php';

$id_p_url = isset($_GET['id']) ? $_GET['id'] : '';
$data_nasabah = null;
$angsuran_terbayar = []; 

if ($id_p_url) {
    $query_nasabah = mysqli_query($conn, "SELECT p.*, a.nama FROM pinjaman p JOIN anggota a ON p.id_anggota = a.id_anggota WHERE p.id_pinjaman = '$id_p_url'");
    $data_nasabah = mysqli_fetch_assoc($query_nasabah);
    
    $query_terbayar = mysqli_query($conn, "SELECT SUM(nominal_bayar) as total FROM angsuran WHERE id_pinjaman = '$id_p_url'");
    $res_terbayar = mysqli_fetch_assoc($query_terbayar);
    $terbayar = $res_terbayar['total'] ?? 0;
    $sisa_hutang = $data_nasabah['total_tagihan'] - $terbayar;

    $query_list_angsuran = mysqli_query($conn, "SELECT angsuran_ke FROM angsuran WHERE id_pinjaman = '$id_p_url'");
    while($row = mysqli_fetch_assoc($query_list_angsuran)) {
        $angsuran_terbayar[] = $row['angsuran_ke'];
    }
}

$pesan = "";
if(isset($_POST['btn_simpan'])){ // Nama harus sama dengan name di button
    $id_p = $_POST['id_pinjaman'];
    $bayar = $_POST['nominal'];
    $tgl_bayar = $_POST['tgl_bayar']; 
    $angsuran_ke = $_POST['angsuran_ke']; 
    $id_kolektor = 1; 

    $cek_double = mysqli_query($conn, "SELECT id_angsuran FROM angsuran WHERE id_pinjaman = '$id_p' AND angsuran_ke = '$angsuran_ke'");
    if(mysqli_num_rows($cek_double) > 0) {
        $pesan = "<div class='alert alert-warning'>Maaf, Angsuran ke-$angsuran_ke sudah pernah diinput sebelumnya!</div>";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO angsuran (id_pinjaman, id_kolektor, nominal_bayar, tanggal_bayar, angsuran_ke) 
                                       VALUES ('$id_p', '$id_kolektor', '$bayar', '$tgl_bayar', '$angsuran_ke')");

        if($insert){
            $cek = mysqli_query($conn, "SELECT total_tagihan, 
                       (SELECT SUM(nominal_bayar) FROM angsuran WHERE id_pinjaman = '$id_p') as total_masuk 
                       FROM pinjaman WHERE id_pinjaman = '$id_p'");
            $data = mysqli_fetch_assoc($cek);

            if($data['total_masuk'] >= $data['total_tagihan']){
                mysqli_query($conn, "UPDATE pinjaman SET status = 'lunas' WHERE id_pinjaman = '$id_p'");
                $pesan = "<div class='alert alert-success'>Pembayaran Berhasil! Status Pinjaman: <strong>LUNAS</strong>.</div>";
            } else {
                $pesan = "<div class='alert alert-primary'>Pembayaran Berhasil Tercatat (Angsuran ke-$angsuran_ke).</div>";
            }
            $angsuran_terbayar[] = $angsuran_ke;
            // Update sisa hutang di tampilan setelah bayar
            $sisa_hutang -= $bayar;
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal menyimpan data: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Setoran - KSP Bhakti Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .card-pay { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header-pay { background: linear-gradient(45deg, #0d6efd, #0099ff); color: white; border-radius: 20px 20px 0 0; padding: 25px; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <?= $pesan; ?>
            
            <div class="card card-pay">
                <div class="header-pay text-center">
                    <i class="fas fa-hand-holding-usd fa-3x mb-3"></i>
                    <h4 class="fw-bold mb-0">Input Setoran Harian</h4>
                </div>
                <div class="card-body p-4">
                    <?php if($data_nasabah): ?>
                        <div class="bg-light p-3 rounded mb-4 border-start border-4 border-primary">
                            <p class="mb-1 text-muted small">Nama Nasabah:</p>
                            <h5 class="fw-bold"><?= $data_nasabah['nama']; ?></h5>
                            <div class="row mt-3">
                                <div class="col-6">
                                    <small class="text-muted">Total Tagihan:</small>
                                    <p class="fw-bold text-dark">Rp <?= number_format($data_nasabah['total_tagihan'], 0, ',', '.'); ?></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Sisa Hutang:</small>
                                    <p class="fw-bold text-danger">Rp <?= number_format($sisa_hutang, 0, ',', '.'); ?></p>
                                </div>
                            </div>
                        </div>

                        <form method="POST">
                            <input type="hidden" name="id_pinjaman" value="<?= $id_p_url; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal Pembayaran</label>
                                <input type="date" name="tgl_bayar" class="form-control form-control-lg" value="<?= date('Y-m-d'); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Angsuran Ke-</label>
                                <select name="angsuran_ke" class="form-select form-select-lg" required>
                                    <option value="">-- Pilih Angsuran --</option>
                                    <?php for($i=1; $i<=25; $i++): ?>
                                        <?php 
                                            $sudah_bayar = in_array($i, $angsuran_terbayar);
                                            $is_disabled = $sudah_bayar ? 'disabled style="background-color: #e9ecef;"' : '';
                                            $label_lunas = $sudah_bayar ? ' (LUNAS)' : '';
                                        ?>
                                        <option value="<?= $i; ?>" <?= $is_disabled; ?>>
                                            Angsuran Ke-<?= $i . $label_lunas; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Nominal Setoran (Rp)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-end-0 text-success">Rp</span>
                                    <input type="number" name="nominal" class="form-control border-start-0 ps-0 fw-bold text-success" value="<?= $data_nasabah['total_tagihan'] / 20; ?>" required>
                                </div>
                                <small class="text-muted mt-2 d-block">* Pastikan uang sudah diterima secara fisik.</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="btn_simpan" class="btn btn-primary btn-lg shadow">
                                    <i class="fas fa-check-circle me-2"></i>Simpan Pembayaran
                                </button>
                                <a href="monitoring.php" class="btn btn-outline-secondary">Batal / Kembali</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                            <p>Data pinjaman tidak ditemukan atau ID tidak valid.</p>
                            <a href="monitoring.php" class="btn btn-primary">Kembali ke Monitoring</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>