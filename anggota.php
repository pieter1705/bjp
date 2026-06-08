<?php
session_start(); 
include 'koneksi.php';

// Proteksi Login
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

/**
 * --- FUNGSI HELPER ---
 */
function uploadFile($file, $prefix) {
    $namaFile = $file['name'];
    $tmpName  = $file['tmp_name'];
    $error    = $file['error'];

    if ($error === 4) return null;

    $ekstensiValid = ['jpg', 'jpeg', 'png'];
    $ekstensiFile  = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

    if (!in_array($ekstensiFile, $ekstensiValid)) return false;

    $namaFileBaru = $prefix . '_' . uniqid() . '.' . $ekstensiFile;
    move_uploaded_file($tmpName, 'uploads/' . $namaFileBaru);
    return $namaFileBaru;
}

function formatTanggalIndo($date) {
    if (!$date || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
        return '<span class="text-muted">-</span>';
    }
    return date('d/m/Y', strtotime($date));
}

/**
 * --- LOGIKA PROSES ---
 */

// 1. Tambah Data
if (isset($_POST['tambah'])) {
    $nama          = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat        = mysqli_real_escape_string($conn, $_POST['alamat']);
    $usaha         = mysqli_real_escape_string($conn, $_POST['tempat_usaha']);
    $telp          = mysqli_real_escape_string($conn, $_POST['telepon']);
    $jenis_anggota = mysqli_real_escape_string($conn, $_POST['jenis_anggota']); 
    $tgl_pinjaman  = !empty($_POST['tgl_pinjaman']) ? $_POST['tgl_pinjaman'] : date('Y-m-d'); 
    $status        = "Belum Lunas";

    $fotoKtp     = uploadFile($_FILES['foto_ktp'], 'ktp') ?: 'default.jpg';
    $fotoNasabah = uploadFile($_FILES['foto_nasabah'], 'profil') ?: 'default.jpg';

    $query = "INSERT INTO anggota (nama, alamat, tempat_usaha, telepon, jenis_anggota, tgl_pinjaman, status_pinjaman, foto_ktp, foto_nasabah) 
              VALUES ('$nama', '$alamat', '$usaha', '$telp', '$jenis_anggota', '$tgl_pinjaman', '$status', '$fotoKtp', '$fotoNasabah')";
    
    if (mysqli_query($conn, $query)) {
        header("Location: anggota.php?status=success");
    }
}

if ($role != 'petugas') {
    // 2. Update Data
    if (isset($_POST['update'])) {
        $id            = intval($_POST['id_anggota']);
        $nama          = mysqli_real_escape_string($conn, $_POST['nama']);
        $alamat        = mysqli_real_escape_string($conn, $_POST['alamat']);
        $usaha         = mysqli_real_escape_string($conn, $_POST['tempat_usaha']);
        $telp          = mysqli_real_escape_string($conn, $_POST['telepon']);
        $jenis_anggota = mysqli_real_escape_string($conn, $_POST['jenis_anggota']);
        $tgl_pinjaman  = $_POST['tgl_pinjaman'];

        $oldData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto_ktp, foto_nasabah FROM anggota WHERE id_anggota = $id"));
        $fotoKtp = uploadFile($_FILES['foto_ktp'], 'ktp');
        $fotoNasabah = uploadFile($_FILES['foto_nasabah'], 'profil');

        if ($fotoKtp) {
            if ($oldData['foto_ktp'] != 'default.jpg') @unlink('uploads/' . $oldData['foto_ktp']);
        } else { $fotoKtp = $oldData['foto_ktp']; }

        if ($fotoNasabah) {
            if ($oldData['foto_nasabah'] != 'default.jpg') @unlink('uploads/' . $oldData['foto_nasabah']);
        } else { $fotoNasabah = $oldData['foto_nasabah']; }

        $queryUpdate = "UPDATE anggota SET nama='$nama', alamat='$alamat', tempat_usaha='$usaha', telepon='$telp', jenis_anggota='$jenis_anggota', tgl_pinjaman='$tgl_pinjaman', foto_ktp='$fotoKtp', foto_nasabah='$fotoNasabah' WHERE id_anggota=$id";
        if (mysqli_query($conn, $queryUpdate)) header("Location: anggota.php?update=success");
    }

    // 3. Hapus 
    if (isset($_GET['hapus'])) {
        $id = intval($_GET['hapus']);
        $res = mysqli_query($conn, "SELECT foto_ktp, foto_nasabah FROM anggota WHERE id_anggota = $id");
        $data = mysqli_fetch_assoc($res);
        if ($data) {
            if ($data['foto_ktp'] != 'default.jpg') @unlink('uploads/' . $data['foto_ktp']);
            if ($data['foto_nasabah'] != 'default.jpg') @unlink('uploads/' . $data['foto_nasabah']);
            mysqli_query($conn, "DELETE FROM anggota WHERE id_anggota = $id");
        }
        header("Location: anggota.php");
    }

    // 4. Set Lunas Manual (Override Admin)
    if (isset($_GET['set_lunas'])) {
        $id = intval($_GET['set_lunas']);
        mysqli_query($conn, "UPDATE anggota SET status_pinjaman = 'Lunas' WHERE id_anggota = $id");
        header("Location: anggota.php");
    }
    if (isset($_GET['set_belum_lunas'])) {
        $id = intval($_GET['set_belum_lunas']);
        mysqli_query($conn, "UPDATE anggota SET status_pinjaman = 'Belum Lunas' WHERE id_anggota = $id");
        header("Location: anggota.php");
    }
}

// 5. Load Data dengan Mengambil data dari Tabel 'angsuran' melalui 'pinjaman'
$keyword = isset($_POST['keyword']) ? mysqli_real_escape_string($conn, $_POST['keyword']) : "";

$query = "SELECT a.*, 
          (SELECT COUNT(ang.id_angsuran) 
           FROM angsuran ang 
           JOIN pinjaman p ON ang.id_pinjaman = p.id_pinjaman 
           WHERE p.id_anggota = a.id_anggota) as total_setoran 
          FROM anggota a";

if ($keyword != "") { 
    $query .= " WHERE a.nama LIKE '%$keyword%' OR a.alamat LIKE '%$keyword%'"; 
}
$query .= " ORDER BY a.id_anggota DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Nasabah - KSP Bhakti Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .badge-lunas { background-color: #dcfce7; color: #15803d; }
        .badge-belum { background-color: #fee2e2; color: #b91c1c; }
        .badge-anggota { font-size: 0.65rem; padding: 3px 8px; margin-top: 4px; display: inline-block; }
        .img-thumb { width: 45px; height: 45px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd; }
    </style>
</head>
<body>

<nav class="navbar navbar-light bg-white border-bottom py-3 mb-4">
    <div class="container-fluid px-4">
        <span class="navbar-brand fw-bold text-primary"><i class="fas fa-university me-2"></i> KSP BHAKTI JAYA</span>
        <div class="d-flex gap-2 text-end">
            <div class="me-3 align-self-center small d-none d-md-block">Halo, <b><?= $_SESSION['nama']; ?></b> (<?= ucfirst($role); ?>)</div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fas fa-plus me-2"></i>Nasabah Baru</button>
            <a href="index.php" class="btn btn-light border">Dashboard</a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="card p-4">
        <div class="d-md-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Daftar Nasabah</h4>
            <form method="POST" style="min-width: 300px;">
                <input type="text" name="keyword" class="form-control" placeholder="Cari nasabah..." value="<?= htmlspecialchars($keyword); ?>">
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Identitas</th>
                        <th>Tgl Pinjam</th>
                        <th>Bisnis & Alamat</th>
                        <th>Setoran</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) : 
                        // LOGIKA OTOMATIS LUNAS BERDASARKAN DATA ANGSURAN
                        $status_tampil = $row['status_pinjaman'];
                        if ($row['total_setoran'] >= 20) {
                            $status_tampil = 'Lunas';
                        }
                    ?>
                    <tr>
                        <td><span class="text-muted small">#<?= $row['id_anggota']; ?></span></td>
                        <td>
                            <?php 
                                $fotoPath = 'uploads/' . $row['foto_nasabah'];
                                $tampilFoto = (file_exists($fotoPath) && !empty($row['foto_nasabah'])) ? $fotoPath : 'uploads/default.jpg';
                            ?>
                            <img src="<?= $tampilFoto; ?>" class="img-thumb shadow-sm">
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($row['nama']); ?></div>
                            <span class="badge rounded-pill bg-info text-dark badge-anggota"><?= $row['jenis_anggota'] ?: 'Anggota Baru'; ?></span>
                            <div class="text-muted small mt-1"><?= $row['telepon'] ?: '-'; ?></div>
                        </td>
                        <td><?= formatTanggalIndo($row['tgl_pinjaman']); ?></td>
                        <td>
                            <div class="fw-semibold small"><?= htmlspecialchars($row['tempat_usaha'] ?: '-'); ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($row['alamat'] ?: '-'); ?></div>
                        </td>
                        <td>
                            <span class="fw-bold <?= $row['total_setoran'] >= 20 ? 'text-success' : 'text-primary' ?>">
                                <?= $row['total_setoran']; ?>/20
                            </span>
                        </td>
                        <td>
                            <span class="badge rounded-pill <?= $status_tampil == 'Lunas' ? 'badge-lunas' : 'badge-belum'; ?> px-3 py-2">
                                <?= $status_tampil; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <?php if($role != 'petugas'): ?>
                                    <?php if($status_tampil == 'Lunas'): ?>
                                        <a href="?set_belum_lunas=<?= $row['id_anggota']; ?>" class="btn btn-sm btn-warning" onclick="return confirm('Ubah status menjadi Belum Lunas secara manual?')">Belum Lunas</a>
                                    <?php else: ?>
                                        <a href="?set_lunas=<?= $row['id_anggota']; ?>" class="btn btn-sm btn-success">Set Lunas</a>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_anggota']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?hapus=<?= $row['id_anggota']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Hapus data?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small italic">Hanya Admin</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nasabah Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="small fw-bold">Nama</label><input type="text" name="nama" class="form-control" required></div>
                    <div class="col-md-6"><label class="small fw-bold">WA</label><input type="text" name="telepon" class="form-control"></div>
                    
                    <div class="col-md-6">
                        <label class="small fw-bold">Jenis Anggota</label>
                        <select name="jenis_anggota" class="form-select">
                            <option value="Anggota Baru">Anggota Baru</option>
                            <option value="Anggota Lanjutan 1">Anggota Lanjutan 1</option>
                            <option value="Anggota Lanjutan 2">Anggota Lanjutan 2</option>
                            <option value="Anggota Lanjutan 3">Anggota Lanjutan 3</option>
                            <option value="Anggota Lanjutan 4">Anggota Lanjutan 4</option>
                        </select>
                    </div>

                    <div class="col-md-6"><label class="small fw-bold">Tgl Pinjam</label><input type="date" name="tgl_pinjaman" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                    <div class="col-md-6"><label class="small fw-bold">Usaha</label><input type="text" name="tempat_usaha" class="form-control"></div>
                    <div class="col-12"><label class="small fw-bold">Alamat</label><textarea name="alamat" class="form-control"></textarea></div>
                    <div class="col-md-6"><label class="small fw-bold">KTP</label><input type="file" name="foto_ktp" class="form-control"></div>
                    <div class="col-md-6"><label class="small fw-bold">Profil</label><input type="file" name="foto_nasabah" class="form-control"></div>
                </div>
                <div class="modal-footer"><button type="submit" name="tambah" class="btn btn-primary">SIMPAN DATA</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>