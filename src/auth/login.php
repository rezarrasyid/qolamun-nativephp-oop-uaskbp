<?php
session_start();
// Jika sudah login, langsung ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    $r = $_SESSION['role'];
    header('Location: ' . ($r === 'admin' ? '../pages/admin/dashboard.php' : '../pages/penulis/dashboard.php'));
    exit;
}
$err = $_GET['err'] ?? null;
include __DIR__ . '/../includes/header.php';
?>

<section class="max-w-md mx-auto px-4 py-12 md:py-20">
    <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-emerald-900/5 p-8">
        <!-- Header Login -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-extrabold text-emerald-800 mb-2">Masuk ke Qolamun</h1>
            <p class="text-sm text-slate-500">Gunakan akun Anda untuk mengelola karya.</p>
        </div>

        <!-- Notifikasi Error -->
        <?php if ($err): ?>
            <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-center gap-2" data-testid="login-error">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form action="proses_login.php" method="POST" class="space-y-5" data-testid="login-form">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 ml-1">Username</label>
                <input type="text" name="username" required placeholder="Masukkan username"
                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                       data-testid="login-username">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 ml-1">Password</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                       data-testid="login-password">
            </div>

            <button type="submit"
                    class="w-full bg-emerald-600 text-white font-bold py-3 rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 active:scale-[0.98] transition duration-200"
                    data-testid="login-submit">
                Masuk Sekarang
            </button>
        </form>

        <!-- Footer Login -->
        <div class="mt-8 pt-6 border-t border-slate-50 text-center">
            <a href="../index.php" class="text-sm font-medium text-emerald-700 hover:text-emerald-800 flex items-center justify-center gap-2 transition" data-testid="login-back">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>