@extends('admin.layout')

@section('title', 'Pesanan ' . $order->receipt_number)

@section('content')
    @php
        $rp = fn ($v) => 'Rp ' . number_format((int) $v, 0, ',', '.');
        $typeLabel = ['dine_in' => 'Makan di sini', 'to_go' => 'Bungkus', 'delivery' => 'Ojol'][$order->order_type] ?? $order->order_type;
        $payLabel = $order->payment_method === 'delivery'
            ? 'Lewat aplikasi ' . ucfirst($order->delivery_platform ?? 'ojol')
            : ($order->payment_method ? ($order->payment_method === 'cash' ? 'Tunai' : strtoupper($order->payment_method)) : 'Belum dibayar');
    @endphp

    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="btn-text">← Kembali ke pesanan</a>
            <h1 class="page-title mt-2">{{ $order->receipt_number }}</h1>
            <p class="page-sub">{{ $order->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY · HH:mm') }} WITA</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($order->status == 'paid')
                <span class="chip-ok text-sm">Lunas</span>
            @elseif ($order->status == 'pending')
                <span class="chip-warn text-sm">Belum dibayar</span>
            @else
                <span class="chip-bad text-sm">Dibatalkan</span>
            @endif
        </div>
    </header>

    <div class="flex flex-wrap items-start gap-6">
        <div class="flex min-w-0 flex-[999_1_520px] flex-col gap-6">
            <section class="card card-pad grid grid-cols-2 gap-5 sm:grid-cols-4">
                <div>
                    <div class="text-xs text-muted">Kasir</div>
                    <div class="mt-0.5 font-medium">{{ $order->user->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-xs text-muted">Pelanggan</div>
                    <div class="mt-0.5 font-medium">{{ $order->customer_name ?: 'Pelanggan umum' }}</div>
                </div>
                <div>
                    <div class="text-xs text-muted">Jenis</div>
                    <div class="mt-0.5 font-medium">{{ $typeLabel }}{{ $order->table_number && $order->table_number !== '-' ? ' · Meja ' . $order->table_number : '' }}</div>
                </div>
                <div>
                    <div class="text-xs text-muted">Cara bayar</div>
                    <div class="mt-0.5 font-medium">{{ $payLabel }}</div>
                </div>
            </section>

            <section class="card">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th class="pl-5">Menu</th>
                            <th class="text-right">Harga</th>
                            <th class="text-right">Jumlah</th>
                            <th class="pr-5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="num">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="pl-5">
                                    <div class="font-medium">{{ $item->menu->name ?? 'Menu dihapus' }}</div>
                                    @if ($item->note)
                                        <div class="text-xs text-warn-ink">Catatan: {{ $item->note }}</div>
                                    @endif
                                </td>
                                <td class="text-right text-muted">{{ $rp($item->price) }}</td>
                                <td class="text-right">{{ $item->qty }}</td>
                                <td class="pr-5 text-right font-medium">{{ $rp($item->subtotal) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            @if ($order->status == 'void')
                <section class="rounded-[14px] border border-bad/20 bg-bad-soft p-5">
                    <h2 class="font-semibold text-bad">Transaksi dibatalkan</h2>
                    <p class="mt-1 text-sm">Alasan: {{ $order->void_reason }}</p>
                    <p class="mt-1 text-xs text-muted">Oleh {{ $order->voidBy->name ?? 'admin' }} · {{ $order->updated_at->format('d/m/y H:i') }}</p>
                </section>
            @endif
        </div>

        <aside class="flex w-full flex-col gap-4 sm:w-[320px]">
            <section class="card card-pad num flex flex-col gap-2 text-sm">
                <h2 class="card-title mb-1">Rincian tagihan</h2>
                <div class="flex justify-between text-muted"><span>Subtotal</span><span>{{ $rp($order->subtotal) }}</span></div>
                <div class="flex justify-between text-muted"><span>Pajak (PB1)</span><span>{{ $rp($order->tax_amount) }}</span></div>
                @if ($order->discount_amount > 0)
                    <div class="flex justify-between text-muted"><span>Diskon</span><span>− {{ $rp($order->discount_amount) }}</span></div>
                @endif
                <div class="mt-2 flex items-baseline justify-between border-t border-line pt-3">
                    <span class="font-semibold">Total</span><span class="text-xl font-bold">{{ $rp($order->total_price) }}</span>
                </div>
                @if ($order->status == 'paid' && $order->payment_method === 'cash')
                    <div class="flex justify-between text-muted"><span>Uang diterima</span><span>{{ $rp($order->amount_paid) }}</span></div>
                    <div class="flex justify-between text-muted"><span>Kembalian</span><span>{{ $rp($order->change_amount) }}</span></div>
                @endif
            </section>

            @if ($order->status != 'void')
                <button type="button" class="btn-ghost text-bad" onclick="document.getElementById('void-dialog').showModal()">
                    Batalkan transaksi
                </button>
            @endif
        </aside>
    </div>

    <dialog id="void-dialog" class="w-[min(92vw,440px)] rounded-[14px] border border-line p-0 backdrop:bg-ink/40">
        <form action="{{ route('admin.orders.void', $order->id) }}" method="POST" class="flex flex-col gap-4 p-6">
            @csrf
            @method('PATCH')
            <div>
                <h2 class="text-[17px] font-semibold">Batalkan {{ $order->receipt_number }}?</h2>
                <p class="mt-1 text-sm text-muted">Stok menu dikembalikan dan total shift dihitung ulang. Tindakan ini tidak bisa diurungkan.</p>
            </div>
            <div>
                <label class="label" for="void_reason">Alasan pembatalan</label>
                <textarea id="void_reason" name="void_reason" rows="3" class="field" required
                    placeholder="Contoh: Salah input menu, pelanggan membatalkan pesanan"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="btn-ghost" onclick="this.closest('dialog').close()">Kembali</button>
                <button type="submit" class="btn-danger">Batalkan transaksi</button>
            </div>
        </form>
    </dialog>
@endsection
