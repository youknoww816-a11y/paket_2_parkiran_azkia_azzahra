<?php
include 'koneksi_parkir.php';

$active_page = 'transaksi_parkir';
//Note: Dimana owner atau pemilik kendaraan ngambil tiket parkir.

// Tiket parkir bisa di download pdf dan cukup itu aja (kalau kamu pake komputer bisa langsung print soalnya).

// Bentuknya mirip preview dari pengambilan_order_bumdes.php atau sample tiket parkir png, 
// tapi fungsinya tidak, bentukannya aja.

// Data tiket parkir akan menjabarkan nama, username, plat nomor,tipe kendaraan, warna kendaraan,
// tanggal dan waktu masuk dan keluar (tergantung), area kendaraan diparkirkan, 
// durasi kendaraan diparkirkan (untuk tiket pulang), info tarif perjam kendaraan diparkirkan (untuk tiket masuk),

// Owner atau pemilik hanya perlu memasukan username dan password, tiket masuk akan langsung dibuat.
// Tiket keluar lahkukan cara yang sama, yang membedakan statusnya (keluar atau masuk).
// Kendaraan atau username yang sudah masuk hanya bisa memilih status keluar.

// Mungkin perlu bar-kode/qrcode untuk mempermudah pengambilan tiket parkir?
// idk, kayanya susah... semoga aja enggak disuruh itu