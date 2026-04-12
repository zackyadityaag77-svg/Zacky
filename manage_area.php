<?php
session_start();
include 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("location: index.php");
    exit();
}

// --- LOGIKA TAMBAH AREA ---
if (isset($_POST['add_area'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_area']);
    $kapasitas = $_POST['kapasitas'];

    $q = mysqli_query($conn, "INSERT INTO tb_area (nama_area, kapasitas, terisi) VALUES ('$nama', '$kapasitas', 0)");
    if ($q) header("location: manage_area.php?status=success");
}

// --- LOGIKA EDIT AREA ---
if (isset($_POST['edit_area'])) {
    $id = $_POST['id_area'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_area']);
    $kapasitas = $_POST['kapasitas'];

    // Update nama dan kapasitas (terisi tetap mengikuti kondisi riil)
    $q = mysqli_query($conn, "UPDATE tb_area SET nama_area='$nama', kapasitas='$kapasitas' WHERE id_area='$id'");
    if ($q) header("location: manage_area.php?status=updated");
}

// --- LOGIKA HAPUS AREA ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tb_area WHERE id_area='$id'");
    header("location: manage_area.php");
}

$areas = mysqli_query($conn, "SELECT * FROM tb_area ORDER BY id_area ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Area Parkir | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --dark: #0f172a; --bg: #f8fafc; --white: #ffffff; --gray: #94a3b8; --success: #10b981; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; display: flex; }

        /* Sidebar Konsisten */
        .sidebar { width: 280px; height: 100vh; background: var(--dark); color: white; position: fixed; }
        .sidebar-brand { padding: 35px 30px; font-size: 22px; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-menu { list-style: none; padding: 25px 0; margin: 0; }
        .sidebar-menu a { padding: 15px 30px; display: block; color: #94a3b8; text-decoration: none; transition: 0.3s; font-size: 14px; }
        .sidebar-menu a:hover, .sidebar-menu a.active { color: white; background: rgba(255,255,255,0.05); border-left: 4px solid var(--primary); }

        /* Content */
        .main { margin-left: 280px; width: 100%; padding: 40px; box-sizing: border-box; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-add { background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 14px; cursor: pointer; border: none; }

        /* Card & Area Grid */
        .card { background: var(--white); border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: var(--gray); font-size: 12px; border-bottom: 2px solid #f1f5f9; text-transform: uppercase; }
        td { padding: 20px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        
        /* Capacity Indicator */
        .cap-pill { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; background: #f1f5f9; color: var(--dark); }
        .progress-mini { width: 100px; height: 6px; background: #f1f5f9; border-radius: 10px; margin-top: 8px; overflow: hidden; }
        .progress-bar { height: 100%; background: var(--primary); border-radius: 10px; }

        .btn-edit { color: var(--primary); font-weight: 700; cursor: pointer; text-decoration: none; margin-right: 15px; }
        .btn-del { color: #ef4444; font-weight: 700; text-decoration: none; }

        /* MODAL STYLE */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(5px); }
        .modal-content { background: white; width: 450px; margin: 10% auto; padding: 35px; border-radius: 28px; position: relative; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 8px; }
        .form-group input { width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 12px; box-sizing: border-box; outline: none; }
        .close-modal { position: absolute; right: 25px; top: 25px; font-size: 24px; cursor: pointer; color: var(--gray); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">🅿️ PARKIR PRO</div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php">🏠 Dashboard</a>
            <a href="manage_user.php">👥 Manage User</a>
            <a href="manage_tarif.php">💰 Tarif Parkir</a>
            <a href="manage_area.php" class="active">📍 Area Parkir</a>
            <a href="manage_kendaraan.php">🚗 Data Kendaraan</a>
            <a href="logout.php" style="color: #ef4444; margin-top: 50px;">🔴 Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <h1 style="margin:0; font-weight: 800;">Manajemen Area</h1>
                <p style="color: var(--gray); margin-top: 5px;">Kelola zonasi lokasi parkir dan kapasitas maksimal kendaraan.</p>
            </div>
            <button class="btn-add" onclick="openModal('modalAdd')">+ Tambah Area</button>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>NAMA AREA</th>
                        <th>KAPASITAS TOTAL</th>
                        <th>STATUS TERISI</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($areas)): 
                        $persen = ($row['kapasitas'] > 0) ? ($row['terisi'] / $row['kapasitas']) * 100 : 0;
                        $warna = ($persen > 80) ? '#ef4444' : '#4361ee';
                    ?>
                    <tr>
                        <td><strong><?= $row['nama_area'] ?></strong></td>
                        <td><span class="cap-pill"><?= $row['kapasitas'] ?> Slot</span></td>
                        <td>
                            <div style="font-weight: 700; font-size: 13px;"><?= $row['terisi'] ?> / <?= $row['kapasitas'] ?> Kendaraan</div>
                            <div class="progress-mini">
                                <div class="progress-bar" style="width: <?= $persen ?>%; background: <?= $warna ?>;"></div>
                            </div>
                        </td>
                        <td>
                            <span class="btn-edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</span>
                            <a href="?delete=<?= $row['id_area'] ?>" class="btn-del" onclick="return confirm('Hapus area ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalAdd" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('modalAdd')">&times;</span>
            <h2 style="margin-top:0;">Tambah Lokasi</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Area / Lokasi</label>
                    <input type="text" name="nama_area" placeholder="Misal: Area A-1 atau Basement" required>
                </div>
                <div class="form-group">
                    <label>Total Kapasitas (Slot)</label>
                    <input type="number" name="kapasitas" placeholder="Contoh: 50" required>
                </div>
                <button type="submit" name="add_area" class="btn-add" style="width:100%; padding: 16px;">Simpan Area</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('modalEdit')">&times;</span>
            <h2 style="margin-top:0;">Edit Lokasi</h2>
            <form method="POST">
                <input type="hidden" name="id_area" id="edit_id">
                <div class="form-group">
                    <label>Nama Area / Lokasi</label>
                    <input type="text" name="nama_area" id="edit_nama" required>
                </div>
                <div class="form-group">
                    <label>Total Kapasitas (Slot)</label>
                    <input type="number" name="kapasitas" id="edit_kapasitas" required>
                </div>
                <button type="submit" name="edit_area" class="btn-add" style="width:100%; padding: 16px;">Update Area</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'block'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id_area;
            document.getElementById('edit_nama').value = data.nama_area;
            document.getElementById('edit_kapasitas').value = data.kapasitas;
            openModal('modalEdit');
        }

        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>