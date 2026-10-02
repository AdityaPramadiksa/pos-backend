@extends('admin.layout')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Riwayat Pengeluaran</h1>
                <p class="text-gray-400">Total: <span class="text-red-500 font-semibold">{{ $expenses->total() }}</span>
                    Catatan Kas Keluar</p>
            </div>

            {{-- 🔥 FITUR BARU: Form Filter Tanggal --}}
            <div class="flex items-center flex-wrap gap-3">
                <form method="GET" action="{{ route('admin.expenses.index') }}"
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

                    {{-- Tombol Reset Filter (Muncul kalau lagi difilter) --}}
                    @if (request('start_date') || request('end_date'))
                        <a href="{{ route('admin.expenses.index') }}"
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

        <div class="bg-[#2D303E] rounded-2xl border border-gray-700 overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#1F1D2B] text-gray-400 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="p-6">Tanggal & Waktu</th>
                            <th class="p-6">Kasir / Petugas</th>
                            <th class="p-6">Keterangan</th>
                            <th class="p-6 text-right">Nominal</th>
                            <th class="p-6 text-center">Bukti Nota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse ($expenses as $expense)
                            <tr class="hover:bg-[#1F1D2B]/50 transition group">
                                <td class="p-6 text-sm text-gray-300">
                                    {{ $expense->created_at->format('d M Y') }}<br>
                                    <span class="text-gray-500 text-xs">{{ $expense->created_at->format('H:i') }}
                                        WITA</span>
                                </td>
                                <td class="p-6 text-white font-medium">
                                    {{ $expense->user->name ?? 'System' }}
                                </td>
                                <td class="p-6 text-white">
                                    <span class="bg-gray-700/50 px-3 py-1 rounded-lg text-sm border border-gray-600">
                                        {{ $expense->description }}
                                    </span>
                                </td>
                                <td class="p-6 text-right font-bold text-red-400">
                                    - Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                </td>
                                <td class="p-6 text-center">
                                    @if ($expense->receipt_image)
                                        <a href="{{ asset('storage/' . $expense->receipt_image) }}" target="_blank"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 rounded-lg text-xs font-bold transition border border-blue-500/20"
                                            title="Lihat Nota">
                                            <i class="fas fa-image mr-1.5"></i> Lihat
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-500 italic"><i class="fas fa-times-circle mr-1"></i>
                                            Tanpa Nota</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-10 text-center text-gray-500 italic">
                                    <i class="fas fa-wallet fa-3x mb-3 block opacity-20"></i>
                                    @if (request('start_date') || request('end_date'))
                                        Tidak ada pengeluaran pada rentang tanggal tersebut.
                                    @else
                                        Belum ada catatan pengeluaran / kas keluar.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($expenses->hasPages())
                <div class="p-4 border-t border-gray-700 bg-[#1F1D2B]">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
