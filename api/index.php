<?php include "koneksi.php"; ?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <title>Dashboard Peramalan - Metode Dekomposisi</title>

    <link rel="stylesheet" href="index.css">
</head>

<body>

    <div class="sidebar">
        <h2 class="sidebar-judul">
            <img src="image/logo_utm.png" alt="Logo UTM" class="logo-utm">
            METODE DEKOMPOSISI
        </h2> 
        <a href="index.php" class="active">Dashboard</a>
        <a href="data_asli.php">Data Asli</a>
        <a href="indeks_musiman.php">Indeks Musiman</a>
        <a href="dekomposisi.php">Dekomposisi</a>
        <a href="forecast.php">Hasil Forecast</a>
    </div>

    <div class="content">
        <h1 class="judul">Dashboard Peramalan</h1>
        <p class="subjudul">Metode: Dekomposisi</p>

        <div class="box">
            <h2>Penjelasan Metode Dekomposisi</h2>
            <p>
                Metode dekomposisi adalah metode peramalan yang memisahkan data deret waktu
                menjadi beberapa komponen utama, yaitu trend (T), seasonal (S),
                cyclical (C), dan irregular (I). Model yang digunakan dalam sistem ini
                adalah model dekomposisi aditif dengan rumus:
                <b>Y = T + S + C + I</b>.
            </p>

            <p>Tahapan pada metode dekomposisi meliputi:</p>

            <ol style="margin-top:0;">
                <li>Menghitung moving average (MA)</li>
                <li>Menghitung centered moving average (CMA)</li>
                <li>Menghitung rasio Y/CMA</li>
                <li>Menentukan indeks musiman berdasarkan rata-rata rasio per bulan</li>
                <li>Menghitung nilai trend menggunakan regresi linier</li>
                <li>Menghitung forecast menggunakan T + S + C + I (nilai I dianggap 1)</li>
            </ol>
        </div>


        <div class="box">
            <h2>Studi Kasus</h2>
            <p>
                Sistem ini menggunakan studi kasus peramalan Suku Bunga Bank Indonesia
                untuk periode tahun 2019 hingga 2023. Hasil peramalan yang ditampilkan
                merupakan nilai prediksi untuk bulan Januari tahun 2024.
                <br><br>
                Sumber data diambil dari skripsi berjudul:
                <b>"PENGGUNAAN MODEL FUZZY TIME SERIES-MARKOV CHAIN UNTUK PERAMALAN SUKU BUNGA BANK INDONESIA"</b>.
            </p>
        </div>

        <div class="box">
            <h2>Dosen Pengampu</h2>
            <p>
                SRI HERAWATI, S.Kom., M.Kom.
            </p>
        </div>

        <div class="box">
            <h2>Anggota Kelompok 7</h2>
            <p>
                1. Sholihatul Muyassaroh (230441100012)<br>
                2. Arhamiz Fegianti (230441100080)
            </p>
        </div>
    </div>

</body>

</html>