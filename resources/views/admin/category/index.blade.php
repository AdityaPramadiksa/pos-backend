@extends('admin.layout')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-1">
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700 shadow-xl">
                <h2 class="text-xl font-bold mb-6 text-[#EA7C69]">Tambah Kategori</h2>
                <form action="{{ route('admin.category.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Nama Kategori</label>
                        <input type="text" name="name"
                            class="w-full bg-[#1F1D2B] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                            placeholder="Contoh: Sate Babi" required>
                    </div>
                    <button type="submit"
                        class="w-full bg-[#EA7C69] hover:bg-[#f08d7d] text-white font-bold py-3 rounded-xl transition shadow-lg shadow-[#ea7c694d]">
                        <i class="fas fa-save mr-2"></i> Simpan
                    </button>
                </form>
            </div>
        </div>

        <div class="md:col-span-2 space-y-4">
            @if (session('success'))
                <div class="bg-green-500/10 border border-green-500 text-green-500 p-4 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-500/10 border border-red-500 text-red-500 p-4 rounded-xl text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-[#2D303E] rounded-2xl border border-gray-700 overflow-hidden shadow-2xl">
                <table class="w-full text-left">
                    <thead class="bg-[#1F1D2B] text-gray-400 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="p-6">Nama Kategori</th>
                            <th class="p-6 text-center">Isi Menu</th>
                            <th class="p-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($categories as $cat)
                            <tr class="hover:bg-[#1F1D2B]/50 transition">
                                <td class="p-6 font-medium text-white">{{ $cat->name }}</td>
                                <td class="p-6 text-center">
                                    <span class="bg-blue-500/10 text-blue-400 px-3 py-1 rounded-lg text-xs font-bold">
                                        {{ $cat->menus_count }} Item
                                    </span>
                                </td>
                                <td class="p-6">
                                    <div class="flex justify-center">
                                        <form action="{{ route('admin.category.destroy', $cat->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2 text-red-400 hover:bg-red-400/10 rounded-lg transition">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
