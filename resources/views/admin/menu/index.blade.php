@extends('admin.layout')

@section('title', 'Menu & stok')

@section('content')
    @php
        $threshold = (int) \App\Models\Setting::getValue('low_stock_threshold', 10);
        $rp = fn ($v) => 'Rp ' . number_format((int) $v, 0, ',', '.');
    @endphp

    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Menu &amp; stok</h1>
            <p class="page-sub">{{ $menus->count() }} menu · {{ $menus->where('is_available', true)->count() }} dijual di aplikasi kasir</p>
        </div>
        <a href="{{ route('admin.menu.create') }}" class="btn-primary"><i class="fa-solid fa-plus text-xs"></i> Tambah menu</a>
    </header>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="tbl min-w-[860px]">
                <thead>
                    <tr>
                        <th class="pl-5">Menu</th>
                        <th>Kategori</th>
                        <th class="text-right">Harga di warung</th>
                        <th class="text-right">Harga ojol</th>
                        <th class="text-right">Stok</th>
                        <th>Status</th>
                        <th class="pr-5 text-right"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="num">
                    @forelse ($menus as $menu)
                        <tr class="{{ $menu->is_available ? '' : 'text-muted' }}">
                            <td class="pl-5">
                                <div class="flex items-center gap-3">
                                    @include('admin.partials.menu-photo', ['menu' => $menu, 'size' => 'w-12 h-12'])
                                    <div class="min-w-0">
                                        <div class="font-medium {{ $menu->is_available ? 'text-ink' : '' }}">{{ $menu->name }}</div>
                                        @unless ($menu->image)
                                            <div class="text-xs text-faint">Belum ada foto</div>
                                        @endunless
                                    </div>
                                </div>
                            </td>
                            <td>{{ $menu->category->name ?? 'Tanpa kategori' }}</td>
                            <td class="text-right">{{ $rp($menu->price_dine_in) }}</td>
                            <td class="text-right">{{ $rp($menu->price_online) }}</td>
                            <td class="text-right">
                                @if ($menu->stock <= 0)
                                    <span class="chip-bad">Habis</span>
                                @elseif ($menu->stock <= $threshold)
                                    <span class="chip-warn">{{ $menu->stock }} porsi</span>
                                @else
                                    {{ $menu->stock }} porsi
                                @endif
                            </td>
                            <td>
                                @if ($menu->is_available)
                                    <span class="chip-ok">Dijual</span>
                                @else
                                    <span class="chip-muted">Disembunyikan</span>
                                @endif
                            </td>
                            <td class="pr-5">
                                <div class="flex items-center justify-end gap-1">
                                    <form action="{{ route('admin.menu.toggle', $menu->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="icon-btn"
                                            title="{{ $menu->is_available ? 'Sembunyikan dari kasir' : 'Tampilkan di kasir' }}"
                                            aria-label="{{ $menu->is_available ? 'Sembunyikan' : 'Tampilkan' }} {{ $menu->name }}">
                                            <i class="fa-regular {{ $menu->is_available ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.menu.edit', $menu->id) }}" class="icon-btn" title="Ubah menu" aria-label="Ubah {{ $menu->name }}">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.menu.destroy', $menu->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-btn hover:text-bad" title="Hapus menu" aria-label="Hapus {{ $menu->name }}"
                                            data-confirm="Hapus {{ $menu->name }}? Riwayat penjualan menu ini juga ikut terhapus. Untuk berhenti menjual sementara, pakai tombol mata.">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-muted">
                                Belum ada menu. <a href="{{ route('admin.menu.create') }}" class="btn-text">Tambah menu pertama</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-xs text-muted">
        Tombol mata menyembunyikan menu dari aplikasi kasir tanpa menghapusnya. Stok bisa juga diubah kasir langsung dari aplikasi.
    </p>
@endsection
