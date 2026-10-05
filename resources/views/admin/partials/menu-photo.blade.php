{{-- Foto menu, atau ikon sendok-garpu abu-abu bila admin belum mengunggah foto --}}
@php $size = $size ?? 'w-14 h-14'; @endphp
@if (!empty($menu->image))
    <img src="{{ asset('storage/' . $menu->image) }}" alt="Foto {{ $menu->name }}"
        class="{{ $size }} shrink-0 rounded-[10px] border border-line object-cover">
@else
    <div class="{{ $size }} grid shrink-0 place-items-center rounded-[10px] bg-[#F1F1EF]" aria-label="Belum ada foto">
        <i class="fa-solid fa-utensils text-[#A3A8A5]"></i>
    </div>
@endif
