<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body class="bg-[#F8FAFC] font-sans text-[#1E293B] antialiased dark:bg-slate-900 dark:text-slate-100">
@php
    $nav = [
        ['dashboard', 'Dashboard', 'layout-dashboard', 'dashboard'],
        ['batches.index', 'Batch', 'layers', 'batches.*'],
        ['feedings.index', 'Pakan', 'utensils', 'feedings.*'],
        ['growth.index', 'Pertumbuhan', 'trending-up', 'growth.*'],
        ['environment.index', 'Lingkungan', 'thermometer', 'environment.*'],
        ['schedule.index', 'Jadwal Panen', 'calendar-days', 'schedule.*'],
        ['harvests.index', 'Hasil Panen', 'package', 'harvests.*'],
        ['reports.index', 'Laporan', 'file-text', 'reports.*'],
    ];
    $notifications = $notifications ?? collect();
@endphp
<div class="min-h-screen lg:flex">
    <div id="overlay" class="fixed inset-0 z-30 hidden bg-black/40 lg:hidden"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 -translate-x-full transform flex-col border-r border-slate-100 bg-white p-4 transition-transform duration-200 dark:border-slate-700 dark:bg-slate-800 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0">
        <a href="{{ route('dashboard') }}" class="mb-6 flex items-center gap-3 px-2">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-forest text-lime-400"><i data-lucide="leaf" class="h-5 w-5"></i></span>
            <span>
                <span class="block text-lg font-extrabold leading-tight text-forest dark:text-lime-400">MaggotTrack</span>
                <span class="block text-[10px] font-medium text-slate-400">Monitor. Manage. Maximize.</span>
            </span>
        </a>
        <nav class="flex-1 space-y-1 overflow-y-auto">
            @foreach ($nav as [$r, $label, $icon, $pattern])
                <a href="{{ route($r) }}" class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
                    <i data-lucide="{{ $icon }}" class="h-5 w-5"></i> {{ $label }}
                </a>
            @endforeach
        </nav>
        <a href="{{ route('profile.edit') }}" class="nav-link mt-2 {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i data-lucide="user" class="h-5 w-5"></i> Profil & Pengaturan
        </a>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-slate-100 bg-white/90 px-4 py-3 backdrop-blur dark:border-slate-700 dark:bg-slate-800/90 lg:px-8">
            <button id="menu-btn" type="button" class="icon-btn lg:hidden" aria-label="Menu"><i data-lucide="menu" class="h-5 w-5"></i></button>

            <form action="{{ route('search') }}" method="GET" class="relative max-w-md flex-1">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari batch, pakan, panen…" class="input pl-9">
            </form>

            <div class="ml-auto flex items-center gap-1">
                <button id="theme-btn" type="button" class="icon-btn" aria-label="Ganti tema">
                    <i data-lucide="moon" class="h-5 w-5 dark:hidden"></i><i data-lucide="sun" class="hidden h-5 w-5 dark:block"></i>
                </button>

                <div class="relative">
                    <button type="button" class="icon-btn relative" data-dd="dd-notif" aria-label="Notifikasi">
                        <i data-lucide="bell" class="h-5 w-5"></i>
                        @if ($notifications->count())
                            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $notifications->count() }}</span>
                        @endif
                    </button>
                    <div id="dd-notif" data-dd-menu class="card absolute right-0 z-30 mt-2 hidden w-80 max-w-[90vw] overflow-hidden">
                        <p class="border-b border-slate-100 px-4 py-3 text-sm font-bold dark:border-slate-700">Notifikasi</p>
                        <div class="max-h-80 overflow-y-auto">
                            @forelse ($notifications as $n)
                                <a href="{{ $n['url'] }}" class="flex gap-3 px-4 py-3 text-sm hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ ['danger' => 'bg-red-500', 'warning' => 'bg-amber-500', 'info' => 'bg-sky-500'][$n['type']] }}"></span>
                                    <span>{{ $n['text'] }}</span>
                                </a>
                            @empty
                                <p class="px-4 py-6 text-center text-sm text-slate-400">Tidak ada notifikasi.</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <button type="button" class="flex items-center gap-2 rounded-xl px-2 py-1 hover:bg-slate-100 dark:hover:bg-slate-700" data-dd="dd-user">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-lime-500 text-sm font-bold text-green-950">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="hidden max-w-[8rem] truncate text-sm font-semibold sm:block">{{ auth()->user()->name }}</span>
                    </button>
                    <div id="dd-user" data-dd-menu class="card absolute right-0 z-30 mt-2 hidden w-52 overflow-hidden py-1">
                        <p class="truncate px-4 py-2 text-xs text-slate-400">{{ auth()->user()->email }}</p>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700/50"><i data-lucide="user" class="h-4 w-4"></i> Profil</a>
                        <form method="POST" action="{{ route('logout') }}" data-noload>
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-red-600 hover:bg-slate-50 dark:hover:bg-slate-700/50"><i data-lucide="log-out" class="h-4 w-4"></i> Keluar</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-8">
            @yield('content')
        </main>
        <footer class="px-8 pb-6 text-center text-xs text-slate-400">MaggotTrack · Monitor Growth. Manage Waste. Maximize Harvest.</footer>
    </div>
</div>

<div id="toasts" class="fixed right-4 top-4 z-50 space-y-2"></div>

<div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="card w-full max-w-sm p-6">
        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600"><i data-lucide="trash-2" class="h-5 w-5"></i></div>
        <h3 class="text-lg font-bold">Konfirmasi hapus</h3>
        <p id="confirm-text" class="mt-1 text-sm text-slate-500 dark:text-slate-300"></p>
        <div class="mt-5 flex justify-end gap-2">
            <button type="button" id="confirm-no" class="btn-ghost">Batal</button>
            <button type="button" id="confirm-yes" class="btn-danger">Ya, hapus</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    const isDark = () => document.documentElement.classList.contains('dark');
    Chart.defaults.font.family = '"Plus Jakarta Sans", sans-serif';
    Chart.defaults.color = isDark() ? '#CBD5E1' : '#475569';
    Chart.defaults.borderColor = isDark() ? '#334155' : '#E2E8F0';

    function toast(msg, type) {
        const box = document.getElementById('toasts');
        const el = document.createElement('div');
        el.className = 'flex max-w-sm items-start gap-2 rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg transition ' + (type === 'error' ? 'bg-red-600' : 'bg-forest');
        el.textContent = msg;
        box.appendChild(el);
        setTimeout(() => { el.style.opacity = 0; setTimeout(() => el.remove(), 300); }, 4000);
    }

    // Sidebar mobile
    const sb = document.getElementById('sidebar'), ov = document.getElementById('overlay');
    const toggleMenu = (open) => { sb.classList.toggle('-translate-x-full', !open); ov.classList.toggle('hidden', !open); };
    document.getElementById('menu-btn').addEventListener('click', () => toggleMenu(true));
    ov.addEventListener('click', () => toggleMenu(false));

    // Dark mode
    document.getElementById('theme-btn').addEventListener('click', () => {
        const dark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('mt-theme', dark ? 'dark' : 'light');
        if (Object.keys(Chart.instances).length) location.reload();
    });

    // Dropdown
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-dd]');
        document.querySelectorAll('[data-dd-menu]').forEach((m) => {
            if (btn && m.id === btn.dataset.dd) m.classList.toggle('hidden'); else m.classList.add('hidden');
        });
    });

    // Konfirmasi hapus + loading state
    let pending = null;
    const modal = document.getElementById('confirm-modal');
    const closeModal = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); pending = null; };
    document.addEventListener('submit', (e) => {
        const f = e.target;
        if (f.dataset.confirm && !f.dataset.ok) {
            e.preventDefault();
            pending = f;
            document.getElementById('confirm-text').textContent = f.dataset.confirm;
            modal.classList.remove('hidden'); modal.classList.add('flex');
            return;
        }
        const b = f.querySelector('button[type=submit]');
        if (b && f.dataset.noload === undefined) {
            setTimeout(() => { b.disabled = true; b.insertAdjacentHTML('afterbegin', '<span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-current border-r-transparent"></span>'); }, 0);
        }
    });
    document.getElementById('confirm-no').addEventListener('click', closeModal);
    document.getElementById('confirm-yes').addEventListener('click', () => {
        if (!pending) return;
        const f = pending; f.dataset.ok = '1'; closeModal(); f.requestSubmit ? f.requestSubmit() : f.submit();
    });
    window.addEventListener('pageshow', (e) => { if (e.persisted) location.reload(); });

    // Preview foto
    function previewImg(input) {
        const img = document.getElementById('preview-' + input.name);
        if (!img || !input.files[0]) return;
        img.src = URL.createObjectURL(input.files[0]); img.classList.remove('hidden');
    }

    @if (session('success')) toast(@json(session('success')), 'success'); @endif
    @if (session('error')) toast(@json(session('error')), 'error'); @endif
    @if ($errors->any()) toast('Periksa kembali isian formulir.', 'error'); @endif
</script>
@stack('scripts')
<script>lucide.createIcons();</script>
</body>
</html>
