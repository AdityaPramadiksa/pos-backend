@extends('admin.layout')

@section('title', 'Ubah menu')

@section('content')
    <header>
        <a href="{{ route('admin.menu.index') }}" class="btn-text">← Kembali ke daftar menu</a>
        <h1 class="page-title mt-2">Ubah {{ $menu->name }}</h1>
    </header>

    <form action="{{ route('admin.menu.update', $menu->id) }}" method="POST" enctype="multipart/form-data" class="card card-pad max-w-2xl">
        @csrf
        @method('PUT')
        @include('admin.menu._form', ['menu' => $menu])
        <div class="mt-6 flex justify-end gap-3 border-t border-line pt-5">
            <a href="{{ route('admin.menu.index') }}" class="btn-ghost">Batal</a>
            <button type="submit" class="btn-primary">Simpan perubahan</button>
        </div>
    </form>
@endsection
