@extends('admin.layout')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Kelola Menu</h1>
                <p class="text-gray-400">Total: <span class="text-[#EA7C69] font-semibold">{{ $menus->count() }}</span> Menu
                    Tersedia</p>
            </div>
            <a href="{{ route('admin.menu.create') }}"
                class="inline-flex items-center bg-[#EA7C69] hover:bg-[#f08d7d] text-white px-6 py-3 rounded-xl font-bold transition shadow-lg shadow-[#ea7c694d]">
                <i class="fas fa-plus mr-2 text-sm"></i> Tambah Menu Baru
            </a>
        </div>

        @if (session('success'))
            <div
                class="flex items-center bg-green-500/10 border border-green-500 text-green-500 p-4 rounded-xl text-sm animate-pulse">
                <i class="fas fa-check-circle mr-3"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-[#2D303E] rounded-2xl border border-gray-700 overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#1F1D2B] text-gray-400 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="p-6 text-center">Status</th>
                            <th class="p-6">Foto</th>
                            <th class="p-6">Nama Menu</th>
                            <th class="p-6">Kategori</th>
                            <th class="p-6">Harga Resto</th>
                            <th class="p-6">Harga Online</th>
                            <th class="p-6">Stok</th>
                            <th class="p-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse ($menus as $menu)
                            <tr
                                class="hover:bg-[#1F1D2B]/50 transition group {{ !$menu->is_available ? 'opacity-50' : '' }}">
                                <td class="p-6 text-center">
                                    <span class="inline-flex items-center justify-center">
                                        <i
                                            class="fas fa-circle text-[10px] {{ $menu->is_available ? 'text-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]' : 'text-red-500' }}"></i>
                                    </span>
                                </td>
                                <td class="p-6">
                                    @if ($menu->image)
                                        <img src="{{ asset('storage/' . $menu->image) }}"
                                            class="w-16 h-16 object-cover rounded-xl border border-gray-600 shadow-md group-hover:scale-105 transition duration-300">
                                    @else
                                        <div
                                            class="w-16 h-16 bg-[#1F1D2B] rounded-xl flex items-center justify-center text-gray-600 border border-dashed border-gray-600">
                                            <i class="fas fa-image fa-2x"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-6">
                                    <div class="font-medium text-white">{{ $menu->name }}</div>
                                    <div class="text-[10px] text-gray-500 mt-1 uppercase tracking-tighter">ID:
                                        #{{ str_pad($menu->id, 4, '0', STR_PAD_LEFT) }}</div>
                                </td>
                                <td class="p-6">
                                    <span
                                        class="px-3 py-1 bg-[#EA7C69]/10 text-[#EA7C69] rounded-lg text-xs font-bold border border-[#EA7C69]/20">
                                        {{ $menu->category->name ?? 'Uncategorized' }}
                                    </span>
                                </td>
                                <td class="p-6 text-gray-300 font-semibold">
                                    <div class="text-xs text-gray-500 font-normal">Dine In / To Go</div>
                                    Rp {{ number_format($menu->price_dine_in, 0, ',', '.') }}
                                </td>
                                <td class="p-6 text-green-400 font-semibold">
                                    <div class="text-xs text-gray-500 font-normal">Delivery Ojol</div>
                                    Rp {{ number_format($menu->price_online, 0, ',', '.') }}
                                </td>
                                <td class="p-6">
                                    @if ($menu->stock < 10)
                                        <span
                                            class="inline-flex items-center text-red-400 font-bold bg-red-400/10 px-2 py-1 rounded-md text-xs">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Low: {{ $menu->stock }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">{{ $menu->stock }} Porsi</span>
                                    @endif
                                </td>
                                <td class="p-6 text-center">
                                    <div class="flex justify-center items-center space-x-2">
                                        <form action="{{ route('admin.menu.toggle', $menu->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="p-2.5 rounded-xl transition duration-200 {{ $menu->is_available ? 'text-green-400 hover:bg-green-400/10' : 'text-gray-500 hover:bg-gray-500/10' }}"
                                                title="{{ $menu->is_available ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i
                                                    class="fas {{ $menu->is_available ? 'fa-eye' : 'fa-eye-slash' }} text-lg"></i>
                                            </button>
                                        </form>

                                        <a href="{{ route('admin.menu.edit', $menu->id) }}"
                                            class="p-2.5 text-blue-400 hover:bg-blue-400/10 rounded-xl transition duration-200"
                                            title="Edit Menu">
                                            <i class="fas fa-edit text-lg"></i>
                                        </a>

                                        <form action="{{ route('admin.menu.destroy', $menu->id) }}" method="POST"
                                            onsubmit="return confirm('PERINGATAN: Menghapus menu babi guling ini akan menghilangkan riwayat penjualannya. Lanjutkan?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2.5 text-red-400 hover:bg-red-400/10 rounded-xl transition duration-200"
                                                title="Hapus Menu">
                                                <i class="fas fa-trash text-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-10 text-center text-gray-500 italic">
                                    <i class="fas fa-folder-open fa-3x mb-3 block"></i>
                                    Belum ada menu yang terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
