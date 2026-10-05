@extends('admin.layout')

@section('title', 'Ringkasan')

@section('content')
    @php
        $rp = fn ($v) => 'Rp ' . number_format((int) $v, 0, ',', '.');
        $levelColor = ['bad' => 'bg-bad', 'warn' => 'bg-warn', 'ok' => 'bg-ok'];
    @endphp

    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-sm text-muted">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</div>
            <h1 class="page-title mt-1">Ringkasan {{ strtolower($periodLabel) }}</h1>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <nav class="seg" aria-label="Periode">
                <a href="{{ route('admin.dashboard') }}" class="{{ $period === 'today' ? 'on' : '' }}">Hari ini</a>
                <a href="{{ route('admin.dashboard', ['period' => '7d']) }}" class="{{ $period === '7d' ? 'on' : '' }}">7 hari</a>
                <a href="{{ route('admin.dashboard', ['period' => '30d']) }}" class="{{ $period === '30d' ? 'on' : '' }}">30 hari</a>
            </nav>
            @php
                $days = ['today' => 0, '7d' => 6, '30d' => 29][$period];
                $exportQuery = ['start_date' => now()->subDays($days)->toDateString(), 'end_date' => now()->toDateString()];
            @endphp
            <a href="{{ route('admin.reports.export', $exportQuery) }}" class="btn-ghost">
                <i class="fa-solid fa-download text-xs"></i> Unduh laporan
            </a>
        </div>
    </header>

    {{-- Angka utama --}}
    <section class="grid grid-cols-1 gap-px overflow-hidden rounded-[14px] border border-line bg-line sm:grid-cols-2 xl:grid-cols-4">
        <div class="bg-white px-5 py-5">
            <div class="text-sm text-muted">Penjualan bersih</div>
            <div class="num mt-1.5 text-[26px] font-bold">{{ $rp($summary['net_sales']) }}</div>
            @if ($change !== null)
                <div class="mt-1 text-xs {{ $change >= 0 ? 'text-ok' : 'text-bad' }}">
                    {{ $change >= 0 ? '+' : '' }}{{ $change }}% dari {{ $comparison['label'] }}
                </div>
            @else
                <div class="mt-1 text-xs text-muted">Belum ada pembanding dari {{ $comparison['label'] }}</div>
            @endif
        </div>
        <div class="bg-white px-5 py-5">
            <div class="text-sm text-muted">Transaksi</div>
            <div class="num mt-1.5 text-[26px] font-bold">{{ number_format($summary['bills'], 0, ',', '.') }} bill</div>
            <div class="mt-1 text-xs text-muted">Rata-rata {{ $rp($summary['average']) }} per bill</div>
        </div>
        <div class="bg-white px-5 py-5">
            <div class="text-sm text-muted">Porsi terjual</div>
            <div class="num mt-1.5 text-[26px] font-bold">{{ number_format($summary['portions'], 0, ',', '.') }} porsi</div>
            <div class="mt-1 truncate text-xs text-muted">
                {{ $topMenus->first() ? 'Terbanyak: ' . $topMenus->first()->name : 'Belum ada penjualan' }}
            </div>
        </div>
        <div class="bg-white px-5 py-5">
            <div class="text-sm text-muted">Uang tunai</div>
            @if ($openShift)
                <div class="num mt-1.5 text-[26px] font-bold">{{ $rp($openShift['expected']) }}</div>
                <div class="mt-1 text-xs text-muted">
                    Shift {{ $openShift['model']->user->name ?? 'kasir' }}, buka {{ $openShift['model']->created_at->format('H:i') }}
                </div>
            @else
                <div class="mt-1.5 text-[26px] font-bold text-faint">—</div>
                <div class="mt-1 text-xs text-muted">Tidak ada shift yang berjalan</div>
            @endif
        </div>
    </section>

    <section class="flex flex-wrap gap-6">
        {{-- Penjualan per jam --}}
        <div class="card card-pad min-w-0 flex-[3_1_520px]">
            <div class="flex items-baseline justify-between gap-4">
                <h2 class="card-title">Penjualan per jam</h2>
                @if ($hourly['peak'] !== null)
                    <span class="text-xs text-muted">Jam ramai {{ sprintf('%02d:00–%02d:00', $hourly['peak'], $hourly['peak'] + 1) }}</span>
                @endif
            </div>
            @php
                $hours = $hourly['rows'];
                $cols = count($hours);
                $axisMax = $hourly['max'];
                $short = fn ($v) => $v >= 1000000 ? 'Rp ' . rtrim(rtrim(number_format($v / 1000000, 1, ',', '.'), '0'), ',') . ' jt' : 'Rp ' . number_format($v / 1000, 0, ',', '.') . ' rb';
            @endphp
            <div class="mt-5 grid grid-cols-[64px_minmax(0,1fr)] gap-2">
                <div class="num flex h-[200px] flex-col justify-between text-right text-[11px] text-faint">
                    <span>{{ $summary['net_sales'] > 0 ? $short($axisMax) : '' }}</span>
                    <span>{{ $summary['net_sales'] > 0 ? $short($axisMax / 2) : '' }}</span>
                    <span>0</span>
                </div>
                <div class="relative h-[200px] border-b border-line"
                    style="background: linear-gradient(#F0F2EF 1px, transparent 1px) 0 0 / 100% 50%;">
                    <div class="absolute inset-0 grid items-end gap-1.5 sm:gap-2.5" style="grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr));">
                        @foreach ($hours as $h)
                            <div class="rounded-t {{ $h['hour'] === $hourly['peak'] ? 'bg-brand' : 'bg-brand-bar' }}"
                                style="height: {{ max($h['percent'], $h['total'] > 0 ? 2 : 0) }}%"
                                title="{{ sprintf('%02d:00', $h['hour']) }} · {{ $rp($h['total']) }}"></div>
                        @endforeach
                    </div>
                </div>
                <div></div>
                <div class="num grid gap-1.5 text-center text-[11px] text-faint sm:gap-2.5" style="grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr));">
                    @foreach ($hours as $h)
                        <span>{{ $h['hour'] }}</span>
                    @endforeach
                </div>
            </div>
            @if ($summary['net_sales'] == 0)
                <p class="mt-4 text-sm text-muted">Belum ada transaksi lunas di periode ini.</p>
            @endif
        </div>

        {{-- Uang masuk per cara bayar --}}
        <div class="card card-pad flex min-w-0 flex-[2_1_320px] flex-col gap-4">
            <h2 class="card-title">Uang masuk per cara bayar</h2>
            <div class="flex flex-col gap-3">
                @foreach ($payments as $p)
                    @php $isOjol = in_array($p['payment_method'], ['gojek', 'grab', 'shopee', 'delivery_other']); @endphp
                    <div>
                        <div class="flex justify-between gap-3 text-[13px]">
                            <span>{{ ucwords(strtolower($p['label'])) }} <span class="text-faint">· {{ $p['qty'] }}</span></span>
                            <span class="num font-semibold">{{ $rp($p['total']) }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 rounded-full bg-line2">
                            <div class="h-1.5 rounded-full {{ $isOjol ? 'bg-brand' : 'bg-ink' }}"
                                style="width: {{ round($p['total'] / $paymentMax * 100, 1) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="border-t border-line pt-3 text-xs text-muted">Hitam: dibayar di warung. Cokelat: dibayar lewat aplikasi ojol.</p>
        </div>
    </section>

    <section class="flex flex-wrap gap-6">
        {{-- Menu terlaris --}}
        <div class="card min-w-0 flex-[3_1_520px]">
            <div class="flex items-baseline justify-between gap-4 px-5 pt-5 sm:px-6">
                <h2 class="card-title">Menu terlaris</h2>
                <a href="{{ route('admin.menu.index') }}" class="btn-text">Kelola menu</a>
            </div>
            <div class="mt-2 overflow-x-auto px-1 pb-2 sm:px-2">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Menu</th>
                            <th class="text-right">Porsi</th>
                            <th class="text-right">Omzet</th>
                            <th class="text-right">Sisa stok</th>
                        </tr>
                    </thead>
                    <tbody class="num">
                        @forelse ($topMenus as $menu)
                            <tr>
                                <td class="font-medium">{{ $menu->name }}</td>
                                <td class="text-right">{{ $menu->total_qty }}</td>
                                <td class="text-right">{{ $rp($menu->total_revenue) }}</td>
                                <td class="text-right">
                                    @if ($menu->stock === null)
                                        <span class="text-faint">—</span>
                                    @elseif ($menu->stock <= 0)
                                        <span class="chip-bad">Habis</span>
                                    @elseif ($menu->stock <= $threshold)
                                        <span class="chip-warn">{{ $menu->stock }}</span>
                                    @else
                                        {{ $menu->stock }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-muted">Belum ada menu terjual di periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Perlu perhatian --}}
        <div class="card card-pad flex min-w-0 flex-[2_1_320px] flex-col">
            <h2 class="card-title mb-2">Perlu perhatian</h2>
            @forelse ($attention as $item)
                <div class="flex gap-3 border-b border-line2 py-2.5 last:border-b-0">
                    <span class="mt-1.5 h-2 w-2 flex-none rounded-full {{ $levelColor[$item['level']] }}"></span>
                    <div class="min-w-0 flex-1 text-sm">
                        <div>{{ $item['title'] }}</div>
                        <div class="text-xs text-muted">{{ $item['detail'] }}</div>
                    </div>
                    <a href="{{ $item['link'] }}" class="btn-text whitespace-nowrap">{{ $item['action'] }}</a>
                </div>
            @empty
                <div class="flex flex-1 items-center gap-3 py-4 text-sm text-muted">
                    <i class="fa-solid fa-circle-check text-ok"></i> Semua aman. Stok cukup dan tidak ada bill tertunda.
                </div>
            @endforelse
        </div>
    </section>

    {{-- Shift terakhir --}}
    <section class="card">
        <div class="flex items-baseline justify-between gap-4 px-5 pt-5 sm:px-6">
            <h2 class="card-title">Shift terakhir</h2>
            <a href="{{ route('admin.settlements.index') }}" class="btn-text">Semua shift</a>
        </div>
        <div class="mt-2 overflow-x-auto px-1 pb-2 sm:px-2">
            <table class="tbl min-w-[680px]">
                <thead>
                    <tr>
                        <th>Kasir</th>
                        <th>Waktu</th>
                        <th class="text-right">Penjualan</th>
                        <th class="text-right">Uang tunai seharusnya</th>
                        <th class="text-right">Selisih</th>
                    </tr>
                </thead>
                <tbody class="num">
                    @forelse ($shifts as $shift)
                        @php $s = $shift['model']; @endphp
                        <tr class="cursor-pointer" onclick="location.href='{{ route('admin.settlements.show', $s->id) }}'">
                            <td>
                                <a href="{{ route('admin.settlements.show', $s->id) }}" class="font-medium hover:underline">{{ $s->user->name ?? 'Kasir' }}</a>
                                @if ($s->status === 'open')
                                    <span class="chip-ok ml-1.5">Berjalan</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                {{ $s->created_at->locale('id')->isoFormat('ddd D MMM, HH:mm') }} –
                                {{ $s->closed_at ? $s->closed_at->format('H:i') : '' }}
                            </td>
                            <td class="text-right">{{ $rp($shift['sales']) }}</td>
                            <td class="text-right">{{ $rp($shift['expected']) }}</td>
                            <td class="text-right">
                                @if ($shift['difference'] === null)
                                    <span class="text-faint">—</span>
                                @elseif ($shift['difference'] === 0)
                                    <span class="chip-ok">Pas</span>
                                @elseif ($shift['difference'] < 0)
                                    <span class="chip-bad">− {{ $rp(abs($shift['difference'])) }}</span>
                                @else
                                    <span class="chip-warn">+ {{ $rp($shift['difference']) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-muted">Belum ada shift.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
