@extends('admin.layout')

@section('content')
    <div class="space-y-8">
        {{-- Header & Filter --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Laporan Settlement</h1>
                <p class="text-gray-400 text-sm">Pantau kas awal dan penjualan harian kasir</p>
            </div>

            {{-- 🔥 UI Filter Tanggal (Single Date) Selaras --}}
            <div class="flex items-center flex-wrap gap-3">
                <form action="{{ route('admin.settlements.index') }}" method="GET"
                    class="flex items-center space-x-2 bg-[#2D303E] p-1.5 rounded-xl border border-gray-700">

                    {{-- Hanya 1 Input Tanggal --}}
                    <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}"
                        class="bg-[#1F1D2B] border border-gray-600 text-gray-300 rounded-lg p-2 text-sm focus:border-[#EA7C69] focus:ring-1 focus:ring-[#EA7C69] outline-none [color-scheme:dark]">

                    <button type="submit" class="p-2 bg-[#EA7C69] hover:bg-[#d66a58] text-white rounded-lg transition"
                        title="Filter Data">
                        <i class="fas fa-filter"></i>
                    </button>

                    {{-- Tombol Reset Filter (Muncul kalau ada parameter date di URL) --}}
                    @if (request()->has('date'))
                        <a href="{{ route('admin.settlements.index') }}"
                            class="p-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg transition"
                            title="Kembali ke Hari Ini">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </form>

                <button onclick="window.location.reload()"
                    class="p-2.5 bg-[#2D303E] border border-gray-700 rounded-xl text-gray-400 hover:text-white transition"
                    title="Refresh Data">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>

        {{-- Statistik Ringkasan Cepat --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700">
                <p class="text-gray-400 text-xs uppercase font-bold tracking-widest mb-2">Total Kasir Aktif</p>
                <h3 class="text-2xl font-bold text-white">{{ $settlements->where('status', 'open')->count() }} <span
                        class="text-sm font-normal text-gray-500">Shift</span></h3>
            </div>
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700">
                <p class="text-gray-400 text-xs uppercase font-bold tracking-widest mb-2">Periode Laporan</p>
                <h3 class="text-lg font-bold text-[#EA7C69]">
                    {{ \Carbon\Carbon::parse(request('date', date('Y-m-d')))->format('d M Y') }}</h3>
            </div>
        </div>

        <div class="bg-[#2D303E] rounded-2xl border border-gray-700 overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#1F1D2B] text-gray-400 uppercase text-[10px] font-bold tracking-wider">
                        <tr>
                            <th class="p-6">Kasir / Waktu</th>
                            <th class="p-6 text-center">Kas Awal</th>
                            <th class="p-6 text-center">Total Penjualan</th>
                            <th class="p-6 text-center">Status</th>
                            <th class="p-6 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse ($settlements as $item)
                            <tr class="hover:bg-[#1F1D2B]/50 transition group">
                                <td class="p-6">
                                    <div class="font-bold text-white group-hover:text-[#EA7C69] transition">
                                        {{ $item->user->name ?? 'Staff' }}</div>
                                    <div class="text-[10px] text-gray-500 mt-1">
                                        <i class="far fa-clock mr-1"></i> {{ $item->created_at->format('H:i') }} WITA
                                    </div>
                                </td>
                                <td class="p-6 text-center text-gray-300">
                                    <span class="text-xs font-medium">Rp</span>
                                    {{ number_format($item->starting_cash, 0, ',', '.') }}
                                </td>
                                <td class="p-6 text-center">
                                    @php
                                        // FIX: Kalkulasi Total Penjualan dengan Credit & Delivery
                                        $totalSales =
                                            $item->total_cash_sales +
                                            $item->total_qris_sales +
                                            $item->total_debit_sales +
                                            $item->total_credit_sales +
                                            $item->total_delivery_sales;
                                    @endphp
                                    <div class="font-bold text-white">
                                        <span class="text-[#EA7C69]">Rp</span> {{ number_format($totalSales, 0, ',', '.') }}
                                    </div>

                                    {{-- FIX: Badge Metode Pembayaran --}}
                                    <div class="flex justify-center flex-wrap gap-1 mt-2 max-w-[150px] mx-auto">
                                        @if ($item->total_cash_sales > 0)
                                            <span title="Tunai"
                                                class="text-[9px] px-1.5 py-0.5 bg-green-500/10 text-green-500 rounded">C:{{ number_format($item->total_cash_sales / 1000, 0) }}k</span>
                                        @endif

                                        @if ($item->total_qris_sales > 0)
                                            <span title="QRIS"
                                                class="text-[9px] px-1.5 py-0.5 bg-blue-500/10 text-blue-500 rounded">Q:{{ number_format($item->total_qris_sales / 1000, 0) }}k</span>
                                        @endif

                                        @if ($item->total_debit_sales > 0)
                                            <span title="Debit"
                                                class="text-[9px] px-1.5 py-0.5 bg-purple-500/10 text-purple-500 rounded">D:{{ number_format($item->total_debit_sales / 1000, 0) }}k</span>
                                        @endif

                                        @if ($item->total_credit_sales > 0)
                                            <span title="Credit"
                                                class="text-[9px] px-1.5 py-0.5 bg-pink-500/10 text-pink-500 rounded">Cr:{{ number_format($item->total_credit_sales / 1000, 0) }}k</span>
                                        @endif

                                        @if ($item->total_delivery_sales > 0)
                                            <span title="Delivery"
                                                class="text-[9px] px-1.5 py-0.5 bg-orange-500/10 text-orange-500 rounded">Dv:{{ number_format($item->total_delivery_sales / 1000, 0) }}k</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-6 text-center">
                                    <span
                                        class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-tighter {{ $item->status == 'open' ? 'bg-green-500/20 text-green-400 border border-green-500/50' : 'bg-gray-500/20 text-gray-400 border border-gray-500/50' }}">
                                        ● {{ $item->status }}
                                    </span>
                                </td>
                                <td class="p-6 text-center">
                                    <a href="{{ route('admin.settlements.show', $item->id) }}"
                                        class="inline-flex items-center justify-center w-10 h-10 bg-[#1F1D2B] text-[#EA7C69] hover:bg-[#EA7C69] hover:text-white rounded-xl transition border border-gray-700 group-hover:border-[#EA7C69]">
                                        <i class="fas fa-chevron-right text-sm"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-20 text-center">
                                    <div class="flex flex-col items-center justify-center opacity-20">
                                        <i class="fas fa-calendar-times fa-4xl mb-4 text-gray-500"></i>
                                        <p class="text-xl font-bold">Tidak Ada Data</p>
                                        <p class="text-sm italic">Belum ada aktivitas kasir pada tanggal dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($settlements->hasPages())
                <div class="p-6 bg-[#1F1D2B]/30 border-t border-gray-700">
                    {{ $settlements->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
