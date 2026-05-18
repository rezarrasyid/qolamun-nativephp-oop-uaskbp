<?php
session_start();
if (($_SESSION['role'] ?? null) !== 'penulis') { header('Location: ../../auth/login.php'); exit; }
require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../classes/Penulis.php';

// [1] Instansiasi object Penulis (child class dari User)
$penulis = new Penulis($koneksi);
$penulis->setId($_SESSION['user_id']);
$penulis->setNama($_SESSION['nama']);
$total = $penulis->hitungKaryaSaya($_SESSION['user_id']);

$pageTitle = 'Dashboard Penulis';
include __DIR__ . '/../../includes/header.php';
?>
<div class="flex">
    <main class="flex-1 max-w-7xl mx-auto w-full p-6 md:p-8">
        <h1 class="text-2xl font-extrabold text-emerald-800 mb-6" data-testid="penulis-dashboard-title">Selamat Datang, <?= htmlspecialchars($penulis->getNama()) ?></h1>

        <div class="grid sm:grid-cols-2 gap-4">
            <div class="bg-white border border-green-100 rounded-2xl p-5" data-testid="stat-karya-saya">
                <p class="text-xs uppercase tracking-wider text-emerald-600">Karya Saya</p>
                <p class="text-4xl font-extrabold text-emerald-800 mt-2"><?= $total ?></p>
            </div>
            <a href="form_karya.php" class="bg-emerald-600 text-white rounded-2xl p-5 hover:bg-emerald-700 flex items-center justify-between" data-testid="cta-tambah-karya">
                <span class="font-semibold">+ Tulis Karya Baru</span>
                <span>&rarr;</span>
            </a>
        </div>
    </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>