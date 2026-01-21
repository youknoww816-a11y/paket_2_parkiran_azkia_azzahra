<?php
include 'koneksi_parkir.php';

$active_page = 'log_aktivitas_parkiran';
//Note: jadi ini untuk kaya log atau riyawat, untuk tau siapa yang masuk, siapa yang keluar, 
//      sama siapa yang masih ada di area parkir.

// Selain itu kamu bisa liat setiap kendaraan terparkir di area mana.

// Kamu bisa liat berapa kapasitas kendaraan dan area parkir yang kosong dan yang penuh.

// Bentukannya mirip riyawat_absensi.php tapi lebih rapi karena ada garis per-hari yang membatasi,
//  sama posisinya. . . masih belum yakin aku
// Soalnya yang pertama kamu liat itu semcam animasi kendaraan terparkir.

// Bisa tersortir atau difilter berdasarkan siapa yang baru masuk, siapa yang baru keluar, 
// siapa yang masih terparkir (bahkan dari hari yang kemarin), dan area spesifik kendaraan terparkir.

// Kamu bisa liat kendaraannya, warna kendaraan, jenis kendaraan, plat nomornya, nama pemilik, nama usernamenya,
// kapan mereka keluar-masuk, berapa lama mereka terparkir, dan dimana area mereka terpakir

// Kamu juga bisa search username atau nama lengkap untuk pecarian riwayat spesifik