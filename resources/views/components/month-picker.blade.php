@php
    $initial = $value
        ? \Carbon\Carbon::parse($value)->format('Y-m-01')
        : now()->format('Y-m-01');
@endphp

<style>
    .month-picker .month-display {
        flex: 0 0 80px; /* fixed width */
        text-align: center;
    }
    .month-picker .btn {
        padding: 2px 8px;
        line-height: 1.1;
    }
    .month-picker {
        margin-bottom: 1rem; /* or 1.5rem, 2rem, etc. */
    }

</style>

<div class="d-flex align-items-center gap-3 month-picker">
    <button type="button" class="btn btn-secondary month-prev">&lt;</button>

    <div class="month-display"></div>

    <button type="button" class="btn btn-secondary month-next">&gt;</button>

    <form method="GET" class="month-form">
        <input type="hidden" name="month" class="month-hidden">
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.month-picker').forEach(picker => {
        const display = picker.querySelector('.month-display');
        const hidden = picker.querySelector('.month-hidden');
        const prev = picker.querySelector('.month-prev');
        const next = picker.querySelector('.month-next');
        const form = picker.querySelector('.month-form');

        let current = new Date("{{ $initial }}");

        function updateUI(submit = false) {
            const year = current.getFullYear();
            const month = String(current.getMonth() + 1).padStart(2, '0');

            display.textContent = current.toLocaleString('en-US', {
                month: 'short',
                year: 'numeric'
            });

            hidden.value = `${year}-${month}`;

            if (submit) {
                form.submit();
            }
        }

        prev.addEventListener('click', () => {
            current.setMonth(current.getMonth() - 1);
            updateUI(true); // submit after change
        });

        next.addEventListener('click', () => {
            current.setMonth(current.getMonth() + 1);
            updateUI(true); // submit after change
        });

        updateUI(false); // initial render only
    });
});
</script>
