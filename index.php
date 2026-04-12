<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $query = mysqli_query($conn, "SELECT * FROM tb_user WHERE username='$username' AND password='$password'");
    
    if (mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
        $_SESSION['role'] = $data['role'];

        // Catat Log Berhasil
        mysqli_query($conn, "INSERT INTO tb_log (id_user, nama_user, aktivitas) VALUES ('".$data['id_user']."', '".$data['nama_lengkap']."', 'Login Berhasil')");

        if($data['role'] == "Admin") header("location:admin_dashboard.php");
        else if($data['role'] == "Petugas") header("location:petugas_dashboard.php");
        else header("location:owner_dashboard.php");
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Smart Parking Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            overflow: hidden;
        }

        /* Dekorasi Lingkaran Latar Belakang */
        .circle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 0;
        }
        .circle-1 { width: 300px; height: 300px; top: -100px; right: -50px; }
        .circle-2 { width: 200px; height: 200px; bottom: -50px; left: -50px; }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .login-card h2 {
            margin: 0 0 10px 0;
            color: #2b2d42;
            font-size: 28px;
            font-weight: 700;
        }

        .login-card p {
            color: #8d99ae;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #4b5563;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            outline: none;
            transition: all 0.3s ease;
            font-size: 15px;
            background: #f9fafb;
        }

        .form-group input:focus {
            border-color: #4361ee;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: #4361ee;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #3730a3;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(67, 97, 238, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .error-msg {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }

        /* Animasi masuk */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-card { animation: fadeInUp 0.6s ease-out; }
    </style>
</head>
<body>

    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <div class="login-container">
        <div class="login-card">
            <h2>Selamat Datang</h2>
            <p>Silakan masuk ke akun Smart Parking Anda</p>

            <?php if(isset($error)): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Masukkan username" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" name="login" class="btn-login">Masuk Sekarang</button>
            </form>
            
            <div style="margin-top: 25px; font-size: 12px; color: #cbd5e1;">
                &copy; 2026 Smart Parking System v2.0
            </div>
        </div>
    </div>

</body>
</html>