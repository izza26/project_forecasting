<?php
include "koneksi.php";

// LIST BULAN
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

// =========================
// DELETE DATA
// =========================
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM data_asli WHERE id='$id'");
    echo "<script>window.location='data_asli.php';</script>";
    exit;
}

// =========================
// PREPARE EDIT MODE
// =========================
$edit_mode = false;
$edit_id = "";
$edit_tahun = "";
$edit_bulan = "";
$edit_nilai = "";

if (isset($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = $_GET['edit'];

    $q = mysqli_query($koneksi, "SELECT * FROM data_asli WHERE id='$edit_id'");
    $d = mysqli_fetch_array($q);

    if ($d) {
        $edit_tahun = $d['tahun'];
        $edit_bulan = intval($d['bulan']);   // dipastikan INT
        $edit_nilai = $d['nilai'];
    }
}

// =========================
// INSERT / UPDATE DATA
// =========================
if (isset($_POST['submit'])) {

    $tahun = $_POST['tahun'];
    $bulan = intval($_POST['bulan']);   // paksa int (fix error)
    $nilai = $_POST['nilai'];

    // MODE UPDATE
    if ($_POST['submit'] == "Ubah Data") {

        $id_update = $_POST['id_update'];

        mysqli_query($koneksi, "
            UPDATE data_asli SET 
                tahun='$tahun',
                bulan='$bulan',
                nilai='$nilai'
            WHERE id='$id_update'
        ");

        echo "<script>alert('Data berhasil diubah!'); window.location='data_asli.php';</script>";
        exit;
    }

    // MODE INSERT
    else {

        mysqli_query($koneksi, "
            INSERT INTO data_asli (tahun, bulan, nilai)
            VALUES ('$tahun', '$bulan', '$nilai')
        ");

        echo "<script>alert('Data berhasil ditambahkan!'); window.location='data_asli.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Asli - Metode Dekomposisi</title>
    <link rel="stylesheet" href="data.css">
</head>

<body>

    <div class="sidebar">
        <h2 class="sidebar-judul">
            <img src="image/logo_utm.png" alt="Logo UTM" class="logo-utm">
            METODE DEKOMPOSISI
        </h2>
        <a href="index.php">Dashboard</a>
        <a href="data_asli.php" class="active">Data Asli</a>
        <a href="indeks_musiman.php">Indeks Musiman</a>
        <a href="dekomposisi.php">Dekomposisi</a>
        <a href="forecast.php">Hasil Forecast</a>
    </div>

    <div class="content">
        <h1 class="judul">Data Asli</h1>
        <p class="subjudul">Input data suku bunga Bank Indonesia untuk perhitungan dekomposisi</p>

        <!-- FORM INPUT -->
        <div class="box form-input">
            <h2><?= $edit_mode ? "Edit Data" : "Tambah Data"; ?></h2>

            <form method="POST">
                <input type="hidden" name="id_update" value="<?= $edit_id ?>">

                <label>Tahun</label>
                <input type="number" name="tahun" required value="<?= $edit_tahun ?>">

                <label>Bulan</label>
                <select name="bulan" required>
                    <option value="">-- Pilih Bulan --</option>

                    <?php
                    foreach ($bulan_list as $key => $val) {
                        $selected = ($edit_bulan == $key) ? "selected" : "";
                        echo "<option value='$key' $selected>$val</option>";
                    }
                    ?>
                </select>

                <label>Nilai</label>
                <input type="number" step="0.01" name="nilai" required value="<?= $edit_nilai ?>">

                <button type="submit" name="submit" value="<?= $edit_mode ? 'Ubah Data' : 'Tambah Data' ?>"
                    class="btn-submit">
                    <?= $edit_mode ? "Ubah Data" : "Tambah Data"; ?>
                </button>

                <?php if ($edit_mode) { ?>
                    <a href="data_asli.php" class="btn-cancel">Batal</a>
                <?php } ?>
            </form>

        </div>

        <!-- TABEL DATA -->
        <div class="box">
            <h2>Daftar Data</h2>

            <table>
                <tr>
                    <th>No</th>
                    <th>Tahun</th>
                    <th>Bulan</th>
                    <th>Nilai</th>
                    <th>Aksi</th>
                </tr>

                <?php
                $no = 1;
                $q = mysqli_query($koneksi, "SELECT * FROM data_asli ORDER BY tahun, bulan");

                while ($row = mysqli_fetch_array($q)) { ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $row['tahun']; ?></td>

                        <td>
                            <?= $bulan_list[(int) $row['bulan']] ?? "—"; ?>
                        </td>

                        <td><?= $row['nilai']; ?></td>

                        <td>
                            <a href="data_asli.php?edit=<?= $row['id']; ?>" class="btn-edit">Edit</a>
                            <a href="data_asli.php?hapus=<?= $row['id']; ?>" class="btn-delete"
                                onclick="return confirm('Hapus data ini?');">Hapus</a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>

</body>

</html>