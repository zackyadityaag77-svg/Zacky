<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Petugas') {
    header("location: index.php");
    exit();
}

// Statistik Cepat
$today = date('Y-m-d');
$res_masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_transaksi WHERE DATE(waktu_masuk) = '$today'"));
$res_area  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(terisi) as t, SUM(kapasitas) as k FROM tb_area"));
$kapasitas_persen = ($res_area['k'] > 0) ? ($res_area['t'] / $res_area['k']) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petugas Center | Smart Parking</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --primary: #4361ee;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--dark);
        }

        /* Top Navigation - Ganti Sidebar */
        .top-nav {
            background: white;
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        .logout-link {
            color: var(--danger);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        /* Container Utama */
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome-section {
            margin-bottom: 40px;
        }

        .welcome-section h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: -0.5px;
        }

        /* Grid Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .icon-box {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
        }

        /* Action Center - Menu Utama */
        .action-center {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .action-card {
            background: white;
            padding: 40px 30px;
            border-radius: 32px;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid transparent;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
        }

        .action-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary);
            box-shadow: 0 20px 40px rgba(67, 97, 238, 0.1);
        }

        .action-card .icon {
            font-size: 50px;
            margin-bottom: 20px;
            display: block;
        }

        .action-card h2 {
            margin: 0 0 10px 0;
            font-size: 20px;
        }

        .action-card p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Progress Bar */
        .progress-container {
            width: 100%;
            height: 8px;
            background: #f1f5f9;
            border-radius: 10px;
            margin-top: 15px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: var(--primary);
            border-radius: 10px;
        }

        .tag {
            font-size: 12px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <nav class="top-nav">
        <div style="font-weight: 800; font-size: 20px; color: var(--primary);">🅿️ PARKING.PRO</div>
        <div class="user-profile">
            <div class="user-info" style="text-align: right;">
                <div style="font-weight: 700; font-size: 14px;"><?= $_SESSION['nama_lengkap'] ?></div>
                <div style="font-size: 12px; color: var(--success);">Petugas Aktif</div>
            </div>
            <div class="avatar"><?= substr($_SESSION['nama_lengkap'], 0, 1) ?></div>
            <div style="margin-left: 10px; border-left: 1px solid #e2e8f0; padding-left: 15px;">
                <a href="logout.php" class="logout-link">Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <p style="color: #64748b; margin-bottom: 5px;">Selamat bekerja!</p>
            <h1>Dashboard Kontrol Petugas</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon-box" style="background: #e0e7ff; color: #4361ee;">📊</div>
                <div>
                    <h3><?= $data_masuk['total'] ?? 0 ?> Unit</h3>
                    <p style="margin:0; font-size: 13px; color: #64748b;">Masuk Hari Ini</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="icon-box" style="background: #dcfce7; color: #10b981;">🚗</div>
                <div style="flex:1;">
                    <div style="display:flex; justify-content:space-between;">
                        <h3 style="margin:0;"><?= $res_area['t'] ?> / <?= $res_area['k'] ?></h3>
                        <span style="font-size: 13px; font-weight: 600;"><?= round($kapasitas_persen) ?>%</span>
                    </div>
                    <p style="margin:0; font-size: 13px; color: #64748b;">Slot Terisi</p>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= $kapasitas_persen ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-center">
            <a href="transaksi_parkir.php" class="action-card">
                <span class="icon">➕</span>
                <span class="tag" style="background: #e0e7ff; color: #4361ee; margin-bottom: 10px; display: inline-block;">Input Baru</span>
                <h2>Masuk Kendaraan</h2>
                <p>Catat nomor plat dan jenis kendaraan yang baru masuk area parkir.</p>
            </a>

            <a href="transaksi_parkir.php#daftar" class="action-card">
                <span class="icon">🧾</span>
                <span class="tag" style="background: #fef3c7; color: #d97706; margin-bottom: 10px; display: inline-block;">Proses Keluar</span>
                <h2>Selesaikan Parkir</h2>
                <p>Proses pembayaran, hitung durasi parkir, dan cetak struk untuk pelanggan.</p>
            </a>
        </div>

        <div style="text-align: center; margin-top: 60px; color: #94a3b8; font-size: 13px;">
            Sistem Parkir Digital &bull; 2026 &bull; Stable Version
        </div>
    </div>

</body>
</html>