@extends('admin.layout')

@section('title', 'Laporan')

@section('content')
    @php
        $rp = fn ($v) => 'Rp ' . number_format((int) $v, 0, ',', '.');
        $range = ['start_date' => $startDate->toDateString(), 'end_date' => $endDate->toDateString()];
        $dailyMax = max(1, $daily->max('total') ?? 0);
    @endphp

    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Laporan penjualan</h1>
            <p class="page-sub">{{ $startDate->locale('id')->isoFormat('D MMM YYYY') }} – {{ $endDate->locale('id')->isoFormat('D MMM YYYY') }}</p>
        </div>
        @include('admin.partials.date-filter', ['action' => route('admin.reports.index'), 'start' => $range['start_date'], 'end' => $range['end_date']])
    </header>

    <section class="card card-pad flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="card-title">Unduh untuk Excel</h2>
            <p class="mt-1 text-sm text-muted">File CSV untuk rentang tanggal di atas. Buka langsung dengan Excel atau Google Sheets.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.export', $range + ['type' => 'transaksi']) }}" class="btn-dark">
                <i class="fa-solid fa-download text-xs"></i> Per transaksi
            </a>
            <a href="{{ route('admin.reports.export', $range + ['type' => 'menu']) }}" class="btn-ghost">
                <i class="fa-solid fa-download text-xs"></i> Per menu
            </a>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-px overflow-hidden rounded-[14px] border border-line bg-line sm:grid-cols-2 xl:grid-cols-4">
        <div class="bg-white px-5 py-5">
            <div class="text-sm text-muted">Penjualan bersih</div>
            <div class="num mt-1.5 text-[26px] font-bold">{{ $rp($summary['net_sales']) }}</div>
            <div class="mt-1 text-xs text-muted">Setelah diskon, termasuk pajak</div>
        </div>
        <div class="bg-white px-5 py-5">
            <div class="text-sm text-muted">Kas keluar</div>
            <div class="num mt-1.5 text-[26px] font-bold">{{ $rp($summary['expenses']) }}</div>
            <div class="mt-1 text-xs text-muted">Pengeluaran yang dicatat kasir</div>
        </div>
        <div class="bg-white px-5 py-5">
            <div class="text-sm text-muted">Penjualan dikurangi kas keluar</div>
            <div class="num mt-1.5 text-[26px] font-bold">{{ $rp($summary['net_after_expenses']) }}</div>
            <div class="mt-1 text-xs text-muted">Belum termasuk modal bahan baku</div>
        </div>
        <div class="bg-white px-5 py-5">
            <div class="text-sm text-muted">Transaksi</div>
            <div class="num mt-1.5 text-[26px] font-bold">{{ number_format($summary['bills'], 0, ',', '.') }} bill</div>
            <div class="mt-1 text-xs text-muted">{{ number_format($summary['portions'], 0, ',', '.') }} porsi terjual</div>
        </div>
    </section>

    <section class="flex flex-wrap gap-6">
        <div class="card min-w-0 flex-[3_1_480px]">
            <h2 class="card-title px-5 pt-5 sm:px-6">Penjualan per hari</h2>
            <div class="mt-2 overflow-x-auto px-1 pb-2 sm:px-2">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-right">Bill</th>
                            <th class="w-[40%]"><span class="sr-only">Grafik</span></th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="num">
                        @forelse ($daily as $day)
                            <tr>
                                <td class="whitespace-nowrap">{{ $day['date']->locale('id')->isoFormat('ddd, D MMM') }}</td>
                                <td class="text-right text-muted">{{ $day['bills'] }}</td>
                                <td>
                                    <div class="h-1.5 rounded-full bg-line2">
                                        <div class="h-1.5 rounded-full bg-ink" style="width: {{ round($day['total'] / $dailyMax * 100, 1) }}%"></div>
                                    </div>
                                </td>
                                <td class="text-right font-medium">{{ $rp($day['total']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-10 text-center text-muted">Tidak ada penjualan di rentang ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card card-pad flex min-w-0 flex-[2_1_300px] flex-col gap-4">
            <h2 class="card-title">Uang masuk per cara bayar</h2>
            @foreach ($payments as $p)
                @php $isOjol = in_array($p['payment_method'], ['gojek', 'grab', 'shopee', 'delivery_other']); @endphp
                <div>
                    <div class="flex justify-between gap-3 text-[13px]">
                        <span>{{ ucwords(strtolower($p['label'])) }} <span class="text-faint">· {{ $p['qty'] }}</span></span>
                        <span class="num font-semibold">{{ $rp($p['total']) }}</span>
                    </div>
                    <div class="mt-1.5 h-1.5 rounded-full bg-line2">
                        <div class="h-1.5 rounded-full {{ $isOjol ? 'bg-brand' : 'bg-ink' }}" style="width: {{ round($p['total'] / $paymentMax * 100, 1) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="card">
        <h2 class="card-title px-5 pt-5 sm:px-6">Menu terlaris</h2>
        <div class="mt-2 overflow-x-auto px-1 pb-2 sm:px-2">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th class="text-right">Porsi</th>
                        <th class="text-right">Omzet</th>
                    </tr>
                </thead>
                <tbody class="num">
                    @forelse ($topMenus as $menu)
                        <tr>
                            <td class="font-medium">{{ $menu->name }}</td>
                            <td class="text-right">{{ $menu->total_qty }}</td>
                            <td class="text-right">{{ $rp($menu->total_revenue) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-10 text-center text-muted">Belum ada menu terjual.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
