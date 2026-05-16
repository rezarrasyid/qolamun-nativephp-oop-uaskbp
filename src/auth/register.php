<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $r = $_SESSION['role'];
    header('Location: ' . ($r === 'admin' ? '../pages/admin/dashboard.php' : '../pages/penulis/dashboard.php'));
    exit;
}
$err = $_GET['err'] ?? null;
$ok  = $_GET['ok']  ?? null;
include __DIR__ . '/../includes/header.php';
?>

<section class="max-w-md mx-auto px-4 py-12 md:py-20">
    <div class="qcard p-8 shadow-xl" style="box-shadow:0 20px 60px -12px rgba(5,150,105,.15)">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white grid place-items-center mx-auto mb-4 shadow-lg shadow-emerald-600/30">
                <span class="font-arabic text-3xl leading-none">ق</span>
            </div>
            <h1 class="text-2xl font-extrabold mb-1" style="color:var(--em-dark)">Daftar sebagai Penulis</h1>
            <p class="text-sm" style="color:var(--text-muted)">Bergabung dan mulai bagikan karyamu di Qolamun.</p>
        </div>

        <!-- Error -->
        <?php if ($err): ?>
            <div class="mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-start gap-2" data-testid="register-error">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <!-- Success -->
        <?php if ($ok): ?>
            <div class="mb-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-start gap-2" data-testid="register-success">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <?= htmlspecialchars($ok) ?> <a href="login.php" class="font-bold underline ml-1">Masuk sekarang &rarr;</a>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="proses_register.php" method="POST" class="space-y-4" data-testid="register-form">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-1.5 ml-1" style="color:var(--text-muted)">Nama Lengkap</label>
                <input type="text" name="nama" required placeholder="Nama lengkapmu"
                       class="qinput" data-testid="register-nama">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-1.5 ml-1" style="color:var(--text-muted)">Username</label>
                <input type="text" name="username" required placeholder="Pilih username unik"
                       class="qinput" data-testid="register-username"
                       pattern="[a-zA-Z0-9_]+" title="Hanya huruf, angka, dan underscore">
                <p class="text-[10px] ml-1 mt-1" style="color:var(--text-muted)">Huruf, angka, dan underscore saja. Tidak bisa diubah.</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-1.5 ml-1" style="color:var(--text-muted)">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="regPass" required placeholder="Minimal 6 karakter"
                           class="qinput pr-10" minlength="6" data-testid="register-password">
                    <button type="button" id="togglePass"
                            class="absolute right-3 top-1/2 -translate-y-1/2"
                            style="color:var(--text-muted)" aria-label="Tampilkan password">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider mb-1.5 ml-1" style="color:var(--text-muted)">Konfirmasi Password</label>
                <input type="password" name="konfirmasi" id="regKonfirmasi" required placeholder="Ulangi password"
                       class="qinput" data-testid="register-konfirmasi">
                <p id="passMsg" class="text-[10px] ml-1 mt-1 hidden"></p>
            </div>

            <!-- Terms -->
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="setuju" required class="mt-1 accent-emerald-600" data-testid="register-setuju">
                <span class="text-xs" style="color:var(--text-muted)">
                    Saya bersedia mempublikasikan karya yang bermanfaat dan sesuai syariat Islam.
                </span>
            </label>

            <button type="submit"
                    class="w-full bg-emerald-600 text-white font-bold py-3 rounded-xl hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 active:scale-[0.98] transition duration-200"
                    data-testid="register-submit">
                Daftar Sekarang
            </button>
        </form>

        <!-- Footer -->
        <div class="mt-6 pt-5 border-t text-center" style="border-color:var(--border)">
            <p class="text-sm" style="color:var(--text-muted)">
                Sudah punya akun?
                <a href="login.php" class="font-bold hover:underline" style="color:var(--em)">Masuk di sini</a>
            </p>
        </div>

        <div class="mt-4 text-center">
            <a href="../index.php" class="text-sm font-medium flex items-center justify-center gap-1.5 transition hover:underline" style="color:var(--em)">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</section>

<script>
// Toggle show/hide password
const regPass = document.getElementById('regPass');
const toggleBtn = document.getElementById('togglePass');
if (toggleBtn && regPass) {
    toggleBtn.addEventListener('click', () => {
        const show = regPass.type === 'password';
        regPass.type = show ? 'text' : 'password';
        toggleBtn.style.opacity = show ? '1' : '.5';
    });
}

// Live password match check
const konfirm = document.getElementById('regKonfirmasi');
const passMsg = document.getElementById('passMsg');
function checkMatch() {
    if (!konfirm.value) { passMsg.classList.add('hidden'); return; }
    const match = regPass.value === konfirm.value;
    passMsg.classList.remove('hidden');
    passMsg.textContent = match ? '✓ Password cocok' : '✗ Password tidak cocok';
    passMsg.style.color = match ? 'var(--em)' : '#ef4444';
}
if (konfirm) {
    konfirm.addEventListener('input', checkMatch);
    regPass.addEventListener('input', checkMatch);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>