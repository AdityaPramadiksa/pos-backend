@extends('admin.layout')

@section('title', 'Pesanan')

@section('content')
    @php
        $rp = fn ($v) => 'Rp ' . number_format((int) $v, 0, ',', '.');
        $typeLabel = ['dine_in' => 'Makan di sini', 'to_go' => 'Bungkus', 'delivery' => 'Ojol'];
        $payLabel = fn ($o) => $o->payment_method === 'delivery'
            ? ucfirst($o->delivery_platform ?? 'Ojol')
            : ($o->payment_method ? ($o->payment_method === 'cash' ? 'Tunai' : strtoupper($o->payment_method)) : '—');
        $statusSelect = '<div><label class="label text-xs text-muted" for="status">Status</label><select id="status" name="status" class="field w-auto py-2">'
            . '<option value="">Semua</option>'
            . collect(['paid' => 'Lunas', 'pending' => 'Belum dibayar', 'void' => 'Dibatalkan'])->map(fn ($l, $k) => '<option value="' . $k . '"' . (request('status') === $k ? ' selected' : '') . '>' . $l . '</option>')->implode('')
            . '</select></div>';
    @endphp

    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Pesanan</h1>
            <p class="page-sub">{{ number_format($orders->total(), 0, ',', '.') }} transaksi tercatat</p>
        </div>
        @include('admin.partials.date-filter', ['action' => route('admin.orders.index'), 'extra' => $statusSelect])
    </header>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="tbl min-w-[860px]">
                <thead>
                    <tr>
                        <th class="pl-5">No. struk</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Jenis</th>
                        <th>Cara bayar</th>
                        <th class="text-right">Total</th>
                        <th class="pr-5">Status</th>
                    </tr>
                </thead>
                <tbody class="num">
                    @forelse ($orders as $order)
                        <tr class="cursor-pointer {{ $order->status == 'void' ? 'text-faint' : '' }}" onclick="location.href='{{ route('admin.orders.show', $order->id) }}'">
                            <td class="pl-5">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="font-medium hover:underline {{ $order->status == 'void' ? 'line-through' : '' }}">{{ $order->receipt_number }}</a>
                            </td>
                            <td class="whitespace-nowrap text-muted">{{ $order->created_at->locale('id')->isoFormat('D MMM, HH:mm') }}</td>
                            <td>
                                <div>{{ $order->customer_name ?: 'Pelanggan umum' }}</div>
                                @if ($order->table_number && $order->table_number !== '-')
                                    <div class="text-xs text-muted">Meja {{ $order->table_number }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">{{ $typeLabel[$order->order_type] ?? $order->order_type }}</td>
                            <td class="whitespace-nowrap">{{ $payLabel($order) }}</td>
                            <td class="text-right font-semibold">{{ $rp($order->total_price) }}</td>
                            <td class="pr-5">
                                @if ($order->status == 'paid')
                                    <span class="chip-ok">Lunas</span>
                                @elseif ($order->status == 'pending')
                                    <span class="chip-warn">Belum dibayar</span>
                                @else
                                    <span class="chip-bad">Dibatalkan</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-muted">
                                {{ request()->hasAny(['start_date', 'end_date', 'status']) ? 'Tidak ada pesanan yang cocok dengan filter.' : 'Belum ada pesanan.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="border-t border-line px-5 py-3">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
