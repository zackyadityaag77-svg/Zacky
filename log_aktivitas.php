<?php
session_start();
include 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("location: index.php");
    exit();
}

// Logika Bersihkan Log (Opsional - Hanya jika log sudah terlalu penuh)
if (isset($_GET['clear'])) {
    mysqli_query($conn, "TRUNCATE TABLE tb_log");
    header("location: log_aktivitas.php");
}

// Ambil Data Log
$logs = mysqli_query($conn, "SELECT * FROM tb_log ORDER BY id_log DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Aktivitas | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --dark: #0f172a; --bg: #f8fafc; --white: #ffffff; --gray: #94a3b8; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; display: flex; }

        /* Sidebar Konsisten */
        .sidebar { width: 280px; height: 100vh; background: var(--dark); color: white; position: fixed; }
        .sidebar-brand { padding: 35px 30px; font-size: 22px; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-menu { list-style: none; padding: 25px 0; margin: 0; }
        .sidebar-menu a { padding: 15px 30px; display: block; color: #94a3b8; text-decoration: none; transition: 0.3s; font-size: 14px; }
        .sidebar-menu a:hover, .sidebar-menu a.active { color: white; background: rgba(255,255,255,0.05); border-left: 4px solid var(--primary); }

        /* Content Area */
        .main { margin-left: 280px; width: 100%; padding: 40px; box-sizing: border-box; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .btn-clear { background: #fee2e2; color: #ef4444; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 13px; }

        /* Card & Timeline Style */
        .card { background: var(--white); border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        
        .timeline { position: relative; padding-left: 40px; }
        .timeline::before { content: ''; position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: #f1f5f9; }

        .log-item { position: relative; margin-bottom: 30px; }
        .log-item::before { 
            content: ''; position: absolute; left: -31px; top: 5px; 
            width: 12px; height: 12px; border-radius: 50%; 
            background: var(--primary); border: 4px solid white; box-shadow: 0 0 0 2px #f1f5f9;
        }

        .log-time { font-size: 11px; color: var(--gray); font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 5px; }
        .log-content { background: #f8fafc; padding: 15px 20px; border-radius: 15px; border: 1px solid #f1f5f9; }
        .log-user { font-weight: 800; color: var(--dark); font-size: 14px; }
        .log-msg { font-size: 14px; color: #475569; margin-top: 5px; display: block; }

        .empty-log { text-align: center; padding: 50px; color: var(--gray); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">🅿️ PARKIR PRO</div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php">🏠 Dashboard</a>
            <a href="manage_user.php">👥 Manage User</a>
            <a href="manage_tarif.php">💰 Tarif Parkir</a>
            <a href="manage_area.php">📍 Area Parkir</a>
            <a href="manage_kendaraan.php">🚗 Data Kendaraan</a>
            <a href="log_aktivitas.php" class="active">📜 Log Aktivitas</a>
            <a href="logout.php" style="color: #ef4444; margin-top: 50px;">🔴 Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <h1 style="margin:0; font-weight: 800;">Log Aktivitas</h1>
                <p style="color: var(--gray); margin-top: 5px;">Riwayat lengkap tindakan seluruh pengguna sistem.</p>
            </div>
            <a href="?clear=true" class="btn-clear" onclick="return confirm('Hapus semua riwayat log?')">Bersihkan Riwayat</a>
        </div>

        <div class="card">
            <?php if(mysqli_num_rows($logs) > 0): ?>
                <div class="timeline">
                    <?php while($l = mysqli_fetch_assoc($logs)): ?>
                    <div class="log-item">
                        <span class="log-time"><?= date('d M Y | H:i:s', strtotime($l['waktu'])) ?></span>
                        <div class="log-content">
                            <span class="log-user"><?= $l['nama_user'] ?></span>
                            <span class="log-msg"><?= $l['aktivitas'] ?></span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-log">
                    <div style="font-size: 40px; margin-bottom: 10px;">📄</div>
                    <p>Belum ada rekaman aktivitas yang tercatat.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>