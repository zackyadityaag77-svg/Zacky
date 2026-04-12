<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman: Hanya Admin yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("location: index.php");
    exit();
}

// Ambil data statistik sederhana dari database
$count_user = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_user"));
$count_area = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_area"));
$count_log  = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tb_log"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Smart Parking</title>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --dark: #2b2d42;
            --light: #f8f9fa;
            --sidebar-width: 260px;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            display: flex;
        }

        /* Sidebar Style */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--dark);
            color: white;
            position: fixed;
            transition: all 0.3s;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            background: rgba(0,0,0,0.2);
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }

        .sidebar-menu li a {
            padding: 15px 25px;
            display: block;
            color: #8d99ae;
            text-decoration: none;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            color: white;
            background: rgba(255,255,255,0.05);
            border-left: 4px solid var(--primary);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 40px;
            box-sizing: border-box;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .user-info {
            background: white;
            padding: 10px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            font-weight: 600;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
        }

        .card:hover { transform: translateY(-5px); }

        .card h3 { margin: 0; color: #8d99ae; font-size: 14px; text-transform: uppercase; }
        .card p { margin: 10px 0 0; font-size: 28px; font-weight: 700; color: var(--dark); }

        .btn-logout {
            background: #ff4d4f;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h2 style="margin:0; font-size: 20px;">🅿️ Admin Parkir</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" class="active">Dashboard</a></li>
            <li><a href="manage_user.php">CRUD User</a></li>
            <li><a href="manage_tarif.php">CRUD Tarif Parkir</a></li>
            <li><a href="manage_area.php">CRUD Area Parkir</a></li>
            <li><a href="manage_kendaraan.php">CRUD Kendaraan</a></li>
            <li><a href="log_aktivitas.php">Akses Log Aktivitas</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header-top">
            <h1>Overview Dashboard</h1>
            <div class="user-info">
                Halo, <?php echo $_SESSION['nama_lengkap']; ?> | 
                <a href="logout.php" class="btn-logout">Keluar</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="card">
                <h3>Total User</h3>
                <p><?php echo $count_user; ?></p>
            </div>
            <div class="card">
                <h3>Area Parkir</h3>
                <p><?php echo $count_area; ?></p>
            </div>
            <div class="card">
                <h3>Log Aktivitas</h3>
                <p><?php echo $count_log; ?></p>
            </div>
        </div>

        <div class="card">
            <h2>Selamat Datang di Panel Administrator</h2>
            <p style="color: #8d99ae; font-size: 16px; line-height: 1.6;">
                Gunakan menu di sebelah kiri untuk mengelola data master kendaraan, tarif, dan area parkir. 
                Anda juga memiliki wewenang penuh untuk memantau log aktivitas sistem.
            </p>
        </div>
    </div>

</body>
</html>