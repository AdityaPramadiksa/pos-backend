@extends('admin.layout')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Riwayat Pesanan</h1>
                <p class="text-gray-400">Total: <span class="text-[#EA7C69] font-semibold">{{ $orders->total() }}</span>
                    Transaksi Tercatat</p>
            </div>

            {{-- 🔥 FITUR BARU: Form Filter Tanggal Selaras --}}
            <div class="flex items-center flex-wrap gap-3">
                <form action="{{ route('admin.orders.index') }}" method="GET"
                    class="flex items-center space-x-2 bg-[#2D303E] p-1.5 rounded-xl border border-gray-700">
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="bg-[#1F1D2B] border border-gray-600 text-gray-300 rounded-lg p-2 text-sm focus:border-[#EA7C69] focus:ring-1 focus:ring-[#EA7C69] outline-none [color-scheme:dark]">

                    <span class="text-gray-500">-</span>

                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="bg-[#1F1D2B] border border-gray-600 text-gray-300 rounded-lg p-2 text-sm focus:border-[#EA7C69] focus:ring-1 focus:ring-[#EA7C69] outline-none [color-scheme:dark]">

                    <button type="submit" class="p-2 bg-[#EA7C69] hover:bg-[#d66a58] text-white rounded-lg transition"
                        title="Filter Data">
                        <i class="fas fa-filter"></i>
                    </button>

                    {{-- Tombol Reset Filter --}}
                    @if (request('start_date') || request('end_date'))
                        <a href="{{ route('admin.orders.index') }}"
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

        @if (session('success'))
            <div
                class="flex items-center bg-green-500/10 border border-green-500 text-green-500 p-4 rounded-xl text-sm animate-pulse">
                <i class="fas fa-check-circle mr-3"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-[#2D303E] rounded-2xl border border-gray-700 overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#1F1D2B] text-gray-400 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="p-6">No. Struk</th>
                            <th class="p-6">Waktu</th>
                            <th class="p-6">Pelanggan / Meja</th>
                            <th class="p-6">Total Harga</th>
                            <th class="p-6 text-center">Status</th>
                            <th class="p-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse ($orders as $order)
                            <tr
                                class="hover:bg-[#1F1D2B]/50 transition group {{ $order->status == 'void' ? 'opacity-50' : '' }}">
                                <td class="p-6">
                                    <span
                                        class="font-bold text-[#EA7C69] tracking-wider">{{ $order->receipt_number }}</span>
                                    <div class="text-[10px] text-gray-500 mt-1 uppercase">
                                        {{ $order->payment_method ?? 'N/A' }}</div>
                                </td>
                                <td class="p-6 text-sm text-gray-300">
                                    {{ $order->created_at->format('d M Y') }}<br>
                                    <span class="text-gray-500 text-xs">{{ $order->created_at->format('H:i') }} WITA</span>
                                </td>
                                <td class="p-6 text-white">
                                    <div class="font-medium">{{ $order->customer_name ?? 'Guest' }}</div>
                                    <div class="text-xs text-gray-500">
                                        Meja: {{ $order->table_number ?? '-' }}
                                        <span
                                            class="ml-2 px-1.5 py-0.5 bg-gray-700 rounded text-[10px] uppercase">{{ $order->order_type }}</span>
                                    </div>
                                </td>
                                <td class="p-6 text-white font-semibold">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                </td>
                                <td class="p-6 text-center">
                                    @if ($order->status == 'paid')
                                        <span
                                            class="inline-flex items-center px-3 py-1 bg-green-500/10 text-green-500 rounded-lg text-xs font-bold border border-green-500/20 shadow-[0_0_10px_rgba(34,197,94,0.2)]">
                                            <i class="fas fa-check-circle mr-1.5"></i> PAID
                                        </span>
                                    @elseif($order->status == 'pending')
                                        <span
                                            class="inline-flex items-center px-3 py-1 bg-orange-500/10 text-orange-500 rounded-lg text-xs font-bold border border-orange-500/20">
                                            <i class="fas fa-clock mr-1.5"></i> PENDING
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-3 py-1 bg-red-500/10 text-red-500 rounded-lg text-xs font-bold border border-red-500/20">
                                            <i class="fas fa-times-circle mr-1.5"></i> VOID
                                        </span>
                                    @endif
                                </td>
                                <td class="p-6">
                                    <div class="flex justify-center items-center">
                                        <a href="{{ route('admin.orders.show', $order->id) }}"
                                            class="p-2.5 text-blue-400 hover:bg-blue-400/10 rounded-xl transition duration-200"
                                            title="Lihat Detail Pesanan">
                                            <i class="fas fa-eye text-lg"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-gray-500 italic">
                                    <i class="fas fa-receipt fa-3x mb-3 block opacity-20"></i>
                                    @if (request('start_date') || request('end_date'))
                                        Tidak ada pesanan pada rentang tanggal tersebut.
                                    @else
                                        Belum ada riwayat pesanan hari ini.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($orders->hasPages())
                <div class="p-4 border-t border-gray-700 bg-[#1F1D2B]">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
