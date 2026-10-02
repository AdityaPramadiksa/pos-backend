@extends('admin.layout')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-1">
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700 shadow-xl">
                <h2 class="text-xl font-bold mb-6 text-[#EA7C69]">Tambah Diskon</h2>

                <form action="{{ route('admin.discounts.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Nama Promo</label>
                        <input type="text" name="name"
                            class="w-full bg-[#1F1D2B] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                            placeholder="Contoh: Promo Pembukaan" required>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Tipe Diskon</label>
                        <select name="type"
                            class="w-full bg-[#1F1D2B] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition">
                            <option value="percentage">Persentase (%)</option>
                            <option value="fixed">Nominal Tetap (Rp)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Nilai Diskon</label>
                        <input type="number" name="value"
                            class="w-full bg-[#1F1D2B] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                            placeholder="Contoh: 10 atau 5000" required>
                    </div>

                    <button type="submit"
                        class="w-full bg-[#EA7C69] hover:bg-[#f08d7d] text-white font-bold py-3 rounded-xl transition shadow-lg shadow-[#ea7c694d]">
                        <i class="fas fa-save mr-2"></i> Simpan Promo
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

            <div class="bg-[#2D303E] rounded-2xl border border-gray-700 overflow-hidden shadow-2xl">
                <table class="w-full text-left">
                    <thead class="bg-[#1F1D2B] text-gray-400 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="p-6">Nama Promo</th>
                            <th class="p-6 text-center">Tipe</th>
                            <th class="p-6 text-center">Potongan</th>
                            <th class="p-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($discounts as $discount)
                            <tr class="hover:bg-[#1F1D2B]/50 transition">
                                <td class="p-6 font-medium text-white">{{ $discount->name }}</td>
                                <td class="p-6 text-center">
                                    <span
                                        class="px-3 py-1 rounded-lg text-xs font-bold {{ $discount->type == 'percentage' ? 'bg-blue-500/10 text-blue-400' : 'bg-green-500/10 text-green-400' }}">
                                        {{ $discount->type == 'percentage' ? 'Persentase' : 'Nominal' }}
                                    </span>
                                </td>
                                <td class="p-6 text-center font-bold text-[#EA7C69]">
                                    {{ $discount->type == 'percentage' ? $discount->value . '%' : 'Rp ' . number_format($discount->value, 0, ',', '.') }}
                                </td>
                                <td class="p-6">
                                    <div class="flex justify-center">
                                        <form action="{{ route('admin.discounts.destroy', $discount->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus promo {{ $discount->name }}?')">
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
