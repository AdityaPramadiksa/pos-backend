@extends('admin.layout')

@section('title', 'Diskon')

@section('content')
    <header>
        <h1 class="page-title">Diskon</h1>
        <p class="page-sub">Diskon aktif bisa dipilih kasir saat membuat pesanan.</p>
    </header>

    <div class="flex flex-wrap items-start gap-6">
        <form action="{{ route('admin.discounts.store') }}" method="POST" class="card card-pad flex w-full flex-col gap-4 md:w-[320px]">
            @csrf
            <h2 class="card-title">Tambah diskon</h2>
            <div>
                <label class="label" for="name">Nama promo</label>
                <input id="name" type="text" name="name" class="field" placeholder="Contoh: Promo pembukaan" required>
            </div>
            <div>
                <label class="label" for="type">Jenis potongan</label>
                <select id="type" name="type" class="field">
                    <option value="percentage">Persen (%)</option>
                    <option value="fixed">Nominal tetap (Rp)</option>
                </select>
            </div>
            <div>
                <label class="label" for="value">Besar potongan</label>
                <input id="value" type="number" min="0" name="value" class="field num" placeholder="Contoh: 10 atau 5000" required>
                <p class="hint">Isi 10 untuk 10%, atau 5000 untuk Rp 5.000.</p>
            </div>
            <button type="submit" class="btn-primary">Simpan diskon</button>
        </form>

        <div class="card min-w-0 flex-[999_1_420px]">
            <table class="tbl">
                <thead>
                    <tr>
                        <th class="pl-5">Promo</th>
                        <th>Jenis</th>
                        <th class="text-right">Potongan</th>
                        <th class="pr-5 text-right"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="num">
                    @forelse ($discounts as $discount)
                        <tr>
                            <td class="pl-5 font-medium">{{ $discount->name }}</td>
                            <td><span class="chip-muted">{{ $discount->type == 'percentage' ? 'Persen' : 'Nominal' }}</span></td>
                            <td class="text-right font-semibold">
                                {{ $discount->type == 'percentage' ? $discount->value . '%' : 'Rp ' . number_format($discount->value, 0, ',', '.') }}
                            </td>
                            <td class="pr-5 text-right">
                                <form action="{{ route('admin.discounts.destroy', $discount->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn hover:text-bad" aria-label="Hapus {{ $discount->name }}"
                                        data-confirm="Hapus diskon {{ $discount->name }}?">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-muted">Belum ada diskon.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
