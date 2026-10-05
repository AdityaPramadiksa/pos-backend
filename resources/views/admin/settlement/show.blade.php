@extends('admin.layout')

@section('title', 'Shift ' . ($report['settlement']['cashier'] ?? ''))

@section('content')
    @php
        $rp = fn ($v) => 'Rp ' . number_format((int) $v, 0, ',', '.');
        $s = $report['summary'];
        $diff = $s['cash_difference'];
    @endphp

    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ route('admin.settlements.index') }}" class="btn-text">← Kembali ke daftar shift</a>
            <h1 class="page-title mt-2">Shift {{ $settlement->user->name ?? 'kasir' }}</h1>
            <p class="page-sub">
                Buka {{ $settlement->created_at->locale('id')->isoFormat('dddd, D MMM YYYY · HH:mm') }}
                @if ($settlement->closed_at)
                    · tutup {{ $settlement->closed_at->format('H:i') }}
                @endif
            </p>
        </div>
        <span class="{{ $settlement->status === 'open' ? 'chip-ok' : 'chip-muted' }} text-sm">{{ $settlement->status === 'open' ? 'Berjalan' : 'Ditutup' }}</span>
    </header>

    <div class="flex flex-wrap items-start gap-6">
        <div class="flex min-w-0 flex-[999_1_520px] flex-col gap-6">
            {{-- Uang tunai --}}
            <section class="rounded-[14px] bg-ink p-5 text-white sm:p-6">
                <div class="text-sm text-[#C9D0CC]">Uang tunai seharusnya</div>
                <div class="num mt-1 text-[30px] font-bold">{{ $rp($s['expected_ending_cash']) }}</div>
                <div class="num mt-4 grid grid-cols-3 gap-3 text-xs text-[#C9D0CC]">
                    <div>Modal awal<span class="mt-0.5 block text-sm font-semibold text-white">{{ $rp($s['starting_cash']) }}</span></div>
                    <div>Penjualan tunai<span class="mt-0.5 block text-sm font-semibold text-white">+ {{ $rp($s['payments_in_cash']) }}</span></div>
                    <div>Kas keluar<span class="mt-0.5 block text-sm font-semibold text-white">− {{ $rp($s['total_expenses']) }}</span></div>
                </div>
                @if ($s['actual_cash'] !== null)
                    <div class="num mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-white/15 pt-4 text-sm">
                        <span>Dihitung kasir: <strong>{{ $rp($s['actual_cash']) }}</strong></span>
                        @if ($diff === 0)
                            <span class="chip bg-ok text-white">Pas</span>
                        @elseif ($diff < 0)
                            <span class="chip bg-bad text-white">Kurang {{ $rp(abs($diff)) }}</span>
                        @else
                            <span class="chip bg-warn text-white">Lebih {{ $rp($diff) }}</span>
                        @endif
                    </div>
                @endif
            </section>

            {{-- Penjualan per menu --}}
            <section class="card">
                <h2 class="card-title px-5 pt-5 sm:px-6">Penjualan per menu</h2>
                <div class="mt-2 overflow-x-auto px-1 pb-2 sm:px-2">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th class="text-right">Harga</th>
                                <th class="text-right">Porsi</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="num">
                            @forelse ($report['menus_by_category'] as $cat)
                                <tr class="bg-ground/70">
                                    <td colspan="3" class="text-xs font-semibold uppercase tracking-wider text-muted">{{ $cat['category'] }}</td>
                                    <td class="text-right text-xs font-semibold text-muted">{{ $rp($cat['total']) }}</td>
                                </tr>
                                @foreach ($cat['items'] as $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-right text-muted">{{ $rp($item['price']) }}</td>
                                        <td class="text-right">{{ $item['qty'] }}</td>
                                        <td class="text-right font-medium">{{ $rp($item['total']) }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="4" class="py-10 text-center text-muted">Belum ada menu terjual di shift ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if (count($report['expenses']) > 0)
                <section class="card">
                    <h2 class="card-title px-5 pt-5 sm:px-6">Kas keluar</h2>
                    <div class="mt-2 px-1 pb-2 sm:px-2">
                        <table class="tbl">
                            <tbody class="num">
                                @foreach ($report['expenses'] as $e)
                                    <tr>
                                        <td class="w-16 text-muted">{{ $e['time'] }}</td>
                                        <td>{{ $e['description'] }}</td>
                                        <td class="text-right font-medium">{{ $rp($e['amount']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>

        <aside class="flex w-full flex-col gap-6 sm:w-[340px]">
            <section class="card card-pad num flex flex-col text-sm">
                <h2 class="card-title mb-2">Uang masuk per cara bayar</h2>
                @foreach ($report['payments'] as $p)
                    <div class="flex justify-between border-b border-line2 py-2 last:border-b-0">
                        <span>{{ ucwords(strtolower($p['label'])) }} <span class="text-faint">· {{ $p['qty'] }}</span></span>
                        <span class="font-medium">{{ $rp($p['total']) }}</span>
                    </div>
                @endforeach
                <div class="mt-2 flex justify-between border-t border-line pt-3 font-semibold">
                    <span>Total penjualan</span><span>{{ $rp($s['net_sales']) }}</span>
                </div>
            </section>

            <section class="card card-pad num flex flex-col gap-2 text-sm">
                <h2 class="card-title mb-1">Ringkasan</h2>
                <div class="flex justify-between text-muted"><span>Penjualan kotor</span><span>{{ $rp($s['gross_sales']) }}</span></div>
                <div class="flex justify-between text-muted"><span>Diskon</span><span>− {{ $rp($s['total_discount']) }}</span></div>
                <div class="flex justify-between text-muted"><span>Pajak (PB1)</span><span>{{ $rp($s['total_tax']) }}</span></div>
                <div class="flex justify-between text-muted"><span>Jumlah bill</span><span>{{ $s['total_bills'] }}</span></div>
                <div class="flex justify-between text-muted"><span>Dibatalkan</span><span>{{ $s['void_count'] }} · {{ $rp($s['void_total']) }}</span></div>
            </section>

            <section class="card card-pad">
                <h2 class="card-title mb-2">Catatan kasir</h2>
                <p class="text-sm {{ $settlement->notes ? '' : 'text-muted' }}">{{ $settlement->notes ?: 'Tidak ada catatan.' }}</p>
            </section>
        </aside>
    </div>
@endsection
