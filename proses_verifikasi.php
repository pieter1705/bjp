<?php
include 'koneksi.php';

// Menangkap data dari URL
$id_kolektor = isset($_GET['id_kolektor']) ? mysqli_real_escape_string($conn, $_GET['id_kolektor']) : '';
$tgl = isset($_GET['tgl']) ? mysqli_real_escape_string($conn, $_GET['tgl']) : date('Y-m-d');

if ($id_kolektor != '') {
    /**
     * LOGIKA VERIFIKASI:
     * Di sini Anda bisa menambahkan kolom 'status' di tabel angsuran 
     * atau memasukkan data ke tabel rekap_setoran baru.
     * Sebagai contoh, kita akan mengupdate status di tabel angsuran.
     */
    
    // Contoh Query: Mengubah status angsuran menjadi 'Terverifikasi' untuk kolektor & tanggal tersebut
    $query = "UPDATE angsuran 
              SET status_verifikasi = '1' 
              WHERE id_kolektor = '$id_kolektor' 
              AND DATE(tgl_bayar) = '$tgl'";
              
    $update = mysqli_query($conn, $query);

    if ($update) {
        echo "<script>
                alert('Setoran Berhasil Diverifikasi!');
                window.location.href='laporan_kolektor.php?tgl=$tgl';
              </script>";
    } else {
        echo "<script>
                alert('Gagal Verifikasi: " . mysqli_error($conn) . "');
                window.location.href='laporan_kolektor.php?tgl=$tgl';
              </script>";
    }
} else {
    header("Location: laporan_kolektor.php");
}
?>