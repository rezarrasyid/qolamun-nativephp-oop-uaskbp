<?php
session_start();

// Validasi akses Admin
if (($_SESSION['role'] ?? null) !== 'admin') { 
    header('Location: ../../auth/login.php'); 
    exit; 
}

require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../classes/Admin.php';

// [1] Instansiasi object Admin (child class dari User)
$admin = new Admin($koneksi);
$admin->setNama($_SESSION['nama'] ?? 'Admin');

// Mengambil data statistik
$totalUser  = (int)mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) t FROM users"))['t'];
$totalKat   = (int)mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) t FROM categories"))['t'];
$totalPost  = (int)mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) t FROM posts"))['t'];

$pageTitle = 'Dashboard Admin';
include __DIR__ . '/../../includes/header.php';
?>

<div class="flex">
    <!-- Sidebar Admin -->
    <?php include __DIR__ . '/../../includes/sidebar_admin.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 p-6 md:p-8 bg-slate-50 min-h-screen">
        <header class="mb-8">
            <h1 class="text-2xl font-extrabold text-emerald-800 mb-1" data-testid="admin-dashboard-title">Dashboard Admin</h1>
            <p class="text-sm text-slate-500">
                <?= htmlspecialchars($admin->tampilkanInfo()) ?>
            </p>
        </header>

        <!-- Statistik Wrapper -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white border border-green-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition" data-testid="stat-users">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-1">Total User</p>
                <p class="text-4xl font-extrabold text-emerald-800"><?= $totalUser ?></p>
            </div>
            
            <div class="bg-white border border-green-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition" data-testid="stat-kategori">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-1">Total Kategori</p>
                <p class="text-4xl font-extrabold text-emerald-800"><?= $totalKat ?></p>
            </div>
            
            <div class="bg-white border border-green-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition" data-testid="stat-karya">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-1">Total Karya</p>
                <p class="text-4xl font-extrabold text-emerald-800"><?= $totalPost ?></p>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid sm:grid-cols-3 gap-5 mt-6">
            <a href="users.php" 
               class="group bg-emerald-600 text-white rounded-2xl p-5 hover:bg-emerald-700 shadow-sm hover:shadow-md shadow-emerald-600/20 transition flex justify-between items-center" 
               data-testid="quick-users">
                <span class="font-semibold">Kelola User</span>
                <span class="opacity-70 group-hover:opacity-100 group-hover:translate-x-1 transition">&rarr;</span>
            </a>
            
            <a href="kategori.php" 
               class="group bg-emerald-700 text-white rounded-2xl p-5 hover:bg-emerald-800 shadow-sm hover:shadow-md shadow-emerald-700/20 transition flex justify-between items-center" 
               data-testid="quick-kategori">
                <span class="font-semibold">Kelola Kategori</span>
                <span class="opacity-70 group-hover:opacity-100 group-hover:translate-x-1 transition">&rarr;</span>
            </a>
            
            <a href="karya.php" 
               class="group bg-emerald-800 text-white rounded-2xl p-5 hover:bg-emerald-900 shadow-sm hover:shadow-md shadow-emerald-800/20 transition flex justify-between items-center" 
               data-testid="quick-karya">
                <span class="font-semibold">Kelola Karya</span>
                <span class="opacity-70 group-hover:opacity-100 group-hover:translate-x-1 transition">&rarr;</span>
            </a>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>