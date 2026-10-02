@extends('admin.layout')

@section('content')
    <div class="space-y-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold">Dashboard</h1>
                <p class="text-gray-400">Selasa, 3 Maret 2026</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-orange-500/10 rounded-lg text-[#EA7C69]">
                        <i class="fas fa-money-bill-wave fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Pendapatan Hari Ini</p>
                        <h3 class="text-2xl font-bold">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h3>
                    </div>
                </div>
            </div>

            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-blue-500/10 rounded-lg text-blue-500">
                        <i class="fas fa-shopping-cart fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Pesanan Hari Ini</p>
                        <h3 class="text-2xl font-bold">{{ $todayOrders }} Pesanan</h3>
                    </div>
                </div>
            </div>

            <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-red-500/10 rounded-lg text-red-500">
                        <i class="fas fa-box fa-2x"></i>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Stok Hampir Habis</p>
                        <h3 class="text-2xl font-bold">{{ $lowStockMenus->count() }} Menu</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-[#2D303E] p-6 rounded-2xl border border-gray-700">
            <h2 class="text-xl font-bold mb-6">Menu Terlaris (Top 5)</h2>
            <table class="w-full text-left">
                <thead>
                    <tr class="text-gray-400 border-b border-gray-700">
                        <th class="pb-4">Nama Menu</th>
                        <th class="pb-4">Harga</th>
                        <th class="pb-4">Terjual</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach ($topMenus as $menu)
                        <tr>
                            <td class="py-4">{{ $menu->name }}</td>
                            <td class="py-4">Rp {{ number_format($menu->price, 0, ',', '.') }}</td>
                            <td class="py-4 text-[#EA7C69]">{{ $menu->total_sold ?? 0 }} porsi</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
