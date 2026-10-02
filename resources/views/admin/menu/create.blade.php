@extends('admin.layout')

@section('content')
    <div class="max-w-2xl bg-[#2D303E] p-8 rounded-2xl shadow-xl border border-gray-700">
        <div class="flex items-center gap-4 mb-8">
            <a href="{{ route('admin.menu.index') }}" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-arrow-left fa-lg"></i>
            </a>
            <h2 class="text-2xl font-bold text-white">Tambah Menu Babi Guling</h2>
        </div>

        <form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Nama Menu --}}
            <div>
                <label class="block text-sm text-gray-400 mb-2 font-medium">Nama Menu</label>
                <input type="text" name="name"
                    class="w-full bg-[#1F1D2B] border border-gray-600 rounded-lg p-3 text-white focus:border-[#EA7C69] focus:ring-1 focus:ring-[#EA7C69] outline-none transition"
                    placeholder="Contoh: Paket Babi Guling Spesial" value="{{ old('name') }}" required>
                @error('name')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Grid Harga --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Harga Dine In --}}
                <div>
                    <label class="block text-sm text-gray-400 mb-2 font-medium">
                        <i class="fas fa-store mr-1 text-[#EA7C69]"></i> Harga Resto (Dine In)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                        <input type="number" name="price_dine_in"
                            class="w-full bg-[#1F1D2B] border border-gray-600 rounded-lg p-3 pl-10 text-white focus:border-[#EA7C69] outline-none transition"
                            placeholder="0" value="{{ old('price_dine_in') }}" required>
                    </div>
                    @error('price_dine_in')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Harga Online --}}
                <div>
                    <label class="block text-sm text-gray-400 mb-2 font-medium">
                        <i class="fas fa-motorcycle mr-1 text-green-400"></i> Harga Online (Ojol)
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-500">Rp</span>
                        <input type="number" name="price_online"
                            class="w-full bg-[#1F1D2B] border border-gray-600 rounded-lg p-3 pl-10 text-white focus:border-green-400 outline-none transition"
                            placeholder="0" value="{{ old('price_online') }}" required>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1 italic">*Biasanya +20% s/d 25% dari harga resto</p>
                    @error('price_online')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Kategori & Stok --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm text-gray-400 mb-2 font-medium">Kategori</label>
                    <select name="category_id"
                        class="w-full bg-[#1F1D2B] border border-gray-600 rounded-lg p-3 text-white focus:border-[#EA7C69] outline-none transition">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-2 font-medium">Stok Awal</label>
                    <input type="number" name="stock"
                        class="w-full bg-[#1F1D2B] border border-gray-600 rounded-lg p-3 text-white focus:border-[#EA7C69] outline-none transition"
                        placeholder="0" value="{{ old('stock', 0) }}" required>
                </div>
            </div>

            {{-- Foto Menu --}}
            <div>
                <label class="block text-sm text-gray-400 mb-2 font-medium">Foto Menu</label>
                <div class="flex items-center justify-center w-full">
                    <label
                        class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-600 border-dashed rounded-lg cursor-pointer bg-[#1F1D2B] hover:bg-[#252836] transition">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <i class="fas fa-cloud-upload-alt fa-2x text-gray-500 mb-2"></i>
                            <p class="text-xs text-gray-500">Klik untuk upload gambar (JPG, PNG)</p>
                        </div>
                        <input type="file" name="image" class="hidden" accept="image/*" />
                    </label>
                </div>
                @error('image')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-[#EA7C69] hover:bg-[#f08d7d] text-white font-bold py-4 rounded-xl transition shadow-lg shadow-[#ea7c694d] flex items-center justify-center gap-2">
                <i class="fas fa-save"></i>
                Simpan Menu Baru
            </button>
        </form>
    </div>
@endsection
