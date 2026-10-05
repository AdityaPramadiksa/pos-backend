@extends('admin.layout')

@section('title', 'Kas keluar')

@section('content')
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Kas keluar</h1>
            <p class="page-sub">Pengeluaran yang dicatat kasir dari laci, misalnya beli es batu atau gas.</p>
        </div>
        @include('admin.partials.date-filter', ['action' => route('admin.expenses.index')])
    </header>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="tbl min-w-[720px]">
                <thead>
                    <tr>
                        <th class="pl-5">Waktu</th>
                        <th>Dicatat oleh</th>
                        <th>Keterangan</th>
                        <th class="text-right">Jumlah</th>
                        <th class="pr-5">Foto nota</th>
                    </tr>
                </thead>
                <tbody class="num">
                    @forelse ($expenses as $expense)
                        <tr>
                            <td class="whitespace-nowrap pl-5 text-muted">{{ $expense->created_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}</td>
                            <td>{{ $expense->user->name ?? '—' }}</td>
                            <td>{{ $expense->description }}</td>
                            <td class="text-right font-semibold">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                            <td class="pr-5">
                                @if ($expense->receipt_image)
                                    <a href="{{ asset('storage/' . $expense->receipt_image) }}" target="_blank" rel="noopener" class="btn-text">Lihat foto</a>
                                @else
                                    <span class="text-faint">Tidak ada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-muted">
                                {{ request()->hasAny(['start_date', 'end_date']) ? 'Tidak ada pengeluaran di rentang tanggal ini.' : 'Belum ada pengeluaran yang dicatat.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($expenses->hasPages())
            <div class="border-t border-line px-5 py-3">{{ $expenses->links() }}</div>
        @endif
    </div>
@endsection
