@extends('admin.layout')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1">
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700 shadow-xl">
                <h2 class="text-xl font-bold mb-6 text-[#EA7C69]">Tambah Staff Baru</h2>
                <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Nama Lengkap</label>
                        <input type="text" name="name"
                            class="w-full bg-[#1F1D2B] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                            placeholder="Contoh: Putu Kasir" required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Email (untuk Login)</label>
                        <input type="email" name="email"
                            class="w-full bg-[#1F1D2B] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                            placeholder="putu@mail.com" required>
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Password</label>
                        <input type="password" name="password"
                            class="w-full bg-[#1F1D2B] border border-gray-600 rounded-xl p-3 text-white focus:border-[#EA7C69] outline-none transition"
                            placeholder="Minimal 8 karakter" required>
                    </div>
                    <button type="submit"
                        class="w-full bg-[#EA7C69] hover:bg-[#f08d7d] text-white font-bold py-3 rounded-xl transition shadow-lg shadow-[#ea7c694d]">
                        Daftarkan Staff
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-4">
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
                <table class="w-full text-left border-collapse">
                    <thead class="bg-[#1F1D2B] text-gray-400 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="p-6">Nama Staff</th>
                            <th class="p-6">Email</th>
                            <th class="p-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($users as $user)
                            <tr class="hover:bg-[#1F1D2B]/50 transition group">
                                <td class="p-6">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-[#EA7C69] flex items-center justify-center font-bold text-xs uppercase shadow-lg shadow-[#ea7c694d]">
                                            {{ substr($user->name, 0, 2) }}
                                        </div>
                                        <span class="font-medium text-white">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td class="p-6 text-gray-400 text-sm italic">{{ $user->email }}</td>
                                <td class="p-6">
                                    <div class="flex justify-center items-center space-x-3">
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.reset_password', $user->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Reset password staff {{ $user->name }} ke default (password123)?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="p-2 text-orange-400 hover:bg-orange-400/10 rounded-lg transition"
                                                    title="Reset ke password123">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                                onsubmit="return confirm('Hapus staff ini secara permanen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="p-2 text-red-400 hover:bg-red-400/10 rounded-lg transition"
                                                    title="Hapus Staff">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span
                                                class="bg-gray-700/50 text-gray-400 px-3 py-1 rounded-full text-[10px] uppercase font-bold tracking-widest italic border border-gray-600">
                                                You (Admin)
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-500 italic ml-2">* Klik ikon kunci <i class="fas fa-key mx-1"></i> untuk mereset
                password staff menjadi <strong>password123</strong></p>
        </div>
    </div>
@endsection
