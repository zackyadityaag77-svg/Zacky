<?php
// Memulai session
session_start();

// Menghapus semua variabel session
$_SESSION = array();

// Menghancurkan session
session_destroy();

// Mengarahkan pengguna kembali ke halaman login (sesuaikan nama filenya, misal index.php atau login.php)
header("Location: index.php");
exit;
?>
