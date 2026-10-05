<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Admin') · Men Gede</title>
    <link rel="icon" type="image/png" href="{{ asset('kasir-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Segoe UI"', 'system-ui', 'sans-serif'],
                        mono: ['"IBM Plex Mono"', 'ui-monospace', 'Consolas', 'monospace'],
                    },
                    colors: {
                        ground: '#F5F6F4',
                        ink: '#1D2422',
                        muted: '#5D6965',
                        faint: '#8A9591',
                        line: '#E2E6E3',
                        line2: '#F0F2EF',
                        field: '#D5DBD7',
                        brand: { DEFAULT: '#9A5317', dark: '#7A4011', soft: '#F6EDE4', bar: '#D9C3AE' },
                        ok: { DEFAULT: '#2E7D4F', soft: '#E7F2EB' },
                        warn: { DEFAULT: '#A86A00', soft: '#FBF1DC', ink: '#6E4600' },
                        bad: { DEFAULT: '#B83A2B', soft: '#FBE9E6' },
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        @layer components {
            .card { @apply bg-white border border-line rounded-[14px]; }
            .card-pad { @apply p-5 sm:p-6; }
            .card-title { @apply text-[15px] font-semibold text-ink; }
            .page-title { @apply text-2xl sm:text-[26px] font-bold tracking-tight text-ink; }
            .page-sub { @apply text-sm text-muted mt-1; }
            .btn { @apply inline-flex items-center justify-center gap-2 rounded-[10px] px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2 disabled:opacity-50; }
            .btn-primary { @apply btn bg-brand text-white hover:bg-brand-dark; }
            .btn-dark { @apply btn bg-ink text-white hover:bg-black; }
            .btn-ghost { @apply btn bg-white text-ink border border-field hover:bg-line2; }
            .btn-danger { @apply btn bg-bad text-white hover:bg-[#9b2f22]; }
            .btn-text { @apply text-sm font-medium text-brand hover:text-brand-dark; }
            .icon-btn { @apply inline-flex items-center justify-center w-9 h-9 rounded-lg text-muted hover:bg-line2 hover:text-ink transition; }
            .label { @apply block text-[13px] font-medium text-ink mb-1.5; }
            .hint { @apply text-xs text-muted mt-1.5; }
            .field { @apply w-full rounded-[10px] border border-field bg-white px-3 py-2.5 text-sm text-ink placeholder:text-faint focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30; }
            .error { @apply text-xs text-bad mt-1.5; }
            .chip { @apply inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap; }
            .chip-ok { @apply chip bg-ok-soft text-ok; }
            .chip-warn { @apply chip bg-warn-soft text-warn-ink; }
            .chip-bad { @apply chip bg-bad-soft text-bad; }
            .chip-muted { @apply chip bg-line2 text-muted; }
            .chip-brand { @apply chip bg-brand-soft text-brand-dark; }
            .tbl { @apply w-full text-sm border-collapse; }
            .tbl th { @apply text-left text-xs font-medium text-muted px-4 py-3 border-b border-line whitespace-nowrap; }
            .tbl td { @apply px-4 py-3 border-b border-line2 align-middle; }
            .tbl tbody tr:last-child td { @apply border-b-0; }
            .tbl tbody tr:hover { @apply bg-ground/60; }
            .num { font-variant-numeric: tabular-nums; }
            .nav-group { @apply text-[11px] font-medium uppercase tracking-[0.08em] text-faint px-3 pt-5 pb-1.5; }
            .nav-link { @apply flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-muted hover:bg-line2 hover:text-ink transition; }
            .nav-link i { @apply w-4 text-center text-[15px]; }
            .nav-link.active { @apply bg-brand-soft text-brand-dark font-semibold; }
            .seg { @apply inline-flex rounded-[10px] border border-line bg-white p-[3px]; }
            .seg a { @apply rounded-[7px] px-3 py-1.5 text-[13px] text-muted hover:text-ink; }
            .seg a.on { @apply bg-ink text-white hover:text-white; }
        }
    </style>
    <style>
        body { -webkit-font-smoothing: antialiased; }
        /* Atribut hidden harus menang dari utilitas display (flex, grid) */
        [hidden] { display: none !important; }
        /* Kolom sr-only (posisi absolut) jangan sampai lolos dari wadah tabel yang di-scroll */
        .overflow-x-auto { position: relative; }
        :focus-visible { outline-color: #9A5317; }
    </style>
    @stack('head')
</head>

<body class="bg-ground font-sans text-ink">
    @php
        $nav = [
            ['group' => null, 'items' => [
                ['Ringkasan', 'admin.dashboard', 'admin/dashboard', 'fa-solid fa-table-cells-large'],
            ]],
            ['group' => 'Penjualan', 'items' => [
                ['Pesanan', 'admin.orders.index', 'admin/orders*', 'fa-solid fa-receipt'],
                ['Laporan', 'admin.reports.index', 'admin/reports*', 'fa-solid fa-chart-column'],
                ['Shift & settlement', 'admin.settlements.index', 'admin/settlements*', 'fa-solid fa-cash-register'],
                ['Kas keluar', 'admin.expenses.index', 'admin/expenses*', 'fa-solid fa-wallet'],
            ]],
            ['group' => 'Menu', 'items' => [
                ['Menu & stok', 'admin.menu.index', 'admin/menus*', 'fa-solid fa-utensils'],
                ['Kategori', 'admin.category.index', 'admin/categories*', 'fa-solid fa-layer-group'],
                ['Diskon', 'admin.discounts.index', 'admin/discounts*', 'fa-solid fa-tag'],
            ]],
            ['group' => 'Toko', 'items' => [
                ['Staff', 'admin.users.index', 'admin/users*', 'fa-solid fa-user-group'],
                ['Aplikasi kasir', 'admin.app_release.index', 'admin/app-release*', 'fa-solid fa-mobile-screen-button'],
                ['Pengaturan', 'admin.settings.index', 'admin/settings*', 'fa-solid fa-gear'],
            ]],
        ];
    @endphp

    {{-- Top bar khusus HP --}}
    <header class="lg:hidden print:hidden sticky top-0 z-30 flex h-14 items-center justify-between border-b border-line bg-white/95 px-4 backdrop-blur">
        <button type="button" onclick="toggleSidebar(true)" aria-label="Buka menu" class="icon-btn -ml-2">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>
        <div class="flex items-center gap-2">
            <img src="{{ asset('kasir-icon.png') }}" alt="" class="h-7 w-7 rounded-md">
            <span class="font-semibold">Men Gede</span>
        </div>
        <span class="w-9"></span>
    </header>

    <div id="sidebar-overlay" onclick="toggleSidebar(false)" class="fixed inset-0 z-40 hidden bg-ink/40 lg:hidden"></div>

    <div class="flex min-h-screen">
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-50 flex w-[248px] -translate-x-full flex-col border-r border-line bg-white px-4 py-6 transition-transform duration-200 lg:translate-x-0 print:hidden">
            <div class="flex items-center justify-between px-2">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('kasir-icon.png') }}" alt="" class="h-8 w-8 rounded-lg">
                    <div>
                        <div class="text-[15px] font-bold leading-tight">Men Gede</div>
                        <div class="text-xs text-muted">Panel admin</div>
                    </div>
                </div>
                <button type="button" onclick="toggleSidebar(false)" aria-label="Tutup menu" class="icon-btn lg:hidden">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <nav class="mt-6 flex-1 overflow-y-auto">
                @foreach ($nav as $section)
                    @if ($section['group'])
                        <div class="nav-group">{{ $section['group'] }}</div>
                    @endif
                    <div class="flex flex-col gap-0.5">
                        @foreach ($section['items'] as [$label, $route, $pattern, $icon])
                            <a href="{{ route($route) }}" class="nav-link {{ Request::is($pattern) ? 'active' : '' }}"
                                @if (Request::is($pattern)) aria-current="page" @endif>
                                <i class="{{ $icon }}"></i>{{ $label }}
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            <div class="mt-4 flex items-center justify-between border-t border-line px-2 pt-4">
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold">{{ auth()->user()->name ?? 'Admin' }}</div>
                    <div class="truncate text-xs text-muted">{{ auth()->user()->email ?? '' }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-bad hover:underline">Keluar</button>
                </form>
            </div>
        </aside>

        <main class="min-w-0 flex-1 px-4 py-6 sm:px-6 lg:ml-[248px] lg:px-10 lg:py-8 print:ml-0">
            <div class="mx-auto flex max-w-[1240px] flex-col gap-6">
                @if (session('success'))
                    <div role="status" class="flex items-start gap-3 rounded-xl border border-ok/20 bg-ok-soft px-4 py-3 text-sm text-ok">
                        <i class="fa-solid fa-circle-check mt-0.5"></i><span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div role="alert" class="flex items-start gap-3 rounded-xl border border-bad/20 bg-bad-soft px-4 py-3 text-sm text-bad">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i><span>{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div role="alert" class="rounded-xl border border-bad/20 bg-bad-soft px-4 py-3 text-sm text-bad">
                        <div class="font-semibold">Ada isian yang perlu diperbaiki:</div>
                        <ul class="mt-1 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar(open) {
            document.getElementById('sidebar').classList.toggle('-translate-x-full', !open);
            document.getElementById('sidebar-overlay').classList.toggle('hidden', !open);
            document.body.classList.toggle('overflow-hidden', open);
        }

        window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => {
            if (e.matches) toggleSidebar(false);
        });

        // Tabel lebar bisa digeser ke samping di HP
        document.querySelectorAll('table').forEach((table) => {
            if (table.closest('.overflow-x-auto')) return;
            const wrapper = document.createElement('div');
            wrapper.className = 'overflow-x-auto';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        });

        // Dialog konfirmasi: <button data-confirm="pesan"> di dalam form
        document.addEventListener('submit', (e) => {
            const msg = e.submitter?.dataset.confirm || e.target.dataset.confirm;
            if (msg && !window.confirm(msg)) e.preventDefault();
        });
    </script>
    @stack('scripts')
</body>

</html>
