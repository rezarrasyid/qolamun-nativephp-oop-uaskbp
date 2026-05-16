<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../classes/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php'); exit;
}

$nama      = trim($_POST['nama']      ?? '');
$username  = trim($_POST['username']  ?? '');
$password  = $_POST['password']       ?? '';
$konfirmasi= $_POST['konfirmasi']     ?? '';
$setuju    = $_POST['setuju']         ?? '';

// Validasi dasar
if (!$nama || !$username || !$password || !$konfirmasi) {
    header('Location: register.php?err=' . urlencode('Semua kolom wajib diisi.')); exit;
}
if (!$setuju) {
    header('Location: register.php?err=' . urlencode('Anda harus menyetujui persyaratan.')); exit;
}
if (strlen($password) < 6) {
    header('Location: register.php?err=' . urlencode('Password minimal 6 karakter.')); exit;
}
if ($password !== $konfirmasi) {
    header('Location: register.php?err=' . urlencode('Password dan konfirmasi tidak cocok.')); exit;
}
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    header('Location: register.php?err=' . urlencode('Username hanya boleh huruf, angka, dan underscore.')); exit;
}

// Cek username sudah ada
$esc = mysqli_real_escape_string($koneksi, $username);
$cek = mysqli_query($koneksi, "SELECT id FROM users WHERE username='$esc' LIMIT 1");
if ($cek && mysqli_num_rows($cek) > 0) {
    header('Location: register.php?err=' . urlencode('Username sudah digunakan. Coba yang lain.')); exit;
}

// Simpan user baru (role: penulis)
$userObj = new User($koneksi);
$ok = $userObj->register($nama, $username, $password, 'penulis');

if ($ok) {
    header('Location: register.php?ok=' . urlencode('Pendaftaran berhasil! Silakan masuk dengan akun barumu.')); exit;
} else {
    header('Location: register.php?err=' . urlencode('Terjadi kesalahan sistem. Coba lagi.')); exit;
}