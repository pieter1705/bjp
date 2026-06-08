<?php
// 1. Koneksi ke Database
$conn = mysqli_connect("localhost", "root", "", "koperasi_harian");

// 2. Cek apakah ada ID yang dikirim melalui URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // 3. Query Hapus Data
    $query = "DELETE FROM daftar_gaji WHERE id = '$id'";
    $delete = mysqli_query($conn, $query);

    if ($delete) {
        // Jika berhasil, tampilkan notifikasi dan kembali ke daftar gaji
        echo "<script>
                alert('Data gaji berhasil dihapus!');
                window.location='daftar_gaji.php';
              </script>";
    } else {
        // Jika gagal
        echo "<script>
                alert('Gagal menghapus data: " . mysqli_error($conn) . "');
                window.location='daftar_gaji.php';
              </script>";
    }
} else {
    // Jika mencoba akses langsung tanpa ID
    header("Location: daftar_gaji.php");
    exit;
}
?>