@props(['name', 'value', 'minMonth', 'maxMonth'])

@php
    $initial = $value
        ? \Carbon\Carbon::parse($value)->format('Y-m')
        : now()->format('Y-m');

    $start = \Carbon\Carbon::parse($minMonth)->startOfMonth();
    $end   = \Carbon\Carbon::parse($maxMonth)->startOfMonth();
@endphp

<div class="d-flex align-items-center gap-2 month-selector">
    <button type="button" class="btn btn-secondary month-prev">&lt;</button>

    <select name="{{ $name }}" class="form-select month-select">
        @for ($date = $start->copy(); $date <= $end; $date->addMonth())
            @php
                $val = $date->format('Y-m');
                $label = $date->format('M Y');
            @endphp

            <option value="{{ $val }}" @selected($val === $initial)>
                {{ $label }}
            </option>
        @endfor
    </select>

    <button type="button" class="btn btn-secondary month-next">&gt;</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.month-selector').forEach(selector => {
        const select = selector.querySelector('.month-select');
        const prev = selector.querySelector('.month-prev');
        const next = selector.querySelector('.month-next');

        function changeMonth(delta) {
            const [year, month] = select.value.split('-').map(Number);
            const date = new Date(year, month - 1 + delta, 1);

            const newVal = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');

            if ([...select.options].some(o => o.value === newVal)) {
                select.value = newVal;
                select.form.submit();
            }
        }

        prev.addEventListener('click', () => changeMonth(-1));
        next.addEventListener('click', () => changeMonth(+1));

        select.addEventListener('change', () => select.form.submit());
    });
});
</script>
