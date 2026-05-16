<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$role = $_SESSION['role'] ?? null;
$nama = $_SESSION['nama'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — Qolamun' : 'Qolamun — Mading Pesantren Digital' ?></title>
    
    <!-- Fonts & Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Lora:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif-art { font-family: 'Lora', serif; }
        .konten-detail p { margin-bottom: 1rem; line-height: 1.85; }
        .konten-detail h1, .konten-detail h2, .konten-detail h3 { font-weight: 700; margin: 1.2rem 0 .6rem; }
        .konten-detail img { max-width: 100%; height: auto; border-radius: .5rem; margin: 1rem 0; }
        .konten-detail ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1rem; }
        .konten-detail ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 1rem; }
    </style>
</head>
<body class="bg-green-50 text-slate-800 min-h-screen">

<nav class="bg-white border-b border-green-100 sticky top-0 z-40">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <!-- Logo -->
        <a href="<?= ($role ? '../../index.php' : 'index.php') ?>" class="flex items-center gap-2" data-testid="nav-home">
            <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white grid place-items-center font-bold">Q</div>
            <div>
                <p class="font-extrabold text-emerald-800 leading-none text-lg">Qolamun</p>
                <p class="text-[10px] text-emerald-600 tracking-wider uppercase">Mading Pesantren Digital</p>
            </div>
        </a>

        <!-- Navigation Actions -->
        <div class="flex items-center gap-2">
            <?php if ($role === 'admin'): ?>
                <a href="../../pages/admin/dashboard.php" 
                   class="text-sm px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" 
                   data-testid="nav-admin-dashboard">
                   Dashboard Admin
                </a>
                <a href="../../auth/logout.php" 
                   class="text-sm px-3 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50" 
                   data-testid="nav-logout">
                   Logout
                </a>
            <?php elseif ($role === 'penulis'): ?>
                <a href="../../pages/penulis/dashboard.php" 
                   class="text-sm px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700" 
                   data-testid="nav-penulis-dashboard">
                   Dashboard Penulis
                </a>
                <a href="../../auth/logout.php" 
                   class="text-sm px-3 py-2 rounded-lg border border-red-300 text-red-600 hover:bg-red-50" 
                   data-testid="nav-logout">
                   Logout
                </a>
            <?php else: ?>
                <a href="auth/login.php" 
                   class="text-sm px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition" 
                   data-testid="nav-login">
                   Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main>