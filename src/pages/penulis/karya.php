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
    <?php include __DIR__ . '/../../includes/sidebar_penulis.php'; ?>
    <main class="flex-1 p-6 md:p-8">
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
            <table class="w-full text-sm" data-testid="karya-saya-table">
                <thead class="bg-emerald-50 text-emerald-800"><tr>
                    <th class="px-4 py-2 text-left">Judul</th>
                    <th class="px-4 py-2 text-left">Kategori</th>
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th></th></tr></thead>
                <tbody>
                <?php while ($p = mysqli_fetch_assoc($list)): ?>
                    <tr class="border-t border-green-50">
                        <td class="px-4 py-2 font-medium text-emerald-900"><?= htmlspecialchars($p['judul']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($p['nama_kategori'] ?? '—') ?></td>
                        <td class="px-4 py-2 text-slate-500"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
                        <td class="px-4 py-2 text-right space-x-1">
                            <a href="../../detail.php?id=<?= $p['id'] ?>" target="_blank" class="text-emerald-700 hover:underline" data-testid="lihat-<?= $p['id'] ?>">Lihat</a>
                            <a href="form_karya.php?id=<?= $p['id'] ?>" class="text-emerald-700 hover:underline" data-testid="edit-<?= $p['id'] ?>">Edit</a>
                            <a href="?aksi=hapus&id=<?= $p['id'] ?>" onclick="return confirm('Hapus karya ini?')" class="text-red-600 hover:underline" data-testid="hapus-<?= $p['id'] ?>">Hapus</a>
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