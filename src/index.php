<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/classes/Karya.php';

$karyaObj  = new Karya($koneksi);
$result    = $karyaObj->ambilSemuaKarya();
$total     = $karyaObj->hitungSemua();
$katResult = $karyaObj->ambilSemuaKategori();

// Kumpulkan semua kategori untuk filter
$kategoriList = [];
while ($kat = mysqli_fetch_assoc($katResult)) {
    $kategoriList[] = $kat;
}

// Hitung jumlah penulis aktif
$rPenulis = mysqli_query($koneksi, "SELECT COUNT(DISTINCT user_id) AS t FROM posts");
$totalPenulis = $rPenulis ? (int)mysqli_fetch_assoc($rPenulis)['t'] : 0;

// Hitung jumlah kategori
$rKat = mysqli_query($koneksi, "SELECT COUNT(*) AS t FROM categories");
$totalKat = $rKat ? (int)mysqli_fetch_assoc($rKat)['t'] : 0;

// Ambil karya terbaru (untuk Unggulan)
$rUnggulan = mysqli_query($koneksi, 
    "SELECT p.*, c.nama_kategori, u.nama AS nama_penulis
     FROM posts p
     LEFT JOIN categories c ON c.id = p.category_id
     LEFT JOIN users u ON u.id = p.user_id
     ORDER BY p.created_at DESC, p.id DESC
     LIMIT 1"
);
$unggulan = $rUnggulan ? mysqli_fetch_assoc($rUnggulan) : null;

// Kumpulkan semua karya ke array (untuk filter JS client-side)
$semuaKarya = [];
while ($row = mysqli_fetch_assoc($result)) {
    $semuaKarya[] = $row;
}

$pageTitle = 'Beranda';
include __DIR__ . '/includes/header.php';
?>

<style>
/* ===== CUSTOM STYLES ===== */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes countUp {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes pulse-soft {
    0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.3); }
    50%       { box-shadow: 0 0 0 10px rgba(16,185,129,0); }
}

.animate-fadeup   { animation: fadeUp 0.6s ease both; }
.animate-fadein   { animation: fadeIn 0.5s ease both; }
.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
.delay-400 { animation-delay: 0.4s; }
.delay-500 { animation-delay: 0.5s; }

.stat-card {
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 1rem;
    transition: transform 0.2s, background 0.2s;
}
.stat-card:hover {
    background: rgba(255,255,255,0.18);
    transform: translateY(-2px);
}

.karya-card {
    transition: transform 0.3s cubic-bezier(.22,.68,0,1.2), box-shadow 0.3s ease;
}
.karya-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px -12px rgba(4,120,87,0.2);
}
.karya-card .thumb-img {
    transition: transform 0.7s cubic-bezier(.22,.68,0,1.2);
}
.karya-card:hover .thumb-img {
    transform: scale(1.08);
}

.filter-btn {
    transition: all 0.2s ease;
}
.filter-btn.active {
    background-color: #059669;
    color: white;
    box-shadow: 0 4px 12px -2px rgba(5,150,105,0.4);
}

.featured-card {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #047857 100%);
}

.search-box:focus-within .search-icon {
    color: #059669;
}

.karya-item { transition: opacity 0.3s ease, transform 0.3s ease; }
.karya-item.hidden-card { 
    display: none;
}

/* Marquee / scroll teks berjalan */
@keyframes marquee {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
}
.marquee-track {
    display: flex;
    animation: marquee 30s linear infinite;
    width: max-content;
}
.marquee-wrap:hover .marquee-track { animation-play-state: paused; }

/* Scrollbar minimal */
.cat-scroll::-webkit-scrollbar { height: 3px; }
.cat-scroll::-webkit-scrollbar-thumb { background: #d1fae5; border-radius: 999px; }

/* Highlight warna pada jumlah karya di hero */
.num-highlight {
    background: linear-gradient(135deg, #fff 30%, #a7f3d0);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* badge pulse */
.live-dot {
    width: 8px; height: 8px;
    background: #34d399;
    border-radius: 50%;
    animation: pulse-soft 2s infinite;
    display: inline-block;
}

/* Ornament Arab dekoratif */
.ornament { 
    font-family: 'Lora', serif; 
    opacity: 0.08; 
    font-size: 7rem; 
    line-height: 1;
    user-select: none;
}
</style>

<!-- ===================== TICKER / RUNNING TEXT ===================== -->
<div class="bg-emerald-900 text-emerald-200 text-xs py-2 overflow-hidden marquee-wrap select-none">
    <div class="marquee-track gap-12" style="gap: 4rem;">
        <?php
        // Tampilkan judul karya berulang dua kali untuk seamless loop
        $tickerItems = array_slice($semuaKarya, 0, 20);
        foreach ([$tickerItems, $tickerItems] as $group):
            foreach ($group as $t): ?>
                <span class="flex items-center gap-3 whitespace-nowrap px-4">
                    <span class="text-emerald-500">✦</span>
                    <?= htmlspecialchars($t['judul']) ?>
                    <span class="text-emerald-600 text-[10px]">— <?= htmlspecialchars($t['nama_penulis'] ?? 'Santri') ?></span>
                </span>
            <?php endforeach;
        endforeach; ?>
    </div>
</div>

<!-- ===================== HERO ===================== -->
<section class="relative overflow-hidden bg-gradient-to-br from-emerald-900 via-emerald-800 to-green-900 text-white">
    <!-- Dekorasi background -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="ornament absolute -top-4 -left-4 leading-none select-none">﷽</div>
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-emerald-600/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-1/4 w-[400px] h-[400px] bg-green-500/10 rounded-full blur-3xl translate-y-1/2"></div>
        <!-- Grid pattern -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.03]" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)" />
        </svg>
    </div>

    <div class="relative max-w-6xl mx-auto px-4 py-16 md:py-24">
        <div class="grid md:grid-cols-2 gap-10 items-center">
            <!-- Teks kiri -->
            <div class="animate-fadeup">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-700/50 border border-emerald-600/40 text-emerald-200 text-xs font-medium mb-5">
                    <span class="live-dot"></span>
                    Mading Pesantren Digital — Aktif
                </div>

                <h1 class="font-serif-art text-4xl md:text-5xl lg:text-6xl font-bold leading-[1.15] mb-5">
                    Setiap goresan<br>pena adalah
                    <span class="italic text-emerald-300"> dakwah</span>.
                </h1>

                <p class="text-emerald-100/80 text-base md:text-lg max-w-md leading-relaxed mb-8">
                    Qolamun adalah ruang digital tempat para santri menuangkan karya — artikel islami, cerpen, puisi, hingga berita pesantren.
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="#karya"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-white text-emerald-800 font-bold rounded-xl hover:bg-emerald-50 transition shadow-lg shadow-black/20"
                       data-testid="hero-cta-explore">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        Jelajahi Karya
                    </a>
                    <a href="auth/login.php"
                       class="inline-flex items-center gap-2 px-6 py-3 border border-white/20 text-white font-semibold rounded-xl hover:bg-white/10 transition"
                       data-testid="hero-cta-login">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14"/></svg>
                        Login Penulis
                    </a>
                </div>
            </div>

            <!-- Stats kanan -->
            <div class="grid grid-cols-2 gap-4 animate-fadeup delay-200">
                <div class="stat-card p-5 col-span-2">
                    <p class="text-emerald-300 text-xs uppercase tracking-widest mb-1">Total Karya Terbit</p>
                    <p class="num-highlight text-6xl font-extrabold" data-testid="total-karya"><?= $total ?></p>
                    <p class="text-emerald-400 text-xs mt-1">karya dari para santri</p>
                </div>
                <div class="stat-card p-5">
                    <p class="text-emerald-300 text-xs uppercase tracking-widest mb-1">Penulis Aktif</p>
                    <p class="text-4xl font-extrabold text-white"><?= $totalPenulis ?></p>
                    <p class="text-emerald-400 text-xs mt-1">santri</p>
                </div>
                <div class="stat-card p-5">
                    <p class="text-emerald-300 text-xs uppercase tracking-widest mb-1">Kategori</p>
                    <p class="text-4xl font-extrabold text-white"><?= $totalKat ?></p>
                    <p class="text-emerald-400 text-xs mt-1">topik bahasan</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== FEATURED / SPOTLIGHT ===================== -->
<?php if ($unggulan):
    $fThumb = !empty($unggulan['thumbnail']) && file_exists(__DIR__ . '/uploads/' . $unggulan['thumbnail'])
        ? 'uploads/' . $unggulan['thumbnail']
        : 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?w=1200&q=80';
?>
<section class="max-w-6xl mx-auto px-4 pt-12 pb-4 animate-fadeup delay-300">
    <div class="flex items-center gap-3 mb-5">
        <span class="w-1.5 h-6 rounded-full bg-emerald-500 inline-block"></span>
        <h2 class="text-lg font-extrabold text-emerald-800 uppercase tracking-wider">Karya Unggulan</h2>
    </div>

    <a href="detail.php?slug=<?= $unggulan['slug'] ?>"
       class="featured-card group relative flex flex-col md:flex-row rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition duration-300"
       data-testid="featured-card">
        
        <!-- Thumbnail -->
        <div class="md:w-1/2 aspect-[16/9] md:aspect-auto overflow-hidden flex-shrink-0">
            <img src="<?= htmlspecialchars($fThumb) ?>"
                 alt="<?= htmlspecialchars($unggulan['judul']) ?>"
                 class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
        </div>

        <!-- Konten -->
        <div class="flex-1 p-8 md:p-10 flex flex-col justify-center text-white relative">
            <!-- Dekorasi -->
            <div class="absolute top-4 right-4 text-white/10 text-7xl font-serif select-none">✦</div>

            <span class="inline-block self-start px-3 py-1 rounded-full bg-white/15 border border-white/20 text-emerald-200 text-[10px] uppercase tracking-widest font-bold mb-4">
                <?= htmlspecialchars($unggulan['nama_kategori'] ?? 'Umum') ?>
            </span>

            <h3 class="font-serif-art text-2xl md:text-3xl font-bold leading-snug mb-4 group-hover:text-emerald-200 transition">
                <?= htmlspecialchars($unggulan['judul']) ?>
            </h3>

            <p class="text-white/70 text-sm leading-relaxed mb-6 line-clamp-3">
                <?= htmlspecialchars(mb_substr(strip_tags($unggulan['konten']), 0, 200)) ?>…
            </p>

            <div class="flex items-center gap-3 text-sm text-white/60">
                <div class="w-7 h-7 rounded-full bg-white/20 grid place-items-center font-bold text-xs text-white">
                    <?= strtoupper(substr($unggulan['nama_penulis'] ?? 'S', 0, 1)) ?>
                </div>
                <span><?= htmlspecialchars($unggulan['nama_penulis'] ?? 'Santri') ?></span>
                <span class="text-white/30">•</span>
                <span><?= date('d M Y', strtotime($unggulan['created_at'])) ?></span>
            </div>

            <div class="mt-6 self-start inline-flex items-center gap-2 text-emerald-300 font-semibold text-sm group-hover:gap-3 transition-all">
                Baca Selengkapnya
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </div>
    </a>
</section>
<?php endif; ?>

<!-- ===================== KARYA GRID ===================== -->
<section id="karya" class="max-w-6xl mx-auto px-4 py-12">

    <!-- Header section + Search + Filter -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="w-1.5 h-6 rounded-full bg-emerald-500 inline-block"></span>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-emerald-800">Semua Karya</h2>
                </div>
                <p class="text-sm text-slate-500 ml-4">Temukan tulisan-tulisan inspiratif dari para santri.</p>
            </div>

            <!-- Search -->
            <div class="search-box relative flex-shrink-0 w-full sm:w-72">
                <span class="search-icon absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1110.5 3a7.5 7.5 0 016.15 13.65z"/></svg>
                </span>
                <input type="text" id="searchInput" placeholder="Cari judul atau penulis…"
                       class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm transition">
            </div>
        </div>

        <!-- Filter Kategori (scrollable) -->
        <div class="cat-scroll overflow-x-auto pb-2 -mx-1 px-1">
            <div class="flex items-center gap-2 w-max" id="filterBtns">
                <button class="filter-btn active px-4 py-1.5 rounded-full text-sm font-semibold border border-emerald-200 bg-emerald-600 text-white whitespace-nowrap"
                        data-kat="" data-testid="filter-semua">
                    Semua
                </button>
                <?php foreach ($kategoriList as $kat): ?>
                    <button class="filter-btn px-4 py-1.5 rounded-full text-sm font-semibold border border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700 whitespace-nowrap"
                            data-kat="<?= htmlspecialchars($kat['id']) ?>"
                            data-testid="filter-<?= $kat['id'] ?>">
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Info count -->
    <p class="text-xs text-slate-400 mb-4" id="countInfo">
        Menampilkan <span id="countVisible" class="font-semibold text-emerald-600"><?= count($semuaKarya) ?></span> karya
    </p>

    <!-- Grid Karya -->
    <?php if (empty($semuaKarya)): ?>
        <div class="bg-white border border-dashed border-emerald-200 rounded-2xl p-10 text-center text-slate-500" data-testid="empty-karya">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-emerald-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="font-semibold text-slate-400">Belum ada karya yang dipublikasikan.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="karyaGrid" data-testid="karya-grid">
            <?php foreach ($semuaKarya as $row):
                $thumb = !empty($row['thumbnail']) && file_exists(__DIR__ . '/uploads/' . $row['thumbnail'])
                    ? 'uploads/' . $row['thumbnail']
                    : 'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?w=800&q=70';

                $cuplikan = mb_substr(strip_tags($row['konten']), 0, 115) . '…';
                $tanggal  = date('d M Y', strtotime($row['created_at']));
            ?>
                <a href="detail.php?slug=<?= $row['slug'] ?>"
                   class="karya-item karya-card group bg-white border border-green-100 rounded-2xl overflow-hidden shadow-sm"
                   data-kat="<?= htmlspecialchars($row['category_id'] ?? '') ?>"
                   data-judul="<?= strtolower(htmlspecialchars($row['judul'])) ?>"
                   data-penulis="<?= strtolower(htmlspecialchars($row['nama_penulis'] ?? '')) ?>"
                   data-testid="karya-card-<?= $row['id'] ?>">

                    <!-- Thumbnail -->
                    <div class="aspect-[16/10] overflow-hidden bg-green-50 relative">
                        <img src="<?= htmlspecialchars($thumb) ?>"
                             alt="<?= htmlspecialchars($row['judul']) ?>"
                             class="thumb-img w-full h-full object-cover">
                        <!-- Overlay kategori -->
                        <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/40 to-transparent">
                            <span class="inline-block px-2 py-0.5 text-[10px] uppercase tracking-wider rounded-full bg-emerald-500/90 text-white font-bold">
                                <?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?>
                            </span>
                        </div>
                    </div>

                    <!-- Konten -->
                    <div class="p-5">
                        <h3 class="font-bold text-emerald-900 text-base leading-snug mb-2 line-clamp-2 group-hover:text-emerald-600 transition">
                            <?= htmlspecialchars($row['judul']) ?>
                        </h3>

                        <p class="text-sm text-slate-500 line-clamp-2 mb-4 leading-relaxed">
                            <?= htmlspecialchars($cuplikan) ?>
                        </p>

                        <div class="flex items-center justify-between pt-3 border-t border-slate-50">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 grid place-items-center font-bold text-xs flex-shrink-0">
                                    <?= strtoupper(substr($row['nama_penulis'] ?? 'S', 0, 1)) ?>
                                </div>
                                <span class="text-xs text-slate-500 font-medium truncate max-w-[100px]">
                                    <?= htmlspecialchars($row['nama_penulis'] ?? '—') ?>
                                </span>
                            </div>
                            <span class="text-[10px] text-slate-400"><?= $tanggal ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Empty state ketika filter tidak ada hasil -->
        <div id="emptyFilter" class="hidden py-16 text-center text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mx-auto mb-3 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="font-semibold">Tidak ada karya yang cocok.</p>
            <p class="text-sm mt-1">Coba kata kunci atau kategori lain.</p>
        </div>
    <?php endif; ?>
</section>

<!-- ===================== TENTANG QOLAMUN ===================== -->
<section class="bg-white border-t border-green-100 mt-4">
    <div class="max-w-6xl mx-auto px-4 py-16">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <!-- Teks -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-1.5 h-6 rounded-full bg-emerald-500 inline-block"></span>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-emerald-800">Tentang Qolamun</h2>
                </div>
                <p class="text-slate-600 leading-relaxed mb-4">
                    <strong class="text-emerald-700">Qolamun</strong> — dari kata arab <em>قلمون</em> — adalah platform mading digital pesantren yang memfasilitasi para santri untuk mempublikasikan karya tulis mereka secara online.
                </p>
                <p class="text-slate-600 leading-relaxed mb-6">
                    Dengan Qolamun, setiap santri bisa menjadi penulis, menyebarkan ilmu, dan menginspirasi sesama melalui tulisan islami yang bermanfaat.
                </p>

                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-emerald-50 rounded-xl p-4 text-center border border-emerald-100">
                        <p class="text-2xl font-extrabold text-emerald-700"><?= $total ?></p>
                        <p class="text-xs text-slate-500 mt-0.5">Karya</p>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-4 text-center border border-emerald-100">
                        <p class="text-2xl font-extrabold text-emerald-700"><?= $totalPenulis ?></p>
                        <p class="text-xs text-slate-500 mt-0.5">Penulis</p>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-4 text-center border border-emerald-100">
                        <p class="text-2xl font-extrabold text-emerald-700"><?= $totalKat ?></p>
                        <p class="text-xs text-slate-500 mt-0.5">Kategori</p>
                    </div>
                </div>
            </div>

            <!-- Visual / CTA -->
            <div class="bg-gradient-to-br from-emerald-600 to-green-800 rounded-2xl p-8 text-white text-center relative overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="p2" x="0" y="0" width="30" height="30" patternUnits="userSpaceOnUse"><circle cx="15" cy="15" r="1.5" fill="white"/></pattern></defs><rect width="100%" height="100%" fill="url(#p2)"/></svg>
                </div>
                <div class="relative">
                    <div class="text-6xl mb-4">🖊️</div>
                    <h3 class="font-serif-art text-2xl font-bold mb-3">Ingin ikut menulis?</h3>
                    <p class="text-emerald-100 text-sm leading-relaxed mb-6">
                        Bergabunglah sebagai penulis Qolamun. Sampaikan ilmu, cerita, dan inspirasi melalui tulisanmu.
                    </p>
                    <a href="auth/login.php"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-white text-emerald-800 font-bold rounded-xl hover:bg-emerald-50 transition shadow-lg"
                       data-testid="about-cta-login">
                        Login & Mulai Menulis
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== JS FILTER & SEARCH ===================== -->
<script>
(function () {
    const filterBtns  = document.querySelectorAll('.filter-btn');
    const karyaItems  = document.querySelectorAll('.karya-item');
    const searchInput = document.getElementById('searchInput');
    const countEl     = document.getElementById('countVisible');
    const emptyEl     = document.getElementById('emptyFilter');

    let activeKat = '';
    let activeQ   = '';

    function applyFilter() {
        let visible = 0;
        karyaItems.forEach(item => {
            const kat    = item.dataset.kat ?? '';
            const judul  = item.dataset.judul ?? '';
            const penulis= item.dataset.penulis ?? '';

            const katMatch = activeKat === '' || kat === activeKat;
            const qMatch   = activeQ   === '' || judul.includes(activeQ) || penulis.includes(activeQ);

            if (katMatch && qMatch) {
                item.classList.remove('hidden-card');
                visible++;
            } else {
                item.classList.add('hidden-card');
            }
        });

        if (countEl) countEl.textContent = visible;
        if (emptyEl) emptyEl.classList.toggle('hidden', visible > 0);
    }

    // Filter kategori
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            activeKat = btn.dataset.kat ?? '';
            applyFilter();
        });
    });

    // Search
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            activeQ = searchInput.value.trim().toLowerCase();
            applyFilter();
        });
    }
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>