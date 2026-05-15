<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/classes/Karya.php';

$karyaObj = new Karya($koneksi); 
$result   = $karyaObj->ambilSemuaKarya();
$total    = $karyaObj->hitungSemua();

$pageTitle = 'Beranda';
include __DIR__ . '/includes/header.php';
?>

<!-- HERO SECTION -->
<section class="bg-gradient-to-br from-emerald-600 via-emerald-700 to-green-800 text-white">
    <div class="max-w-6xl mx-auto px-4 py-16 md:py-24 grid md:grid-cols-2 gap-8 items-center">
        <div>
            <p class="uppercase tracking-[0.25em] text-emerald-200 text-xs mb-3">Mading Pesantren Digital</p>
            <h1 class="font-serif-art text-4xl md:text-5xl font-bold leading-tight mb-4">
                Setiap goresan pena<br>adalah <span class="italic">dakwah</span>.
            </h1>
            <p class="text-emerald-100 max-w-md leading-relaxed mb-6">
                Qolamun adalah ruang digital tempat santri menuangkan karya: artikel islami, cerpen, puisi, hingga berita pesantren.
            </p>
            <div class="flex items-center gap-3">
                <a href="#karya" class="px-5 py-2.5 bg-white text-emerald-700 font-semibold rounded-lg hover:bg-emerald-50" data-testid="hero-cta-explore">
                    Jelajahi Karya
                </a>
                <a href="auth/login.php" class="px-5 py-2.5 border border-emerald-300/40 rounded-lg hover:bg-white/10" data-testid="hero-cta-login">
                    Login Penulis
                </a>
            </div>
        </div>
        <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6">
            <p class="text-emerald-100 text-sm mb-2">Total Karya Terbit</p>
            <p class="text-6xl font-extrabold" data-testid="total-karya"><?= $total ?></p>
            <p class="text-emerald-200 text-sm mt-2">Dari santri-santri pesantren kita.</p>
        </div>
    </div>
</section>

<!-- KARYA SECTION -->
<section id="karya" class="max-w-6xl mx-auto px-4 py-12">
    <div class="flex items-end justify-between mb-8">
        <div>
            <h2 class="text-2xl md:text-3xl font-extrabold text-emerald-800">Karya Terbaru</h2>
            <p class="text-sm text-slate-500">Tulisan-tulisan terbaru dari para santri.</p>
        </div>
    </div>

    <?php if (!$result || mysqli_num_rows($result) === 0): ?>
        <div class="bg-white border border-dashed border-emerald-200 rounded-2xl p-10 text-center text-slate-500" data-testid="empty-karya">
            Belum ada karya yang dipublikasikan.
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" data-testid="karya-grid">
            <?php while ($row = mysqli_fetch_assoc($result)):
                // Penentuan Thumbnail
                $thumb = !empty($row['thumbnail']) && file_exists(__DIR__ . '/uploads/' . $row['thumbnail'])
                    ? 'uploads/' . $row['thumbnail']
                    : 'https://images.unsplash.com/photo-1499209974431-9dddcece7f88?w=800&q=70';
                
                // Pembuatan Cuplikan Konten
                $cuplikan = mb_substr(strip_tags($row['konten']), 0, 110) . '…';
            ?>
                <a href="detail.php?id=<?= $row['id'] ?>" 
                   class="group bg-white border border-green-100 rounded-2xl overflow-hidden hover:shadow-xl hover:-translate-y-1 transition duration-300" 
                   data-testid="karya-card-<?= $row['id'] ?>">
                    
                    <div class="aspect-[16/10] overflow-hidden bg-green-50">
                        <img src="<?= htmlspecialchars($thumb) ?>" 
                             alt="<?= htmlspecialchars($row['judul']) ?>" 
                             class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                    </div>
                    
                    <div class="p-5">
                        <span class="inline-block px-2 py-0.5 text-[10px] uppercase tracking-wider rounded-full bg-emerald-100 text-emerald-700 mb-3">
                            <?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?>
                        </span>
                        
                        <h3 class="font-bold text-emerald-900 text-lg leading-snug mb-2 line-clamp-2 group-hover:text-emerald-700 transition">
                            <?= htmlspecialchars($row['judul']) ?>
                        </h3>
                        
                        <p class="text-sm text-slate-600 line-clamp-2 mb-4">
                            <?= htmlspecialchars($cuplikan) ?>
                        </p>
                        
                        <div class="flex items-center gap-2 pt-2 border-t border-slate-50">
                            <p class="text-xs text-slate-500">
                                oleh <span class="font-semibold text-emerald-700"><?= htmlspecialchars($row['nama_penulis'] ?? '—') ?></span>
                            </p>
                        </div>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>