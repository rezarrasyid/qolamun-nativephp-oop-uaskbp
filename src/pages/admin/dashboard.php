<?php
session_start();
if (($_SESSION['role'] ?? null) !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../classes/Admin.php';

$admin = new Admin($koneksi);
$admin->setNama($_SESSION['nama'] ?? 'Admin');

$totalUser = (int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) t FROM users"))['t'];
$totalKat  = (int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) t FROM categories"))['t'];
$totalPost = (int)mysqli_fetch_assoc(mysqli_query($koneksi,"SELECT COUNT(*) t FROM posts"))['t'];

// Karya terbaru 5
$recentKarya = mysqli_query($koneksi,
    "SELECT p.judul, u.nama AS penulis, p.created_at, p.id
     FROM posts p LEFT JOIN users u ON u.id=p.user_id
     ORDER BY p.id DESC LIMIT 5");

$pageTitle = 'Dashboard Admin';
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <main class="flex-1 max-w-7xl mx-auto w-full p-6 md:p-8">
        
        <div class="mb-8">
            <h1 class="text-2xl font-extrabold text-emerald-900 mb-1" data-testid="admin-dashboard-title">Dashboard Admin</h1>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
            <div class="bg-white border border-emerald-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition duration-300" data-testid="stat-users">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Total User</p>
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5"/></svg>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-emerald-900"><?= $totalUser ?></p>
            </div>
            
            <div class="bg-white border border-emerald-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition duration-300" data-testid="stat-kategori">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Kategori</p>
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-emerald-900"><?= $totalKat ?></p>
            </div>
            
            <div class="bg-white border border-emerald-100 rounded-2xl p-6 shadow-sm hover:shadow-md transition duration-300" data-testid="stat-karya">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Total Karya</p>
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-emerald-50 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </div>
                <p class="text-4xl font-extrabold text-emerald-900"><?= $totalPost ?></p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="bg-white border border-emerald-100 rounded-2xl p-6 shadow-sm h-fit">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-5">Menu Cepat</h2>
                <div class="space-y-3">
                    <a href="users.php" class="flex items-center justify-between px-5 py-3.5 rounded-xl font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition text-sm" data-testid="quick-users">
                        <span>Kelola User</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="kategori.php" class="flex items-center justify-between px-5 py-3.5 rounded-xl font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition text-sm" data-testid="quick-kategori">
                        <span>Kelola Kategori</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="karya.php" class="flex items-center justify-between px-5 py-3.5 rounded-xl font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition text-sm" data-testid="quick-karya">
                        <span>Kelola Karya</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            <div class="bg-white border border-emerald-100 rounded-2xl p-6 shadow-sm lg:col-span-2">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-2">Karya Terbaru</h2>
                <div class="divide-y divide-slate-100">
                    <?php if (mysqli_num_rows($recentKarya) > 0): ?>
                        <?php while ($k = mysqli_fetch_assoc($recentKarya)): ?>
                            <div class="flex items-center justify-between py-4 group">
                                <div class="min-w-0 flex-1 pr-4">
                                    <p class="text-sm font-bold text-slate-800 truncate group-hover:text-emerald-700 transition"><?= htmlspecialchars($k['judul']) ?></p>
                                    <p class="text-xs mt-1 text-slate-500">
                                        oleh <span class="font-medium text-slate-700"><?= htmlspecialchars($k['penulis'] ?? 'Anonim') ?></span> &bull; <?= date('d M Y', strtotime($k['created_at'])) ?>
                                    </p>
                                </div>
                                <a href="../../detail.php?id=<?= $k['id'] ?>" target="_blank"
                                   class="flex-shrink-0 p-2 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition" title="Lihat Karya">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="py-8 text-center text-sm text-slate-500">Belum ada karya yang diterbitkan.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>