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

$data_tampil = [];
$final_forecast = null;
$forecast_tahun = null;
$forecast_bulan = null;

// =====================================================================
// 1. Ambil data dari dekomposisi
// =====================================================================
$q = mysqli_query($koneksi, "SELECT * FROM dekomposisi ORDER BY tahun, bulan");

while ($r = mysqli_fetch_array($q)) {
    $r_data = $r;

    // Identifikasi nilai forecast masa depan
    if ($r['nilai_asli'] === null && $r['forecast'] !== null) {
        $final_forecast = (double) $r['forecast'];
        $forecast_tahun = $r['tahun'];
        $forecast_bulan = $r['bulan'];
    }

    $data_tampil[] = $r_data;
}

// =====================================================================
// 2. Simpan final forecast ke tabel hasil_forecast
// =====================================================================
if ($final_forecast !== null) {
    mysqli_query($koneksi, "TRUNCATE TABLE hasil_forecast");

    $final_forecast_rounded = round($final_forecast, 4);

    mysqli_query($koneksi, "
        INSERT INTO hasil_forecast (tahun, bulan, forecast)
        VALUES ('$forecast_tahun', '$forecast_bulan', '$final_forecast_rounded')
    ");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Hasil Forecast</title>
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
        <a href="indeks_musiman.php">Indeks Musiman</a>
        <a href="dekomposisi.php">Dekomposisi</a>
        <a href="forecast.php" class="active">Hasil Forecast</a>
    </div>

    <div class="content">

        <h1 class="judul">Hasil Forecast</h1>

        <div class="box">
            <h2>Hasil Peramalan Periode Berikutnya</h2>
            <?php if ($final_forecast !== null) { ?>
                <p>Hasil peramalan untuk **<?php echo $bulan_list[$forecast_bulan]; ?>     <?php echo $forecast_tahun; ?>**
                    adalah:</p>
                <h1 style="color: green;"><?php echo round($final_forecast, 4); ?></h1>
            <?php } else { ?>
                <p>Belum ada data forecast yang tersedia. Harap proses data di halaman Dekomposisi terlebih dahulu.</p>
            <?php } ?>
        </div>

        <div class="box">
            <h2>Tabel Perbandingan Data Historis vs. Forecast</h2>

            <table>
                <tr>
                    <th>No</th>
                    <th>Tahun</th>
                    <th>Bulan</th>
                    <th>Nilai Asli (Y)</th>
                    <th>Forecast (Ŷ)</th>
                </tr>

                <?php
                $no = 1;
                foreach ($data_tampil as $r) {
                    $is_forecast_row = $r['nilai_asli'] == null;
                    ?>
                    <tr class="<?php echo $is_forecast_row ? 'forecast-row' : ''; ?>">
                        <td><?= $no++; ?></td>
                        <td><?= $r['tahun']; ?></td>
                        <td><?= $bulan_list[$r['bulan']]; ?></td>
                        <td style="font-weight: bold;"><?= $r['nilai_asli'] ?? ''; ?></td>
                        <td style="color: <?php echo $is_forecast_row ? 'green' : 'blue'; ?>; font-weight: bold;">
                            <?= $r['forecast'] !== null ? round($r['forecast'], 4) : ''; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>
</body>

</html>