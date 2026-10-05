<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk · Panel Admin Men Gede</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', '"Segoe UI"', 'system-ui', 'sans-serif'] },
                    colors: {
                        ground: '#F5F6F4', ink: '#1D2422', muted: '#5D6965', line: '#E2E6E3', field: '#D5DBD7',
                        brand: { DEFAULT: '#9A5317', dark: '#7A4011' }, bad: { DEFAULT: '#B83A2B', soft: '#FBE9E6' },
                    },
                },
            },
        };
    </script>
</head>

<body class="flex min-h-screen items-center justify-center bg-ground px-4 font-sans text-ink">
    <main class="w-full max-w-[400px]">
        <div class="mb-8 flex items-center gap-3">
            <span class="grid h-10 w-10 place-items-center rounded-[10px] bg-brand text-sm font-bold text-white">MG</span>
            <div>
                <div class="text-lg font-bold leading-tight">Men Gede</div>
                <div class="text-sm text-muted">Panel admin</div>
            </div>
        </div>

        <div class="rounded-[14px] border border-line bg-white p-6 sm:p-8">
            <h1 class="text-xl font-bold">Masuk</h1>
            <p class="mt-1 text-sm text-muted">Gunakan email dan password akun admin.</p>

            @if ($errors->any())
                <div role="alert" class="mt-5 rounded-[10px] bg-bad-soft px-3.5 py-2.5 text-sm text-bad">{{ $errors->first() }}</div>
            @endif
            @if (session('error'))
                <div role="alert" class="mt-5 rounded-[10px] bg-bad-soft px-3.5 py-2.5 text-sm text-bad">{{ session('error') }}</div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="mt-6 flex flex-col gap-4">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-[13px] font-medium">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        class="w-full rounded-[10px] border border-field px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-[13px] font-medium">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="w-full rounded-[10px] border border-field px-3 py-2.5 text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30">
                </div>
                <button type="submit" class="mt-2 rounded-[10px] bg-brand px-4 py-3 text-sm font-semibold text-white hover:bg-brand-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-brand focus-visible:ring-offset-2">
                    Masuk
                </button>
            </form>
        </div>
        <p class="mt-4 text-center text-xs text-muted">Kasir masuk lewat aplikasi kasir dengan PIN.</p>
    </main>
</body>

</html>
