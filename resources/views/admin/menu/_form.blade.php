{{-- Form bersama untuk tambah & ubah menu. $menu null saat menambah. --}}
@php $m = $menu ?? null; @endphp

<div class="flex flex-col gap-5">
    <div>
        <label class="label" for="name">Nama menu</label>
        <input id="name" type="text" name="name" class="field" value="{{ old('name', $m->name ?? '') }}"
            placeholder="Contoh: Nasi Campur Babi Guling" required>
        @error('name') <p class="error">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label" for="price_dine_in">Harga di warung</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted">Rp</span>
                <input id="price_dine_in" type="number" min="0" step="500" name="price_dine_in" class="field num pl-10"
                    value="{{ old('price_dine_in', $m->price_dine_in ?? '') }}" required>
            </div>
            <p class="hint">Untuk makan di sini dan bungkus.</p>
            @error('price_dine_in') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="label" for="price_online">Harga ojol</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted">Rp</span>
                <input id="price_online" type="number" min="0" step="500" name="price_online" class="field num pl-10"
                    value="{{ old('price_online', $m->price_online ?? '') }}" required>
            </div>
            <p class="hint">Untuk pesanan GoFood, GrabFood, dan ShopeeFood.</p>
            @error('price_online') <p class="error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <label class="label" for="category_id">Kategori</label>
            <select id="category_id" name="category_id" class="field" required>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $m->category_id ?? null) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            @if ($categories->isEmpty())
                <p class="error">Belum ada kategori. <a href="{{ route('admin.category.index') }}" class="underline">Buat kategori</a> dulu.</p>
            @endif
        </div>
        <div>
            <label class="label" for="stock">{{ $m ? 'Sisa stok' : 'Stok awal' }}</label>
            <div class="relative">
                <input id="stock" type="number" min="0" name="stock" class="field num pr-16" value="{{ old('stock', $m->stock ?? 0) }}" required>
                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm text-muted">porsi</span>
            </div>
            @error('stock') <p class="error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <span class="label">Foto menu</span>
        <div class="flex items-center gap-4 rounded-xl border border-dashed border-field p-4">
            <div id="photo-preview">
                @include('admin.partials.menu-photo', ['menu' => $m ?? (object) ['image' => null, 'name' => ''], 'size' => 'w-20 h-20'])
            </div>
            <div class="min-w-0 flex-1">
                <label for="image" class="btn-ghost cursor-pointer">
                    <i class="fa-solid fa-upload text-xs"></i> {{ !empty($m?->image) ? 'Ganti foto' : 'Pilih foto' }}
                </label>
                <input id="image" type="file" name="image" accept="image/jpeg,image/png" class="sr-only">
                <p class="hint">JPG atau PNG, maksimal 2 MB. Tanpa foto, menu tampil dengan ikon sendok-garpu.</p>
                @error('image') <p class="error">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.getElementById('image').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = 'Foto baru';
            img.className = 'w-20 h-20 rounded-[10px] border border-line object-cover';
            document.getElementById('photo-preview').replaceChildren(img);
        });
    </script>
@endpush
