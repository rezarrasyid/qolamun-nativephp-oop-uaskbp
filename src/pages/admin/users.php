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
$msg = $_GET['msg'] ?? null;

// AKSI: tambah, edit, hapus
$aksi = $_GET['aksi'] ?? null;
$editId = (int)($_GET['id'] ?? 0);
$editData = $editId ? $admin->ambilUserById($editId) : null;

// Proses Form Submit (Tambah / Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $role     = $_POST['role'];
    $password = $_POST['password'] ?? '';
    $id       = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $admin->editUser($id, $nama, $username, $role, $password ?: null);
        header('Location: users.php?msg=' . urlencode('User berhasil diperbarui.')); 
        exit;
    } else {
        $admin->register($nama, $username, $password, $role);
        header('Location: users.php?msg=' . urlencode('User baru ditambahkan.')); 
        exit;
    }
}

// Proses Hapus User
if ($aksi === 'hapus' && $editId > 0) {
    $admin->hapusUser($editId);
    header('Location: users.php?msg=' . urlencode('User berhasil dihapus.')); 
    exit;
}

$list = $admin->ambilSemuaUser();
$pageTitle = 'Kelola User';
include __DIR__ . '/../../includes/header.php';
?>

<div class="flex">
    
    <main class="flex-1 max-w-7xl mx-auto w-full p-6 md:p-8">
        <h1 class="text-2xl font-extrabold text-emerald-800 mb-4" data-testid="admin-users-title">Kelola User</h1>

        <!-- Pesan Notifikasi -->
        <?php if ($msg): ?>
            <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm" data-testid="flash-msg">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-3 gap-6">
            
            <!-- FORM TAMBAH / EDIT -->
            <div class="bg-white border border-green-100 rounded-2xl p-6 shadow-sm h-fit">
                <h2 class="font-bold text-emerald-800 mb-4"><?= $editData ? 'Edit User' : 'Tambah User' ?></h2>
                
                <form method="POST" class="space-y-4" data-testid="user-form">
                    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
                        <input name="nama" required value="<?= htmlspecialchars($editData['nama'] ?? '') ?>" 
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none transition" 
                               data-testid="user-nama">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
                        <input name="username" required value="<?= htmlspecialchars($editData['username'] ?? '') ?>" 
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none transition" 
                               data-testid="user-username">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Password <?= $editData ? '<span class="text-xs text-slate-400 font-normal">(kosongkan jika tidak ganti)</span>' : '' ?>
                        </label>
                        <input type="password" name="password" <?= $editData ? '' : 'required' ?> 
                               class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none transition" 
                               data-testid="user-password">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
                        <select name="role" 
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none transition" 
                                data-testid="user-role">
                            <option value="penulis" <?= ($editData['role'] ?? '') === 'penulis' ? 'selected' : '' ?>>Penulis</option>
                            <option value="admin" <?= ($editData['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="pt-3">
                        <button class="w-full bg-emerald-600 text-white font-semibold py-2.5 rounded-xl hover:bg-emerald-700 transition" data-testid="user-submit">
                            <?= $editData ? 'Simpan Perubahan' : 'Tambah User' ?>
                        </button>
                        
                        <?php if ($editData): ?>
                            <a href="users.php" class="block text-center mt-3 text-sm text-slate-500 hover:text-emerald-700 transition" data-testid="user-cancel">
                                Batal Edit
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- TABEL LIST USER -->
            <div class="lg:col-span-2 bg-white border border-green-100 rounded-2xl shadow-sm overflow-hidden h-fit">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left whitespace-nowrap" data-testid="users-table">
                        <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5 font-bold">Nama</th>
                                <th class="px-5 py-3.5 font-bold">Username</th>
                                <th class="px-5 py-3.5 font-bold">Role</th>
                                <th class="px-5 py-3.5 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        <?php while ($u = mysqli_fetch_assoc($list)): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3"><?= htmlspecialchars($u['nama']) ?></td>
                                <td class="px-5 py-3"><?= htmlspecialchars($u['username']) ?></td>
                                <td class="px-5 py-3">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold tracking-wider uppercase <?= $u['role'] === 'admin' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                                        <?= $u['role'] ?>
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right space-x-3">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="?id=<?= $u['id'] ?>" class="text-emerald-500 hover:text-emerald-700 transition" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>

                                        <a href="?aksi=hapus&id=<?= $u['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="text-red-500 hover:text-red-700 transition" title="Hapus">
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