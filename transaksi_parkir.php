<?php
session_start();
include 'koneksi.php';

// Proteksi Petugas
if ($_SESSION['role'] !== 'Petugas') {
    header("location: index.php");
    exit();
}

// 1. PROSES KENDARAAN MASUK
if (isset($_POST['masuk'])) {
    $plat      = mysqli_real_escape_string($conn, $_POST['no_plat']);
    $id_tarif  = $_POST['id_tarif'];
    $id_area   = $_POST['id_area'];
    $id_user   = $_SESSION['id_user'];
    $waktu_in  = date('Y-m-d H:i:s');
    $kode      = "PKR-" . date('dHis');

    // Cek sisa kuota area
    $check_area = mysqli_fetch_assoc(mysqli_query($conn, "SELECT terisi, kapasitas FROM tb_area WHERE id_area='$id_area'"));
    if ($check_area['terisi'] < $check_area['kapasitas']) {
        $q = mysqli_query($conn, "INSERT INTO tb_transaksi (kode_parkir, no_plat, id_tarif, id_area, id_user, waktu_masuk, status) 
                                 VALUES ('$kode', '$plat', '$id_tarif', '$id_area', '$id_user', '$waktu_in', 'Masuk')");
        
        if ($q) {
            mysqli_query($conn, "UPDATE tb_area SET terisi = terisi + 1 WHERE id_area = '$id_area'");
            header("location: transaksi_parkir.php?msg=masuk_berhasil");
        }
    } else {
        $error = "Maaf, Area Parkir sudah penuh!";
    }
}

// 2. PROSES KELUAR & HITUNG BIAYA (Menggunakan biaya_total)
if (isset($_GET['keluar'])) {
    $id_p      = $_GET['keluar'];
    $waktu_out = date('Y-m-d H:i:s');
    
    // Ambil data masuk & tarif detail
    $sql  = "SELECT t.*, tr.harga_dasar, tr.biaya_perjam 
             FROM tb_transaksi t 
             JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
             WHERE t.id_parkir = '$id_p'";
    $data = mysqli_fetch_assoc(mysqli_query($conn, $sql));
    
    // Hitung Durasi (Pembulatan ke atas per jam)
    $awal  = new DateTime($data['waktu_masuk']);
    $akhir = new DateTime($waktu_out);
    $diff  = $awal->diff($akhir);
    $jam   = $diff->h + ($diff->days * 24);
    if ($diff->i > 0 || $diff->s > 0) $jam++; 

    // Rumus: Harga Dasar + (Jam Tambahan * Biaya Perjam)
    $biaya = $data['harga_dasar'];
    if ($jam > 1) {
        $biaya += ($jam - 1) * $data['biaya_perjam'];
    }

    // Update Data Keluar ke kolom biaya_total
    $update = mysqli_query($conn, "UPDATE tb_transaksi SET 
                                    waktu_keluar = '$waktu_out', 
                                    biaya_total = '$biaya', 
                                    status = 'Selesai' 
                                    WHERE id_parkir = '$id_p'");
    
    if ($update) {
        mysqli_query($conn, "UPDATE tb_area SET terisi = terisi - 1 WHERE id_area = '".$data['id_area']."'");
        header("location: cetak_struk.php?id=$id_p");
    }
}

// Data Pendukung UI
$tarif_list = mysqli_query($conn, "SELECT * FROM tb_tarif");
$area_list  = mysqli_query($conn, "SELECT * FROM tb_area");
$parkir_aktif = mysqli_query($conn, "SELECT t.*, tr.jenis_kendaraan, a.nama_area 
                                     FROM tb_transaksi t 
                                     LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
                                     LEFT JOIN tb_area a ON t.id_area = a.id_area 
                                     WHERE t.status = 'Masuk' ORDER BY t.waktu_masuk DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Parkir | Petugas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --success: #10b981; --danger: #ef4444; --bg: #f8fafc; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; }
        
        .nav { background: white; padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav a { text-decoration: none; color: #64748b; font-weight: 600; font-size: 14px; }

        .container { max-width: 1100px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); margin-bottom: 25px; border: 1px solid #e2e8f0; }
        
        h2 { font-size: 18px; margin-bottom: 20px; color: #1e293b; }
        .form-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 15px; }
        
        input, select { padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; outline: none; transition: 0.3s; }
        input:focus { border-color: var(--primary); }
        
        .btn-submit { background: var(--primary); color: white; border: none; font-weight: 700; cursor: pointer; border-radius: 10px; }
        .btn-submit:hover { background: #3730a3; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #64748b; font-size: 12px; border-bottom: 2px solid #f1f5f9; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        
        .badge-plat { background: #1e293b; color: white; padding: 4px 10px; border-radius: 6px; font-family: monospace; font-size: 15px; }
        .btn-out { background: var(--danger); color: white; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 700; }
        
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; }
        .alert-error { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>

    <div class="nav">
        <div style="font-weight: 800; color: var(--primary);">🅿️ SMART PARKING</div>
        <div>
            <a href="petugas_dashboard.php">Dashboard</a>
            <a href="logout.php" style="margin-left: 20px; color: var(--danger);">Keluar</a>
        </div>
    </div>

    <div class="container">
        <?php if(isset($error)): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Input Kendaraan Masuk</h2>
            <form method="POST" class="form-grid">
                <input type="text" name="no_plat" placeholder="Nomor Plat (Contoh: D 1234 ABC)" required onkeyup="this.value = this.value.toUpperCase()">
                <select name="id_tarif" required>
                    <option value="">Pilih Jenis</option>
                    <?php while($t = mysqli_fetch_assoc($tarif_list)): ?>
                        <option value="<?= $t['id_tarif'] ?>"><?= $t['jenis_kendaraan'] ?></option>
                    <?php endwhile; ?>
                </select>
                <select name="id_area" required>
                    <option value="">Pilih Area</option>
                    <?php while($a = mysqli_fetch_assoc($area_list)): ?>
                        <option value="<?= $a['id_area'] ?>"><?= $a['nama_area'] ?> (Sisa: <?= $a['kapasitas'] - $a['terisi'] ?>)</option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="masuk" class="btn-submit">Catat Masuk</button>
            </form>
        </div>

        <div class="card">
            <h2>Kendaraan di Dalam Area (Belum Keluar)</h2>
            <table>
                <thead>
                    <tr>
                        <th>WAKTU MASUK</th>
                        <th>NOMOR PLAT</th>
                        <th>JENIS</th>
                        <th>AREA</th>
                        <th style="text-align: right;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($parkir_aktif) > 0): ?>
                        <?php while($p = mysqli_fetch_assoc($parkir_aktif)): ?>
                        <tr>
                            <td style="color: #64748b;"><?= date('H:i:s', strtotime($p['waktu_masuk'])) ?> <br> <small><?= date('d M Y', strtotime($p['waktu_masuk'])) ?></small></td>
                            <td><span class="badge-plat"><?= $p['no_plat'] ?></span></td>
                            <td><strong><?= $p['jenis_kendaraan'] ?></strong></td>
                            <td><?= $p['nama_area'] ?></td>
                            <td style="text-align: right;">
                                <a href="?keluar=<?= $p['id_parkir'] ?>" class="btn-out" onclick="return confirm('Konfirmasi Kendaraan Keluar?')">PROSES KELUAR</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 30px; color: #94a3b8;">Tidak ada kendaraan aktif.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>