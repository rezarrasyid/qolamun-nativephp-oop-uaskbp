<?php
session_start();

// Validasi akses Penulis
if (($_SESSION['role'] ?? null) !== 'penulis') { 
    header('Location: ../../auth/login.php'); 
    exit; 
}

require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../classes/Penulis.php';

$penulis = new Penulis($koneksi);
$user_id = (int)$_SESSION['user_id'];

$id = (int)($_GET['id'] ?? 0);
$editData = null;

if ($id > 0) {
    $r = mysqli_query($koneksi, "SELECT * FROM posts WHERE id=$id AND user_id=$user_id LIMIT 1");
    $editData = $r ? mysqli_fetch_assoc($r) : null;
    
    if (!$editData) { 
        header('Location: karya.php'); 
        exit; 
    }
}

$err = null;

// Proses Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul       = trim($_POST['judul']);
    $konten      = $_POST['konten'] ?? '';
    $category_id = (int)$_POST['category_id'];
    $thumbnailNama = $editData['thumbnail'] ?? '';

    // Upload thumbnail
    if (!empty($_FILES['thumbnail']['name'])) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $err = 'Format thumbnail harus jpg, jpeg, png, gif, atau webp.';
        } else {
            $newName = 'thumb_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target  = __DIR__ . '/../../uploads/' . $newName;
            
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
                if ($editData && !empty($editData['thumbnail']) && file_exists(__DIR__.'/../../uploads/'.$editData['thumbnail'])) {
                    @unlink(__DIR__.'/../../uploads/'.$editData['thumbnail']);
                }
                $thumbnailNama = $newName;
            } else {
                $err = 'Gagal mengunggah thumbnail. Pastikan folder uploads memiliki izin tulis (write permissions).';
            }
        }
    }

    if (!$err) {
        if ($editData) {
            $penulis->editKarya($editData['id'], $judul, $konten, $category_id, $thumbnailNama, $user_id);
            header('Location: karya.php?msg=' . urlencode('Karya berhasil diperbarui.')); 
            exit;
        } else {
            $penulis->tambahKarya($judul, $konten, $category_id, $thumbnailNama, $user_id);
            header('Location: karya.php?msg=' . urlencode('Karya baru berhasil dipublikasikan.')); 
            exit;
        }
    }
}

$kat = mysqli_query($koneksi, "SELECT * FROM categories ORDER BY nama_kategori ASC");
$pageTitle = $editData ? 'Edit Karya' : 'Tambah Karya';

include __DIR__ . '/../../includes/header.php';
?>

<div class="flex">
    <main class="flex-1 p-6 md:p-8 bg-slate-50 min-h-screen">
        <h1 class="text-2xl font-extrabold text-emerald-800 mb-6" data-testid="form-karya-title">
            <?= $editData ? 'Edit Karya' : 'Tulis Karya Baru' ?>
        </h1>
        
        <?php if ($err): ?>
            <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-center gap-2" data-testid="form-error">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white border border-green-100 rounded-2xl p-6 md:p-8 shadow-sm space-y-6" data-testid="karya-form">
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Judul Tulisan</label>
                <input name="judul" required value="<?= htmlspecialchars($editData['judul'] ?? '') ?>" 
                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none transition" 
                       data-testid="karya-judul" placeholder="Masukkan judul yang menarik...">
            </div>
            
            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Kategori</label>
                    <select name="category_id" required 
                            class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none transition bg-white" 
                            data-testid="karya-kategori">
                        <option value="">-- Pilih Kategori --</option>
                        <?php while ($k = mysqli_fetch_assoc($kat)): ?>
                            <option value="<?= $k['id'] ?>" <?= ($editData['category_id'] ?? null) == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kategori']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Gambar Thumbnail <?= $editData ? '<span class="text-xs text-slate-400 font-normal">(biarkan kosong jika tidak diubah)</span>' : '' ?>
                    </label>
                    <input type="file" name="thumbnail" accept="image/*" 
                           class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 transition" 
                           data-testid="karya-thumbnail">
                    
                    <?php if (!empty($editData['thumbnail'])): ?>
                        <div class="mt-3">
                            <p class="text-xs text-slate-400 mb-1">Thumbnail saat ini:</p>
                            <img src="../../uploads/<?= htmlspecialchars($editData['thumbnail']) ?>" class="h-20 object-cover rounded-lg border border-slate-200 shadow-sm">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Isi Konten</label>
                <textarea name="konten" id="editorKonten" rows="18" 
                          class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none transition" 
                          data-testid="karya-konten"><?= $editData['konten'] ?? '' ?></textarea>
            </div>
            
            <div class="flex items-center gap-3 pt-4 border-t border-slate-50">
                <button type="submit" class="bg-emerald-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition" data-testid="karya-submit">
                    <?= $editData ? 'Simpan Perubahan' : 'Publikasikan Karya' ?>
                </button>
                <a href="karya.php" class="px-6 py-3 font-semibold text-slate-500 hover:bg-slate-100 rounded-xl transition" data-testid="karya-cancel">
                    Batal
                </a>
            </div>
        </form>
    </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    tinymce.init({
        selector: '#editorKonten',
        height: 500,
        menubar: false,
        branding: false, /* Menghilangkan logo tiny di pojok bawah */
        plugins: 'lists link image table code preview fullscreen',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | preview fullscreen code',
        content_style: "@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600&display=swap'); body { font-family:'Plus Jakarta Sans', sans-serif; font-size:16px; line-height:1.8; color: #1e293b; }",
        setup: function (editor) {
            editor.on('change', function () {
                editor.save(); // Memastikan data masuk ke textarea saat form disubmit
            });
        }
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>