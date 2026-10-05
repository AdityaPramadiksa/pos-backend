@extends('admin.layout')

@section('title', 'Tambah menu')

@section('content')
    <header>
        <a href="{{ route('admin.menu.index') }}" class="btn-text">← Kembali ke daftar menu</a>
        <h1 class="page-title mt-2">Tambah menu</h1>
    </header>

    <form action="{{ route('admin.menu.store') }}" method="POST" enctype="multipart/form-data" class="card card-pad max-w-2xl">
        @csrf
        @include('admin.menu._form', ['menu' => null])
        <div class="mt-6 flex justify-end gap-3 border-t border-line pt-5">
            <a href="{{ route('admin.menu.index') }}" class="btn-ghost">Batal</a>
            <button type="submit" class="btn-primary">Simpan menu</button>
        </div>
    </form>
@endsection
