@extends('admin.layout')

@section('title', 'Aplikasi kasir')

@php
    use App\Services\KasirApk;
    use Carbon\Carbon;
@endphp

@section('content')
    <header>
        <h1 class="page-title">Aplikasi kasir</h1>
        <p class="page-sub">HP outlet memasang dan memperbarui aplikasi dari halaman unduhan, tanpa kabel atau mode developer.</p>
    </header>

    <div class="flex flex-wrap items-start gap-6">
        <section class="card card-pad flex min-w-0 flex-[999_1_420px] flex-col gap-5">
            <div class="flex items-start gap-4">
                <img src="{{ asset('kasir-icon.png') }}" alt="" class="h-14 w-14 shrink-0 rounded-[14px]">
                <div class="min-w-0">
                    <h2 class="card-title">Kasir Men Gede</h2>
                    @if ($info)
                        <p class="mt-0.5 text-sm text-muted num">
                            Versi {{ $info['version'] }} · {{ KasirApk::humanSize($info['size']) }} ·
                            diunggah {{ Carbon::parse($info['uploaded_at'])->timezone(config('app.timezone'))->locale('id')->translatedFormat('j M Y, H:i') }}
                        </p>
                    @else
                        <p class="mt-0.5 text-sm text-warn">Belum ada APK. Unggah dulu supaya halaman unduhan bisa dipakai.</p>
                    @endif
                </div>
            </div>

            <div>
                <label class="label" for="download-url">Link unduhan untuk HP outlet</label>
                <div class="flex gap-2">
                    <input id="download-url" type="text" class="field num" value="{{ $downloadUrl }}" readonly>
                    <button type="button" class="btn-ghost shrink-0" id="copy-url">
                        <i class="fa-regular fa-copy"></i><span>Salin</span>
                    </button>
                    <a href="{{ $downloadUrl }}" target="_blank" rel="noopener" class="btn-ghost shrink-0">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i><span class="hidden sm:inline">Buka</span>
                    </a>
                </div>
                <p class="mt-1.5 text-xs text-muted">Kirim link ini lewat WhatsApp, atau cetak kode QR di bawah dan tempel di meja kasir.</p>
            </div>

            <div id="qr-card" class="flex flex-wrap items-center gap-5 rounded-xl border border-line p-4">
                <div id="qr" class="rounded-lg bg-white p-2" aria-label="Kode QR halaman unduhan"></div>
                <div class="min-w-0 flex-1 text-sm">
                    <div class="font-semibold">Pindai untuk mengunduh</div>
                    <p class="mt-1 text-muted">Buka kamera HP, arahkan ke kode ini, lalu ketuk link yang muncul.</p>
                    <button type="button" class="btn-ghost mt-3" onclick="window.print()">
                        <i class="fa-solid fa-print"></i><span>Cetak kode QR</span>
                    </button>
                </div>
            </div>

            @if ($info && $info['notes'])
                <div>
                    <div class="label">Yang baru di versi ini</div>
                    <p class="whitespace-pre-line text-sm text-muted">{{ $info['notes'] }}</p>
                </div>
            @endif
        </section>

        <form action="{{ route('admin.app_release.store') }}" method="POST" enctype="multipart/form-data"
            class="card card-pad flex w-full flex-col gap-4 md:w-[360px] print:hidden" id="upload-form">
            @csrf
            <h2 class="card-title">{{ $info ? 'Unggah versi baru' : 'Unggah APK' }}</h2>
            <div>
                <label class="label" for="apk">File APK</label>
                <input id="apk" type="file" name="apk" accept=".apk,application/vnd.android.package-archive" class="field py-2" required>
                <p class="mt-1.5 text-xs text-muted">
                    Dari laptop: <span class="num">build\app\outputs\flutter-apk\app-release.apk</span>
                    (satu file untuk semua jenis HP).
                </p>
            </div>
            <div>
                <label class="label" for="version">Nomor versi</label>
                <input id="version" type="text" name="version" class="field num" required maxlength="20"
                    value="{{ old('version') }}" placeholder="Contoh: 1.0.1">
                <p class="mt-1.5 text-xs text-muted">Samakan dengan <span class="num">version</span> di pubspec.yaml (tanpa angka setelah tanda +).</p>
            </div>
            <div>
                <label class="label" for="notes">Yang baru <span class="font-normal text-muted">(opsional)</span></label>
                <textarea id="notes" name="notes" rows="3" class="field" maxlength="1000"
                    placeholder="Contoh: Ikon baru, perbaikan cetak struk">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="btn-primary" id="upload-btn">
                <i class="fa-solid fa-cloud-arrow-up"></i><span>Unggah &amp; bagikan</span>
            </button>
            <p class="text-xs text-muted">Versi lama langsung diganti. HP yang sudah terpasang cukup mengunduh ulang dan memilih <b>Update</b>; datanya tidak hilang.</p>
        </form>
    </div>
@endsection

@push('head')
    <style>
        /* Cetak kode QR saja, untuk ditempel di meja kasir */
        @media print {
            body * { visibility: hidden; }
            #qr-card, #qr-card * { visibility: visible; }
            #qr-card { position: absolute; left: 0; top: 0; border: 0; }
            #qr-card button { display: none; }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('qr'), {
            text: @json($downloadUrl), width: 136, height: 136,
            colorDark: '#1D2422', colorLight: '#FFFFFF', correctLevel: QRCode.CorrectLevel.M,
        });

        document.getElementById('copy-url').addEventListener('click', async (e) => {
            const input = document.getElementById('download-url');
            const label = e.currentTarget.querySelector('span');
            try {
                await navigator.clipboard.writeText(input.value);
            } catch (_) {
                input.select();
                document.execCommand('copy');
            }
            label.textContent = 'Tersalin';
            setTimeout(() => (label.textContent = 'Salin'), 2000);
        });

        // APK besar butuh waktu: cegah tombol ditekan dua kali
        document.getElementById('upload-form').addEventListener('submit', () => {
            const btn = document.getElementById('upload-btn');
            btn.disabled = true;
            btn.querySelector('span').textContent = 'Mengunggah… jangan tutup halaman';
        });
    </script>
@endpush
