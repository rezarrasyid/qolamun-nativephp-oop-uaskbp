<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../classes/User.php';

// Validasi Request Method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php'); 
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi Input Kosong
if ($username === '' || $password === '') {
    $msg = urlencode('Username dan password wajib diisi.');
    header("Location: login.php?err=$msg"); 
    exit;
}


$userObj = new User($koneksi);
$data = $userObj->login($username, $password);

if ($data) {
    $_SESSION['user_id']  = $data['id'];
    $_SESSION['nama']     = $data['nama'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['role']     = $data['role'];

    // Redirection berdasarkan Role
    if ($data['role'] === 'admin') {
        header('Location: ../pages/admin/dashboard.php');
    } else {
        header('Location: ../pages/penulis/dashboard.php');
    }
    exit;
}

$msg = urlencode('Username atau password salah.');
header("Location: login.php?err=$msg");
exit;