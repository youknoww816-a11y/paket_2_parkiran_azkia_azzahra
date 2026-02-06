<?php
include 'koneksi_parkir.php';

$active_page = 'rekap_transaksi_parkir';

// Note:

// Intinya rekap transaksi parkir khusus untuk user role owner dan siapa yang login untuk melihat
// transaksi yang mereka lahkukan

// tb_log_aktivas akan mencatat tb_transaksi untuk di rekap agar owner tahu aktivitas mereka sendiri.
// Jadi mirip log_aktivitas_parkiran.php, tapi hanya untuk melihat riwayat owner sendiri

// Lokasi kendaraan mereka dimana, kapan mereka masuk dan keluar, tarif parkir, 
// informasi yang ada di tiket parkir ada semua

// Dan bisa di print pdf

?>