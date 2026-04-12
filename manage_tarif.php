<?php
session_start();
include 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("location: index.php");
    exit();
}

// --- LOGIKA TAMBAH TARIF ---
if (isset($_POST['add_tarif'])) {
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
    $harga_dasar = $_POST['harga_dasar'];
    $biaya_perjam = $_POST['biaya_perjam'];

    $q = mysqli_query($conn, "INSERT INTO tb_tarif (jenis_kendaraan, harga_dasar, biaya_perjam) VALUES ('$jenis', '$harga_dasar', '$biaya_perjam')");
    if ($q) header("location: manage_tarif.php?status=success");
}

// --- LOGIKA EDIT TARIF ---
if (isset($_POST['edit_tarif'])) {
    $id = $_POST['id_tarif'];
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis_kendaraan']);
    $harga_dasar = $_POST['harga_dasar'];
    $biaya_perjam = $_POST['biaya_perjam'];

    $q = mysqli_query($conn, "UPDATE tb_tarif SET jenis_kendaraan='$jenis', harga_dasar='$harga_dasar', biaya_perjam='$biaya_perjam' WHERE id_tarif='$id'");
    if ($q) header("location: manage_tarif.php?status=updated");
}

// --- LOGIKA HAPUS TARIF ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tb_tarif WHERE id_tarif='$id'");
    header("location: manage_tarif.php");
}

$tarif = mysqli_query($conn, "SELECT * FROM tb_tarif ORDER BY id_tarif ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Tarif | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --dark: #0f172a; --bg: #f8fafc; --white: #ffffff; --gray: #94a3b8; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; display: flex; }

        /* Sidebar (Konsisten dengan Dashboard) */
        .sidebar { width: 280px; height: 100vh; background: var(--dark); color: white; position: fixed; }
        .sidebar-brand { padding: 35px 30px; font-size: 22px; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-menu { list-style: none; padding: 25px 0; margin: 0; }
        .sidebar-menu a { padding: 15px 30px; display: block; color: #94a3b8; text-decoration: none; transition: 0.3s; font-size: 14px; }
        .sidebar-menu a:hover, .sidebar-menu a.active { color: white; background: rgba(255,255,255,0.05); border-left: 4px solid var(--primary); }

        /* Content */
        .main { margin-left: 280px; width: 100%; padding: 40px; box-sizing: border-box; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-add { background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 14px; cursor: pointer; border: none; }

        /* Card & Table */
        .card { background: var(--white); border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: var(--gray); font-size: 12px; border-bottom: 2px solid #f1f5f9; text-transform: uppercase; letter-spacing: 1px; }
        td { padding: 20px 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        
        .price-tag { font-weight: 700; color: var(--dark); }
        .btn-edit { color: var(--primary); font-weight: 700; cursor: pointer; text-decoration: none; margin-right: 15px; }
        .btn-del { color: #ef4444; font-weight: 700; text-decoration: none; }

        /* MODAL STYLE */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(5px); }
        .modal-content { background: white; width: 450px; margin: 8% auto; padding: 35px; border-radius: 28px; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .modal-content h2 { margin-top: 0; font-weight: 800; font-size: 24px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 8px; color: var(--dark); }
        .form-group input { width: 100%; padding: 14px; border: 1px solid #e2e8f0; border-radius: 12px; box-sizing: border-box; font-size: 15px; outline: none; }
        .form-group input:focus { border-color: var(--primary); box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1); }
        .close-modal { position: absolute; right: 25px; top: 25px; font-size: 24px; cursor: pointer; color: var(--gray); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">🅿️ PARKIR PRO</div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php">🏠 Dashboard</a>
            <a href="manage_user.php">👥 Manage User</a>
            <a href="manage_tarif.php" class="active">💰 Tarif Parkir</a>
            <a href="manage_area.php">📍 Area Parkir</a>
            <a href="manage_kendaraan.php">🚗 Data Kendaraan</a>
            <a href="logout.php" style="color: #ef4444; margin-top: 50px;">🔴 Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <div>
                <h1 style="margin:0; font-weight: 800;">Pengaturan Tarif</h1>
                <p style="color: var(--gray); margin-top: 5px;">Kelola biaya parkir per jam untuk setiap jenis kendaraan.</p>
            </div>
            <button class="btn-add" onclick="openModal('modalAdd')">+ Tambah Tarif</button>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>JENIS KENDARAAN</th>
                        <th>HARGA DASAR (1 JAM)</th>
                        <th>BIAYA PER JAM BERIKUTNYA</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($tarif)): ?>
                    <tr>
                        <td><strong><?= $row['jenis_kendaraan'] ?></strong></td>
                        <td class="price-tag">Rp <?= number_format($row['harga_dasar'], 0, ',', '.') ?></td>
                        <td class="price-tag">Rp <?= number_format($row['biaya_perjam'], 0, ',', '.') ?></td>
                        <td>
                            <span class="btn-edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</span>
                            <a href="?delete=<?= $row['id_tarif'] ?>" class="btn-del" onclick="return confirm('Hapus tarif ini?')">Hapus</a>
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
            <h2>Tambah Tarif</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Jenis Kendaraan</label>
                    <input type="text" name="jenis_kendaraan" placeholder="Misal: Mobil, Motor, Bus" required>
                </div>
                <div class="form-group">
                    <label>Harga Dasar (Jam Pertama)</label>
                    <input type="number" name="harga_dasar" placeholder="5000" required>
                </div>
                <div class="form-group">
                    <label>Biaya Per Jam Berikutnya</label>
                    <input type="number" name="biaya_perjam" placeholder="2000" required>
                </div>
                <button type="submit" name="add_tarif" class="btn-add" style="width:100%; padding: 16px;">Simpan Tarif</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('modalEdit')">&times;</span>
            <h2>Edit Tarif</h2>
            <form method="POST">
                <input type="hidden" name="id_tarif" id="edit_id">
                <div class="form-group">
                    <label>Jenis Kendaraan</label>
                    <input type="text" name="jenis_kendaraan" id="edit_jenis" required>
                </div>
                <div class="form-group">
                    <label>Harga Dasar (Jam Pertama)</label>
                    <input type="number" name="harga_dasar" id="edit_harga" required>
                </div>
                <div class="form-group">
                    <label>Biaya Per Jam Berikutnya</label>
                    <input type="number" name="biaya_perjam" id="edit_perjam" required>
                </div>
                <button type="submit" name="edit_tarif" class="btn-add" style="width:100%; padding: 16px;">Update Tarif</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'block'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id_tarif;
            document.getElementById('edit_jenis').value = data.jenis_kendaraan;
            document.getElementById('edit_harga').value = data.harga_dasar;
            document.getElementById('edit_perjam').value = data.biaya_perjam;
            openModal('modalEdit');
        }

        // Close modal if user clicks outside of it
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>