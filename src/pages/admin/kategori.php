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
$msg   = $_GET['msg'] ?? null;
$aksi  = $_GET['aksi'] ?? null;
$editId = (int)($_GET['id'] ?? 0);
$editData = null;

// Ambil data kategori jika sedang dalam mode Edit
if ($editId) {
    $r = mysqli_query($koneksi, "SELECT * FROM categories WHERE id=$editId LIMIT 1");
    $editData = $r ? mysqli_fetch_assoc($r) : null;
}

// Proses Form Submit (Tambah / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_kategori']);
    $slug = trim($_POST['slug']) ?: strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($nama)));
    $id   = (int)($_POST['id'] ?? 0);
    
    if ($id > 0) {
        $admin->editKategori($id, $nama, $slug);
        $pesan = 'Kategori berhasil diperbarui.';
    } else {
        $admin->tambahKategori($nama, $slug);
        $pesan = 'Kategori baru berhasil ditambahkan.';
    }
    
    header('Location: kategori.php?msg=' . urlencode($pesan)); 
    exit;
}

// Proses Hapus Kategori
if ($aksi === 'hapus' && $editId > 0) {
    $admin->hapusKategori($editId);
    header('Location: kategori.php?msg=' . urlencode('Kategori berhasil dihapus.')); 
    exit;
}

// Ambil semua daftar kategori
$list = mysqli_query($koneksi, "SELECT * FROM categories ORDER BY id DESC");
$pageTitle = 'Kelola Kategori';
include __DIR__ . '/../../includes/header.php';
?>

<div class="flex">
    <?php include __DIR__ . '/../../includes/sidebar_admin.php'; ?>
    
    <main class="flex-1 p-6 md:p-8 bg-slate-50 min-h-screen">
        <h1 class="text-2xl font-extrabold text-emerald-800 mb-1" data-testid="admin-kategori-title">Kelola Kategori</h1>
        <p class="text-sm text-slate-500 mb-6">Tambah, edit, atau hapus kategori untuk mengelompokkan karya.</p>
        
        <!-- Pesan Notifikasi -->
        <?php if ($msg): ?>
            <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm" data-testid="flash-msg">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>
        
        <div class="grid lg:grid-cols-3 gap-6">
            
            <!-- FORM TAMBAH / EDIT KATEGORI -->
            <div class="bg-white border border-green-100 rounded-2xl p-6 shadow-sm h-fit">
                <h2 class="font-bold text-emerald-800 mb-4"><?= $editData ? 'Edit Kategori' : 'Tambah Kategori' ?></h2>
                
                <form method="POST" class="space-y-4" data-testid="kategori-form">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Kategori</label>
                        <input name="nama_kategori" required value="<?= htmlspecialchars($editData['nama_kategori'] ?? '') ?>" 
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none transition" 
                               data-testid="kategori-nama" placeholder="Misal: Cerpen Islami">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Slug <span class="text-xs text-slate-400 font-normal">(otomatis bila kosong)</span>
                        </label>
                        <input name="slug" value="<?= htmlspecialchars($editData['slug'] ?? '') ?>" 
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none transition" 
                               data-testid="kategori-slug" placeholder="cerpen-islami">
                    </div>
                    
                    <div class="pt-3">
                        <button class="w-full bg-emerald-600 text-white font-semibold py-2.5 rounded-xl hover:bg-emerald-700 transition" data-testid="kategori-submit">
                            <?= $editData ? 'Simpan Perubahan' : 'Tambah Kategori' ?>
                        </button>
                        
                        <?php if ($editData): ?>
                            <a href="kategori.php" class="block text-center mt-3 text-sm text-slate-500 hover:text-emerald-700 transition" data-testid="kategori-cancel">
                                Batal Edit
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- TABEL LIST KATEGORI -->
            <div class="lg:col-span-2 bg-white border border-green-100 rounded-2xl shadow-sm overflow-hidden h-fit">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left whitespace-nowrap" data-testid="kategori-table">
                        <thead class="bg-emerald-50 text-emerald-800 border-b border-green-100">
                            <tr>
                                <th class="px-5 py-3.5 font-semibold">Nama Kategori</th>
                                <th class="px-5 py-3.5 font-semibold">Slug URL</th>
                                <th class="px-5 py-3.5 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        <?php while ($k = mysqli_fetch_assoc($list)): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3 font-medium text-slate-800"><?= htmlspecialchars($k['nama_kategori']) ?></td>
                                <td class="px-5 py-3 text-slate-500">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded font-mono text-xs">
                                        <?= htmlspecialchars($k['slug']) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right space-x-3">
                                    <a href="?id=<?= $k['id'] ?>" class="text-emerald-600 hover:text-emerald-800 font-medium transition" data-testid="edit-kat-<?= $k['id'] ?>">Edit</a>
                                    <a href="?aksi=hapus&id=<?= $k['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')" class="text-red-500 hover:text-red-700 font-medium transition" data-testid="hapus-kat-<?= $k['id'] ?>">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </main>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>