@extends('admin.layout')

@section('title', 'Pengaturan')

@section('content')
    @php $v = fn ($key) => old($key, $settings[$key]); @endphp

    <header>
        <h1 class="page-title">Pengaturan toko</h1>
        <p class="page-sub">Perubahan di sini dipakai aplikasi kasir: isi struk, pajak, modal awal shift, dan peringatan stok.</p>
    </header>

    <div class="flex flex-wrap items-start gap-6">
        {{-- Daftar bagian --}}
        <nav aria-label="Bagian pengaturan" class="flex w-full flex-row gap-1 overflow-x-auto lg:w-[220px] lg:flex-col">
            @foreach ([['struk', 'Identitas & struk', 'Nama, alamat, catatan kaki'], ['kasir', 'Kasir & pajak', 'Modal awal, PB1, stok'], ['pin', 'PIN admin', 'Untuk menyetujui pembatalan']] as [$id, $title, $sub])
                <button type="button" data-tab="{{ $id }}"
                    class="settings-tab flex shrink-0 flex-col rounded-lg border border-transparent px-3 py-2.5 text-left hover:bg-line2">
                    <span class="text-sm font-medium">{{ $title }}</span>
                    <span class="text-xs text-muted">{{ $sub }}</span>
                </button>
            @endforeach
        </nav>

        <div class="min-w-0 flex-[999_1_480px]">
            <form id="settings-form" action="{{ route('admin.settings.update') }}" method="POST" class="flex flex-col gap-6">
                @csrf

                {{-- Identitas & struk --}}
                <section data-panel="struk" class="flex flex-wrap items-start gap-6">
                    <div class="card card-pad flex min-w-0 flex-[999_1_380px] flex-col gap-5">
                        <div>
                            <h2 class="text-[17px] font-semibold">Identitas &amp; struk</h2>
                            <p class="mt-1 text-[13px] text-muted">Tampil di bagian atas dan bawah setiap struk pelanggan.</p>
                        </div>
                        <div>
                            <label class="label" for="shop_name">Nama warung</label>
                            <textarea id="shop_name" name="shop_name" rows="2" class="field" data-preview="name" required>{{ $v('shop_name') }}</textarea>
                            <p class="hint">Setiap baris jadi satu baris di struk, dicetak tebal di tengah.</p>
                        </div>
                        <div>
                            <label class="label" for="shop_address">Alamat</label>
                            <textarea id="shop_address" name="shop_address" rows="2" class="field" data-preview="address" required>{{ $v('shop_address') }}</textarea>
                            <p class="hint">Maksimal 32 huruf per baris supaya tidak terpotong di kertas 58 mm.</p>
                        </div>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="label" for="shop_phone">Nomor telepon</label>
                                <input id="shop_phone" name="shop_phone" class="field" data-preview="phone" value="{{ $v('shop_phone') }}" required>
                            </div>
                            <div>
                                <label class="label" for="receipt_footer">Catatan kaki struk</label>
                                <textarea id="receipt_footer" name="receipt_footer" rows="2" class="field" data-preview="footer" required>{{ $v('receipt_footer') }}</textarea>
                            </div>
                        </div>
                        <label class="flex cursor-pointer items-center justify-between gap-4 border-t border-line pt-5">
                            <span class="text-sm font-medium">Tampilkan nama kasir
                                <span class="block text-xs font-normal text-muted">Membantu melacak struk saat ada keluhan pelanggan</span>
                            </span>
                            <input type="checkbox" name="receipt_show_cashier" value="1" data-preview="cashier"
                                class="h-[18px] w-[18px] accent-brand" @checked($v('receipt_show_cashier') === '1')>
                        </label>
                    </div>

                    {{-- Pratinjau struk --}}
                    <aside class="flex w-full flex-col gap-2.5 sm:w-[320px]">
                        <div class="flex items-baseline justify-between">
                            <h2 class="card-title">Pratinjau struk</h2>
                            <span class="text-xs text-muted">Kertas 58 mm</span>
                        </div>
                        <div class="flex justify-center rounded-[14px] bg-line2 p-5">
                            <div class="w-[264px] bg-white px-3.5 pb-6 pt-5 font-mono text-[11px] leading-[1.5] text-ink shadow-sm"
                                style="font-variant-numeric: tabular-nums">
                                <div id="pv-name" class="whitespace-pre-line text-center font-semibold"></div>
                                <div id="pv-address" class="whitespace-pre-line text-center"></div>
                                <div class="text-center">Telp: <span id="pv-phone"></span></div>
<div class="whitespace-pre"><div>--------------------------------</div><div>Inv  : INV-{{ now()->format('Ymd') }}-0064</div><div id="pv-cashier">Kasir: {{ auth()->user()->name ?? 'Kasir' }}</div><div>Waktu: {{ now()->format('d/m/y H:i') }}</div><div>Tipe : DINE IN · Meja 7</div><div>--------------------------------</div><div class="font-semibold">Nasi Campur Babi Guling</div><div>2 x 35.000              70.000</div><div class="font-semibold">Es Teh</div><div>1 x 5.000                5.000</div><div>--------------------------------</div><div>SUBTOTAL                 75.000</div><div>PAJAK (PB1)               7.500</div><div class="font-semibold">TOTAL                 Rp 82.500</div><div> </div><div>TUNAI                Rp 100.000</div><div>KEMBALI               Rp 17.500</div><div> </div></div>
                                <div id="pv-footer" class="whitespace-pre-line text-center"></div>
                            </div>
                        </div>
                    </aside>
                </section>

                {{-- Kasir & pajak --}}
                <section data-panel="kasir" class="card card-pad flex flex-col gap-5" hidden>
                    <div>
                        <h2 class="text-[17px] font-semibold">Kasir &amp; pajak</h2>
                        <p class="mt-1 text-[13px] text-muted">Berlaku untuk shift dan transaksi berikutnya.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        <div>
                            <label class="label" for="starting_cash">Modal awal shift</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted">Rp</span>
                                <input id="starting_cash" type="number" min="0" step="1000" name="starting_cash" class="field num pl-10" value="{{ $v('starting_cash') }}" required>
                            </div>
                            <p class="hint">Uang tunai di laci saat kasir membuka shift.</p>
                        </div>
                        <div>
                            <label class="label" for="tax_rate">Pajak (PB1)</label>
                            <div class="relative">
                                <input id="tax_rate" type="number" min="0" max="100" step="0.5" name="tax_rate" class="field num pr-10" value="{{ $v('tax_rate') }}" required>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-muted">%</span>
                            </div>
                            <p class="hint">Dihitung dari subtotal sebelum diskon.</p>
                        </div>
                        <div>
                            <label class="label" for="low_stock_threshold">Batas stok menipis</label>
                            <div class="relative">
                                <input id="low_stock_threshold" type="number" min="0" name="low_stock_threshold" class="field num pr-16" value="{{ $v('low_stock_threshold') }}" required>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-muted">porsi</span>
                            </div>
                            <p class="hint">Menu di bawah angka ini ditandai di Ringkasan dan aplikasi kasir.</p>
                        </div>
                    </div>
                </section>

                {{-- Bar simpan --}}
                <div data-savebar class="sticky bottom-0 z-10 -mx-1 flex flex-wrap items-center justify-end gap-3 rounded-[14px] border border-line bg-white px-5 py-3.5 shadow-[0_-4px_16px_rgba(29,36,34,0.06)]">
                    <span id="dirty-note" class="mr-auto text-[13px] text-muted">Semua perubahan tersimpan</span>
                    <button type="reset" class="btn-ghost">Batalkan</button>
                    <button type="submit" class="btn-primary">Simpan perubahan</button>
                </div>
            </form>

            {{-- PIN admin (form terpisah) --}}
            <section data-panel="pin" class="card card-pad max-w-xl" hidden>
                <form action="{{ route('admin.settings.update_pin') }}" method="POST" class="flex flex-col gap-5">
                    @csrf
                    <div>
                        <h2 class="text-[17px] font-semibold">PIN admin</h2>
                        <p class="mt-1 text-[13px] text-muted">
                            PIN 4 angka milik akun Anda ({{ auth()->user()->name }}). Diminta aplikasi kasir saat membatalkan transaksi.
                            PIN login kasir diatur di halaman <a href="{{ route('admin.users.index') }}" class="btn-text">Staff</a>.
                        </p>
                    </div>
                    <div>
                        <label class="label" for="new_pin">PIN baru</label>
                        <input id="new_pin" type="password" name="new_pin" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" placeholder="4 angka" required
                            class="field max-w-[180px] text-center text-xl font-semibold tracking-[0.5em]">
                    </div>
                    <div>
                        <label class="label" for="admin_password">Password akun Anda</label>
                        <input id="admin_password" type="password" name="admin_password" class="field" placeholder="Untuk memastikan ini Anda" required>
                    </div>
                    <div>
                        <button type="submit" class="btn-dark">Ganti PIN</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.getElementById('settings-form');
            const saveBar = form.querySelector('[data-savebar]');

            // --- Tab ---
            const tabs = document.querySelectorAll('.settings-tab');
            const panels = document.querySelectorAll('[data-panel]');
            function show(id) {
                panels.forEach((p) => (p.hidden = p.dataset.panel !== id));
                tabs.forEach((t) => {
                    const on = t.dataset.tab === id;
                    t.classList.toggle('bg-white', on);
                    t.classList.toggle('border-line', on);
                    t.setAttribute('aria-current', on ? 'true' : 'false');
                });
                saveBar.hidden = id === 'pin';
                try { localStorage.setItem('settings-tab', id); } catch (e) {}
            }
            tabs.forEach((t) => t.addEventListener('click', () => show(t.dataset.tab)));
            let start = 'struk';
            try { start = localStorage.getItem('settings-tab') || 'struk'; } catch (e) {}
            @if ($errors->has('new_pin') || $errors->has('admin_password'))
                start = 'pin';
            @endif
            show(document.querySelector(`[data-tab="${start}"]`) ? start : 'struk');

            // --- Pratinjau struk ---
            const get = (k) => form.querySelector(`[data-preview="${k}"]`);
            function render() {
                document.getElementById('pv-name').textContent = get('name').value;
                document.getElementById('pv-address').textContent = get('address').value;
                document.getElementById('pv-phone').textContent = get('phone').value;
                document.getElementById('pv-footer').textContent = get('footer').value;
                document.getElementById('pv-cashier').hidden = !get('cashier').checked;
            }

            // --- Penanda perubahan belum disimpan ---
            const initial = new FormData(form);
            function dirtyCount() {
                const now = new FormData(form);
                let n = 0;
                for (const key of new Set([...initial.keys(), ...now.keys()])) {
                    if (key === '_token') continue;
                    if ((initial.get(key) ?? '') !== (now.get(key) ?? '')) n++;
                }
                return n;
            }
            function update() {
                render();
                const n = dirtyCount();
                document.getElementById('dirty-note').textContent = n ? `${n} perubahan belum disimpan` : 'Semua perubahan tersimpan';
            }
            form.addEventListener('input', update);
            form.addEventListener('change', update);
            form.addEventListener('reset', () => setTimeout(update));
            render();
        })();
    </script>
@endpush
