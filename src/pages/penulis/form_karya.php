<?php
session_start();
if (($_SESSION['role'] ?? null) !== 'penulis') { header('Location: ../../auth/login.php'); exit; }
require_once __DIR__ . '/../../koneksi.php';
require_once __DIR__ . '/../../classes/Penulis.php';

$penulis = new Penulis($koneksi);
$user_id = (int)$_SESSION['user_id'];

$id = (int)($_GET['id'] ?? 0);
$editData = null;
if ($id > 0) {
    $r = mysqli_query($koneksi, "SELECT * FROM posts WHERE id=$id AND user_id=$user_id LIMIT 1");
    $editData = $r ? mysqli_fetch_assoc($r) : null;
    if (!$editData) { header('Location: karya.php'); exit; }
}

$err = null;
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
            $err = 'Format thumbnail harus jpg/png/gif/webp.';
        } else {
            $newName = 'thumb_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target  = __DIR__ . '/../../uploads/' . $newName;
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target)) {
                // Hapus thumbnail lama jika edit
                if ($editData && !empty($editData['thumbnail']) && file_exists(__DIR__.'/../../uploads/'.$editData['thumbnail'])) {
                    @unlink(__DIR__.'/../../uploads/'.$editData['thumbnail']);
                }
                $thumbnailNama = $newName;
            } else {
                $err = 'Gagal mengupload thumbnail.';
            }
        }
    }

    if (!$err) {
        if ($editData) {
            $penulis->editKarya($editData['id'], $judul, $konten, $category_id, $thumbnailNama, $user_id);
            header('Location: karya.php?msg=' . urlencode('Karya berhasil diperbarui.')); exit;
        } else {
            $penulis->tambahKarya($judul, $konten, $category_id, $thumbnailNama, $user_id);
            header('Location: karya.php?msg=' . urlencode('Karya baru berhasil dipublikasikan.')); exit;
        }
    }
}
$kat = mysqli_query($koneksi, "SELECT * FROM categories ORDER BY nama_kategori ASC");
$pageTitle = $editData ? 'Edit Karya' : 'Tambah Karya';
include __DIR__ . '/../../includes/header.php';
?>
<div class="flex">
    <?php include __DIR__ . '/../../includes/sidebar_penulis.php'; ?>
    <main class="flex-1 p-6 md:p-8">
        <h1 class="text-2xl font-extrabold text-emerald-800 mb-4" data-testid="form-karya-title"><?= $editData ? 'Edit Karya' : 'Tulis Karya Baru' ?></h1>
        <?php if ($err): ?><div class="mb-4 px-3 py-2 rounded-lg bg-red-50 text-red-700 text-sm" data-testid="form-error"><?= htmlspecialchars($err) ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white border border-green-100 rounded-2xl p-6 space-y-4" data-testid="karya-form">
            <div>
                <label class="text-sm font-medium">Judul</label>
                <input name="judul" required value="<?= htmlspecialchars($editData['judul'] ?? '') ?>" class="w-full px-3 py-2 border rounded-lg" data-testid="karya-judul">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Kategori</label>
                    <select name="category_id" required class="w-full px-3 py-2 border rounded-lg" data-testid="karya-kategori">
                        <option value="">-- Pilih Kategori --</option>
                        <?php while ($k = mysqli_fetch_assoc($kat)): ?>
                            <option value="<?= $k['id'] ?>" <?= ($editData['category_id'] ?? null) == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kategori']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Thumbnail <?= $editData ? '<span class="text-xs text-slate-400">(kosongkan jika tidak diubah)</span>' : '' ?></label>
                    <input type="file" name="thumbnail" accept="image/*" class="w-full text-sm" data-testid="karya-thumbnail">
                    <?php if (!empty($editData['thumbnail'])): ?>
                        <img src="../../uploads/<?= htmlspecialchars($editData['thumbnail']) ?>" class="mt-2 w-32 rounded border">
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium">Konten</label>
                <textarea name="konten" id="editorKonten" rows="14" class="w-full px-3 py-2 border rounded-lg" data-testid="karya-konten"><?= $editData['konten'] ?? '' ?></textarea>
            </div>
            <div class="flex gap-2">
                <button class="bg-emerald-600 text-white px-5 py-2 rounded-lg hover:bg-emerald-700" data-testid="karya-submit"><?= $editData ? 'Simpan Perubahan' : 'Publikasikan' ?></button>
                <a href="karya.php" class="px-5 py-2 border rounded-lg hover:bg-slate-50" data-testid="karya-cancel">Batal</a>
            </div>
        </form>
    </main>
</div>

<!-- TinyMCE via CDN -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#editorKonten',
    height: 460,
    menubar: false,
    plugins: 'lists link image table code preview fullscreen',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link image table | code preview fullscreen',
    content_style: "body { font-family:'Plus Jakarta Sans', sans-serif; font-size:15px; line-height:1.7 }"
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>