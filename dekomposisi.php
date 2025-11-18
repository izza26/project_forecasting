<?php
include "koneksi.php";

// Ambil indeks musiman dari database
$si_list = [];
$qsi = mysqli_query($koneksi, "SELECT bulan, indeks_musiman FROM indeks_musiman");
while ($r = mysqli_fetch_array($qsi)) {
    $si_list[(int) $r['bulan']] = (double) $r['indeks_musiman'];
}

// List bulan
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

$hasil = [];
$a = 0;
$b = 0;

// =====================================================================
//  Jika user menekan proses
// =====================================================================
if (isset($_POST['proses'])) {

    $k = intval($_POST['k']);   // MA window size

    // Ambil seluruh data asli
    $data = [];
    $q = mysqli_query($koneksi, "SELECT * FROM data_asli ORDER BY tahun, bulan");
    while ($r = mysqli_fetch_array($q)) {
        $data[] = $r;
    }

    $n_data_asli = count($data);

    // =================================================================
    // TAMBAH 1 BARIS DATA UNTUK FORECAST
    // =================================================================
    if ($n_data_asli > 0) {
        $last_data = $data[$n_data_asli - 1];
        $next_bulan = $last_data['bulan'] % 12 + 1;
        $next_tahun = $last_data['tahun'] + floor($last_data['bulan'] / 12);

        $data[] = [
            'tahun' => $next_tahun,
            'bulan' => $next_bulan,
            'nilai' => null,
            'id' => null
        ];
    }

    $n_data = count($data); // Total baris (data asli + 1 baris forecast)

    // Siapkan array dengan ukuran baru
    $MA = array_fill(0, $n_data, null);
    $CMA = array_fill(0, $n_data, null);
    $trend = array_fill(0, $n_data, null);
    $CF = array_fill(0, $n_data, null);
    $SI = array_fill(0, $n_data, null);
    $forecast = array_fill(0, $n_data, null);

    // =================================================================
    // 3. Perhitungan Regresi (Trend)
    // =================================================================
    $X = [];
    $Y = [];
    for ($i = 0; $i < $n_data_asli; $i++) {
        $X[] = $i;
        $Y[] = (double) $data[$i]['nilai'];
    }

    $n = count($X);
    $sumX = array_sum($X);
    $sumY = array_sum($Y);

    $sumX2 = 0;
    $sumXY = 0;

    for ($i = 0; $i < $n; $i++) {
        $sumX2 += $X[$i] * $X[$i];
        $sumXY += $X[$i] * $Y[$i];
    }

    $den = ($n * $sumX2) - ($sumX * $sumX);
    if ($den != 0) {
        $a = (($sumY * $sumX2) - ($sumX * $sumXY)) / $den;
        $b = (($n * $sumXY) - ($sumX * $sumY)) / $den;
    }

    // =================================================================
    // 4. Hitung Trend = a + bX
    // =================================================================
    for ($i = 0; $i < $n_data; $i++) {
        $trend[$i] = $a + $b * $i;
    }

    // =================================================================
    // 1. Hitung MA (Moving Average) - HISTORIS & EKSTRAPOLASI
    // =================================================================
    // Perhitungan Historis
    for ($i = $k; $i < $n_data_asli; $i++) {
        $sum = 0;
        for ($j = 1; $j <= $k; $j++) {
            $sum += $data[$i - $j]['nilai'];
        }
        $MA[$i] = $sum / $k;
    }
    // Perhitungan Ekstrapolasi MA untuk baris forecast ($T+1$)
    if ($n_data > $n_data_asli) {
        $i = $n_data_asli;
        $sum = 0;
        // MA[T+1] menggunakan k data Y historis terakhir
        for ($j = 1; $j <= $k; $j++) {
            if (isset($data[$i - $j]['nilai'])) {
                $sum += $data[$i - $j]['nilai'];
            }
        }
        $MA[$i] = $sum / $k;
    }

    // =================================================================
    // 2. Hitung CMA = (MA[t] + MA[t-1]) / 2 - HISTORIS & EKSTRAPOLASI
    // =================================================================
    // Perhitungan Historis
    for ($i = $k + 1; $i < $n_data_asli; $i++) {
        if ($MA[$i] !== null && $MA[$i - 1] !== null) {
            $CMA[$i] = ($MA[$i] + $MA[$i - 1]) / 2;
        }
    }
    // Perhitungan Ekstrapolasi CMA untuk baris forecast ($T+1$)
    if ($n_data > $n_data_asli) {
        $i = $n_data_asli;
        if ($MA[$i] !== null && $MA[$i - 1] !== null) {
            $CMA[$i] = ($MA[$i] + $MA[$i - 1]) / 2;
        }
    }


    // =================================================================
    // 5. CF = CMA / Trend - HISTORIS & EKSTRAPOLASI
    // =================================================================
    for ($i = 0; $i < $n_data; $i++) {
        if ($CMA[$i] !== null && $trend[$i] != 0) {
            $CF[$i] = $CMA[$i] / $trend[$i];
        }
    }

    // =================================================================
    // 6. Ambil SI dari tabel indeks_musiman
    // =================================================================
    for ($i = 0; $i < $n_data; $i++) {
        $bln = $data[$i]['bulan'];
        $SI[$i] = $si_list[$bln] ?? null;
    }

    // =================================================================
    // 7. Forecast = Trend + CF + SI + 1 (FORMULA UNIFIED)
    // =================================================================
    for ($i = 0; $i < $n_data; $i++) {
        if ($trend[$i] !== null && $CF[$i] !== null && $SI[$i] !== null) {
            $forecast[$i] = $trend[$i] + $CF[$i] + $SI[$i] + 1;
        }
    }


    // =================================================================
    // 8. Simpan ke database (dengan pembulatan 4 desimal)
    // =================================================================
    mysqli_query($koneksi, "TRUNCATE TABLE dekomposisi");

    $presisi = 4;

    for ($i = 0; $i < $n_data; $i++) {
        $ma_val = $MA[$i] !== null ? round($MA[$i], $presisi) : "NULL";
        $cma_val = $CMA[$i] !== null ? round($CMA[$i], $presisi) : "NULL";
        $trend_val = $trend[$i] !== null ? round($trend[$i], $presisi) : "NULL";
        $cf_val = $CF[$i] !== null ? round($CF[$i], $presisi) : "NULL";
        $si_val = $SI[$i] !== null ? round($SI[$i], $presisi) : "NULL";
        $forecast_val = $forecast[$i] !== null ? round($forecast[$i], $presisi) : "NULL";

        $nilai_asli_val = $data[$i]['nilai'] ?? 'NULL';

        $sql = "
            INSERT INTO dekomposisi
            (tahun, bulan, nilai_asli, ma, cma, trend, cf, si, forecast)
            VALUES (
                '" . $data[$i]['tahun'] . "',
                '" . $data[$i]['bulan'] . "',
                " . $nilai_asli_val . ",
                " . $ma_val . ",
                " . $cma_val . ",
                " . $trend_val . ",
                " . $cf_val . ",
                " . $si_val . ",
                " . $forecast_val . "
            )
        ";
        mysqli_query($koneksi, $sql);
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Dekomposisi</title>
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
        <a href="dekomposisi.php" class="active">Dekomposisi</a>
        <a href="forecast.php">Hasil Forecast</a>
    </div>

    <div class="content">

        <h1 class="judul">Dekomposisi Time Series</h1>

        <div class="box form-input">
            <h2>Pilih Nilai K (Moving Average)</h2>

            <form method="POST">
                <label>Nilai K</label>
                <select name="k" required>
                    <option value="12" <?= (isset($_POST['k']) && $_POST['k'] == 12) ? 'selected' : ''; ?>>12</option>
                    <option value="6" <?= (isset($_POST['k']) && $_POST['k'] == 6) ? 'selected' : ''; ?>>6</option>
                    <option value="4" <?= (isset($_POST['k']) && $_POST['k'] == 4) ? 'selected' : ''; ?>>4</option>
                    <option value="3" <?= (isset($_POST['k']) && $_POST['k'] == 3) ? 'selected' : ''; ?>>3</option>
                </select>

                <button type="submit" name="proses" class="btn-submit">Proses</button>
            </form>

            <?php if (isset($_POST['proses'])) { ?>
                <p><b>Nilai a :</b> <?= round($a, 4); ?>
                    &nbsp; | &nbsp;
                    <b>Nilai b :</b> <?= round($b, 4); ?>
                </p>
            <?php } ?>
        </div>


        <?php
        $q_tampil = mysqli_query($koneksi, "SELECT * FROM dekomposisi ORDER BY tahun, bulan");

        if (mysqli_num_rows($q_tampil) > 0) {
            ?>
            <div class="box">
                <h2>Hasil Perhitungan Dekomposisi</h2>

                <table>
                    <tr>
                        <th>No</th>
                        <th>Tahun</th>
                        <th>Bulan</th>
                        <th>Y (Asli)</th>
                        <th>MA</th>
                        <th>CMA</th>
                        <th>Trend (T)</th>
                        <th>CF (CMA/T)</th>
                        <th>SI</th>
                        <th>Forecast (Y)</th>
                    </tr>

                    <?php
                    $no = 1;
                    while ($r = mysqli_fetch_array($q_tampil)) {
                        ?>
                        <tr class="<?= $r['nilai_asli'] == null ? 'forecast-row' : ''; ?>">
                            <td><?= $no++; ?></td>
                            <td><?= $r['tahun']; ?></td>
                            <td><?= $bulan_list[$r['bulan']]; ?></td>
                            <td><?= $r['nilai_asli']; ?></td>
                            <td><?= $r['ma'] !== null ? round($r['ma'], 4) : ''; ?></td>
                            <td><?= $r['cma'] !== null ? round($r['cma'], 4) : ''; ?></td>
                            <td><?= $r['trend'] !== null ? round($r['trend'], 4) : ''; ?></td>
                            <td><?= $r['cf'] !== null ? round($r['cf'], 4) : ''; ?></td>
                            <td><?= $r['si'] !== null ? round($r['si'], 4) : ''; ?></td>
                            <td><?= $r['forecast'] !== null ? round($r['forecast'], 4) : ''; ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        <?php } ?>

    </div>
</body>

</html>