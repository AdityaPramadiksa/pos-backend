@extends('admin.layout')

@section('content')
    <div class="max-w-2xl bg-[#2D303E] p-8 rounded-2xl shadow-xl border border-gray-700">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('admin.menu.index') }}" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-arrow-left fa-lg"></i>
            </a>
            <h2 class="text-2xl font-bold text-white">Edit Menu: {{ $menu->name }}</h2>
        </div>

        <form action="{{ route('admin.menu.update', $menu->id) }}" method="POST" enctype="multipart/form-data"
            class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Nama Menu --}}
            <div>
                <label class="block text-sm text-gray-400 mb-2 font-medium">Nama Menu</label>
                <input type="text" name="name"
                    class="w-full bg-[#1F1D2B] border border-gray-600 rounded-lg p-3 text-white focus:border-[#EA7C69] outline-none transition"
                    value="{{ old('name', $menu->name) }}" required>
                @error('name')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Grid Harga --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Edit Harga Dine In --}}
                <div>
                    <label class="block text-sm text-gray-400 mb-2 font-medium">
                        <i class="fas fa-store mr-1 text-[#EA7C69]"></i> Harga Resto (Dine In)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                        <input type="number" name="price_dine_in"
                            class="w-full bg-[#1F1D2B] border border-gray-600 rounded-lg p-3 pl-10 text-white focus:border-[#EA7C69] outline-none transition"
                            value="{{ old('price_dine_in', $menu->price_dine_in) }}" required>
                    </div>
                </div>

                {{-- Edit Harga Online --}}
                <div>
                    <label class="block text-sm text-gray-400 mb-2 font-medium">
                        <i class="fas fa-motorcycle mr-1 text-green-400"></i> Harga Online (Ojol)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                        <input type="number" name="price_online"
                            class="w-full bg-[#1F1D2B] border border-gray-600 rounded-lg p-3 pl-10 text-white focus:border-green-400 outline-none transition"
                            value="{{ old('price_online', $menu->price_online) }}" required>
                    </div>
                </div>
            </div>

            {{-- Kategori & Stok --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-gray-400 mb-2 font-medium">Kategori</label>
                    <select name="category_id"
                        class="w-full bg-[#1F1D2B] border border-gray-600 rounded-lg p-3 text-white focus:border-[#EA7C69] outline-none transition">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $menu->category_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-2 font-medium">Stok Saat Ini</label>
                    <input type="number" name="stock"
                        class="w-full bg-[#1F1D2B] border border-gray-600 rounded-lg p-3 text-white focus:border-[#EA7C69] outline-none transition"
                        value="{{ old('stock', $menu->stock) }}" required>
                </div>
            </div>

            {{-- Foto Menu --}}
            <div>
                <label class="block text-sm text-gray-400 mb-2 font-medium">Foto Menu</label>
                <div class="flex items-start gap-4 p-4 bg-[#1F1D2B] rounded-xl border border-gray-600">
                    <div class="shrink-0">
                        @if ($menu->image)
                            <img src="{{ asset('storage/' . $menu->image) }}"
                                class="w-24 h-24 object-cover rounded-lg border border-gray-700">
                            <p class="text-[10px] text-gray-500 mt-1 text-center italic">Foto Saat Ini</p>
                        @else
                            <div class="w-24 h-24 bg-[#2D303E] rounded-lg flex items-center justify-center text-gray-600">
                                <i class="fas fa-image fa-2x"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-400 mb-2">Ganti foto? Klik tombol di bawah:</p>
                        <input type="file" name="image"
                            class="block w-full text-xs text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-xs file:font-semibold
                            file:bg-[#EA7C69] file:text-white
                            hover:file:bg-[#f08d7d] cursor-pointer" />
                        <p class="mt-2 text-[10px] text-gray-500">Format: JPG, PNG, JPEG. Max: 2MB</p>
                    </div>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-[#EA7C69] hover:bg-[#f08d7d] text-white font-bold py-4 rounded-xl transition shadow-lg shadow-[#ea7c694d] flex items-center justify-center gap-2">
                <i class="fas fa-sync-alt"></i>
                Update Menu Babi Guling
            </button>
        </form>
    </div>
@endsection
