<?php
session_start();
include 'koneksi.php';

// Pastikan request datang dari form (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query untuk mencari user di database
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    $cek = mysqli_num_rows($query);

    if ($cek > 0) {
        $data = mysqli_fetch_assoc($query);
        
        // Simpan data ke session
        $_SESSION['username'] = $data['username'];
        $_SESSION['role']     = $data['role'];
        $_SESSION['nama']     = $data['nama_lengkap'];
        
        // Arahkan ke dashboard
        header("location:index.php");
        exit();
    } else {
        // Jika gagal, kembali ke login dengan pesan error
        header("location:login.php?pesan=gagal");
        exit();
    }
}
?>