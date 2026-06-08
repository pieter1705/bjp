<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

/**
 * --- LOGIKA API UNTUK PENGISIAN MANUAL (AJAX) ---
 */
if (isset($_POST['action']) && $_POST['action'] == 'update_angsuran') {
    if ($role == 'admin' || $role == 'manager') {
        $pid = intval($_POST['id_pinjaman']);
        $tgl = mysqli_real_escape_string($conn, $_POST['tanggal']);
        $nominal = floatval($_POST['target']);

        $check = mysqli_query($conn, "SELECT id_angsuran FROM angsuran WHERE id_pinjaman = $pid AND tanggal_bayar = '$tgl'");
        
        if (mysqli_num_rows($check) > 0) {
            mysqli_query($conn, "DELETE FROM angsuran WHERE id_pinjaman = $pid AND tanggal_bayar = '$tgl'");
            echo json_encode(['status' => 'removed', 'nominal' => $nominal]);
        } else {
            $query = "INSERT INTO angsuran (id_pinjaman, tanggal_bayar, nominal_bayar, status_verifikasi) 
                      VALUES ($pid, '$tgl', $nominal, 1)";
            mysqli_query($conn, $query);
            echo json_encode(['status' => 'added', 'nominal' => $nominal]);
        }
    }
    exit();
}

// 1. Pengaturan Waktu
$bulan = isset($_GET['bulan']) && $_GET['bulan'] != "" ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) && $_GET['tahun'] != "" ? (int)$_GET['tahun'] : (int)date('Y');

if ($bulan < 1 || $bulan > 12) $bulan = (int)date('m');
if ($tahun < 1) $tahun = (int)date('Y');

$jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// 2. Query Utama
$query = "SELECT 
            p.id_pinjaman, 
            p.id_anggota, 
            a.nama, 
            p.tgl_cair, 
            p.nominal_bayar, 
            p.plafon_pinjaman, 
            p.total_tagihan,
            a.status_pinjaman
          FROM anggota a
          INNER JOIN pinjaman p ON a.id_anggota = p.id_anggota
          ORDER BY a.nama ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("<div class='alert alert-danger m-3'><b>Kesalahan Database:</b> " . mysqli_error($conn) . "</div>");
}

// 3. Ambil Data Angsuran
$data_setoran = [];
$q_setoran = mysqli_query($conn, "SELECT id_pinjaman, tanggal_bayar, nominal_bayar 
                                   FROM angsuran 
                                   WHERE MONTH(tanggal_bayar) = $bulan AND YEAR(tanggal_bayar) = $tahun");
if ($q_setoran) {
    while ($s = mysqli_fetch_assoc($q_setoran)) {
        $tgl_key = date('Y-m-d', strtotime($s['tanggal_bayar']));
        $data_setoran[$s['id_pinjaman']][$tgl_key] = $s['nominal_bayar'];
    }
}

$grand_total_target = 0;
$grand_total_plafon = 0;
$grand_total_bayar_bulan_ini = 0;
$grand_total_saldo = 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Angsuran - KSP Bhak'Ti Jaya Prima</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 11px; }
        .wrapper { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); margin: 20px; }
        .table-responsive { overflow-x: auto; max-height: 75vh; border: 1px solid #e2e8f0; }
        .sticky-name { position: sticky; left: 0; background: #ffffff !important; z-index: 10; border-right: 2px solid #cbd5e1 !important; min-width: 180px; }
        .table-custom thead th { background: #1e40af !important; color: white; text-align: center; vertical-align: middle; border: 1px solid #3730a3; padding: 8px 4px; }
        .table-custom tfoot td { background: #f8fafc; font-weight: bold; }
        .bg-sunday { background-color: #fee2e2 !important; color: #991b1b; }
        .check-mark { color: #16a34a; font-size: 13px; }
        .text-target { color: #2563eb; font-weight: bold; }
        .td-angsuran { cursor: pointer; transition: background 0.2s; }
        .td-angsuran:hover { background-color: #e0f2fe !important; }
        .loading-spinner { font-size: 10px; color: #64748b; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold m-0 text-dark text-uppercase">BUKU ANGSURAN (MASTER BULANAN)</h4>
                <p class="text-muted small mb-0">Lokasi: KSP Bhak'Ti Jaya - Ambon</p>
            </div>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-secondary btn-sm px-3">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
                
                <form method="GET" class="d-flex gap-2 bg-light p-2 rounded shadow-sm m-0">
                    <select name="bulan" class="form-select form-select-sm">
                        <?php 
                        $list_bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                        foreach($list_bulan as $k => $v) { echo "<option value='$k' ".($k == $bulan ? 'selected' : '').">$v</option>"; }
                        ?>
                    </select>
                    <input type="number" name="tahun" class="form-control form-control-sm" value="<?= $tahun ?>" style="width: 80px;">
                    <button type="submit" class="btn btn-primary btn-sm px-3">Buka</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm table-custom align-middle">
                <thead>
                    <tr>
                        <th rowspan="2" class="sticky-name">NAMA ANGGOTA</th>
                        <th rowspan="2">TGL DROP</th>
                        <th rowspan="2">TARGET</th>
                        <th rowspan="2">PLAFON</th>
                        <th colspan="<?= $jumlah_hari ?>">TANGGAL PENAGIHAN: <?= $list_bulan[$bulan] ?> <?= $tahun ?></th>
                        <th rowspan="2">TOTAL</th>
                        <th rowspan="2">SALDO</th>
                    </tr>
                    <tr>
                        <?php for($i=1; $i<=$jumlah_hari; $i++): 
                            $dt = "$tahun-" . str_pad($bulan, 2, "0", STR_PAD_LEFT) . "-" . str_pad($i, 2, "0", STR_PAD_LEFT);
                            $is_sun = (date('N', strtotime($dt)) == 7); ?>
                        <th class="<?= $is_sun ? 'bg-sunday' : '' ?>"><?= $i ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        $pid = $row['id_pinjaman'];
                        $total_bulan_ini = 0;
                    ?>
                    <tr>
                        <td class="sticky-name fw-bold"><?= strtoupper($row['nama']) ?></td>
                        <td class="text-center"><?= !empty($row['tgl_cair']) ? date('d/m/y', strtotime($row['tgl_cair'])) : '-' ?></td>
                        <td class="text-end text-target">Rp<span><?= number_format($row['nominal_bayar'], 0, ',', '.') ?></span></td>
                        <td class="text-end fw-bold">Rp<?= number_format($row['plafon_pinjaman'], 0, ',', '.') ?></td>

                        <?php for($i=1; $i<=$jumlah_hari; $i++): 
                            $tgl_cek = "$tahun-" . str_pad($bulan, 2, "0", STR_PAD_LEFT) . "-" . str_pad($i, 2, "0", STR_PAD_LEFT);
                            $content = "";
                            if(isset($data_setoran[$pid][$tgl_cek])) {
                                $total_bulan_ini += $data_setoran[$pid][$tgl_cek];
                                $content = "<i class='fas fa-check-circle check-mark'></i>";
                            }
                            $is_sun_inner = (date('N', strtotime($tgl_cek)) == 7); ?>
                        <td class="text-center td-angsuran <?= $is_sun_inner ? 'bg-sunday' : '' ?>" 
                            data-pid="<?= $pid ?>" 
                            data-tgl="<?= $tgl_cek ?>" 
                            data-target="<?= $row['nominal_bayar'] ?>">
                            <?= $content ?>
                        </td>
                        <?php endfor; ?>

                        <?php 
                            $qs = mysqli_query($conn, "SELECT SUM(nominal_bayar) as s FROM angsuran WHERE id_pinjaman = '$pid'");
                            $terbayar = mysqli_fetch_assoc($qs)['s'] ?? 0;
                            $saldo_akhir = $row['total_tagihan'] - $terbayar;

                            $grand_total_target += $row['nominal_bayar'];
                            $grand_total_plafon += $row['plafon_pinjaman'];
                            $grand_total_bayar_bulan_ini += $total_bulan_ini;
                            $grand_total_saldo += $saldo_akhir;
                        ?>

                        <td class="text-end fw-bold text-success">Rp<span class="row-total"><?= number_format($total_bulan_ini, 0, ',', '.') ?></span></td>
                        <td class="text-end fw-bold text-danger">Rp<span class="row-saldo"><?= number_format($saldo_akhir, 0, ',', '.') ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold align-middle">
                        <td colspan="2" class="text-center sticky-name">TOTAL KESELURUHAN</td>
                        <td class="text-end text-primary">Rp<span id="grand-target"><?= number_format($grand_total_target, 0, ',', '.') ?></span></td>
                        <td class="text-end">Rp<?= number_format($grand_total_plafon, 0, ',', '.') ?></td>
                        <td colspan="<?= $jumlah_hari ?>" class="bg-light"></td>
                        <td class="text-end text-success">Rp<span id="grand-total-bayar"><?= number_format($grand_total_bayar_bulan_ini, 0, ',', '.') ?></span></td>
                        <td class="text-end text-danger">Rp<span id="grand-saldo"><?= number_format($grand_total_saldo, 0, ',', '.') ?></span></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.td-angsuran').on('click', function() {
        const cell = $(this);
        const pid = cell.data('pid');
        const tgl = cell.data('tgl');
        const targetVal = cell.data('target');
        const row = cell.closest('tr');

        const userRole = "<?= $role ?>";
        if(userRole !== 'admin' && userRole !== 'manager') {
            alert('Hanya Admin dan Manager yang dapat mengisi manual.');
            return;
        }

        cell.html('<i class="fas fa-spinner fa-spin loading-spinner"></i>');

        $.ajax({
            url: '<?= $_SERVER['PHP_SELF'] ?>',
            type: 'POST',
            data: {
                action: 'update_angsuran',
                id_pinjaman: pid,
                tanggal: tgl,
                target: targetVal
            },
            success: function(response) {
                const res = JSON.parse(response);
                let totalCell = row.find('.row-total');
                let grandTotalBayarCell = $('#grand-total-bayar');
                
                let currentTotal = parseInt(totalCell.text().replace(/\./g, ''));
                let currentGrandTotal = parseInt(grandTotalBayarCell.text().replace(/\./g, ''));

                if (res.status === 'added') {
                    cell.html("<i class='fas fa-check-circle check-mark'></i>");
                    currentTotal += res.nominal;
                    currentGrandTotal += res.nominal;
                } else {
                    cell.html("");
                    currentTotal -= res.nominal;
                    currentGrandTotal -= res.nominal;
                }

                totalCell.text(currentTotal.toLocaleString('id-ID'));
                grandTotalBayarCell.text(currentGrandTotal.toLocaleString('id-ID'));
            },
            error: function() {
                alert('Gagal memperbarui data.');
                location.reload();
            }
        });
    });
});
</script>
</body>
</html>