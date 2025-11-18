<?php
include "koneksi.php";

$bulan_list = [
    1 => "Januari",
    2 => "Februari",
    3 => "Maret",
    4 => "April",
    5 => "Mei",
    6 => "Juni",
    7 => "Juli",
    8 => "Agustus",
    9 => "September",
    10 => "Oktober",
    11 => "November",
    12 => "Desember"
];


// ===============================
// 1. Ambil Data Asli
// ===============================
$q = mysqli_query($koneksi, "SELECT bulan, nilai FROM data_asli ORDER BY tahun, bulan");

$jumlah_bulan = [];
$total_semua = 0;

while ($row = mysqli_fetch_assoc($q)) {
    $b = intval($row['bulan']);
    $v = floatval($row['nilai']);

    // jumlah per bulan
    if (!isset($jumlah_bulan[$b]))
        $jumlah_bulan[$b] = 0;
    $jumlah_bulan[$b] += $v;

    // total semua bulan
    $total_semua += $v;
}


// Pastikan 12 bulan ada
for ($i = 1; $i <= 12; $i++) {
    if (!isset($jumlah_bulan[$i]))
        $jumlah_bulan[$i] = 0;
}


// ===============================
// 2. Hitung Rasio dan Indeks Awal
// ===============================
$rasio = [];
$indeks_awal = [];

foreach ($jumlah_bulan as $bulan => $sum_nilai) {

    $rasio[$bulan] = $sum_nilai / $total_semua;
    $indeks_awal[$bulan] = $rasio[$bulan] * 12; // metode multiplicative
}


// ===============================
// 3. Hitung Correction Factor (CF)
// ===============================

$total_indeks_awal = array_sum($indeks_awal);
$faktor_koreksi = 12 / $total_indeks_awal;


// ===============================
// 4. Hitung Seasonal Index Final
// ===============================
$indeks_musiman = [];

foreach ($indeks_awal as $bulan => $ia) {
    $indeks_musiman[$bulan] = $ia * $faktor_koreksi;
}


// ===============================
// 5. Simpan ke Database
// ===============================
mysqli_query($koneksi, "TRUNCATE TABLE indeks_musiman");

foreach ($jumlah_bulan as $bulan => $sum_val) {

    $r = $rasio[$bulan];
    $ia = $indeks_awal[$bulan];
    $im = $indeks_musiman[$bulan];

    mysqli_query($koneksi, "
        INSERT INTO indeks_musiman (bulan, jumlah_bulanan, rasio, indeks_awal, faktor_koreksi, indeks_musiman)
        VALUES ('$bulan', '$sum_val', '$r', '$ia', '$faktor_koreksi', '$im')
    ");
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Indeks Musiman</title>
    <link rel="stylesheet" href="data.css">
</head>

<body>

    <div class="sidebar">
        <h2 class="sidebar-judul">
            <img src="image/logo_utm.png" alt="Logo UTM" class="logo-utm">
            METODE DEKOMPOSISI
        </h2>
        <a href="index.php">Dashboard</a>
        <a href="data_asli.php">Data Asli</a>
        <a href="indeks_musiman.php" class="active">Indeks Musiman</a>
        <a href="dekomposisi.php">Dekomposisi</a>
        <a href="forecast.php">Hasil Forecast</a>
    </div>

    <div class="content">

        <h1 class="judul">Indeks Musiman</h1>
        <p class="subjudul">Perhitungan Seasonal Index (Metode Dekomposisi)</p>

        <div class="box">
            <h2>Hasil Indeks Musiman</h2>

            <table>
                <tr>
                    <th>No</th>
                    <th>Bulan</th>
                    <th>Jumlah Bulanan</th>
                    <th>Rasio</th>
                    <th>Indeks Awal</th>
                    <th>Faktor Koreksi</th>
                    <th>Indeks Musiman (Final)</th>
                </tr>

                <?php
                $q = mysqli_query($koneksi, "SELECT * FROM indeks_musiman ORDER BY bulan");
                $no = 1;

                while ($row = mysqli_fetch_assoc($q)) { ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $bulan_list[$row['bulan']]; ?></td>
                        <td><?= $row['jumlah_bulanan']; ?></td>
                        <td><?= number_format($row['rasio'], 5); ?></td>
                        <td><?= number_format($row['indeks_awal'], 5); ?></td>
                        <td><?= number_format($row['faktor_koreksi'], 5); ?></td>
                        <td><b><?= number_format($row['indeks_musiman'], 3); ?></b></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>

</body>

</html>