@extends('admin.layout')

@section('title', 'Kategori')

@section('content')
    <header>
        <h1 class="page-title">Kategori</h1>
        <p class="page-sub">Kategori jadi tab filter di aplikasi kasir.</p>
    </header>

    <div class="flex flex-wrap items-start gap-6">
        <form action="{{ route('admin.category.store') }}" method="POST" class="card card-pad flex w-full flex-col gap-4 md:w-[320px]">
            @csrf
            <h2 class="card-title">Tambah kategori</h2>
            <div>
                <label class="label" for="name">Nama kategori</label>
                <input id="name" type="text" name="name" class="field" placeholder="Contoh: Lauk tambahan" required>
            </div>
            <button type="submit" class="btn-primary">Simpan kategori</button>
        </form>

        <div class="card min-w-0 flex-[999_1_420px]">
            <table class="tbl">
                <thead>
                    <tr>
                        <th class="pl-5">Kategori</th>
                        <th class="text-right">Jumlah menu</th>
                        <th class="pr-5 text-right"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="num">
                    @forelse ($categories as $cat)
                        <tr>
                            <td class="pl-5 font-medium">{{ $cat->name }}</td>
                            <td class="text-right text-muted">{{ $cat->menus_count }} menu</td>
                            <td class="pr-5 text-right">
                                <form action="{{ route('admin.category.destroy', $cat->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn hover:text-bad" aria-label="Hapus {{ $cat->name }}"
                                        data-confirm="Hapus kategori {{ $cat->name }}?">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-10 text-center text-muted">Belum ada kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
