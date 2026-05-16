<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (($_SESSION['role'] ?? null) !== 'penulis') { header('Location: ../../auth/login.php'); exit; }
$cur = basename($_SERVER['PHP_SELF']);

$links = [
    ['href' => 'dashboard.php',  'label' => 'Dashboard',    'file' => 'dashboard.php',
     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
    ['href' => 'karya.php',      'label' => 'Karya Saya',   'file' => 'karya.php',
     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
    ['href' => 'form_karya.php', 'label' => '+ Tulis Baru', 'file' => 'form_karya.php',
     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>'],
];
?>
<div class="panel-nav shadow-sm">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center gap-1 overflow-x-auto py-2 scrollbar-none">

            <!-- Greeting -->
            <span class="text-xs font-medium mr-3 flex-shrink-0" style="color:var(--text-muted)">
                Halo, <strong style="color:var(--em)"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></strong>
            </span>

            <?php foreach ($links as $l): ?>
                <a href="<?= $l['href'] ?>"
                   class="<?= ($cur === $l['file']) ? 'active' : '' ?> whitespace-nowrap"
                   data-testid="nav-<?= $l['file'] ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <?= $l['icon'] ?>
                    </svg>
                    <?= $l['label'] ?>
                </a>
            <?php endforeach; ?>

            <div class="flex-1"></div>

            <a href="../../index.php" class="whitespace-nowrap" data-testid="nav-public">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/></svg>
                Lihat Publik
            </a>
        </div>
    </div>
</div>