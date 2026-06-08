<?php
session_start(); // Memulai session untuk mengecek role
include 'koneksi.php';

// Proteksi Login
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role']; // Mengambil role user (admin/manager/petugas)

// Menangkap filter tanggal jika ada
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : '';

// Query: Memunculkan SEMUA nasabah aktif. 
$query = "SELECT 
            p.id_pinjaman, a.nama, a.telepon, a.alamat, a.tempat_usaha, 
            a.foto_nasabah, a.foto_ktp, p.total_tagihan, p.plafon_pinjaman, 
            p.tgl_cair, p.lama_hari,
            -- Menghitung terbayar HANYA pada periode tanggal yang dipilih
            (SELECT IFNULL(SUM(nominal_bayar), 0) FROM angsuran 
             WHERE id_pinjaman = p.id_pinjaman " . 
             (!empty($tgl_mulai) ? " AND tanggal_bayar BETWEEN '$tgl_mulai' AND '$tgl_selesai'" : "") . 
            ") as terbayar_periode,
            -- Menghitung total terbayar SEUMUR HIDUP
            (SELECT IFNULL(SUM(nominal_bayar), 0) FROM angsuran WHERE id_pinjaman = p.id_pinjaman) as terbayar_total,
            -- Menghitung berapa kali sudah angsur
            (SELECT COUNT(*) FROM angsuran WHERE id_pinjaman = p.id_pinjaman) as kali_bayar,
            -- Cek setoran terakhir
            (SELECT MAX(tanggal_bayar) FROM angsuran WHERE id_pinjaman = p.id_pinjaman) as tgl_bayar_terakhir,
            (p.total_tagihan / p.lama_hari) as tagihan_per_hari
          FROM pinjaman p 
          JOIN anggota a ON p.id_anggota = a.id_anggota 
          WHERE p.status = 'aktif'
          ORDER BY a.nama ASC";

$result = mysqli_query($conn, $query) or die(mysqli_error($conn));

$grand_total_plafon = 0; 
$grand_total_tagihan = 0;
$grand_total_harian = 0;
$grand_total_masuk = 0;
$grand_total_sisa = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Laporan - KSP Bhakti Jaya</title>
    <link rel="icon" type="image/png" href="logo.jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { background-color: #f1f5f9; font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .table thead th { background-color: #f8fafc; text-transform: uppercase; font-size: 0.7rem; color: #64748b; vertical-align: middle; }
        .total-row { background-color: #f8fafc; font-weight: 700; border-top: 2px solid #cbd5e1; }
        .img-detail { width: 100%; border-radius: 12px; object-fit: cover; border: 1px solid #e2e8f0; height: 200px; }
        .info-label { font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; }
        
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            @page { size: A4 landscape; margin: 1cm; }
            body { background-color: white !important; color: black; }
            .card { box-shadow: none !important; border: none !important; }
            .table { width: 100% !important; border: 1px solid #dee2e6 !important; font-size: 0.8rem; }
            .table th { background-color: #eee !important; color: black !important; -webkit-print-color-adjust: exact; }
        }
        .print-only { display: none; }
        .kop-surat { border-bottom: 3px double #000; margin-bottom: 20px; padding-bottom: 10px; }
    </style>
</head>
<body>

<div class="container-fluid py-5 px-4">
    <div class="print-only">
        <div class="kop-surat text-center">
            <h2 class="fw-bold mb-0">KSP BHAKTI JAYA</h2>
            <p class="mb-0">Sistem Informasi Operasional Kolektor Harian</p>
            <p class="small mb-0">Ambon, Maluku - Indonesia</p>
        </div>
        <h4 class="text-center fw-bold text-decoration-underline">LAPORAN MONITORING SETORAN NASABAH</h4>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2 class="fw-bold text-primary mb-1"><i class="fas fa-file-invoice-dollar me-2"></i>Monitoring Kolektor</h2>
            <p class="text-muted mb-0">Monitor setoran harian dan sisa tenor nasabah</p>
        </div>
        <a href="index.php" class="btn btn-outline-secondary px-4 fw-bold shadow-sm"><i class="fas fa-arrow-left me-2"></i>Dashboard</a>
    </div>

    <?php if ($role != 'petugas') : ?>
    <div class="card p-4 mb-4 no-print">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold small">Tanggal Mulai</label>
                <input type="date" name="tgl_mulai" class="form-control" value="<?= $tgl_mulai ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold small">Tanggal Selesai</label>
                <input type="date" name="tgl_selesai" class="form-control" value="<?= $tgl_selesai ?>">
            </div>
            <div class="col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">FILTER DATA</button>
                <button type="button" onclick="exportExcel()" class="btn btn-success btn-sm px-3 fw-bold">SIMPAN EXCEL</button>
                <button type="button" onclick="window.print()" class="btn btn-danger btn-sm px-3 fw-bold">SIMPAN PDF (CETAK)</button>
                <a href="monitoring.php" class="btn btn-light btn-sm border px-3 fw-bold">RESET</a>
            </div>
        </form>
    </div>
    <?php else : ?>
        <div class="alert alert-info no-print border-0 shadow-sm mb-4">
            <i class="fas fa-info-circle me-2"></i> <b>Informasi:</b> Anda melihat laporan setoran real-time untuk tanggal hari ini <b>(<?= date('d/m/Y') ?>)</b>.
        </div>
    <?php endif; ?>

    <div class="card p-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="mainTable">
                <thead>
                    <tr>
                        <th class="ps-3">Nasabah</th>
                        <th>Besaran Pinjam</th> <th>Tagihan Total</th>
                        <th>Setoran/Hari</th>
                        <th>Uang Masuk</th>
                        <th>Sisa Hutang</th>
                        <th>Sisa Kali</th>
                        <th>Status</th>
                        <th class="text-center no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)): 
                            $sisa_hutang = $row['total_tagihan'] - $row['terbayar_total'];
                            $sisa_kali = $row['lama_hari'] - $row['kali_bayar'];
                            $tgl_sekarang = date('Y-m-d');
                            $sudah_bayar_hari_ini = ($row['tgl_bayar_terakhir'] == $tgl_sekarang);
                            
                            $grand_total_plafon += $row['plafon_pinjaman'];
                            $grand_total_tagihan += $row['total_tagihan'];
                            $grand_total_harian += $row['tagihan_per_hari'];
                            $grand_total_masuk += $row['terbayar_periode'];
                            $grand_total_sisa += $sisa_hutang;
                    ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold"><?= strtoupper($row['nama']) ?></div>
                            <small class="text-muted small">ID: PJN-<?= $row['id_pinjaman'] ?></small>
                        </td>
                        <td class="fw-bold text-dark">Rp <?= number_format($row['plafon_pinjaman'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($row['total_tagihan'], 0, ',', '.') ?></td>
                        <td class="fw-bold text-primary">Rp <?= number_format($row['tagihan_per_hari'], 0, ',', '.') ?></td>
                        <td class="text-success fw-bold">Rp <?= number_format($row['terbayar_periode'], 0, ',', '.') ?></td>
                        <td class="text-danger fw-bold">Rp <?= number_format($sisa_hutang, 0, ',', '.') ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $sisa_kali ?>x</span></td>
                        <td>
                            <?php if($sudah_bayar_hari_ini): ?>
                                <span class="badge bg-success-subtle text-success border-0 px-2">SETOR</span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning border-0 px-2">BELUM</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center no-print">
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['id_pinjaman'] ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="bayar.php?id=<?= $row['id_pinjaman'] ?>" class="btn btn-primary btn-sm <?= $sudah_bayar_hari_ini ? 'disabled' : '' ?>">
                                    <i class="fas fa-hand-holding-usd"></i>
                                </a>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade no-print" id="detailModal<?= $row['id_pinjaman'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-primary text-white border-0">
                                    <h5 class="modal-title fw-bold"><i class="fas fa-id-card me-2"></i>Profil Lengkap Nasabah</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4 text-start">
                                    <div class="row">
                                        <div class="col-md-5 text-center border-end">
                                            <p class="info-label mb-2">Foto Nasabah</p>
                                            <img src="uploads/<?= $row['foto_nasabah'] ?>" class="img-detail" onerror="this.src='https://via.placeholder.com/150'">
                                            <p class="info-label mb-2 mt-3">Foto KTP</p>
                                            <img src="uploads/<?= $row['foto_ktp'] ?>" class="img-detail" onerror="this.src='https://via.placeholder.com/300x150?text=KTP+Tidak+Ada'">
                                        </div>
                                        <div class="col-md-7 ps-md-4">
                                            <h4 class="fw-bold"><?= $row['nama'] ?></h4>
                                            <p class="text-muted"><i class="fab fa-whatsapp me-2"></i><?= $row['telepon'] ?></p>
                                            <hr>
                                            <div class="row g-3">
                                                <div class="col-6"><p class="info-label mb-0">ID PINJAMAN</p><p class="fw-bold">PJN-<?= $row['id_pinjaman'] ?></p></div>
                                                <div class="col-6"><p class="info-label mb-0">SISA TENOR</p><p class="fw-bold text-danger"><?= $sisa_kali ?> Kali</p></div>
                                                <div class="col-12">
                                                    <div class="p-2 bg-light border-start border-4 border-info rounded">
                                                        <p class="info-label mb-0">TANGGAL TERAKHIR SETOR</p>
                                                        <p class="fw-bold mb-0 text-info">
                                                            <i class="fas fa-calendar-alt me-1"></i>
                                                            <?= ($row['tgl_bayar_terakhir']) ? date('d F Y', strtotime($row['tgl_bayar_terakhir'])) : 'Belum pernah setor'; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="col-12"><p class="info-label mb-0">USAHA / ALAMAT</p><p class="fw-semibold small"><?= $row['tempat_usaha'] ?> - <?= $row['alamat'] ?></p></div>
                                                <div class="col-6"><p class="info-label mb-0">BESARAN PINJAM (PLAFON)</p><p class="fw-bold">Rp <?= number_format($row['plafon_pinjaman'],0,',','.') ?></p></div>
                                                <div class="col-6"><p class="info-label mb-0">TAGIHAN PER HARI</p><p class="fw-bold text-primary">Rp <?= number_format($row['tagihan_per_hari'],0,',','.') ?></p></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
                                    <a href="bayar.php?id=<?= $row['id_pinjaman'] ?>" class="btn btn-primary px-4">Input Setoran</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php endwhile; ?>
                    
                    <tr class="total-row">
                        <td class="ps-3 text-end">TOTAL KESELURUHAN:</td>
                        <td class="text-dark">Rp <?= number_format($grand_total_plafon, 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($grand_total_tagihan, 0, ',', '.') ?></td>
                        <td class="text-primary">Rp <?= number_format($grand_total_harian, 0, ',', '.') ?></td>
                        <td class="text-success">Rp <?= number_format($grand_total_masuk, 0, ',', '.') ?></td>
                        <td class="text-danger">Rp <?= number_format($grand_total_sisa, 0, ',', '.') ?></td>
                        <td colspan="3" class="bg-white"></td>
                    </tr>
                    <?php else: ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted small">Data nasabah tidak ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function exportExcel() {
    let table = document.getElementById("mainTable");
    let html = table.outerHTML.replace(/<td class="text-center no-print">.*?<\/td>/g, '')
                               .replace(/<th class="text-center no-print">.*?<\/th>/g, '');
    let url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    let link = document.createElement("a");
    link.download = "Laporan_KSP_BhaktiJaya_<?= date('Y-m-d') ?>.xls";
    link.href = url;
    link.click();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>