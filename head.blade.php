<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'MaggotTrack') — MaggotTrack</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script>
    (function () {
        var t = localStorage.getItem('mt-theme');
        if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
<script src="https://cdn.tailwindcss.com/3.4.17"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] }, colors: { forest: '#14532D' } } }
    };
</script>
@verbatim
<style type="text/tailwindcss">
    @layer components {
        .card { @apply rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800; }
        .input { @apply w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:border-lime-500 focus:outline-none focus:ring-2 focus:ring-lime-500/30 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100; }
        .label { @apply mb-1 block text-xs font-semibold text-slate-600 dark:text-slate-300; }
        .btn { @apply inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-60; }
        .btn-primary { @apply btn bg-forest text-white hover:bg-green-800; }
        .btn-lime { @apply btn bg-lime-500 text-green-950 hover:bg-lime-400; }
        .btn-ghost { @apply btn border border-slate-300 text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700; }
        .btn-danger { @apply btn bg-red-600 text-white hover:bg-red-700; }
        .icon-btn { @apply inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-white; }
        .nav-link { @apply flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-lime-50 hover:text-green-900 dark:text-slate-300 dark:hover:bg-slate-700; }
        .nav-link.active { @apply bg-forest text-white hover:bg-forest hover:text-white; }
    }
</style>
@endverbatim
