@extends('admin.layout')

@section('title', 'Shift & settlement')

@section('content')
    @php $rp = fn ($v) => 'Rp ' . number_format((int) $v, 0, ',', '.'); @endphp

    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Shift &amp; settlement</h1>
            <p class="page-sub">{{ $openCount }} shift sedang berjalan. Selisih = uang tunai yang dihitung kasir dikurangi yang seharusnya.</p>
        </div>
        <form method="GET" action="{{ route('admin.settlements.index') }}" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="label text-xs text-muted" for="date">Tanggal buka</label>
                <input id="date" type="date" name="date" value="{{ request('date') }}" class="field w-auto py-2">
            </div>
            <label class="flex items-center gap-2 rounded-[10px] border border-field bg-white px-3 py-2 text-sm">
                <input type="checkbox" name="filter" value="selisih" class="accent-brand" @checked(request('filter') === 'selisih')> Hanya yang ada selisih
            </label>
            <button type="submit" class="btn-dark py-2">Terapkan</button>
            @if (request()->hasAny(['date', 'filter']))
                <a href="{{ route('admin.settlements.index') }}" class="btn-ghost py-2">Hapus filter</a>
            @endif
        </form>
    </header>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="tbl min-w-[860px]">
                <thead>
                    <tr>
                        <th class="pl-5">Kasir</th>
                        <th>Buka – tutup</th>
                        <th class="text-right">Modal awal</th>
                        <th class="text-right">Penjualan</th>
                        <th class="text-right">Uang tunai seharusnya</th>
                        <th class="text-right">Dihitung kasir</th>
                        <th class="pr-5 text-right">Selisih</th>
                    </tr>
                </thead>
                <tbody class="num">
                    @forelse ($rows as $row)
                        @php $s = $row['model']; @endphp
                        <tr class="cursor-pointer" onclick="location.href='{{ route('admin.settlements.show', $s->id) }}'">
                            <td class="pl-5">
                                <a href="{{ route('admin.settlements.show', $s->id) }}" class="font-medium hover:underline">{{ $s->user->name ?? 'Kasir' }}</a>
                                @if ($s->status === 'open')
                                    <span class="chip-ok ml-1.5">Berjalan</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-muted">
                                {{ $s->created_at->locale('id')->isoFormat('ddd D MMM, HH:mm') }} – {{ $s->closed_at ? $s->closed_at->format('H:i') : '…' }}
                            </td>
                            <td class="text-right text-muted">{{ $rp($s->starting_cash) }}</td>
                            <td class="text-right">{{ $rp($row['sales']) }}</td>
                            <td class="text-right">{{ $rp($row['expected']) }}</td>
                            <td class="text-right">{{ $row['actual'] !== null ? $rp($row['actual']) : '—' }}</td>
                            <td class="pr-5 text-right">
                                @if ($row['difference'] === null)
                                    <span class="text-faint">—</span>
                                @elseif ($row['difference'] === 0)
                                    <span class="chip-ok">Pas</span>
                                @elseif ($row['difference'] < 0)
                                    <span class="chip-bad">Kurang {{ $rp(abs($row['difference'])) }}</span>
                                @else
                                    <span class="chip-warn">Lebih {{ $rp($row['difference']) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-muted">Tidak ada shift yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($settlements->hasPages())
            <div class="border-t border-line px-5 py-3">{{ $settlements->links() }}</div>
        @endif
    </div>
@endsection
