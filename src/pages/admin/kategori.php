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
    
    <main class="flex-1 max-w-7xl mx-auto w-full p-6 md:p-8">
        <h1 class="text-2xl font-extrabold text-emerald-800 mb-4" data-testid="admin-kategori-title">Kelola Kategori</h1>
        
        <!-- Pesan Notifikasi -->
        <?php if ($msg): ?>
            <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm" data-testid="flash-msg">
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
                        <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5 font-bold">Nama Kategori</th>
                                <th class="px-5 py-3.5 font-bold">Slug URL</th>
                                <th class="px-5 py-3.5 font-bold text-right">Aksi</th>
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
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="?id=<?= $k['id'] ?>" class="text-emerald-500 hover:text-emerald-700 transition" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>

                                        <a href="?aksi=hapus&id=<?= $k['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="text-red-500 hover:text-red-700 transition" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </a>
                                    </div>
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