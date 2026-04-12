<?php
session_start();
include 'koneksi.php';

// Proteksi Admin
if ($_SESSION['role'] !== 'Admin') {
    header("location: index.php");
    exit();
}

// --- LOGIKA TAMBAH USER ---
if (isset($_POST['add_user'])) {
    $nama = $_POST['nama_lengkap'];
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $role = $_POST['role'];

    $q = mysqli_query($conn, "INSERT INTO tb_user (nama_lengkap, username, password, role) VALUES ('$nama', '$user', '$pass', '$role')");
    if ($q) header("location: manage_user.php?status=success");
}

// --- LOGIKA EDIT USER ---
if (isset($_POST['edit_user'])) {
    $id   = $_POST['id_user'];
    $nama = $_POST['nama_lengkap'];
    $user = $_POST['username'];
    $role = $_POST['role'];
    
    // Password hanya diupdate jika diisi
    $pass_query = "";
    if (!empty($_POST['password'])) {
        $pass = $_POST['password'];
        $pass_query = ", password='$pass'";
    }

    $q = mysqli_query($conn, "UPDATE tb_user SET nama_lengkap='$nama', username='$user', role='$role' $pass_query WHERE id_user='$id'");
    if ($q) header("location: manage_user.php?status=updated");
}

// --- LOGIKA HAPUS USER ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM tb_user WHERE id_user='$id'");
    header("location: manage_user.php");
}

$users = mysqli_query($conn, "SELECT * FROM tb_user ORDER BY id_user DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #4361ee; --dark: #0f172a; --bg: #f8fafc; --white: #ffffff; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); margin: 0; display: flex; }

        /* Sidebar Modern */
        .sidebar { width: 280px; height: 100vh; background: var(--dark); color: white; position: fixed; }
        .sidebar-brand { padding: 30px; font-size: 20px; font-weight: 800; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar-menu { list-style: none; padding: 20px 0; margin: 0; }
        .sidebar-menu a { padding: 15px 30px; display: block; color: #94a3b8; text-decoration: none; transition: 0.3s; font-size: 14px; }
        .sidebar-menu a:hover, .sidebar-menu a.active { color: white; background: rgba(255,255,255,0.05); border-left: 4px solid var(--primary); }

        /* Content */
        .main { margin-left: 280px; width: 100%; padding: 40px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-add { background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 14px; cursor: pointer; border: none; }

        /* Card & Table */
        .card { background: var(--white); border-radius: 20px; padding: 25px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); border: 1px solid #e2e8f0; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #64748b; font-size: 12px; border-bottom: 2px solid #f1f5f9; text-transform: uppercase; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        
        .role-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #e0e7ff; color: #4361ee; }
        .btn-edit { color: var(--primary); font-weight: 600; text-decoration: none; margin-right: 15px; cursor: pointer; }
        .btn-del { color: #ef4444; font-weight: 600; text-decoration: none; }

        /* MODAL STYLE */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-content { background: white; width: 400px; margin: 10% auto; padding: 30px; border-radius: 24px; position: relative; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; box-sizing: border-box; }
        .close-modal { position: absolute; right: 25px; top: 25px; font-size: 20px; cursor: pointer; color: #64748b; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-brand">🅿️ PARKIR PRO</div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php">Dashboard Overview</a>
            <a href="manage_user.php" class="active">Manage User</a>
            <a href="manage_tarif.php">Tarif Parkir</a>
            <a href="manage_area.php">Area Parkir</a>
            <a href="manage_kendaraan.php">Data Kendaraan</a>
            <a href="logout.php" style="color: #ef4444; margin-top: 50px;">Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>Manajemen User</h1>
            <button class="btn-add" onclick="openModal('modalAdd')">+ Tambah User</button>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>NAMA LENGKAP</th>
                        <th>USERNAME</th>
                        <th>ROLE</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><strong><?= $row['nama_lengkap'] ?></strong></td>
                        <td><?= $row['username'] ?></td>
                        <td><span class="role-badge"><?= $row['role'] ?></span></td>
                        <td>
                            <span class="btn-edit" onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</span>
                            <a href="?delete=<?= $row['id_user'] ?>" class="btn-del" onclick="return confirm('Hapus user ini?')">Hapus</a>
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
            <h2>Tambah User Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role">
                        <option value="Admin">Admin</option>
                        <option value="Petugas">Petugas</option>
                        <option value="Owner">Owner</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn-add" style="width:100%">Simpan User</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('modalEdit')">&times;</span>
            <h2>Edit Data User</h2>
            <form method="POST">
                <input type="hidden" name="id_user" id="edit_id">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="edit_nama" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_user" required>
                </div>
                <div class="form-group">
                    <label>Password (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="edit_role">
                        <option value="Admin">Admin</option>
                        <option value="Petugas">Petugas</option>
                        <option value="Owner">Owner</option>
                    </select>
                </div>
                <button type="submit" name="edit_user" class="btn-add" style="width:100%">Update User</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'block'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id_user;
            document.getElementById('edit_nama').value = data.nama_lengkap;
            document.getElementById('edit_user').value = data.username;
            document.getElementById('edit_role').value = data.role;
            openModal('modalEdit');
        }
    </script>
</body>
</html> 