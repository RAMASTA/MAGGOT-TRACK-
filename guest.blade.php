<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body class="bg-[#F8FAFC] font-sans text-[#1E293B] antialiased dark:bg-slate-900 dark:text-slate-100">
<div class="grid min-h-screen lg:grid-cols-2">
    <div class="relative hidden flex-col justify-between bg-forest p-12 text-white lg:flex">
        <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-lime-500 text-green-950"><i data-lucide="leaf" class="h-6 w-6"></i></span>
            <span class="text-2xl font-extrabold">MaggotTrack</span>
        </div>
        <div>
            <h1 class="text-4xl font-extrabold leading-tight">Monitor Growth.<br>Manage Waste.<br><span class="text-lime-400">Maximize Harvest.</span></h1>
            <p class="mt-4 max-w-md text-green-100">Catat telur hingga panen, pantau pertumbuhan dan lingkungan, dan lihat laporan produksi budidaya maggot BSF Anda dalam satu tempat.</p>
        </div>
        <p class="text-sm text-green-200">© {{ date('Y') }} MaggotTrack</p>
    </div>
    <div class="flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <div class="mb-6 flex items-center gap-2 lg:hidden">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-forest text-lime-400"><i data-lucide="leaf" class="h-5 w-5"></i></span>
                <span class="text-xl font-extrabold text-forest dark:text-lime-400">MaggotTrack</span>
            </div>
            <div class="card p-8">@yield('content')</div>
        </div>
    </div>
</div>
<script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script>
    lucide.createIcons();
    document.addEventListener('submit', (e) => {
        const b = e.target.querySelector('button[type=submit]');
        if (b) setTimeout(() => { b.disabled = true; b.insertAdjacentHTML('afterbegin', '<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-current border-r-transparent"></span>'); }, 0);
    });
    window.addEventListener('pageshow', (e) => { if (e.persisted) location.reload(); });
</script>
</body>
</html>
