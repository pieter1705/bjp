<?php
session_start();
include 'koneksi.php';

// Proteksi akses: Hanya user yang sudah login yang bisa memproses data
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

/**
 * 1. PROSES TAMBAH BON BARU
 */
if (isset($_POST['simpan_bon'])) {
    // Mengambil data dan membersihkannya dari karakter berbahaya
    $id_anggota  = mysqli_real_escape_string($conn, $_POST['id_anggota']);
    $tgl_bon     = mysqli_real_escape_string($conn, $_POST['tgl_bon']);
    $nominal_bon = mysqli_real_escape_string($conn, $_POST['nominal_bon']);
    $keterangan  = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // Validasi sederhana: pastikan nominal tidak negatif atau nol
    if ($nominal_bon <= 0) {
        echo "<script>alert('Nominal bon harus lebih dari 0!'); window.history.back();</script>";
        exit();
    }

    $query = "INSERT INTO bon (id_anggota, tgl_bon, nominal_bon, keterangan, status_bon) 
              VALUES ('$id_anggota', '$tgl_bon', '$nominal_bon', '$keterangan', 'aktif')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data Bon berhasil dicatat!'); window.location='buku_bon.php';</script>";
    } else {
        echo "<script>alert('Gagal Simpan: " . mysqli_error($conn) . "'); window.history.back();</script>";
    }
}

/**
 * 2. PROSES BAYAR BON (OPSIONAL - Jika Anda butuh fitur cicil bon)
 */
if (isset($_POST['bayar_bon'])) {
    $id_bon        = mysqli_real_escape_string($conn, $_POST['id_bon']);
    $tgl_bayar     = mysqli_real_escape_string($conn, $_POST['tgl_bayar']);
    $nominal_bayar = mysqli_real_escape_string($conn, $_POST['nominal_bayar']);

    $query = "INSERT INTO bayar_bon (id_bon, tgl_bayar, nominal_bayar) 
              VALUES ('$id_bon', '$tgl_bayar', '$nominal_bayar')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Pembayaran Bon berhasil dicatat!'); window.location='buku_bon.php';</script>";
    } else {
        echo "<script>alert('Gagal Bayar: " . mysqli_error($conn) . "'); window.history.back();</script>";
    }
}

/**
 * 3. PROSES HAPUS BON (Jika admin salah input)
 */
if (isset($_GET['hapus_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus_id']);
    
    // Karena menggunakan CASCADE di SQL tadi, menghapus bon otomatis menghapus riwayat bayarnya
    $query = "DELETE FROM bon WHERE id_bon = '$id'";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data Bon berhasil dihapus!'); window.location='buku_bon.php';</script>";
    } else {
        echo "<script>alert('Gagal Hapus: " . mysqli_error($conn) . "'); window.location='buku_bon.php';</script>";
    }
}
?>