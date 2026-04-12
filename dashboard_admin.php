<?php
session_start();
include 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("location: index.php");
    exit();
}

// 1. Ambil Statistik Ringkas
$res_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_user"));
$res_tarif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM tb_tarif"));
$res_area = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(terisi) as t, SUM(kapasitas) as k FROM tb_area"));

// 2. Ambil 5 Log Aktivitas Terakhir
$logs = mysqli_query($conn, "SELECT * FROM tb_log ORDER BY id_log DESC LIMIT 5");

// 3. Hitung Persentase Okupansi Lahan
$persen_parkir = ($res_area['k'] > 0) ? ($res_area['t'] / $res_area['k']) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Smart Parking Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --dark: #0f172a;
            --bg: #f8fafc;
            --white: #ffffff;
            --gray: #94a3b8;
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--dark);
            display: flex;
        }

        /* SIDEBAR NAVIGASI */
        .sidebar {
            width: 280px;
            height: 100vh;
            background: var(--dark);
            color: white;
            position: fixed;
            transition: all 0.3s;
        }

        .sidebar-brand {
            padding: 35px 30px;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--white);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-menu { list-style: none; padding: 25px 0; margin: 0; }
        .sidebar-menu li a {
            padding: 15px 30px;
            display: flex;
            align-items: center;
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            color: white;
            background: rgba(255,255,255,0.05);
            border-left: 4px solid var(--primary);
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            padding: 40px;
            box-sizing: border-box;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .header h1 { font-size: 28px; font-weight: 800; margin: 0; }

        /* KARTU STATISTIK */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
        }

        .stat-card h3 { font-size: 13px; color: var(--gray); text-transform: uppercase; margin: 0; letter-spacing: 1px; }
        .stat-card .value { font-size: 32px; font-weight: 800; margin: 15px 0 5px 0; }
        
        /* PROGRESS BAR PARKIR */
        .progress-container {
            width: 100%; height: 8px; background: #f1f5f9; border-radius: 10px; margin: 15px 0; overflow: hidden;
        }
        .progress-bar {
            height: 100%; background: var(--primary); border-radius: 10px; transition: 0.5s;
        }

        /* DUA KOLOM BAWAH */
        .bottom-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        .card {
            background: var(--white);
            padding: 30px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            border: 1px solid #f1f5f9;
        }

        .log-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f8fafc;
        }
        .log-item:last-child { border-bottom: none; }
        .log-icon { width: 40px; height: 40px; background: #e0e7ff; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px; font-size: 18px; }
        .log-info h4 { margin: 0; font-size: 14px; font-weight: 700; }
        .log-info p { margin: 2px 0 0; font-size: 12px; color: var(--gray); }

        .btn-logout {
            color: #ef4444 !important;
            margin-top: 50px;
            font-weight: 700 !important;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">🅿️ PARKIR PRO</div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php" class="active">🏠 Dashboard</a></li>
            <li><a href="manage_user.php">👥 Manage User</a></li>
            <li><a href="manage_tarif.php">💰 Tarif Parkir</a></li>
            <li><a href="manage_area.php">📍 Area Parkir</a></li>
            <li><a href="manage_kendaraan.php">🚗 Data Kendaraan</a></li>
            <li><a href="log_aktivitas.php">📜 Log Aktivitas</a></li>
            <li><a href="logout.php" class="btn-logout">🔴 Keluar</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <div>
                <h1>Ringkasan Sistem</h1>
                <p style="color: var(--gray); margin-top: 5px;">Selamat datang kembali, Admin.</p>
            </div>
            <div style="background: white; padding: 10px 20px; border-radius: 15px; font-weight: 600; border: 1px solid #e2e8f0;">
                📅 <?= date('d M Y'); ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Pengguna</h3>
                <div class="value"><?= $res_user['total']; ?></div>
                <p style="margin:0; font-size: 12px; color: var(--gray);">Admin, Petugas, Owner</p>
            </div>
            <div class="stat-card">
                <h3>Kapasitas Lahan</h3>
                <div class="value"><?= $res_area['t'] ?? 0; ?> <span style="font-size: 16px; color: var(--gray);">/ <?= $res_area['k'] ?? 0; ?></span></div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: <?= $persen_parkir; ?>%;"></div>
                </div>
                <p style="margin:0; font-size: 12px; color: var(--gray);">Okupansi: <?= round($persen_parkir); ?>% Terisi</p>
            </div>
            <div class="stat-card">
                <h3>Jenis Tarif</h3>
                <div class="value"><?= $res_tarif['total']; ?></div>
                <p style="margin:0; font-size: 12px; color: var(--gray);">Motor, Mobil, dll.</p>
            </div>
        </div>

        <div class="bottom-grid">
            <div class="card">
                <h2 style="font-size: 18px; margin-top: 0;">Log Aktivitas Terakhir</h2>
                <?php while($l = mysqli_fetch_assoc($logs)): ?>
                <div class="log-item">
                    <div class="log-icon">📝</div>
                    <div class="log-info">
                        <h4><?= $l['nama_user']; ?></h4>
                        <p><?= $l['aktivitas']; ?> &bull; <span style="font-size: 11px;">#<?= $l['id_log']; ?></span></p>
                    </div>
                </div>
                <?php endwhile; ?>
                <a href="log_aktivitas.php" style="display: block; text-align: center; margin-top: 20px; font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 600;">Lihat Semua Log →</a>
            </div>

            <div class="card" style="background: var(--primary); color: white; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
                <div style="font-size: 40px; margin-bottom: 15px;">🚀</div>
                <h3 style="margin: 0; font-size: 20px;">Sistem Optimal</h3>
                <p style="opacity: 0.8; font-size: 13px; margin-top: 10px;">Semua layanan berjalan dengan lancar tanpa kendala teknis.</p>
            </div>
        </div>
    </div>

</body>
</html>