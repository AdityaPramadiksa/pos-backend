{{-- Filter rentang tanggal. $action = url tujuan; $slot (opsional) = isian tambahan. --}}
<form action="{{ $action }}" method="GET" class="flex flex-wrap items-end gap-2">
    <div>
        <label class="label text-xs text-muted" for="start_date">Dari</label>
        <input id="start_date" type="date" name="start_date" value="{{ $start ?? request('start_date') }}" class="field w-auto py-2">
    </div>
    <div>
        <label class="label text-xs text-muted" for="end_date">Sampai</label>
        <input id="end_date" type="date" name="end_date" value="{{ $end ?? request('end_date') }}" class="field w-auto py-2">
    </div>
    {!! $extra ?? '' !!}
    <button type="submit" class="btn-dark py-2">Terapkan</button>
    @if (request()->hasAny(['start_date', 'end_date', 'status', 'order_type', 'platform']))
        <a href="{{ $action }}" class="btn-ghost py-2">Hapus filter</a>
    @endif
</form>
