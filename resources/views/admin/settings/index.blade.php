@extends('admin.layout')

@section('content')
    {{-- Notifikasi Sukses/Error --}}
    @if (session('success'))
        <div
            class="bg-green-500/10 border border-green-500 text-green-500 p-4 rounded-xl mb-6 flex items-center animate-pulse">
            <i class="fas fa-check-circle mr-3"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-500/10 border border-red-500 text-red-500 p-4 rounded-xl mb-6 flex items-center">
            <i class="fas fa-exclamation-triangle mr-3"></i>
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row gap-8">
        {{-- SIDEBAR SETTINGS --}}
        <div class="w-full md:w-80 space-y-2">
            <h1 class="text-3xl font-bold text-white mb-6">Settings</h1>

            <div class="bg-[#1F1D2B] rounded-2xl overflow-hidden border border-gray-700">
                <button onclick="showSetting('resto')"
                    class="setting-nav w-full flex items-center p-6 gap-4 hover:bg-[#2D303E] transition active-setting"
                    id="nav-resto">
                    <i class="fas fa-store text-[#EA7C69]"></i>
                    <div class="text-left">
                        <p class="text-white font-medium">Your Restaurant</p>
                        <p class="text-xs text-gray-500">Shop name, address, phone</p>
                    </div>
                </button>

                <button onclick="showSetting('security')"
                    class="setting-nav w-full flex items-center p-6 gap-4 hover:bg-[#2D303E] transition" id="nav-security">
                    <i class="fas fa-lock text-[#EA7C69]"></i>
                    <div class="text-left">
                        <p class="text-white font-medium">Security & PIN</p>
                        <p class="text-xs text-gray-500">App login PIN for staff</p>
                    </div>
                </button>

                <button onclick="showSetting('modal')"
                    class="setting-nav w-full flex items-center p-6 gap-4 hover:bg-[#2D303E] transition" id="nav-modal">
                    <i class="fas fa-cash-register text-[#EA7C69]"></i>
                    <div class="text-left">
                        <p class="text-white font-medium">Kasir & Modal</p>
                        <p class="text-xs text-gray-500">Fixed starting cash amount</p>
                    </div>
                </button>

                <button onclick="showSetting('tax')"
                    class="setting-nav w-full flex items-center p-6 gap-4 hover:bg-[#2D303E] transition" id="nav-tax">
                    <i class="fas fa-percentage text-[#EA7C69]"></i>
                    <div class="text-left">
                        <p class="text-white font-medium">Tax & Service</p>
                        <p class="text-xs text-gray-500">PB1, Service charge settings</p>
                    </div>
                </button>

                <button onclick="showSetting('stock')"
                    class="setting-nav w-full flex items-center p-6 gap-4 hover:bg-[#2D303E] transition" id="nav-stock">
                    <i class="fas fa-exclamation-triangle text-[#EA7C69]"></i>
                    <div class="text-left">
                        <p class="text-white font-medium">Inventory Alert</p>
                        <p class="text-xs text-gray-500">Low stock notification threshold</p>
                    </div>
                </button>

                <button onclick="showSetting('printer')"
                    class="setting-nav w-full flex items-center p-6 gap-4 hover:bg-[#2D303E] transition" id="nav-printer">
                    <i class="fas fa-print text-[#EA7C69]"></i>
                    <div class="text-left">
                        <p class="text-white font-medium">Printer Setup</p>
                        <p class="text-xs text-gray-500">Bluetooth & IP printer config</p>
                    </div>
                </button>

                <button onclick="showSetting('database')"
                    class="setting-nav w-full flex items-center p-6 gap-4 hover:bg-[#2D303E] transition" id="nav-database">
                    <i class="fas fa-database text-[#EA7C69]"></i>
                    <div class="text-left">
                        <p class="text-white font-medium">Backup & Reset</p>
                        <p class="text-xs text-gray-500">Secure your transaction data</p>
                    </div>
                </button>
            </div>
        </div>

        {{-- CONTENT SETTINGS --}}
        <div class="flex-1 relative pb-24"> {{-- Padding bottom ditambahkan untuk ruang tombol save --}}
            {{-- BUNGKUS SEMUA DENGAN SATU FORM UTAMA --}}
            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf

                <div class="bg-[#1F1D2B] rounded-2xl border border-gray-700 p-8 shadow-xl min-h-[500px]">

                    {{-- SECTION: RESTO IDENTITY --}}
                    <div id="section-resto" class="setting-section space-y-6">
                        <h2 class="text-xl font-bold text-white mb-6 underline decoration-[#EA7C69] underline-offset-8">
                            Restaurant Identity</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Nama Restaurant</label>
                                <input type="text" name="shop_name" value="{{ old('shop_name', $shop_name ?? '') }}"
                                    class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Nomor Telepon</label>
                                <input type="text" name="shop_phone" value="{{ old('shop_phone', $shop_phone ?? '') }}"
                                    class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                                    required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm text-gray-400 mb-2">Alamat Lengkap (Struk Header)</label>
                                <textarea name="shop_address" rows="3"
                                    class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                                    required>{{ old('shop_address', $shop_address ?? '') }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm text-gray-400 mb-2">Pesan Kaki (Footer Struk)</label>
                                <input type="text" name="receipt_footer"
                                    value="{{ old('receipt_footer', $receipt_footer ?? '') }}"
                                    class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                                    required>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: KASIR & MODAL --}}
                    <div id="section-modal" class="setting-section hidden space-y-6">
                        <h2 class="text-xl font-bold text-white mb-6 underline decoration-[#EA7C69] underline-offset-8">
                            Konfigurasi Kasir</h2>
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Kas Awal Tetap (Modal Kembalian)</label>
                            <div class="relative max-w-xs">
                                <span class="absolute left-4 top-3.5 text-gray-500">Rp</span>
                                <input type="number" name="starting_cash"
                                    value="{{ old('starting_cash', $starting_cash ?? 0) }}"
                                    class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-3 pl-12 text-white focus:border-[#EA7C69] outline-none transition"
                                    required>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 italic">*Akan muncul otomatis di laci kasir setiap
                                pembukaan shift.</p>
                        </div>
                    </div>

                    {{-- SECTION: PAJAK --}}
                    <div id="section-tax" class="setting-section hidden space-y-6">
                        <h2 class="text-xl font-bold text-white mb-6 underline decoration-[#EA7C69] underline-offset-8">
                            Pajak & Layanan</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Pajak Restoran (PB1)</label>
                                <div class="relative">
                                    <input type="number" name="tax_rate" value="{{ old('tax_rate', $tax_rate ?? 0) }}"
                                        class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                                        required>
                                    <span class="absolute right-4 top-3.5 text-gray-500">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Service Charge</label>
                                <div class="relative">
                                    <input type="number" name="service_charge"
                                        value="{{ old('service_charge', $service_charge ?? 0) }}"
                                        class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                                        required>
                                    <span class="absolute right-4 top-3.5 text-gray-500">Rp</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: STOK --}}
                    <div id="section-stock" class="setting-section hidden space-y-6">
                        <h2 class="text-xl font-bold text-white mb-6 underline decoration-[#EA7C69] underline-offset-8">
                            Stok Alert</h2>
                        <div class="max-w-xs">
                            <label class="block text-sm text-gray-400 mb-2">Ambang Batas Stok Rendah</label>
                            <div class="flex items-center gap-4">
                                <input type="number" name="low_stock_threshold"
                                    value="{{ old('low_stock_threshold', $low_stock_threshold ?? 10) }}"
                                    class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                                    required>
                                <span class="text-gray-400">Porsi</span>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: PRINTER --}}
                    <div id="section-printer" class="setting-section hidden space-y-6">
                        <h2 class="text-xl font-bold text-white mb-6 underline decoration-[#EA7C69] underline-offset-8">
                            Printer Default</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Target Printer (IP Address / Name)</label>
                                <input type="text" name="printer_target"
                                    value="{{ old('printer_target', $printer_target ?? '') }}"
                                    placeholder="192.168.1.100"
                                    class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-2">Tipe Koneksi</label>
                                <select name="printer_type"
                                    class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                                    required>
                                    <option value="network" {{ ($printer_type ?? '') == 'network' ? 'selected' : '' }}>
                                        Network / IP</option>
                                    <option value="bluetooth"
                                        {{ ($printer_type ?? '') == 'bluetooth' ? 'selected' : '' }}>Bluetooth</option>
                                    <option value="usb" {{ ($printer_type ?? '') == 'usb' ? 'selected' : '' }}>USB
                                        (Local)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- TOMBOL SAVE GLOBAL (Untuk Semua Tab di Atas) --}}
                    <div
                        class="absolute bottom-0 left-0 right-0 bg-[#2D303E] p-6 border-t border-gray-700 rounded-b-2xl flex justify-end">
                        <button type="submit"
                            class="bg-[#EA7C69] hover:bg-[#f08d7d] text-white font-bold py-3 px-8 rounded-xl transition shadow-lg shadow-[#ea7c694d] flex items-center gap-2">
                            <i class="fas fa-save"></i> Save All Settings
                        </button>
                    </div>
                </div>
            </form>

            {{-- SECTION: SECURITY (FORM TERPISAH KARENA INI KHUSUS GANTI PIN) --}}
            <div id="section-security"
                class="setting-section hidden absolute top-0 left-0 w-full h-full bg-[#1F1D2B] rounded-2xl border border-gray-700 p-8 z-10 shadow-xl">
                <form action="{{ route('admin.settings.update_pin') }}" method="POST">
                    @csrf
                    <h2 class="text-xl font-bold text-white mb-6 underline decoration-[#EA7C69] underline-offset-8">
                        Security & PIN Login</h2>
                    <div class="max-w-md space-y-6">
                        <div>
                            <label class="block text-sm text-gray-400 mb-2 font-bold">PIN Login Baru (4 Digit
                                Angka)</label>
                            <input type="password" name="new_pin" maxlength="4" placeholder="XXXX" required
                                class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-4 text-white text-center font-bold text-2xl tracking-[0.5em] focus:border-[#EA7C69] outline-none transition">
                            <p class="text-[10px] text-gray-500 mt-2 italic">*PIN ini digunakan oleh Staff/Kasir untuk
                                login ke aplikasi POS.</p>
                        </div>
                        <div class="pt-6 border-t border-gray-700">
                            <label class="block text-sm text-red-400 mb-2 font-bold uppercase tracking-tighter">Konfirmasi
                                Password Admin</label>
                            <input type="password" name="admin_password" required
                                placeholder="Masukkan password login Anda"
                                class="w-full bg-[#2D303E] border border-gray-600 rounded-xl p-3 text-white focus:border-red-500 outline-none transition">
                        </div>
                        <button type="submit"
                            class="w-full bg-red-500 hover:bg-red-400 text-white font-bold py-4 rounded-xl transition shadow-lg flex items-center justify-center gap-2">
                            <i class="fas fa-key"></i> Update Security PIN
                        </button>
                    </div>
                </form>
            </div>

            {{-- SECTION: DATABASE (FORM TERPISAH) --}}
            <div id="section-database"
                class="setting-section hidden absolute top-0 left-0 w-full h-full bg-[#1F1D2B] rounded-2xl border border-gray-700 p-8 z-10 shadow-xl">
                <h2 class="text-xl font-bold text-white mb-6 underline decoration-[#EA7C69] underline-offset-8">Data
                    Security</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button type="button"
                        class="p-6 bg-blue-500/10 border border-blue-500 text-blue-500 rounded-2xl hover:bg-blue-500 hover:text-white transition flex flex-col items-center gap-2">
                        <i class="fas fa-download text-2xl"></i>
                        <span class="font-bold">Backup Database (.sql)</span>
                    </button>
                    <button type="button"
                        class="p-6 bg-red-500/10 border border-red-500 text-red-500 rounded-2xl hover:bg-red-500 hover:text-white transition flex flex-col items-center gap-2"
                        onclick="return confirm('SEMUA data transaksi akan dihapus. Lanjutkan?')">
                        <i class="fas fa-trash-alt text-2xl"></i>
                        <span class="font-bold">Reset Transaksi (Ulang 0)</span>
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        function showSetting(id) {
            // Sembunyikan semua section
            document.querySelectorAll('.setting-section').forEach(s => s.classList.add('hidden'));
            // Tampilkan yang dipilih
            document.getElementById('section-' + id).classList.remove('hidden');

            // Atur styling nav button
            document.querySelectorAll('.setting-nav').forEach(n => n.classList.remove('active-setting', 'bg-[#2D303E]'));
            const activeNav = document.getElementById('nav-' + id);
            if (activeNav) activeNav.classList.add('active-setting', 'bg-[#2D303E]');
        }

        // CSS Custom via JS for active state
        const style = document.createElement('style');
        style.innerHTML = `
            .active-setting { border-right: 4px solid #EA7C69; }
        `;
        document.head.appendChild(style);
    </script>
@endsection
