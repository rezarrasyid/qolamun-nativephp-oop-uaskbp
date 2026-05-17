<?php
session_start();

// Validasi akses Admin
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

// Proses Hapus Karya
if ($aksi === 'hapus' && $id > 0) {
    $admin->hapusKaryaApapun($id);
    header('Location: karya.php?msg=' . urlencode('Karya berhasil dihapus.')); 
    exit;
}

// Ambil data karya beserta kategori dan nama penulis
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
        <h1 class="text-2xl font-extrabold text-emerald-800 mb-1" data-testid="admin-karya-title">Kelola Karya</h1>
        <p class="text-sm text-slate-500 mb-6">Admin memiliki akses penuh untuk meninjau dan menghapus karya milik siapapun.</p>
        
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
                    <thead class="bg-emerald-50 text-emerald-800 border-b border-green-100">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Judul Karya</th>
                            <th class="px-5 py-3.5 font-semibold">Kategori</th>
                            <th class="px-5 py-3.5 font-semibold">Penulis</th>
                            <th class="px-5 py-3.5 font-semibold">Tanggal Publish</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Aksi</th>
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
                                <td class="px-5 py-4 text-right space-x-3">
                                    <a href="../../detail.php?id=<?= $p['id'] ?>" target="_blank" class="text-emerald-600 hover:text-emerald-800 font-medium transition" data-testid="lihat-karya-<?= $p['id'] ?>">Lihat</a>
                                    <a href="?aksi=hapus&id=<?= $p['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus karya ini?')" class="text-red-500 hover:text-red-700 font-medium transition" data-testid="hapus-karya-<?= $p['id'] ?>">Hapus</a>
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