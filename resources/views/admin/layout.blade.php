<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin POS Babi Guling</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom scrollbar tema dark */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #1F1D2B;
        }

        ::-webkit-scrollbar-thumb {
            background: #2D303E;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #EA7C69;
        }

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-[#1F1D2B] text-white">
    <div class="flex min-h-screen">
        <div class="w-64 bg-[#1F1D2B] border-r border-gray-700 p-6 flex flex-col fixed h-full z-50">
            <div class="flex items-center space-x-3 mb-10">
                <div class="bg-[#EA7C69] p-2 rounded-lg shadow-lg shadow-[#ea7c694d]">
                    <i class="fas fa-piggy-bank text-white text-xl"></i>
                </div>
                <h2 class="text-xl font-bold text-white tracking-tight">Babi Guling <span
                        class="text-[#EA7C69]">POS</span></h2>
            </div>
            <nav class="space-y-1 flex-1 overflow-y-auto pr-2">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center space-x-3 p-3 rounded-xl transition duration-200 {{ Request::is('admin/dashboard') ? 'bg-[#EA7C69] text-white shadow-lg shadow-[#ea7c694d]' : 'text-gray-400 hover:bg-[#2D303E] hover:text-white' }}">
                    <i class="fas fa-home w-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <div class="pt-6 pb-2 ml-3">
                    <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold">Master Data</p>
                </div>

                <a href="{{ route('admin.menu.index') }}"
                    class="flex items-center space-x-3 p-3 rounded-xl transition duration-200 {{ Request::is('admin/menus*') ? 'bg-[#EA7C69] text-white shadow-lg shadow-[#ea7c694d]' : 'text-gray-400 hover:bg-[#2D303E] hover:text-white' }}">
                    <i class="fas fa-utensils w-5"></i>
                    <span class="font-medium">Kelola Menu</span>
                </a>

                <a href="{{ route('admin.category.index') }}"
                    class="flex items-center space-x-3 p-3 rounded-xl transition duration-200 {{ Request::is('admin/categories*') ? 'bg-[#EA7C69] text-white shadow-lg shadow-[#ea7c694d]' : 'text-gray-400 hover:bg-[#2D303E] hover:text-white' }}">
                    <i class="fas fa-list w-5"></i>
                    <span class="font-medium">Kategori</span>
                </a>

                <a href="{{ route('admin.discounts.index') }}"
                    class="flex items-center space-x-3 p-3 rounded-xl transition duration-200 {{ Request::is('admin/discounts*') ? 'bg-[#EA7C69] text-white shadow-lg shadow-[#ea7c694d]' : 'text-gray-400 hover:bg-[#2D303E] hover:text-white' }}">
                    <i class="fas fa-percentage w-5"></i>
                    <span class="font-medium">Kelola Diskon</span>
                </a>

                <div class="pt-6 pb-2 ml-3">
                    <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold">Operasional</p>
                </div>

                <a href="{{ route('admin.orders.index') }}"
                    class="flex items-center space-x-3 p-3 rounded-xl transition duration-200 {{ Request::is('admin/orders*') ? 'bg-[#EA7C69] text-white shadow-lg shadow-[#ea7c694d]' : 'text-gray-400 hover:bg-[#2D303E] hover:text-white' }}">
                    <i class="fas fa-shopping-bag w-5"></i>
                    <span class="font-medium">Riwayat Pesanan</span>
                </a>

                <a href="{{ route('admin.reports.index') }}"
                    class="flex items-center space-x-3 p-3 rounded-xl transition duration-200 {{ Request::is('admin/reports*') ? 'bg-[#EA7C69] text-white shadow-lg shadow-[#ea7c694d]' : 'text-gray-400 hover:bg-[#2D303E] hover:text-white' }}">
                    <i class="fas fa-chart-pie w-5"></i>
                    <span class="font-medium">Laporan Penjualan</span>
                </a>

                <a href="{{ route('admin.settlements.index') }}"
                    class="flex items-center space-x-3 p-3 rounded-xl transition duration-200 {{ Request::is('admin/settlements*') ? 'bg-[#EA7C69] text-white shadow-lg shadow-[#ea7c694d]' : 'text-gray-400 hover:bg-[#2D303E] hover:text-white' }}">
                    <i class="fas fa-file-invoice-dollar w-5"></i>
                    <span class="font-medium">Settlement / Shift</span>
                </a>

                {{-- <a href="{{ route('admin.expenses.index') }}"
                    class="flex items-center space-x-3 p-3 rounded-xl transition duration-200 {{ Request::is('admin/expenses*') ? 'bg-[#EA7C69] text-white shadow-lg shadow-[#ea7c694d]' : 'text-gray-400 hover:bg-[#2D303E] hover:text-white' }}">
                    <i class="fas fa-wallet w-5"></i>
                    <span class="font-medium">Kas Keluar</span>
                </a> --}}

                <div class="pt-6 pb-2 ml-3">
                    <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold">Sistem</p>
                </div>

                <a href="{{ route('admin.users.index') }}"
                    class="flex items-center space-x-3 p-3 rounded-xl transition duration-200 {{ Request::is('admin/users*') ? 'bg-[#EA7C69] text-white shadow-lg shadow-[#ea7c694d]' : 'text-gray-400 hover:bg-[#2D303E] hover:text-white' }}">
                    <i class="fas fa-users-cog w-5"></i>
                    <span class="font-medium">Kelola Staff</span>
                </a>

                <a href="{{ route('admin.settings.index') }}"
                    class="flex items-center space-x-3 p-3 rounded-xl transition duration-200 {{ Request::is('admin/settings*') ? 'bg-[#EA7C69] text-white shadow-lg shadow-[#ea7c694d]' : 'text-gray-400 hover:bg-[#2D303E] hover:text-white' }}">
                    <i class="fas fa-cog w-5"></i>
                    <span class="font-medium">Settings</span>
                </a>
            </nav>

            <div class="border-t border-gray-700 pt-6 mt-4">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex items-center space-x-3 p-3 rounded-xl text-red-400 hover:bg-red-400/10 transition duration-200 w-full group">
                        <i class="fas fa-sign-out-alt transition group-hover:translate-x-1 text-lg"></i>
                        <span class="font-bold">Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="flex-1 ml-64 min-h-screen bg-[#252836] p-10">
            @yield('content')
        </div>
    </div>
</body>

</html>
