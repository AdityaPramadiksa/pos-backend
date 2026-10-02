@extends('admin.layout')

@section('content')
    <div class="space-y-6">
        {{-- Header & Filter --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Laporan Penjualan</h1>
                <p class="text-gray-400">Periode: <span class="text-[#EA7C69] font-semibold">{{ $startDate->format('d M Y') }}
                        - {{ $endDate->format('d M Y') }}</span></p>
            </div>

            {{-- 🔥 UI Filter Tanggal Selaras dengan Petty Cash --}}
            <div class="flex items-center flex-wrap gap-3">
                <form action="{{ route('admin.reports.index') }}" method="GET"
                    class="flex items-center space-x-2 bg-[#2D303E] p-1.5 rounded-xl border border-gray-700">
                    <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                        class="bg-[#1F1D2B] border border-gray-600 text-gray-300 rounded-lg p-2 text-sm focus:border-[#EA7C69] focus:ring-1 focus:ring-[#EA7C69] outline-none [color-scheme:dark]">

                    <span class="text-gray-500">-</span>

                    <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                        class="bg-[#1F1D2B] border border-gray-600 text-gray-300 rounded-lg p-2 text-sm focus:border-[#EA7C69] focus:ring-1 focus:ring-[#EA7C69] outline-none [color-scheme:dark]">

                    <button type="submit" class="p-2 bg-[#EA7C69] hover:bg-[#d66a58] text-white rounded-lg transition"
                        title="Filter Data">
                        <i class="fas fa-filter"></i>
                    </button>

                    {{-- Tombol Reset Filter (Hanya muncul jika ada pencarian khusus) --}}
                    @if (request('start_date') || request('end_date'))
                        <a href="{{ route('admin.reports.index') }}"
                            class="p-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg transition" title="Reset Filter">
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

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6"> {{-- Diubah ke grid-cols-4 agar muat 4 kotak --}}
            {{-- Omzet Kotor --}}
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700">
                <p class="text-gray-400 text-xs uppercase font-bold mb-2">Total Pendapatan (Omzet)</p>
                <h2 class="text-3xl font-black text-white">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                </h2>
                <p class="text-[10px] text-gray-500 mt-1 italic">*Total semua uang masuk</p>
            </div>

            {{-- Pengeluaran --}}
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700">
                <p class="text-gray-400 text-xs uppercase font-bold mb-2">Total Pengeluaran (Petty Cash)</p>
                <h2 class="text-3xl font-black text-orange-400">- Rp
                    {{ number_format($summary['total_expenses'], 0, ',', '.') }}</h2>
                <p class="text-[10px] text-gray-500 mt-1 italic">*Uang keluar dari kasir</p>
            </div>

            {{-- Keuntungan Bersih --}}
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700 border-b-4 border-b-green-500">
                <p class="text-gray-400 text-xs uppercase font-bold mb-2">Keuntungan Bersih (Profit)</p>
                <h2 class="text-3xl font-black text-green-400">Rp {{ number_format($summary['net_profit'], 0, ',', '.') }}
                </h2>
                <p class="text-[10px] text-gray-500 mt-1 italic">*Pendapatan dikurangi pengeluaran</p>
            </div>

            {{-- Total Order --}}
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700">
                <p class="text-gray-400 text-xs uppercase font-bold mb-2">Total Pesanan</p>
                <h2 class="text-3xl font-black text-[#EA7C69]">{{ number_format($summary['total_orders'], 0) }}
                    <span class="text-sm font-normal text-gray-500 italic">Order</span>
                </h2>
                <p class="text-[10px] text-gray-500 mt-1 italic">*Total transaksi berhasil</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Tabel Menu Terlaris --}}
            <div class="bg-[#2D303E] rounded-2xl border border-gray-700 overflow-hidden">
                <div class="p-6 border-b border-gray-700 flex justify-between items-center">
                    <h3 class="text-white font-bold">5 Menu Terlaris</h3>
                    <i class="fas fa-fire text-orange-500"></i>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-[#1F1D2B] text-gray-500 uppercase text-[10px]">
                        <tr>
                            <th class="p-4">Menu</th>
                            <th class="p-4 text-center">Terjual</th>
                            <th class="p-4 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse ($topMenus as $menu)
                            {{-- Ganti @foreach jadi @forelse --}}
                            <tr class="text-gray-300">
                                <td class="p-4 font-medium">{{ $menu->name }}</td>
                                <td class="p-4 text-center text-white">{{ $menu->total_qty }}</td>
                                <td class="p-4 text-right">Rp {{ number_format($menu->total_revenue, 0, ',', '.') }}</td>
                            </tr>
                        @empty {{-- Tambahkan ini: Jika data kosong --}}
                            <tr>
                                <td colspan="3" class="p-10 text-center text-gray-500 italic">
                                    <div class="flex flex-col items-center justify-center opacity-30">
                                        <i class="fas fa-utensils fa-2xl mb-3"></i>
                                        <p class="text-xs uppercase tracking-widest font-bold">Belum ada menu terjual</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse {{-- Tutup dengan @endforelse --}}
                    </tbody>
                </table>
            </div>

            {{-- Breakdown Penjualan --}}
            <div class="space-y-6">
                {{-- Per Tipe Order --}}
                <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700">
                    <h3 class="text-white font-bold mb-4 italic">Breakdown Tipe Order</h3>
                    <div class="space-y-4">
                        @foreach ($salesByType as $type)
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-400 uppercase">{{ $type->order_type }}</span>
                                    <span class="text-white">Rp
                                        {{ number_format($type->total_amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-[#EA7C69] h-1.5 rounded-full"
                                        style="width: {{ ($type->total_amount / max($summary['total_revenue'], 1)) * 100 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Per Payment Method --}}
                <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700">
                    <h3 class="text-white font-bold mb-4 italic">Metode Pembayaran</h3>
                    <div class="flex flex-wrap gap-4">
                        @foreach ($salesByPayment as $pay)
                            <div class="bg-[#1F1D2B] p-3 rounded-xl border border-gray-700">
                                <p class="text-[10px] text-gray-500 uppercase mb-1">{{ $pay->payment_method }}</p>
                                <p class="text-white font-bold">Rp {{ number_format($pay->total_amount, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
