<?php
$host = "db";
$user = "root";
$pass = "rootpassword";
$db = "db_qolamun";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>