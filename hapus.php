<?php
include 'koneksi.php';

if (isset($_GET['id'])) {
    // Sanitasi input ID untuk keamanan
    $id = intval($_GET['id']);

    // 1. Ambil informasi foto terlebih dahulu sebelum data dihapus
    $query_foto = "SELECT foto_ktp, foto_nasabah FROM anggota WHERE id_anggota = $id";
    $result_foto = mysqli_query($conn, $query_foto);
    $data_foto = mysqli_fetch_assoc($result_foto);

    if ($data_foto) {
        // --- PROSES HAPUS BERTAHAP ---

        // 2. Hapus data di tabel 'angsuran' terlebih dahulu (Tabel Cucu)
        // Kita hapus angsuran yang memiliki relasi melalui id_pinjaman milik anggota tersebut
        mysqli_query($conn, "DELETE FROM angsuran WHERE id_pinjaman IN (SELECT id_pinjaman FROM pinjaman WHERE id_anggota = $id)");

        // 3. Hapus data di tabel 'pinjaman' (Tabel Anak)
        mysqli_query($conn, "DELETE FROM pinjaman WHERE id_anggota = $id");

        // 4. Baru hapus data di tabel 'anggota' (Tabel Induk)
        $query_hapus = "DELETE FROM anggota WHERE id_anggota = $id";
        
        if (mysqli_query($conn, $query_hapus)) {
            // 5. Hapus file fisik gambar dari folder uploads jika bukan default
            if ($data_foto['foto_ktp'] != 'default.jpg' && file_exists('uploads/' . $data_foto['foto_ktp'])) {
                @unlink('uploads/' . $data_foto['foto_ktp']);
            }
            if ($data_foto['foto_nasabah'] != 'default.jpg' && file_exists('uploads/' . $data_foto['foto_nasabah'])) {
                @unlink('uploads/' . $data_foto['foto_nasabah']);
            }

            // Redirect dengan pesan sukses
            echo "<script>alert('Seluruh data anggota, pinjaman, dan angsuran berhasil dihapus!'); window.location='anggota.php';</script>";
        } else {
            echo "<script>alert('Gagal menghapus data: " . mysqli_error($conn) . "'); window.location='anggota.php';</script>";
        }
    } else {
        echo "<script>alert('Data tidak ditemukan!'); window.location='anggota.php';</script>";
    }
} else {
    header("Location: anggota.php");
    exit();
}
?>