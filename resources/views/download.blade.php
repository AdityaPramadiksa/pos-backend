@php
    use App\Services\KasirApk;
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Unduh Kasir Men Gede</title>
    <link rel="icon" type="image/png" href="{{ asset('kasir-icon.png') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', '"Segoe UI"', 'system-ui', 'sans-serif'] },
                    colors: {
                        ground: '#F5F6F4', ink: '#1D2422', muted: '#5D6965', line: '#E2E6E3',
                        brand: { DEFAULT: '#9A5317', dark: '#7A4011', soft: '#F6EDE4' },
                        warn: { DEFAULT: '#A86A00', soft: '#FBF1DC', ink: '#6E4600' },
                    },
                },
            },
        };
    </script>
    <style>.num { font-variant-numeric: tabular-nums; }</style>
</head>

<body class="min-h-screen bg-ground px-4 py-10 font-sans text-ink">
    <main class="mx-auto flex w-full max-w-[440px] flex-col gap-5">
        <div class="flex items-center gap-4">
            <img src="{{ asset('kasir-icon.png') }}" alt="" class="h-16 w-16 rounded-[18px]">
            <div>
                <h1 class="text-xl font-bold leading-tight">Kasir Men Gede</h1>
                <p class="text-sm text-muted">Aplikasi kasir Warung Babi Guling Men Gede</p>
            </div>
        </div>

        @if ($info)
            <section class="rounded-[14px] border border-line bg-white p-5">
                <a href="{{ route('download.apk') }}"
                    class="flex w-full items-center justify-center gap-2 rounded-[12px] bg-brand px-4 py-3.5 text-base font-semibold text-white hover:bg-brand-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v11m0 0-4.5-4.5M12 15l4.5-4.5M5 19h14" />
                    </svg>
                    Unduh aplikasi
                </a>
                <p class="mt-3 text-center text-sm text-muted num">
                    Versi {{ $info['version'] }} · {{ KasirApk::humanSize($info['size']) }} ·
                    {{ Carbon::parse($info['uploaded_at'])->timezone(config('app.timezone'))->locale('id')->translatedFormat('j F Y') }}
                </p>
                @if ($info['notes'])
                    <div class="mt-4 border-t border-line pt-4">
                        <div class="text-[13px] font-semibold">Yang baru</div>
                        <p class="mt-1 whitespace-pre-line text-sm text-muted">{{ $info['notes'] }}</p>
                    </div>
                @endif
            </section>

            <section class="rounded-[14px] border border-line bg-white p-5">
                <h2 class="font-semibold">Cara memasang</h2>
                <ol class="mt-3 flex flex-col gap-3 text-sm">
                    @foreach ([
                        ['Ketuk <b>Unduh aplikasi</b>.', 'Kalau browser bertanya "file ini bisa membahayakan", pilih <b>Tetap unduh</b>.'],
                        ['Buka file yang terunduh.', 'Lewat notifikasi unduhan, atau aplikasi <b>File</b> → folder <b>Download</b>.'],
                        ['Izinkan pemasangan.', 'Kalau muncul "Instal aplikasi tidak dikenal", ketuk <b>Setelan</b>, aktifkan <b>Izinkan dari sumber ini</b>, lalu kembali.'],
                        ['Ketuk <b>Instal</b> (atau <b>Update</b>).', 'Kalau aplikasi sudah terpasang, data dan transaksi di HP tetap aman.'],
                        ['Buka <b>Kasir Men Gede</b>, masuk dengan PIN kasir.', 'Masuk pertama kali harus saat HP tersambung internet.'],
                    ] as $i => [$title, $hint])
                        <li class="flex gap-3">
                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-soft text-xs font-bold text-brand-dark">{{ $i + 1 }}</span>
                            <div>
                                <div>{!! $title !!}</div>
                                <div class="mt-0.5 text-muted">{!! $hint !!}</div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <p class="px-1 text-xs text-muted">
                Hanya untuk HP Android. Minta PIN kasir ke pemilik warung.
                Google Play Protect mungkin menampilkan peringatan karena aplikasi ini tidak dari Play Store; pilih <b>Tetap instal</b>.
            </p>
        @else
            <section class="rounded-[14px] border border-warn/20 bg-warn-soft p-5 text-sm text-warn-ink">
                Aplikasi belum tersedia untuk diunduh. Hubungi pemilik warung.
            </section>
        @endif
    </main>
</body>

</html>
