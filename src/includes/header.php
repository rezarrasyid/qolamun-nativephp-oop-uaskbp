<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$role = $_SESSION['role'] ?? null;
$nama = $_SESSION['nama'] ?? 'Santri';
$cur = basename($_SERVER['PHP_SELF']);
$uri = $_SERVER['REQUEST_URI'];

// 1. Deteksi otomatis posisi halaman saat ini
$isAdminPanel   = strpos($uri, '/admin/') !== false;
$isPenulisPanel = strpos($uri, '/penulis/') !== false;

// 2. Proteksi Keamanan Panel (Tendang jika role tidak sesuai)
if ($isAdminPanel && $role !== 'admin') { header('Location: ../../auth/login.php'); exit; }
if ($isPenulisPanel && $role !== 'penulis') { header('Location: ../../auth/login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — Qolamun' : 'Qolamun — Mading Pesantren Digital' ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Lora:ital,wght@0,400;0,500;0,600;1,400&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-art { font-family: 'Lora', serif; }
        
        /* Class Khusus untuk Font Arab */
        .font-arabic { font-family: 'Amiri', serif; }
        
        /* Style Khusus Navbar Horizontal */
        .panel-nav a.active { color: #059669; border-bottom: 2px solid #059669; font-weight: 600; }
        .panel-nav a { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; color: #64748b; font-size: 0.875rem; transition: all 0.2s;}
        .panel-nav a:hover { color: #059669; }
        .scrollbar-none::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-green-50 text-slate-800 min-h-screen flex flex-col">
<?php if ($isAdminPanel): ?>
    <?php
    $adminLinks = [
        ['href' => 'dashboard.php', 'label' => 'Dashboard', 'file' => 'dashboard.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
        ['href' => 'users.php',     'label' => 'Kelola User',  'file' => 'users.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2h5m6 0v-2a4 4 0 00-4-4H6"/>'],
        ['href' => 'kategori.php',  'label' => 'Kategori',     'file' => 'kategori.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>'],
        ['href' => 'karya.php',     'label' => 'Kelola Karya', 'file' => 'karya.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
    ];
    ?>
    <div class="panel-nav shadow-sm bg-white border-b border-green-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 flex items-center overflow-x-auto py-2 scrollbar-none">
            <div class="flex items-center gap-2 mr-6 flex-shrink-0">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white grid place-items-center font-bold font-arabic text-lg">ق</div>
                <div class="flex items-baseline gap-1.5">
                    <span class="font-arabic text-xl font-bold text-emerald-800">قلمون</span>
                    <span class="text-[9px] font-sans font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">ADMIN</span>
                </div>
            </div>

            <?php foreach ($adminLinks as $l): ?>
                <a href="<?= $l['href'] ?>" class="<?= $cur === $l['file'] ? 'active' : '' ?> whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><?= $l['icon'] ?></svg>
                    <?= $l['label'] ?>
                </a>
            <?php endforeach; ?>
            <div class="flex-1"></div>
            <a href="../../auth/logout.php" class="whitespace-nowrap text-red-600 hover:text-red-700 !font-semibold">Logout</a>
        </div>
    </div>

<?php elseif ($isPenulisPanel): ?>
    <?php
    $penulisLinks = [
        ['href' => 'dashboard.php',  'label' => 'Dashboard',    'file' => 'dashboard.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
        ['href' => 'karya.php',      'label' => 'Karya Saya',   'file' => 'karya.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
        ['href' => 'form_karya.php', 'label' => 'Tulis Baru', 'file' => 'form_karya.php', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>'],
    ];
    ?>
    <div class="panel-nav shadow-sm bg-white border-b border-green-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 flex items-center overflow-x-auto py-2 scrollbar-none">
            <div class="flex items-center gap-2 mr-6 flex-shrink-0">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white grid place-items-center font-bold font-arabic text-lg">ق</div>
                <div class="flex items-baseline gap-1.5">
                    <span class="font-arabic text-xl font-bold text-emerald-800">قلمون</span>
                    <span class="text-[9px] font-sans font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">PENULIS</span>
                </div>
            </div>

            <span class="text-xs font-medium mr-3 flex-shrink-0 text-slate-400">| &nbsp; Halo, <strong class="text-slate-700"><?= htmlspecialchars($nama) ?></strong></span>

            <?php foreach ($penulisLinks as $l): ?>
                <a href="<?= $l['href'] ?>" class="<?= $cur === $l['file'] ? 'active' : '' ?> whitespace-nowrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><?= $l['icon'] ?></svg>
                    <?= $l['label'] ?>
                </a>
            <?php endforeach; ?>
            <div class="flex-1"></div>
            <a href="../../auth/logout.php" class="whitespace-nowrap text-red-600 hover:text-red-700 !font-semibold">Logout</a>
            <a href="../../index.php" class="whitespace-nowrap"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>Lihat Publik</a>
        </div>
    </div>

<?php else: ?>
    <nav class="bg-white border-b border-green-100 sticky top-0 z-40 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="<?= strpos($uri, 'detail.php') !== false ? 'index.php' : '#' ?>" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white grid place-items-center font-bold font-arabic text-xl shadow-md shadow-emerald-600/20">ق</div>
                <div>
                    <p class="font-arabic text-2xl font-bold text-emerald-800 leading-none tracking-wide">قلمون</p>
                    <p class="text-[10px] text-emerald-600 tracking-wider uppercase font-sans mt-0.5">Mading Pesantren Digital</p>
                </div>
            </a>
            <div class="flex items-center gap-2">
                <?php if ($role === 'admin'): ?>
                    <a href="pages/admin/dashboard.php" class="text-sm px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 font-semibold transition">Masuk Panel Admin</a>
                <?php elseif ($role === 'penulis'): ?>
                    <a href="pages/penulis/dashboard.php" class="text-sm px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 font-semibold transition">Masuk Panel Penulis</a>
                <?php else: ?>
                    <a href="auth/login.php" class="text-sm px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 font-semibold transition shadow-md shadow-emerald-600/10">Login</a>
                    <a href="auth/register.php" class="text-sm px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 font-semibold transition shadow-md shadow-emerald-600/10">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
<?php endif; ?>

<main class="flex-grow">