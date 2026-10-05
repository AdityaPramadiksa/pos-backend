@extends('admin.layout')

@section('title', 'Staff')

@section('content')
    <header>
        <h1 class="page-title">Staff</h1>
        <p class="page-sub">Kasir masuk ke aplikasi dengan PIN 4 angka. Admin masuk ke panel ini dengan email dan password.</p>
    </header>

    <div class="flex flex-wrap items-start gap-6">
        {{-- Tambah staff --}}
        <form action="{{ route('admin.users.store') }}" method="POST" class="card card-pad flex w-full flex-col gap-4 md:w-[340px]" id="add-staff">
            @csrf
            <h2 class="card-title">Tambah staff</h2>
            <div>
                <label class="label" for="name">Nama</label>
                <input id="name" type="text" name="name" class="field" value="{{ old('name') }}" placeholder="Contoh: Putu" required>
            </div>
            <div>
                <span class="label">Peran</span>
                <div class="grid grid-cols-2 gap-2" role="radiogroup">
                    <label class="flex cursor-pointer items-center gap-2 rounded-[10px] border border-field px-3 py-2.5 text-sm has-[:checked]:border-brand has-[:checked]:bg-brand-soft">
                        <input type="radio" name="role" value="cashier" class="accent-brand" @checked(old('role', 'cashier') === 'cashier')> Kasir
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-[10px] border border-field px-3 py-2.5 text-sm has-[:checked]:border-brand has-[:checked]:bg-brand-soft">
                        <input type="radio" name="role" value="admin" class="accent-brand" @checked(old('role') === 'admin')> Admin
                    </label>
                </div>
            </div>
            <div>
                <label class="label" for="pin">PIN login (4 angka)</label>
                <input id="pin" type="text" name="pin" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" class="field num tracking-[0.4em]"
                    value="{{ old('pin') }}" placeholder="0000" required>
                <p class="hint">Dipakai untuk masuk ke aplikasi kasir. Setiap staff harus punya PIN berbeda.</p>
            </div>
            <div data-admin-only class="flex flex-col gap-4">
                <div>
                    <label class="label" for="email">Email</label>
                    <input id="email" type="email" name="email" class="field" value="{{ old('email') }}" placeholder="nama@email.com">
                </div>
                <div>
                    <label class="label" for="password">Password</label>
                    <input id="password" type="password" name="password" class="field" placeholder="Minimal 8 karakter">
                </div>
            </div>
            <button type="submit" class="btn-primary">Tambah staff</button>
        </form>

        {{-- Daftar staff --}}
        <div class="card min-w-0 flex-[999_1_480px]">
            <table class="tbl min-w-[560px]">
                <thead>
                    <tr>
                        <th class="pl-5">Nama</th>
                        <th>Peran</th>
                        <th>PIN login</th>
                        <th class="pr-5 text-right"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        @php $isMe = $user->id === auth()->id(); @endphp
                        <tr>
                            <td class="pl-5">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-8 w-8 flex-none place-items-center rounded-full bg-line2 text-xs font-semibold uppercase text-muted">{{ mb_substr($user->name, 0, 2) }}</span>
                                    <div class="min-w-0">
                                        <div class="font-medium">{{ $user->name }} @if ($isMe)<span class="text-xs font-normal text-muted">(Anda)</span>@endif</div>
                                        @if ($user->role === 'admin')
                                            <div class="truncate text-xs text-muted">{{ $user->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td><span class="{{ $user->role === 'admin' ? 'chip-brand' : 'chip-muted' }}">{{ $user->role === 'admin' ? 'Admin' : 'Kasir' }}</span></td>
                            <td>
                                @if ($user->pin)
                                    <span class="num tracking-[0.3em] text-muted">••••</span>
                                @else
                                    <span class="chip-warn">Belum ada PIN</span>
                                @endif
                            </td>
                            <td class="pr-5">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn-text px-2" onclick="document.getElementById('pin-dialog-{{ $user->id }}').showModal()">
                                        {{ $user->pin ? 'Ganti PIN' : 'Atur PIN' }}
                                    </button>
                                    @if (!$isMe)
                                        @if ($user->role === 'admin')
                                            <form action="{{ route('admin.users.reset_password', $user->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="icon-btn" title="Reset password ke password123" aria-label="Reset password {{ $user->name }}"
                                                    data-confirm="Reset password {{ $user->name }} menjadi password123?">
                                                    <i class="fa-solid fa-key text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn hover:text-bad" aria-label="Hapus {{ $user->name }}"
                                                data-confirm="Hapus akun {{ $user->name }}? Staff ini tidak bisa masuk lagi.">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                {{-- Dialog ganti PIN --}}
                                <dialog id="pin-dialog-{{ $user->id }}" class="w-[min(92vw,380px)] rounded-[14px] border border-line p-0 backdrop:bg-ink/40">
                                    <form action="{{ route('admin.users.update_pin', $user->id) }}" method="POST" class="flex flex-col gap-4 p-6">
                                        @csrf
                                        @method('PATCH')
                                        <h3 class="text-[17px] font-semibold">PIN untuk {{ $user->name }}</h3>
                                        <div>
                                            <label class="label" for="pin-{{ $user->id }}">PIN baru (4 angka)</label>
                                            <input id="pin-{{ $user->id }}" type="text" name="pin" inputmode="numeric" maxlength="4" pattern="[0-9]{4}"
                                                class="field num text-center text-xl font-semibold tracking-[0.5em]" required>
                                            <p class="hint">Staff perlu masuk ulang di aplikasi kasir dengan PIN baru.</p>
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" class="btn-ghost" onclick="this.closest('dialog').close()">Batal</button>
                                            <button type="submit" class="btn-primary">Simpan PIN</button>
                                        </div>
                                    </form>
                                </dialog>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Email & password hanya muncul untuk peran admin
        (() => {
            const form = document.getElementById('add-staff');
            const adminFields = form.querySelector('[data-admin-only]');
            function sync() {
                const isAdmin = form.querySelector('input[name="role"]:checked')?.value === 'admin';
                adminFields.hidden = !isAdmin;
                adminFields.querySelectorAll('input').forEach((i) => (i.required = isAdmin));
            }
            form.querySelectorAll('input[name="role"]').forEach((r) => r.addEventListener('change', sync));
            sync();
        })();
    </script>
@endpush
