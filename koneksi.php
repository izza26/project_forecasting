<?php
$koneksi = mysqli_connect("localhost", "root", "", "db_peramalan");

if (!$koneksi) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
}
?>
