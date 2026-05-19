<?php
session_start();

if (($_SESSION['role'] ?? null) !== 'admin') { 
    header('Location: ../../auth/login.php'); 
    exit; 
}

require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../classes/Admin.php';

$admin = new Admin($koneksi);
$msg  = $_GET['msg'] ?? null;
$aksi = $_GET['aksi'] ?? null;
$id   = (int)($_GET['id'] ?? 0);

if ($aksi === 'hapus' && $id > 0) {
    $admin->hapusKaryaApapun($id);
    header('Location: karya.php?msg=' . urlencode('Karya berhasil dihapus.')); 
    exit;
}

$sql = "SELECT p.*, c.nama_kategori, u.nama AS nama_penulis
        FROM posts p
        LEFT JOIN categories c ON c.id = p.category_id
        LEFT JOIN users u ON u.id = p.user_id
        ORDER BY p.id DESC";
$list = mysqli_query($koneksi, $sql);

$pageTitle = 'Kelola Karya';
include __DIR__ . '/../../includes/header.php';
?>

<div class="flex">
    
    <main class="flex-1 max-w-7xl mx-auto w-full p-6 md:p-8">
        <h1 class="text-2xl font-extrabold text-emerald-800 mb-4" data-testid="admin-karya-title">Kelola Karya</h1>
        
        <!-- Pesan Notifikasi -->
        <?php if ($msg): ?>
            <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm" data-testid="flash-msg">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <!-- Tabel List Karya -->
        <div class="bg-white border border-green-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left whitespace-nowrap" data-testid="karya-table">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5 font-bold">Judul Karya</th>
                            <th class="px-5 py-3.5 font-bold">Kategori</th>
                            <th class="px-5 py-3.5 font-bold">Penulis</th>
                            <th class="px-5 py-3.5 font-bold">Tanggal Publish</th>
                            <th class="px-5 py-3.5 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php if (mysqli_num_rows($list) > 0): ?>
                        <?php while ($p = mysqli_fetch_assoc($list)): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-4">
                                    <p class="font-bold text-emerald-900 truncate max-w-[250px]" title="<?= htmlspecialchars($p['judul']) ?>">
                                        <?= htmlspecialchars($p['judul']) ?>
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider">
                                        <?= htmlspecialchars($p['nama_kategori'] ?? 'Umum') ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-medium text-slate-700">
                                    <?= htmlspecialchars($p['nama_penulis'] ?? 'Anonim') ?>
                                </td>
                                <td class="px-5 py-4 text-slate-500">
                                    <?= date('d M Y', strtotime($p['created_at'])) ?>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-row items-center justify-end gap-4">
                                        <a href="../../detail.php?id=<?= $p['id'] ?>" target="_blank" class="text-emerald-600 hover:text-emerald-800 transition transform hover:scale-110" title="Lihat Karya">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>

                                        <a href="?aksi=hapus&id=<?= $p['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus karya ini?')" class="text-red-500 hover:text-red-700 transition transform hover:scale-110" title="Hapus Karya">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-500">
                                Belum ada karya yang dipublikasikan di sistem.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>