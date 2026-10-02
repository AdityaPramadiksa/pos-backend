<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - POS Babi Guling</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#1F1D2B] flex items-center justify-center min-h-screen">
    <div class="bg-[#2D303E] p-10 rounded-2xl shadow-2xl w-full max-w-md border border-gray-700">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-[#EA7C69]">Welcome Back</h1>
            <p class="text-gray-400 mt-2">Please login to admin account</p>
        </div>

        {{-- 1. Tampilkan Error Validasi (Email/Password Salah) --}}
        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500 text-red-500 p-3 rounded-lg mb-6 text-sm flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        {{-- 2. Tampilkan Error Keamanan (Akses Dibatasi / Session Expired) --}}
        @if (session('error'))
            <div
                class="bg-orange-500/10 border border-orange-500 text-orange-500 p-3 rounded-lg mb-6 text-sm flex items-center">
                <i class="fas fa-shield-alt mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-6">
            @csrf <div>
                <label class="block text-gray-400 mb-2 text-sm">Email Address</label>
                <input type="email" name="email" required
                    class="w-full bg-[#1F1D2B] border border-gray-600 rounded-xl p-4 text-white focus:border-[#EA7C69] outline-none transition"
                    placeholder="admin@pos.com">
            </div>

            <div>
                <label class="block text-gray-400 mb-2 text-sm">Password</label>
                <input type="password" name="password" required
                    class="w-full bg-[#1F1D2B] border border-gray-600 rounded-xl p-4 text-white focus:border-[#EA7C69] outline-none transition"
                    placeholder="••••••••">
            </div>

            <button type="submit"
                class="w-full bg-[#EA7C69] hover:bg-[#f08d7d] text-white font-bold py-4 rounded-xl transition shadow-lg shadow-[#ea7c694d]">
                Login to Dashboard
            </button>
        </form>
    </div>
</body>

</html>
