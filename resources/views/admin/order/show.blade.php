@extends('admin.layout')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.orders.index') }}"
                    class="p-2 bg-[#2D303E] rounded-xl text-gray-400 hover:text-white transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">Detail Pesanan</h1>
                    <p class="text-sm text-gray-400">ID Transaksi: <span
                            class="text-[#EA7C69] font-mono">{{ $order->receipt_number }}</span></p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                @if ($order->status == 'paid')
                    <span
                        class="px-4 py-2 bg-green-500/10 text-green-500 rounded-xl border border-green-500/20 text-xs font-bold tracking-widest">LUNAS</span>
                    <button onclick="window.print()"
                        class="p-2 bg-blue-500/10 text-blue-400 rounded-xl border border-blue-400/20 hover:bg-blue-400/20 transition">
                        <i class="fas fa-print mr-2"></i> Print Struk
                    </button>
                @elseif($order->status == 'void')
                    <span
                        class="px-4 py-2 bg-red-500/10 text-red-500 rounded-xl border border-red-500/20 text-xs font-bold tracking-widest">DIBATALKAN
                        (VOID)</span>
                @else
                    <span
                        class="px-4 py-2 bg-orange-500/10 text-orange-500 rounded-xl border border-orange-500/20 text-xs font-bold tracking-widest">PENDING</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700 grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">Kasir / Petugas</p>
                        <p class="text-white font-medium">{{ $order->user->name ?? 'System' }}</p>
                        <p class="text-xs text-gray-400">{{ $order->created_at->format('d M Y - H:i') }} WITA</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">Pelanggan</p>
                        <p class="text-white font-medium">{{ $order->customer_name ?? 'Guest / Walk-in' }}</p>
                        <p class="text-xs text-gray-400">Meja: {{ $order->table_number ?? '-' }}
                            ({{ strtoupper($order->order_type) }})</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">Metode Pembayaran</p>
                        <p class="text-white font-medium uppercase">{{ $order->payment_method ?? 'Belum Bayar' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold mb-2">Status Dapur</p>
                        <span class="text-green-400 text-xs"><i class="fas fa-check-circle mr-1"></i> Pesanan Sudah Dicetak
                            ke Dapur</span>
                    </div>
                </div>

                <div class="bg-[#2D303E] rounded-2xl border border-gray-700 overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-[#1F1D2B] text-gray-400 text-xs uppercase">
                            <tr>
                                <th class="p-4">Item Menu</th>
                                <th class="p-4 text-center">Harga</th>
                                <th class="p-4 text-center">Qty</th>
                                <th class="p-4 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="p-4">
                                        <div class="font-medium text-white">{{ $item->menu->name }}</div>
                                        {{-- 🔥 FITUR BARU: Menampilkan Note di Tabel Admin --}}
                                        @if ($item->note)
                                            <div class="text-xs text-yellow-500 italic mt-1 flex items-start">
                                                <i class="fas fa-edit mt-0.5 mr-1.5 text-[10px]"></i>
                                                <span>Note: {{ $item->note }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center text-gray-400">Rp
                                        {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td class="p-4 text-center text-white">{{ $item->qty }}</td>
                                    <td class="p-4 text-right text-white font-semibold">Rp
                                        {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($order->status == 'void')
                    <div class="bg-red-500/5 border border-red-500/20 p-6 rounded-2xl">
                        <h3 class="text-red-500 font-bold mb-2"><i class="fas fa-exclamation-circle mr-2"></i> Alasan
                            Pembatalan (VOID)</h3>
                        <p class="text-gray-300 text-sm">"{{ $order->void_reason }}"</p>
                        <p class="text-xs text-gray-500 mt-2 italic">Dibatalkan oleh: {{ $order->voidBy->name ?? 'Admin' }}
                            pada {{ $order->updated_at->format('d/m/y H:i') }}</p>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700 shadow-xl relative overflow-hidden">
                    <div class="absolute -top-4 -left-4 w-8 h-8 bg-[#1F1D2B] rounded-full"></div>
                    <div class="absolute -top-4 -right-4 w-8 h-8 bg-[#1F1D2B] rounded-full"></div>

                    <h3 class="text-lg font-bold text-white mb-6 text-center border-b border-dashed border-gray-600 pb-4">
                        Ringkasan Tagihan</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between text-gray-400">
                            <span>Subtotal</span>
                            <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Pajak (PB1 10%)</span>
                            <span>Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-gray-400">
                            <span>Diskon</span>
                            <span class="text-red-400">- Rp
                                {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>

                        <div class="border-t border-dashed border-gray-600 pt-4 mt-4 flex justify-between items-end">
                            <span class="text-white font-bold">TOTAL AKHIR</span>
                            <span class="text-2xl font-bold text-[#EA7C69]">Rp
                                {{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </div>

                        @if ($order->status == 'paid')
                            <div class="flex justify-between text-gray-400 pt-4 text-sm border-t border-gray-700">
                                <span>Dibayar</span>
                                <span>Rp {{ number_format($order->amount_paid, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-white font-medium">
                                <span>Kembali</span>
                                <span>Rp {{ number_format($order->amount_paid - $order->total_price, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($order->status != 'void')
                    <div class="bg-[#2D303E] p-4 rounded-2xl border border-gray-700">
                        <button onclick="document.getElementById('voidModal').classList.remove('hidden')"
                            class="w-full py-3 text-red-500 hover:bg-red-500/10 rounded-xl transition font-bold text-sm">
                            <i class="fas fa-times-circle mr-2"></i> Batalkan Transaksi (VOID)
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div id="voidModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
        <div class="bg-[#2D303E] w-full max-w-md p-8 rounded-3xl border border-gray-700 shadow-2xl">
            <h2 class="text-xl font-bold text-white mb-4">Konfirmasi VOID</h2>
            <p class="text-gray-400 text-sm mb-6">Harap masukkan alasan pembatalan transaksi <span
                    class="text-white font-bold">{{ $order->receipt_number }}</span> ini.</p>

            <form action="{{ route('admin.orders.void', $order->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <textarea name="void_reason" required
                    class="w-full bg-[#1F1D2B] border border-gray-600 rounded-xl p-4 text-white focus:border-red-500 outline-none transition"
                    placeholder="Contoh: Salah input menu / Pelanggan membatalkan pesanan" rows="3"></textarea>

                <div class="flex space-x-3">
                    <button type="button" onclick="document.getElementById('voidModal').classList.add('hidden')"
                        class="flex-1 py-3 text-gray-400 font-bold">Batal</button>
                    <button type="submit"
                        class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold transition">YA, VOID
                        SEKARANG</button>
                </div>
            </form>
        </div>
    </div>
@endsection
