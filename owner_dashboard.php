<?php
session_start();
include 'koneksi.php';

// 1. Proteksi Owner: Hanya role Owner yang bisa masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Owner') {
    header("location: index.php");
    exit();
}

// 2. Ambil Statistik Pendapatan (Menggunakan kolom biaya_total sesuai DB kamu)
$today = date('Y-m-d');

// Total Seluruh Pendapatan
$query_total = mysqli_query($conn, "SELECT SUM(biaya_total) as grand FROM tb_transaksi WHERE status='Selesai'");
$res_total = ($query_total) ? mysqli_fetch_assoc($query_total) : ['grand' => 0];

// Pendapatan Hari Ini
$query_today = mysqli_query($conn, "SELECT SUM(biaya_total) as daily FROM tb_transaksi WHERE DATE(waktu_keluar) = '$today' AND status='Selesai'");
$res_today = ($query_today) ? mysqli_fetch_assoc($query_today) : ['daily' => 0];

// Jumlah Transaksi Selesai
$query_count = mysqli_query($conn, "SELECT COUNT(*) as jml FROM tb_transaksi WHERE status='Selesai'");
$res_count = ($query_count) ? mysqli_fetch_assoc($query_count) : ['jml' => 0];

// 3. Ambil Data Rekap Transaksi untuk Tabel
$rekap = mysqli_query($conn, "SELECT t.*, tr.jenis_kendaraan, u.nama_lengkap as petugas 
                              FROM tb_transaksi t 
                              LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif 
                              LEFT JOIN tb_user u ON t.id_user = u.id_user 
                              WHERE t.status = 'Selesai' 
                              ORDER BY t.waktu_keluar DESC LIMIT 20");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard | Smart Parking System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --primary: #7209b7; 
            --dark: #1e1e2d;
            --white: #ffffff;
            --gray: #94a3b8;
            --success: #10b981;
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--dark);
        }

        /* Navigasi Atas */
        .top-header {
            background: var(--white);
            padding: 15px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo { font-weight: 800; font-size: 22px; color: var(--primary); letter-spacing: -1px; }
        
        .nav-actions a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 600;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 12px;
            transition: 0.3s;
        }

        .logout-btn { background: #fee2e2; color: #ef4444 !important; margin-left: 10px; }
        .logout-btn:hover { background: #fecaca; }

        /* Container Content */
        .main-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .welcome-header { margin-bottom: 40px; }
        .welcome-header h1 { font-size: 32px; margin: 0; letter-spacing: -1px; }
        .welcome-header p { color: var(--gray); margin-top: 5px; }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            padding: 30px;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            position: relative;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover { transform: translateY(-5px); }

        .stat-card::after {
            content: ''; position: absolute; left: 0; top: 25%; height: 50%; width: 5px; 
            background: var(--primary); border-radius: 0 5px 5px 0;
        }

        .stat-card h3 { font-size: 13px; color: var(--gray); text-transform: uppercase; margin: 0; letter-spacing: 1px; }
        .stat-card .amount { font-size: 32px; font-weight: 800; margin: 15px 0 5px 0; color: var(--dark); }
        .stat-card .desc { font-size: 12px; color: var(--success); font-weight: 600; }

        /* Tabel Laporan */
        .report-card {
            background: var(--white);
            padding: 30px;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }

        .report-card h2 { font-size: 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }

        .print-btn {
            background: var(--dark);
            color: white;
            padding: 8px 18px;
            border-radius: 10px;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
        }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: var(--gray); font-size: 12px; border-bottom: 2px solid #f1f5f9; }
        td { padding: 18px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }

        .plate-no { font-weight: 700; color: var(--dark); }
        .badge-petugas { background: #f1f5f9; padding: 5px 10px; border-radius: 8px; font-size: 12px; font-weight: 600; }
        .total-col { font-weight: 800; color: var(--primary); }

        /* Media Print */
        @media print {
            .top-header, .print-btn { display: none; }
            .main-container { margin: 0; padding: 0; }
            .stat-card { border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

    <header class="top-header">
        <div class="logo">OWNER.INSIGHT</div>
        <div class="nav-actions">
            <span>Role: <strong>Owner</strong></span>
            <a href="logout.php" class="logout-btn">Keluar</a>
        </div>
    </header>

    <div class="main-container">
        <div class="welcome-header">
            <p>Ringkasan Eksekutif</p>
            <h1>Performa Pendapatan</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Pendapatan Hari Ini</h3>
                <div class="amount">Rp <?= number_format($res_today['daily'] ?? 0, 0, ',', '.') ?></div>
                <div class="desc">● Update Terkini</div>
            </div>
            <div class="stat-card" style="border-left: 1px solid #e2e8f0;">
                <h3 style="color: var(--secondary);">Total Akumulasi</h3>
                <div class="amount">Rp <?= number_format($res_total['grand'] ?? 0, 0, ',', '.') ?></div>
                <div class="desc" style="color: var(--primary);">Dari <?= $res_count['jml'] ?> Kendaraan</div>
            </div>
        </div>

        <div class="report-card">
            <h2>
                Riwayat Transaksi Terakhir
                <button class="print-btn" onclick="window.print()">Cetak Laporan</button>
            </h2>
            
            <table>
                <thead>
                    <tr>
                        <th>NO. PLAT</th>
                        <th>KENDARAAN</th>
                        <th>WAKTU KELUAR</th>
                        <th>PETUGAS</th>
                        <th>PENDAPATAN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($rekap) > 0): ?>
                        <?php while($r = mysqli_fetch_assoc($rekap)): ?>
                        <tr>
                            <td class="plate-no"><?= strtoupper($r['no_plat']) ?></td>
                            <td><?= $r['jenis_kendaraan'] ?? '-' ?></td>
                            <td style="color: var(--gray);"><?= date('d M Y, H:i', strtotime($r['waktu_keluar'])) ?></td>
                            <td><span class="badge-petugas"><?= $r['petugas'] ?? 'Sistem' ?></span></td>
                            <td class="total-col">Rp <?= number_format($r['biaya_total'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--gray);">Belum ada data transaksi yang selesai.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: center; margin-top: 50px; color: var(--gray); font-size: 12px;">
            Dibuat secara otomatis oleh Smart Parking System &copy; 2026
        </div>
    </div>

</body>
</html>