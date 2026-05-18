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

// Kumpulkan semua karya ke dalam array
$semuaKarya = [];
while ($row = mysqli_fetch_assoc($result)) {
    $semuaKarya[] = $row;
}

// --- LOGIKA PEMBAGIAN KONTEN YANG DIPERBAIKI ---

// 1. Filter Kategori Super Ketat (Tidak ada campuran)
$beritaList = [];
$artikelList = [];

foreach ($semuaKarya as $k) {
    $kn = strtolower($k['nama_kategori'] ?? '');
    
    // Jika nama kategori mengandung kata 'berita'
    if (strpos($kn, 'berita') !== false) {
        $beritaList[] = $k;
    } 
    // Jika nama kategori mengandung kata 'artikel'
    elseif (strpos($kn, 'artikel') !== false) {
        $artikelList[] = $k;
    }
}

// Batasi jumlah yang tampil
$beritaList = array_slice($beritaList, 0, 4);
$artikelList = array_slice($artikelList, 0, 6);

// 2. Logika Headline & Carousel Dinamis
$carouselItems = array_slice($semuaKarya, 0, 4); // 4 untuk carousel
$sideHeadlines = array_slice($semuaKarya, 4, 4); // Sisa 4 untuk samping

// Jika samping kosong (karena artikel < 5), buat carousel full width!
$carouselWidthClass = empty($sideHeadlines) ? 'lg:col-span-3' : 'lg:col-span-2';

// 3. Logika Karya Pilihan (Acak agar selalu terlihat penuh/dinamis)
$pilihanSidebar = $semuaKarya;
shuffle($pilihanSidebar); // Acak urutannya
$pilihanSidebar = array_slice($pilihanSidebar, 0, 5); // Ambil maksimal 5

// Fungsi utilitas untuk gambar
function getThumb($row) {
    if (!empty($row['thumbnail']) && file_exists(__DIR__ . '/uploads/' . $row['thumbnail'])) {
        return 'uploads/' . $row['thumbnail'];
    }
    return 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?w=800&q=80';
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
.animate-fadeup { animation: fadeUp 0.6s ease both; }

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

.hover-img-zoom:hover img { transform: scale(1.05); }
.karya-card { transition: transform 0.3s cubic-bezier(.22,.68,0,1.2), box-shadow 0.3s ease; }
.karya-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -10px rgba(4,120,87,0.15); }
.cat-scroll::-webkit-scrollbar { height: 3px; }
.cat-scroll::-webkit-scrollbar-thumb { background: #d1fae5; border-radius: 999px; }
.filter-btn.active { background-color: #059669; color: white; border-color: #059669; }
.karya-item.hidden-card { display: none; }
</style>

<div class="bg-emerald-900 text-emerald-200 text-xs py-2 overflow-hidden marquee-wrap select-none border-b border-emerald-800">
    <div class="marquee-track gap-12" style="gap: 4rem;">
        <?php
        $tickerItems = array_slice($semuaKarya, 0, 15);
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

<section class="max-w-7xl mx-auto px-4 pt-6 pb-8">
    <div class="grid lg:grid-cols-3 gap-4 h-auto lg:h-[480px]">
        <?php if (!empty($carouselItems)): ?>
        <div class="<?= $carouselWidthClass ?> relative rounded-2xl overflow-hidden group h-[300px] lg:h-full" id="mainCarousel">
            <?php foreach ($carouselItems as $index => $item): ?>
            <a href="detail.php?slug=<?= $item['slug'] ?>" 
               class="carousel-slide absolute inset-0 w-full h-full block transition-opacity duration-1000 ease-in-out hover-img-zoom <?= $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' ?>">
                <img src="<?= htmlspecialchars(getThumb($item)) ?>" class="absolute inset-0 w-full h-full object-cover transition duration-700" alt="">
                <div class="absolute inset-0 bg-gradient-to-t from-emerald-950/90 via-emerald-900/40 to-transparent"></div>
                
                <div class="absolute bottom-0 left-0 right-0 p-6 lg:p-8">
                    <span class="inline-block px-3 py-1 bg-emerald-600 text-white text-[10px] font-bold uppercase tracking-wider rounded-md mb-3">
                        <?= htmlspecialchars($item['nama_kategori'] ?? 'Berita') ?>
                    </span>
                    <h1 class="text-2xl lg:text-4xl font-serif-art font-bold text-white leading-tight mb-3 group-hover:text-emerald-200 transition max-w-4xl">
                        <?= htmlspecialchars($item['judul']) ?>
                    </h1>
                    <div class="flex items-center gap-3 text-white/70 text-xs font-medium">
                        <span>Oleh <?= htmlspecialchars($item['nama_penulis'] ?? 'Santri') ?></span>
                        <span>•</span>
                        <span><?= date('d M Y', strtotime($item['created_at'])) ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
            
            <?php if(count($carouselItems) > 1): ?>
            <div class="absolute bottom-4 right-6 z-20 flex gap-2">
                <?php foreach ($carouselItems as $index => $item): ?>
                <button class="carousel-dot w-2 h-2 rounded-full transition-all <?= $index === 0 ? 'bg-white w-5' : 'bg-white/50 hover:bg-white/80' ?>" data-slide="<?= $index ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($sideHeadlines)): ?>
        <div class="grid grid-rows-4 gap-4 h-full hidden lg:grid">
            <?php foreach ($sideHeadlines as $sh): ?>
            <a href="detail.php?slug=<?= $sh['slug'] ?>" class="group flex gap-4 bg-slate-50 hover:bg-emerald-50 rounded-xl p-3 border border-slate-100 transition items-center overflow-hidden hover-img-zoom">
                <div class="w-24 h-full rounded-lg overflow-hidden flex-shrink-0 relative">
                    <img src="<?= htmlspecialchars(getThumb($sh)) ?>" class="absolute inset-0 w-full h-full object-cover transition duration-500" alt="">
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mb-1 block">
                        <?= htmlspecialchars($sh['nama_kategori'] ?? 'Umum') ?>
                    </span>
                    <h3 class="font-bold text-slate-800 text-sm leading-snug line-clamp-2 group-hover:text-emerald-700 transition">
                        <?= htmlspecialchars($sh['judul']) ?>
                    </h3>
                    <p class="text-[10px] text-slate-400 mt-2"><?= date('d M Y', strtotime($sh['created_at'])) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 mb-8 hidden md:block">
    <div class="bg-gradient-to-r from-emerald-800 to-green-700 rounded-2xl p-4 flex items-center justify-between shadow-lg shadow-emerald-900/10 text-white">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
            <div>
                <h4 class="font-bold text-sm">Jadwal Sholat & Imsak</h4>
                <p class="text-xs text-emerald-200"><?= date('d F Y') ?> / 15 Dzulqa'dah 1447 H</p>
            </div>
        </div>
        <div class="flex gap-6 text-center">
            <div><p class="text-[10px] text-emerald-200 uppercase tracking-wider">Subuh</p><p class="font-bold">04:30</p></div>
            <div><p class="text-[10px] text-emerald-200 uppercase tracking-wider">Dzuhur</p><p class="font-bold">11:45</p></div>
            <div><p class="text-[10px] text-emerald-200 uppercase tracking-wider">Ashar</p><p class="font-bold">15:02</p></div>
            <div><p class="text-[10px] text-emerald-200 uppercase tracking-wider">Maghrib</p><p class="font-bold">17:50</p></div>
            <div><p class="text-[10px] text-emerald-200 uppercase tracking-wider">Isya</p><p class="font-bold">19:02</p></div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-6">
    <div class="grid lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-12">
            
            <div>
                <div class="flex items-center justify-between mb-6 border-b-2 border-slate-100 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="w-1.5 h-6 rounded-full bg-emerald-500 inline-block"></span>
                        <h2 class="text-2xl font-extrabold text-emerald-900 uppercase tracking-wide">Berita Pesantren</h2>
                    </div>
                </div>
                
                <div class="flex flex-col gap-5">
                    <?php if(empty($beritaList)): ?>
                        <div class="bg-slate-50 border border-dashed border-slate-200 rounded-xl p-8 text-center text-slate-400 text-sm">
                            Belum ada publikasi untuk kategori Berita Pesantren.
                        </div>
                    <?php else: ?>
                        <?php foreach ($beritaList as $berita): ?>
                        <a href="detail.php?slug=<?= $berita['slug'] ?>" class="group flex flex-col sm:flex-row gap-5 items-start hover-img-zoom">
                            <div class="w-full sm:w-48 aspect-video sm:aspect-square rounded-xl overflow-hidden relative flex-shrink-0">
                                <img src="<?= htmlspecialchars(getThumb($berita)) ?>" class="absolute inset-0 w-full h-full object-cover transition duration-500" alt="">
                            </div>
                            <div class="flex-1 py-1">
                                <div class="flex items-center gap-2 mb-2 text-xs text-slate-500 font-medium">
                                    <span class="text-emerald-600 font-bold"><?= htmlspecialchars($berita['nama_kategori'] ?? 'Berita') ?></span>
                                    <span>•</span>
                                    <span><?= date('d M Y', strtotime($berita['created_at'])) ?></span>
                                </div>
                                <h3 class="text-xl font-bold text-slate-800 leading-snug mb-2 group-hover:text-emerald-600 transition">
                                    <?= htmlspecialchars($berita['judul']) ?>
                                </h3>
                                <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed">
                                    <?= htmlspecialchars(mb_substr(strip_tags($berita['konten']), 0, 150)) ?>…
                                </p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-6 border-b-2 border-slate-100 pb-3">
                    <div class="flex items-center gap-3">
                        <span class="w-1.5 h-6 rounded-full bg-emerald-500 inline-block"></span>
                        <h2 class="text-2xl font-extrabold text-emerald-900 uppercase tracking-wide">Artikel Islami</h2>
                    </div>
                </div>
                
                <?php if(empty($artikelList)): ?>
                    <div class="bg-slate-50 border border-dashed border-slate-200 rounded-xl p-8 text-center text-slate-400 text-sm">
                        Belum ada publikasi untuk kategori Artikel Islami.
                    </div>
                <?php else: ?>
                    <div class="grid sm:grid-cols-2 gap-6">
                        <?php foreach ($artikelList as $artikel): ?>
                        <a href="detail.php?slug=<?= $artikel['slug'] ?>" class="group block hover-img-zoom">
                            <div class="w-full aspect-[16/10] rounded-xl overflow-hidden relative mb-4">
                                <img src="<?= htmlspecialchars(getThumb($artikel)) ?>" class="absolute inset-0 w-full h-full object-cover transition duration-500" alt="">
                                <div class="absolute top-2 left-2 px-2 py-1 bg-white/90 backdrop-blur text-emerald-800 text-[10px] font-bold uppercase tracking-wider rounded">
                                    <?= htmlspecialchars($artikel['nama_kategori'] ?? 'Artikel') ?>
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-slate-800 leading-snug mb-2 group-hover:text-emerald-600 transition line-clamp-2">
                                <?= htmlspecialchars($artikel['judul']) ?>
                            </h3>
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 grid place-items-center font-bold text-[10px]">
                                    <?= strtoupper(substr($artikel['nama_penulis'] ?? 'S', 0, 1)) ?>
                                </div>
                                <span><?= htmlspecialchars($artikel['nama_penulis'] ?? 'Santri') ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-8">
            
            <div class="bg-gradient-to-br from-emerald-900 to-green-900 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/20 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
                <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    Statistik Qolamun
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-white/10">
                        <span class="text-emerald-200 text-sm">Total Karya</span>
                        <span class="font-bold text-xl"><?= $total ?></span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-white/10">
                        <span class="text-emerald-200 text-sm">Penulis Aktif</span>
                        <span class="font-bold text-xl"><?= $totalPenulis ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-200 text-sm">Kategori Topik</span>
                        <span class="font-bold text-xl"><?= $totalKat ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm">
                <h3 class="font-extrabold text-emerald-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <span class="w-1 h-4 bg-emerald-500 rounded-full"></span>
                    Karya Pilihan
                </h3>
                <div class="flex flex-col gap-4">
                    <?php if(empty($pilihanSidebar)): ?>
                        <p class="text-slate-400 text-xs italic">Belum ada karya pilihan.</p>
                    <?php else: ?>
                        <?php $no = 1; foreach ($pilihanSidebar as $ps): ?>
                        <a href="detail.php?slug=<?= $ps['slug'] ?>" class="group flex gap-3 items-start">
                            <div class="text-2xl font-serif-art font-bold text-emerald-200 group-hover:text-emerald-400 transition w-6 text-center leading-none mt-1">
                                <?= $no++ ?>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-700 text-sm leading-snug group-hover:text-emerald-600 transition line-clamp-2 mb-1">
                                    <?= htmlspecialchars($ps['judul']) ?>
                                </h4>
                                <p class="text-[10px] text-slate-400"><?= htmlspecialchars($ps['nama_kategori'] ?? 'Umum') ?> • <?= date('d M Y', strtotime($ps['created_at'])) ?></p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6 text-center relative overflow-hidden">
                <div class="text-4xl text-emerald-200 absolute top-2 left-4 font-serif">"</div>
                <p class="text-emerald-900 font-serif-art text-lg italic leading-relaxed mb-3 relative z-10">
                    "Barangsiapa menempuh suatu jalan untuk menuntut ilmu, maka Allah akan mudahkan baginya jalan menuju Surga."
                </p>
                <p class="text-xs font-bold text-emerald-700 uppercase tracking-wider">— HR. Muslim</p>
            </div>

        </div>
    </div>
</section>

<section class="bg-white border-t border-slate-200 mt-12 py-16">
    <div class="max-w-7xl mx-auto px-2">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-1.5 h-6 rounded-full bg-emerald-500 inline-block"></span>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-emerald-800 uppercase tracking-wide">Tentang Qolamun</h2>
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

            <div class="bg-gradient-to-br from-emerald-600 to-green-800 rounded-2xl p-8 text-white text-center relative overflow-hidden shadow-xl">
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

<section id="karya" class="bg-slate-50 border-t border-slate-200 mt-8 py-12">
    <div class="max-w-7xl mx-auto px-4">
        
        <div class="text-center mb-10">
            <h2 class="text-3xl font-extrabold text-emerald-900 mb-3">Arsip Semua Karya</h2>
            <p class="text-slate-500 text-sm max-w-xl mx-auto">Telusuri seluruh karya tulis, opini, dan berita yang telah diterbitkan oleh para santri.</p>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
            <div class="cat-scroll overflow-x-auto w-full md:w-auto pb-2">
                <div class="flex items-center gap-2 w-max">
                    <button class="filter-btn active px-4 py-1.5 rounded-full text-sm font-semibold border border-emerald-200 bg-emerald-600 text-white transition" data-kat="">Semua</button>
                    <?php foreach ($kategoriList as $kat): ?>
                        <button class="filter-btn px-4 py-1.5 rounded-full text-sm font-semibold border border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:text-emerald-700 whitespace-nowrap transition"
                                data-kat="<?= htmlspecialchars($kat['id']) ?>">
                            <?= htmlspecialchars($kat['nama_kategori']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="relative w-full md:w-72">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16.65 16.65A7.5 7.5 0 1110.5 3a7.5 7.5 0 016.15 13.65z"/></svg>
                </span>
                <input type="text" id="searchInput" placeholder="Cari karya..."
                       class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-xl bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm transition">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" id="karyaGrid">
            <?php foreach ($semuaKarya as $row): ?>
                <a href="detail.php?slug=<?= $row['slug'] ?>"
                   class="karya-item karya-card group bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm flex flex-col"
                   data-kat="<?= htmlspecialchars($row['category_id'] ?? '') ?>"
                   data-judul="<?= strtolower(htmlspecialchars($row['judul'])) ?>"
                   data-penulis="<?= strtolower(htmlspecialchars($row['nama_penulis'] ?? '')) ?>">

                    <div class="aspect-[16/10] overflow-hidden bg-slate-100 relative">
                        <img src="<?= htmlspecialchars(getThumb($row)) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" alt="">
                        <div class="absolute bottom-2 left-2">
                            <span class="px-2 py-1 text-[10px] uppercase tracking-wider rounded bg-emerald-600/90 text-white font-bold backdrop-blur-sm">
                                <?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-4 flex flex-col flex-1">
                        <h3 class="font-bold text-slate-800 text-sm leading-snug mb-2 line-clamp-2 group-hover:text-emerald-600 transition">
                            <?= htmlspecialchars($row['judul']) ?>
                        </h3>
                        <div class="mt-auto pt-3 flex items-center justify-between text-[10px] text-slate-400 border-t border-slate-50">
                            <span class="font-medium text-slate-600 truncate max-w-[100px]"><?= htmlspecialchars($row['nama_penulis'] ?? 'Santri') ?></span>
                            <span><?= date('d M', strtotime($row['created_at'])) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div id="emptyFilter" class="hidden py-10 text-center text-slate-400">
            <p class="font-semibold">Tidak ada karya yang cocok.</p>
        </div>

    </div>
</section>

<script>
(function () {
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.carousel-dot');
    let currentSlide = 0;
    let slideInterval;

    function showSlide(index) {
        slides.forEach((slide, i) => {
            if (i === index) {
                slide.classList.remove('opacity-0', 'z-0');
                slide.classList.add('opacity-100', 'z-10');
                if(dots[i]) {
                    dots[i].classList.remove('bg-white/50');
                    dots[i].classList.add('bg-white', 'w-5');
                }
            } else {
                slide.classList.add('opacity-0', 'z-0');
                slide.classList.remove('opacity-100', 'z-10');
                if(dots[i]) {
                    dots[i].classList.add('bg-white/50');
                    dots[i].classList.remove('bg-white', 'w-5');
                }
            }
        });
        currentSlide = index;
    }

    function nextSlide() {
        if(slides.length <= 1) return;
        let next = (currentSlide + 1) % slides.length;
        showSlide(next);
    }

    if (slides.length > 1) {
        slideInterval = setInterval(nextSlide, 5000);
        dots.forEach((dot, index) => {
            dot.addEventListener('click', (e) => {
                e.preventDefault();
                clearInterval(slideInterval);
                showSlide(index);
                slideInterval = setInterval(nextSlide, 5000);
            });
        });
    }

    // Filter Logic
    const filterBtns  = document.querySelectorAll('.filter-btn');
    const karyaItems  = document.querySelectorAll('.karya-item');
    const searchInput = document.getElementById('searchInput');
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

        if (emptyEl) emptyEl.classList.toggle('hidden', visible > 0);
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => {
                b.classList.remove('active', 'bg-emerald-600', 'text-white');
                b.classList.add('bg-white', 'text-slate-600');
            });
            btn.classList.remove('bg-white', 'text-slate-600');
            btn.classList.add('active', 'bg-emerald-600', 'text-white');
            activeKat = btn.dataset.kat ?? '';
            applyFilter();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            activeQ = searchInput.value.trim().toLowerCase();
            applyFilter();
        });
    }
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>