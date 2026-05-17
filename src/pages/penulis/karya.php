<?php
session_start();
if (($_SESSION['role'] ?? null) !== 'penulis') { header('Location: ../../auth/login.php'); exit; }
require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../classes/Penulis.php';

$penulis = new Penulis($koneksi);
$msg = $_GET['msg'] ?? null;
$aksi = $_GET['aksi'] ?? null;
$id   = (int)($_GET['id'] ?? 0);

if ($aksi === 'hapus' && $id > 0) {
    $penulis->hapusKarya($id, $_SESSION['user_id']);
    header('Location: karya.php?msg=' . urlencode('Karya dihapus.')); exit;
}

$list = $penulis->ambilKaryaSaya($_SESSION['user_id']);
$pageTitle = 'Karya Saya';
include __DIR__ . '/../../includes/header.php';
?>
<div class="flex">
    <main class="flex-1 p-6 md:p-8 max-w-7xl mx-auto w-full">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-extrabold text-emerald-800" data-testid="penulis-karya-title">Karya Saya</h1>
            <a href="form_karya.php" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 text-sm" data-testid="btn-tambah-karya">+ Tambah Karya</a>
        </div>
        <?php if ($msg): ?><div class="mb-4 px-3 py-2 rounded-lg bg-emerald-50 text-emerald-700 text-sm" data-testid="flash-msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

        <?php if (!$list || mysqli_num_rows($list)===0): ?>
            <div class="bg-white border border-dashed border-emerald-200 rounded-2xl p-10 text-center text-slate-500" data-testid="empty-karya">
                Anda belum punya karya. Yuk mulai menulis!
            </div>
        <?php else: ?>
        <div class="bg-white border border-green-100 rounded-2xl overflow-x-auto">
            <table class="w-full text-sm text-left" data-testid="karya-saya-table">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 font-bold">Judul</th>
                        <th class="px-5 py-3.5 font-bold">Kategori</th>
                        <th class="px-5 py-3.5 font-bold">Tanggal</th>
                        <th class="px-5 py-3.5 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($p = mysqli_fetch_assoc($list)): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 py-4">
                            <a href="../../detail.php?slug=<?= $p['slug'] ?>" class="font-bold text-emerald-900 hover:underline block" title="<?= htmlspecialchars($p['judul']) ?>">
                                <?= htmlspecialchars($p['judul']) ?>
                            </a>
                        </td>
                        <td class="px-5 py-4">
                            <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider">
                                <?= htmlspecialchars($p['nama_kategori'] ?? 'Umum') ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-slate-500"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                        <td class="px-5 py-3 text-right space-x-3">
                            <div class="flex items-center justify-end gap-3">
                                <a href="form_karya.php?id=<?= $p['id'] ?>" class="text-emerald-500 hover:text-emerald-700 transition" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>

                                <a href="?aksi=hapus&id=<?= $p['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>