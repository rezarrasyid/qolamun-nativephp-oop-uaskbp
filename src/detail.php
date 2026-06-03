<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/classes/Karya.php';

$id = (int)($_GET['id'] ?? 0);
$slug = $_GET['slug'] ?? '';
$karyaObj = new Karya($koneksi);

if ($id > 0) {
    $karya = $karyaObj->ambilKaryaById($id);
} else {
    $karya = $karyaObj->ambilKaryaBySlug($slug);
}

if (!$karya) {
    http_response_code(404);
    echo "Karya tidak ditemukan."; 
    exit;
}

$pageTitle = $karya['judul'];
include __DIR__ . '/includes/header.php';

$thumb = !empty($karya['thumbnail']) && file_exists(__DIR__ . '/uploads/' . $karya['thumbnail'])
    ? 'uploads/' . $karya['thumbnail']
    : 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?w=800&q=80';

// Ambil karya terkait (kategori sama, exclude karya ini)
$kategoriId = $karya['category_id'] ?? null;
$currentId  = $karya['id'] ?? null;

$karyaTerkait = [];
if ($kategoriId) {
    $q = mysqli_prepare($koneksi,
        "SELECT p.id, p.judul, p.slug, p.thumbnail, p.created_at,
                c.nama_kategori, u.nama AS nama_penulis
         FROM posts p
         LEFT JOIN categories c ON p.category_id = c.id
         LEFT JOIN users u ON p.user_id = u.id
         WHERE p.category_id = ? AND p.id != ?
         ORDER BY p.created_at DESC
         LIMIT 4"
    );
    if ($q) {
        mysqli_stmt_bind_param($q, 'ii', $kategoriId, $currentId);
        mysqli_stmt_execute($q);
        $res = mysqli_stmt_get_result($q);
        while ($r = mysqli_fetch_assoc($res)) $karyaTerkait[] = $r;
        mysqli_stmt_close($q);
    }
}

// Karya populer/acak untuk sidebar
$karyaSidebar = [];
$qSide = mysqli_prepare($koneksi,
    "SELECT p.id, p.judul, p.slug, p.thumbnail, p.created_at,
            c.nama_kategori, u.nama AS nama_penulis
     FROM posts p
     LEFT JOIN categories c ON p.category_id = c.id
     LEFT JOIN users u ON p.user_id = u.id
     WHERE p.id != ?
     ORDER BY RAND()
     LIMIT 6"
);
if ($qSide) {
    mysqli_stmt_bind_param($qSide, 'i', $currentId);
    mysqli_stmt_execute($qSide);
    $resSide = mysqli_stmt_get_result($qSide);
    while ($r = mysqli_fetch_assoc($resSide)) $karyaSidebar[] = $r;
    mysqli_stmt_close($qSide);
}

function getThumbDetail($row, $baseDir) {
    if (!empty($row['thumbnail']) && file_exists($baseDir . '/uploads/' . $row['thumbnail'])) {
        return 'uploads/' . $row['thumbnail'];
    }
    return 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?w=800&q=80';
}

$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$encodedUrl   = urlencode($currentUrl);
$encodedJudul = urlencode($karya['judul']);
?>

<style>
.konten-detail h2 { font-size: 1.5rem; font-weight: 700; color: #064e3b; margin: 2rem 0 0.75rem; }
.konten-detail h3 { font-size: 1.2rem; font-weight: 600; color: #065f46; margin: 1.5rem 0 0.5rem; }
.konten-detail p  { margin-bottom: 1.25rem; line-height: 1.85; }
.konten-detail blockquote {
    border-left: 4px solid #059669;
    background: #f0fdf4;
    padding: 1rem 1.25rem;
    margin: 1.5rem 0;
    border-radius: 0 0.5rem 0.5rem 0;
    color: #065f46;
    font-style: italic;
}
.konten-detail img { border-radius: 0.75rem; max-width: 100%; margin: 1.5rem auto; display: block; }
.konten-detail ul, .konten-detail ol { padding-left: 1.5rem; margin-bottom: 1.25rem; }
.konten-detail li { margin-bottom: 0.4rem; }
.konten-detail a { color: #059669; text-decoration: underline; }

/* Share buttons */
.share-btn {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.45rem 1rem; border-radius: 999px; font-size: 0.78rem;
    font-weight: 600; transition: all 0.2s; cursor: pointer; border: none;
}
.share-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

/* Sidebar card */
.sidebar-card:hover .sidebar-title { color: #059669; }
.sidebar-card:hover img { transform: scale(1.05); }

/* Artikel terkait */
.terkait-card { transition: transform 0.25s ease, box-shadow 0.25s ease; }
.terkait-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px -8px rgba(4,120,87,0.18); }
.terkait-card:hover img { transform: scale(1.06); }

/* Copy toast */
#copyToast {
    position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%) translateY(60px);
    background: #064e3b; color: white; padding: 0.6rem 1.4rem;
    border-radius: 999px; font-size: 0.8rem; font-weight: 600;
    opacity: 0; transition: all 0.35s ease; z-index: 9999;
    pointer-events: none;
}
#copyToast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
</style>

<div id="copyToast">🔗 Link berhasil disalin!</div>

<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-10">

        <!-- ===================== KONTEN UTAMA ===================== -->
        <article class="flex-1 min-w-0">

            <a href="index.php" class="text-sm text-emerald-700 hover:text-emerald-800 font-medium mb-6 inline-flex items-center gap-1 transition" data-testid="detail-back">
                <span>&larr;</span> Kembali ke Beranda
            </a>

            <!-- Badge Kategori -->
            <div class="mb-3 mt-4">
                <span class="inline-block px-3 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full bg-emerald-100 text-emerald-700">
                    <?= htmlspecialchars($karya['nama_kategori'] ?? 'Umum') ?>
                </span>
            </div>

            <!-- Judul & Meta -->
            <header class="mb-7">
                <h1 class="font-serif-art text-3xl md:text-[2.6rem] font-bold text-emerald-900 leading-tight mb-4" data-testid="detail-title">
                    <?= htmlspecialchars($karya['judul']) ?>
                </h1>
                <div class="flex items-center gap-3 text-sm text-slate-500">
                    <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 grid place-items-center font-bold text-base flex-shrink-0">
                        <?= strtoupper(substr($karya['nama_penulis'] ?? 'U', 0, 1)) ?>
                    </div>
                    <p>
                        Oleh <span class="font-semibold text-emerald-700"><?= htmlspecialchars($karya['nama_penulis'] ?? 'Anonim') ?></span>
                        <span class="mx-1 text-slate-300">•</span>
                        <?= date('d M Y', strtotime($karya['created_at'])) ?>
                    </p>
                </div>
            </header>

            <!-- Thumbnail -->
            <?php if ($thumb): ?>
            <div class="mb-8">
                <img src="<?= htmlspecialchars($thumb) ?>"
                     alt="<?= htmlspecialchars($karya['judul']) ?>"
                     class="w-full max-h-[460px] rounded-2xl shadow-sm border border-green-100 object-cover">
            </div>
            <?php endif; ?>

            <!-- Konten Artikel -->
            <div class="konten-detail text-slate-800 text-[17.5px] leading-relaxed" data-testid="detail-content">
                <?= $karya['konten'] ?>
            </div>

            <!-- ===== SHARE BUTTONS ===== -->
            <div class="mt-10 pt-8 border-t border-green-100">
                <p class="text-sm font-bold text-slate-600 mb-4 uppercase tracking-wider">Bagikan Tulisan Ini</p>
                <div class="flex flex-wrap gap-2">

                    <!-- WhatsApp -->
                    <a href="https://wa.me/?text=<?= $encodedJudul ?>%20-%20<?= $encodedUrl ?>"
                       target="_blank" rel="noopener"
                       class="share-btn text-white"
                       style="background:#25D366;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.116.554 4.105 1.524 5.832L0 24l6.335-1.505A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.802 9.802 0 01-5.002-1.37l-.36-.213-3.76.893.952-3.664-.234-.375A9.785 9.785 0 012.182 12C2.182 6.578 6.578 2.182 12 2.182S21.818 6.578 21.818 12 17.422 21.818 12 21.818z"/>
                        </svg>
                        WhatsApp
                    </a>

                    <!-- Telegram -->
                    <a href="https://t.me/share/url?url=<?= $encodedUrl ?>&text=<?= $encodedJudul ?>"
                       target="_blank" rel="noopener"
                       class="share-btn text-white"
                       style="background:#2AABEE;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                        Telegram
                    </a>

                    <!-- Twitter / X -->
                    <a href="https://twitter.com/intent/tweet?text=<?= $encodedJudul ?>&url=<?= $encodedUrl ?>"
                       target="_blank" rel="noopener"
                       class="share-btn text-white"
                       style="background:#000;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.736-8.848L1.254 2.25H8.08l4.262 5.635L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/>
                        </svg>
                        X / Twitter
                    </a>

                    <!-- Facebook -->
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>"
                       target="_blank" rel="noopener"
                       class="share-btn text-white"
                       style="background:#1877F2;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        Facebook
                    </a>

                    <!-- Copy Link -->
                    <button onclick="copyLink('<?= htmlspecialchars($currentUrl, ENT_QUOTES) ?>')"
                            class="share-btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Salin Link
                    </button>
                </div>
            </div>

            <!-- Footer artikel -->
            <footer class="mt-8 pt-6 border-t border-green-100">
                <p class="text-sm text-slate-400 italic text-center">
                    Barakallahu fiikum. Semoga tulisan ini bermanfaat bagi umat.
                </p>
            </footer>

            <!-- ===== ARTIKEL TERKAIT ===== -->
            <?php if (!empty($karyaTerkait)): ?>
            <section class="mt-12">
                <div class="flex items-center gap-3 mb-6">
                    <span class="block w-1 h-6 rounded-full bg-emerald-600"></span>
                    <h2 class="text-xl font-bold text-emerald-900">Artikel Terkait</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <?php foreach ($karyaTerkait as $t):
                        $tThumb = getThumbDetail($t, __DIR__);
                    ?>
                    <a href="detail.php?slug=<?= htmlspecialchars($t['slug']) ?>"
                       class="terkait-card group flex gap-4 bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm">
                        <div class="w-28 flex-shrink-0 overflow-hidden">
                            <img src="<?= htmlspecialchars($tThumb) ?>"
                                 alt="<?= htmlspecialchars($t['judul']) ?>"
                                 class="w-full h-full object-cover transition duration-500">
                        </div>
                        <div class="p-3 flex flex-col justify-center min-w-0">
                            <span class="text-[9px] font-bold uppercase tracking-widest text-emerald-600 mb-1">
                                <?= htmlspecialchars($t['nama_kategori'] ?? 'Umum') ?>
                            </span>
                            <h3 class="text-sm font-bold text-slate-800 leading-snug line-clamp-3 group-hover:text-emerald-700 transition">
                                <?= htmlspecialchars($t['judul']) ?>
                            </h3>
                            <p class="text-[11px] text-slate-400 mt-2">
                                <?= htmlspecialchars($t['nama_penulis'] ?? 'Santri') ?> · <?= date('d M Y', strtotime($t['created_at'])) ?>
                            </p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

        </article>

        <!-- ===================== SIDEBAR ===================== -->
        <aside class="w-full lg:w-80 flex-shrink-0">
            <div class="sticky top-6 space-y-8">

                <!-- Karya Pilihan -->
                <?php if (!empty($karyaSidebar)): ?>
                <div class="bg-white border border-slate-100 rounded-2xl shadow-sm overflow-hidden">
                    <div class="bg-emerald-700 px-5 py-3 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-300 inline-block"></span>
                        <h3 class="text-white font-bold text-sm uppercase tracking-wider">Karya Lainnya</h3>
                    </div>
                    <div class="divide-y divide-slate-50">
                        <?php foreach ($karyaSidebar as $s):
                            $sThumb = getThumbDetail($s, __DIR__);
                        ?>
                        <a href="detail.php?slug=<?= htmlspecialchars($s['slug']) ?>"
                           class="sidebar-card flex gap-3 p-4 hover:bg-slate-50 transition group">
                            <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0">
                                <img src="<?= htmlspecialchars($sThumb) ?>"
                                     alt=""
                                     class="w-full h-full object-cover transition duration-500">
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-600 mb-0.5 block">
                                    <?= htmlspecialchars($s['nama_kategori'] ?? 'Umum') ?>
                                </span>
                                <p class="sidebar-title text-slate-800 text-[13px] font-semibold leading-snug line-clamp-3 transition">
                                    <?= htmlspecialchars($s['judul']) ?>
                                </p>
                                <p class="text-[10px] text-slate-400 mt-1.5">
                                    <?= date('d M Y', strtotime($s['created_at'])) ?>
                                </p>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="p-4 border-t border-slate-50">
                        <a href="index.php#karya" class="block text-center text-sm font-semibold text-emerald-700 hover:text-emerald-800 transition">
                            Lihat Semua Karya &rarr;
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Bagikan via sidebar (shortcut) -->
                <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-5">
                    <p class="text-sm font-bold text-emerald-800 mb-3">📢 Sebarkan Kebaikan</p>
                    <p class="text-xs text-emerald-700 mb-4 leading-relaxed">
                        Bantu menyebarkan karya santri agar lebih banyak yang mengambil manfaat.
                    </p>
                    <div class="flex gap-2 flex-wrap">
                        <a href="https://wa.me/?text=<?= $encodedJudul ?>%20-%20<?= $encodedUrl ?>"
                           target="_blank" rel="noopener"
                           class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-white text-xs font-bold transition hover:opacity-90"
                           style="background:#25D366; min-width:80px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.116.554 4.105 1.524 5.832L0 24l6.335-1.505A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.802 9.802 0 01-5.002-1.37l-.36-.213-3.76.893.952-3.664-.234-.375A9.785 9.785 0 012.182 12C2.182 6.578 6.578 2.182 12 2.182S21.818 6.578 21.818 12 17.422 21.818 12 21.818z"/></svg>
                            WA
                        </a>
                        <a href="https://t.me/share/url?url=<?= $encodedUrl ?>&text=<?= $encodedJudul ?>"
                           target="_blank" rel="noopener"
                           class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-white text-xs font-bold transition hover:opacity-90"
                           style="background:#2AABEE; min-width:80px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                            Telegram
                        </a>
                        <button onclick="copyLink('<?= htmlspecialchars($currentUrl, ENT_QUOTES) ?>')"
                                class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-slate-200 text-slate-700 text-xs font-bold transition hover:bg-slate-300" style="min-width:80px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Salin
                        </button>
                    </div>
                </div>

            </div>
        </aside>

    </div>
</div>

<script>
function copyLink(url) {
    navigator.clipboard.writeText(url).then(function() {
        const toast = document.getElementById('copyToast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>