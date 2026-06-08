# BJP - Sistem Koperasi Bhakti Jaya Prima
Aplikasi Manajemen Koperasi Simpan Pinjam

Sistem informasi untuk mengelola data anggota, transaksi simpan pinjam, 
angsuran, dan laporan keuangan Koperasi Bhakti Jaya Prima.

## Tech Stack
- **Backend**: PHP Native, MySQL
- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Server**: XAMPP/WAMP

## Fitur Utama
- **Data Anggota**: CRUD data anggota + upload foto KTP
- **Simpan Pinjam**: Input pinjaman, hitung bunga otomatis
- **Angsuran**: Cicilan per bulan, denda keterlambatan
- **Transaksi**: Simpan wajib, simpan sukarela, penarikan
- **Laporan**: Laporan keuangan, SHU, rekap angsuran ke Excel/PDF
- **Login**: Multi-level Admin, Bendahara, Ketua

## Cara Install
1. Clone repo: `git clone https://github.com/pieter1705/bjp`
2. Import `database.sql` ke phpMyAdmin
3. Edit `config.php` sesuai host DB lu
4. Buka `localhost/bjp`
5. Login default: `admin / admin123`

## Screenshot
![Dashboard Koperasi](screenshot/dashboard.jpg)
![Input Pinjaman](screenshot/pinjaman.jpg)
![Laporan Angsuran](screenshot/angsuran.jpg)

## Catatan
Project portfolio untuk sistem koperasi simpan pinjam. 
Data anggota sudah di-anonimkan untuk keamanan.
