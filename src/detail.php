<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/classes/Karya.php';

$slug = $_GET['slug'] ?? '';
$karyaObj = new Karya($koneksi);
$karya = $karyaObj->ambilKaryaBySlug($slug);

if (!$karya) {
    http_response_code(404);
    echo "Karya tidak ditemukan."; 
    exit;
}

$pageTitle = $karya['judul'];
include __DIR__ . '/includes/header.php';

// Penentuan Thumbnail
$thumb = !empty($karya['thumbnail']) && file_exists(__DIR__ . '/uploads/' . $karya['thumbnail'])
    ? 'uploads/' . $karya['thumbnail']
    : null;
?>

<article class="max-w-3xl mx-auto px-4 py-10">
    <!-- Breadcrumb / Kembali -->
    <a href="index.php" class="text-sm text-emerald-700 hover:text-emerald-800 font-medium mb-6 inline-flex items-center gap-1 transition" data-testid="detail-back">
        <span>&larr;</span> Kembali ke Beranda
    </a>

    <!-- Badge Kategori -->
    <div class="mb-4">
        <span class="inline-block px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full bg-emerald-100 text-emerald-700">
            <?= htmlspecialchars($karya['nama_kategori'] ?? 'Umum') ?>
        </span>
    </div>

    <!-- Judul & Meta -->
    <header class="mb-8">
        <h1 class="font-serif-art text-3xl md:text-5xl font-bold text-emerald-900 leading-tight mb-4" data-testid="detail-title">
            <?= htmlspecialchars($karya['judul']) ?>
        </h1>
        
        <div class="flex items-center gap-3 text-sm text-slate-500">
            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 grid place-items-center font-bold">
                <?= strtoupper(substr($karya['nama_penulis'] ?? 'U', 0, 1)) ?>
            </div>
            <p>
                Oleh <span class="font-semibold text-emerald-700"><?= htmlspecialchars($karya['nama_penulis'] ?? 'Anonim') ?></span> 
                <span class="mx-1 text-slate-300">•</span> 
                <?= date('d M Y', strtotime($karya['created_at'])) ?>
            </p>
        </div>
    </header>

    <!-- Gambar Utama -->
    <?php if ($thumb): ?>
        <div class="mb-10">
            <img src="<?= htmlspecialchars($thumb) ?>" 
                 alt="<?= htmlspecialchars($karya['judul']) ?>" 
                 class="w-full rounded-2xl shadow-sm border border-green-100 object-cover">
        </div>
    <?php endif; ?>

    <!-- Isi Konten -->
    <div class="konten-detail text-slate-800 text-[18px] leading-relaxed" data-testid="detail-content">
        <?= $karya['konten'] // Raw HTML dari TinyMCE ?>
    </div>

    <!-- Footer Artikel -->
    <footer class="mt-12 pt-8 border-t border-green-100">
        <p class="text-sm text-slate-400 italic text-center">
            Barakallahu fiikum. Semoga tulisan ini bermanfaat bagi umat.
        </p>
    </footer>
</article>

<?php include __DIR__ . '/includes/footer.php'; ?>