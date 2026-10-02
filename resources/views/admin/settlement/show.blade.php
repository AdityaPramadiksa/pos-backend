@extends('admin.layout')

@section('content')
    <div class="space-y-6">
        {{-- Header & Tombol Kembali --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.settlements.index') }}"
                    class="p-2.5 bg-[#2D303E] text-gray-400 hover:text-white rounded-xl border border-gray-700 transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">Detail Settlement
                        #{{ str_pad($settlement->id, 5, '0', STR_PAD_LEFT) }}</h1>
                    <p class="text-gray-500 text-sm italic">Laporan shift kasir secara mendalam</p>
                </div>
            </div>

            {{-- Status Badge Gede --}}
            <div
                class="px-6 py-2 rounded-2xl {{ $settlement->status == 'open' ? 'bg-green-500/10 border border-green-500/50 text-green-400' : 'bg-gray-500/10 border border-gray-500/50 text-gray-400' }}">
                <span class="text-xs font-bold uppercase tracking-widest">{{ $settlement->status }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Card 1: Informasi Kasir --}}
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700 shadow-xl">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 bg-blue-500/10 text-blue-400 rounded-xl">
                        <i class="fas fa-user-circle fa-lg"></i>
                    </div>
                    <h3 class="text-white font-bold uppercase text-xs tracking-wider">Identitas Shift</h3>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between border-b border-gray-700 pb-2">
                        <span class="text-gray-500 text-sm">Nama Kasir</span>
                        <span class="text-white font-medium">{{ $settlement->user->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-700 pb-2">
                        <span class="text-gray-500 text-sm">Waktu Buka</span>
                        <span class="text-white font-medium">{{ $settlement->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-700 pb-2">
                        <span class="text-gray-500 text-sm">Waktu Tutup</span>
                        <span
                            class="text-white font-medium">{{ $settlement->closed_at ? \Carbon\Carbon::parse($settlement->closed_at)->format('H:i') : '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Card 2: Rincian Laci Tunai (Duit di Tangan) --}}
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700 shadow-xl border-t-4 border-t-[#EA7C69]">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 bg-[#EA7C69]/10 text-[#EA7C69] rounded-xl">
                        <i class="fas fa-cash-register fa-lg"></i>
                    </div>
                    <h3 class="text-white font-bold uppercase text-xs tracking-wider">Laporan Kas Tunai</h3>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-sm">Modal Awal</span>
                        <span class="text-white">Rp {{ number_format($settlement->starting_cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-sm">Total Sales (Cash)</span>
                        <span class="text-green-400 font-medium">+ Rp
                            {{ number_format($settlement->total_cash_sales, 0, ',', '.') }}</span>
                    </div>

                    {{-- 🔥 BARU: BARIS PETTY CASH (PENGELUARAN) --}}
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-sm">Kas Keluar (Petty Cash)</span>
                        <span class="text-red-400 font-medium">- Rp
                            {{ number_format($settlement->total_expenses ?? 0, 0, ',', '.') }}</span>
                    </div>

                    <div class="pt-4 mt-4 border-t border-gray-700">
                        <div class="flex justify-between items-center">
                            <div class="flex flex-col">
                                <span class="text-[#EA7C69] font-black text-sm uppercase">Total di Laci</span>
                                <span class="text-[10px] text-gray-500 italic">(Modal + Cash - Keluar)</span>
                            </div>
                            <span class="text-[#EA7C69] text-xl font-black italic">
                                Rp {{ number_format($expectedCash, 0, ',', '.') }}
                            </span>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1 italic text-right">*Wajib disetorkan ke Bos</p>
                    </div>
                </div>
            </div>
            {{-- Card 3: Rincian Non-Tunai --}}
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700 shadow-xl border-t-4 border-t-blue-400">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-3 bg-blue-400/10 text-blue-400 rounded-xl">
                        <i class="fas fa-credit-card fa-lg"></i>
                    </div>
                    <h3 class="text-white font-bold uppercase text-xs tracking-wider">Laporan Digital</h3>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-sm">QRIS</span>
                        <span class="text-white font-medium">Rp
                            {{ number_format($settlement->total_qris_sales, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-sm">Debit Card</span>
                        <span class="text-white font-medium">Rp
                            {{ number_format($settlement->total_debit_sales, 0, ',', '.') }}</span>
                    </div>
                    {{-- FIX: Tambahan Baris Credit --}}
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-sm">Credit Card</span>
                        <span class="text-white font-medium">Rp
                            {{ number_format($settlement->total_credit_sales, 0, ',', '.') }}</span>
                    </div>
                    {{-- FIX: Tambahan Baris Delivery/Ojol --}}
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-sm">Delivery (Ojol)</span>
                        <span class="text-white font-medium">Rp
                            {{ number_format($settlement->total_delivery_sales, 0, ',', '.') }}</span>
                    </div>

                    <div class="pt-4 mt-4 border-t border-gray-700">
                        <div class="flex justify-between items-center">
                            <span class="text-blue-400 font-bold text-sm uppercase">Total Digital</span>
                            <span class="text-white font-bold text-lg">
                                Rp {{ number_format($totalDigitalSales, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row Bawah: Omzet Keseluruhan & Notes --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div
                class="lg:col-span-2 bg-[#1F1D2B] p-6 rounded-2xl border border-gray-700 flex flex-col justify-center shadow-xl border-b-4 border-b-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-gray-400 font-bold uppercase text-[10px] tracking-widest mb-1">Total Omzet Penjualan
                            (Kotor)</h4>
                        <h2 class="text-4xl font-black text-green-400 italic shadow-sm">
                            Rp {{ number_format($grandTotalSales, 0, ',', '.') }}
                        </h2>
                    </div>
                    <div class="hidden md:block">
                        <div class="p-4 bg-green-500/10 rounded-full">
                            <i class="fas fa-chart-line text-5xl text-green-500 shadow-sm"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700 shadow-xl">
                <h4 class="text-gray-400 font-bold uppercase text-[10px] tracking-widest mb-3">Catatan Kasir</h4>
                <div class="bg-[#1F1D2B] p-4 rounded-xl border border-gray-700 min-h-[80px]">
                    <p class="text-gray-300 text-sm italic leading-relaxed">
                        "{{ $settlement->notes ?? 'Kasir tidak meninggalkan catatan khusus.' }}"
                    </p>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex justify-end gap-3 pt-4">
            <button onclick="window.print()"
                class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-lg">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
            <a href="{{ route('admin.settlements.index') }}"
                class="px-6 py-3 bg-[#EA7C69] hover:bg-[#f08d7d] text-white rounded-xl font-bold transition shadow-lg shadow-[#ea7c694d]">
                Kembali ke Daftar
            </a>
        </div>
    </div>
@endsection
