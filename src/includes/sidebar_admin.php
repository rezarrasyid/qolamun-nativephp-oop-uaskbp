<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (($_SESSION['role'] ?? null) !== 'admin') { header('Location: ../../auth/login.php'); exit; }
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="w-64 bg-white border-r border-green-100 min-h-screen p-4 hidden md:block">
    <div class="flex items-center gap-2 mb-6">
        <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white grid place-items-center font-bold">Q</div>
        <div>
            <p class="font-extrabold text-emerald-800 leading-none">Qolamun</p>
            <p class="text-[10px] text-emerald-600 uppercase tracking-wider">Panel Admin</p>
        </div>
    </div>

    <nav class="space-y-1 text-sm">
        <a href="dashboard.php" 
           class="<?= $current === 'dashboard.php' ? 'bg-emerald-100 text-emerald-800' : 'text-slate-700 hover:bg-green-50' ?> block px-3 py-2 rounded-lg" 
           data-testid="sidebar-dashboard">
           Dashboard
        </a>

        <a href="users.php" 
           class="<?= $current === 'users.php' ? 'bg-emerald-100 text-emerald-800' : 'text-slate-700 hover:bg-green-50' ?> block px-3 py-2 rounded-lg" 
           data-testid="sidebar-users">
           Kelola User
        </a>

        <a href="kategori.php" 
           class="<?= $current === 'kategori.php' ? 'bg-emerald-100 text-emerald-800' : 'text-slate-700 hover:bg-green-50' ?> block px-3 py-2 rounded-lg" 
           data-testid="sidebar-kategori">
           Kelola Kategori
        </a>

        <a href="karya.php" 
           class="<?= $current === 'karya.php' ? 'bg-emerald-100 text-emerald-800' : 'text-slate-700 hover:bg-green-50' ?> block px-3 py-2 rounded-lg" 
           data-testid="sidebar-karya">
           Kelola Karya
        </a>

        <hr class="my-3 border-emerald-50">

        <a href="../../index.php" 
           class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-green-50" 
           data-testid="sidebar-public">
           Lihat Publik
        </a>

        <a href="../../auth/logout.php" 
           class="block px-3 py-2 rounded-lg text-red-600 hover:bg-red-50" 
           data-testid="sidebar-logout">
           Logout
        </a>
    </nav>
</aside>