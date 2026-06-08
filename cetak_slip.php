<?php
// 1. Koneksi ke Database
$conn = mysqli_connect("localhost", "root", "", "koperasi_harian");

// 2. Ambil ID dari URL
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// 3. Query Ambil Data Slip Gaji
$query = mysqli_query($conn, "SELECT * FROM daftar_gaji WHERE id = '$id'");
$data  = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan, kembali ke daftar gaji
if (!$data) {
    echo "<script>alert('Data gaji tidak ditemukan!'); window.location='daftar_gaji.php';</script>";
    exit;
}

// 4. Fungsi Terbilang Otomatis
function terbilang($angka) {
    $angka = abs($angka);
    $baca  = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
    $temp  = "";
    if ($angka < 12) { $temp = " " . $baca[$angka]; }
    else if ($angka < 20) { $temp = terbilang($angka - 10) . " Belas"; }
    else if ($angka < 100) { $temp = terbilang($angka / 10) . " Puluh" . terbilang($angka % 10); }
    else if ($angka < 200) { $temp = " Seratus" . terbilang($angka - 100); }
    else if ($angka < 1000) { $temp = terbilang($angka / 100) . " Ratus" . terbilang($angka % 100); }
    else if ($angka < 2000) { $temp = " Seribu" . terbilang($angka - 1000); }
    else if ($angka < 1000000) { $temp = terbilang($angka / 1000) . " Ribu" . terbilang($angka % 1000); }
    else if ($angka < 1000000000) { $temp = terbilang($angka / 1000000) . " Juta" . terbilang($angka % 1000000); }
    return $temp;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - <?= $data['nama_staf']; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { background-color: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px 0; -webkit-print-color-adjust: exact; }
        
        /* Container Slip */
        .slip-card { 
            background: #fff; 
            width: 100%;
            max-width: 850px; /* Lebar optimal untuk A4 potrait */
            margin: 0 auto; 
            padding: 40px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            border-top: 8px solid #27ae60; 
            position: relative; 
        }

        .kop-surat { border-bottom: 2px solid #334155; padding-bottom: 15px; margin-bottom: 25px; }
        .label-slip { text-transform: uppercase; font-weight: 800; letter-spacing: 2px; color: #27ae60; font-size: 1.4rem; }
        .info-table td { padding: 4px 0; font-size: 0.9rem; }
        .section-title { background: #f8fafc; padding: 8px 12px; font-weight: 700; font-size: 0.85rem; color: #475569; margin: 15px 0 10px; border-left: 4px solid #27ae60; }
        .amount { font-family: 'Courier New', Courier, monospace; font-weight: 700; }
        .total-box { background: #27ae60 !important; color: white !important; padding: 15px 20px; border-radius: 10px; margin-top: 25px; }

        /* Pengaturan Khusus Print */
        @media print {
            @page { 
                size: auto;   /* Auto handle potrait/landscape */
                margin: 10mm; /* Jarak aman tepi kertas */
            }
            body { background: white; padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .slip-card { 
                box-shadow: none; 
                margin: 0 auto; 
                width: 100%; 
                max-width: 100%; 
                border-top: 5px solid #27ae60; 
            }
            .total-box { 
                border: 1px solid #27ae60; 
                background-color: #27ae60 !important; 
                color: white !important; 
                -webkit-print-color-adjust: exact; 
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="text-center mb-4 no-print">
        <button onclick="window.print()" class="btn btn-dark btn-lg shadow">
            <i class="fas fa-print me-2"></i> Cetak Slip
        </button>
        <a href="daftar_gaji.php" class="btn btn-outline-secondary btn-lg ms-2">Kembali</a>
    </div>

    <div class="slip-card">
        <div class="kop-surat d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold mb-1 text-dark">BHAK'TI JAYA PRIMA</h4>
                <p class="text-muted small mb-0">Jl.Tanah Tinggi, Lorong Kedondong, Kel. Uritetu, Kec. Sirimau,</p>
                <p class="text-muted small mb-0">RT.001, RW. 003, Kota Ambon</p>
                <p class="text-muted small">Telp : 0852 5433 9294</p>
            </div>
            <div class="text-end">
                <div class="label-slip">SLIP GAJI</div>
                <div class="text-muted fw-bold small"><?= strtoupper($data['bulan_tahun']); ?></div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <table class="info-table w-100">
                    <tr>
                        <td width="35%">ID Transaksi</td>
                        <td>: <strong>#BJP-<?= $data['id']; ?></strong></td>
                    </tr>
                    <tr>
                        <td>Nama Lengkap</td>
                        <td>: <strong><?= $data['nama_staf']; ?></strong></td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="info-table w-100">
                    <tr>
                        <td width="40%">Jabatan</td>
                        <td>: <strong><?= $data['jabatan']; ?></strong></td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>: <span class="text-success fw-bold"><?= strtoupper($data['status_bayar']); ?></span></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-6 pe-4">
                <div class="section-title">PENGHASILAN</div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span>Gaji Pokok</span>
                    <span class="amount">Rp <?= number_format($data['gaji_pokok'], 0, ',', '.'); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span>Bonus / Lembur</span>
                    <span class="amount">Rp <?= number_format($data['bonus'], 0, ',', '.'); ?></span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold small">
                    <span>Total Bruto</span>
                    <span class="amount">Rp <?= number_format($data['gaji_pokok'] + $data['bonus'], 0, ',', '.'); ?></span>
                </div>
            </div>

            <div class="col-6 ps-4 border-start">
                <div class="section-title">POTONGAN</div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span>Kas / Absen</span>
                    <span class="amount">Rp <?= number_format($data['potongan'], 0, ',', '.'); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span>Lain-lain</span>
                    <span class="amount">Rp 0</span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2 mt-2 fw-bold small">
                    <span>Total Potongan</span>
                    <span class="amount">Rp <?= number_format($data['potongan'], 0, ',', '.'); ?></span>
                </div>
            </div>
        </div>

        <div class="total-box d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">TAKE HOME PAY (TOTAL BERSIH)</h6>
            <h4 class="mb-0 fw-bold">Rp <?= number_format($data['total_diterima'], 0, ',', '.'); ?></h4>
        </div>

        <p class="mt-3 small text-muted text-center italic">
            <i>Terbilang: <?= terbilang($data['total_diterima']); ?> Rupiah</i>
        </p>

        <div class="row mt-5 pt-3">
            <div class="col-4 ms-auto text-center">
                <p class="mb-5 small">Ambon, <?= date('d F Y'); ?><br>Penerima,</p>
                <div class="mt-4 border-top pt-2"><strong><?= $data['nama_staf']; ?></strong></div>
            </div>
        </div>

        <div class="mt-5 text-center no-print">
            <hr>
            <small class="text-muted">Menerangkan bahwa slip gaji ini sah dan diproses secara digital.</small>
        </div>
    </div>
</div>

</body>
</html>