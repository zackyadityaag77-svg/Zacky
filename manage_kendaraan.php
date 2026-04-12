<?php
session_start();
include 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("location: index.php");
    exit();
}

// --- LOGIKA HAPUS DATA (Optional untuk Admin) ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tb_transaksi WHERE id_parkir='$id'");
    header("location: manage_kendaraan.php");
}

// --- LOGIKA PENCARIAN ---
$where = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where = "WHERE t.no_plat LIKE '%$search%' OR t.kode_parkir LIKE '%$search%'";
}

// Ambil Data Gabungan
$query = "SELECT t.*, tr.jenis_kendaraan, a.nama_area, u.nama_lengkap 
          FROM tb_transaksi t
          LEFT JOIN tb_tarif tr ON t.id_tarif = tr.id_tarif
          LEFT JOIN tb_area a ON t.id_area = a.id_area
          LEFT JOIN tb_user u ON t.id_user = u.id_user
          $where
          ORDER BY t.id_parkir DESC";
$data_kendaraan = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kendaraan | Admin Panel</title>
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
        
        .search-box { position: relative; }
        .search-box input { padding: 12px 20px; border-radius: 12px; border: 1px solid #e2e8f0; width: 300px; outline: none; transition: 0.3s; }
        .search-box input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.05); }

        /* Card & Table */
        .card { background: var(--white); border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { text-align: left; padding: 15px; color: var(--gray); font-size: 11px; border-bottom: 2px solid #f1f5f9; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 18px 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        
        .badge-status { padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-masuk { background: #dcfce7; color: #10b981; }
        .status-selesai { background: #f1f5f9; color: #64748b; }

        .plat-box { background: #1e293b; color: white; padding: 4px 10px; border-radius: 6px; font-family: 'Courier New', monospace; font-weight: 700; }
        .btn-del { color: #ef4444; font-weight: 700; text-decoration: none; font-size: 12px; }
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
            <a href="manage_kendaraan.php" class="active">🚗 Data Kendaraan</a>
            <a href="logout.php" style="color: #ef4444; margin-top: 50px;">🔴 Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <h1 style="margin:0; font-weight: 800;">Log Kendaraan</h1>
                <p style="color: var(--gray); margin-top: 5px;">Data lengkap seluruh kendaraan yang terdaftar di sistem.</p>
            </div>
            <form action="" method="GET" class="search-box">
                <input type="text" name="search" placeholder="Cari Plat Nomor..." value="<?= @$_GET['search'] ?>">
            </form>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>KODE/PLAT</th>
                        <th>JENIS</th>
                        <th>AREA</th>
                        <th>WAKTU MASUK</th>
                        <th>WAKTU KELUAR</th>
                        <th>BIAYA</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($data_kendaraan) > 0): ?>
                        <?php while($r = mysqli_fetch_assoc($data_kendaraan)): ?>
                        <tr>
                            <td>
                                <span style="font-size: 10px; color: var(--gray); display: block;"><?= $r['kode_parkir'] ?></span>
                                <span class="plat-box"><?= $r['no_plat'] ?></span>
                            </td>
                            <td><strong><?= $r['jenis_kendaraan'] ?></strong></td>
                            <td><?= $r['nama_area'] ?></td>
                            <td style="font-size: 12px;">
                                <?= date('d/m/Y', strtotime($r['waktu_masuk'])) ?><br>
                                <span style="color: var(--gray);"><?= date('H:i', strtotime($r['waktu_masuk'])) ?></span>
                            </td>
                            <td style="font-size: 12px;">
                                <?= ($r['waktu_keluar']) ? date('d/m/Y', strtotime($r['waktu_keluar'])) : '-' ?><br>
                                <span style="color: var(--gray);"><?= ($r['waktu_keluar']) ? date('H:i', strtotime($r['waktu_keluar'])) : '' ?></span>
                            </td>
                            <td style="font-weight: 700; color: var(--primary);">
                                <?= ($r['biaya_total']) ? 'Rp '.number_format($r['biaya_total'], 0, ',', '.') : '-' ?>
                            </td>
                            <td>
                                <span class="badge-status <?= ($r['status'] == 'Masuk') ? 'status-masuk' : 'status-selesai' ?>">
                                    <?= $r['status'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="?delete=<?= $r['id_parkir'] ?>" class="btn-del" onclick="return confirm('Hapus record permanen?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 50px; color: var(--gray);">Data tidak ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>