@php
    $initial = $value
        ? \Carbon\Carbon::parse($value)->format('Y-m-01')
        : now()->format('Y-m-01');
@endphp

<style>
    .month-selector .month-display {
        flex: 0 0 80px;
        text-align: center;
    }
    .month-selector .btn {
        padding: 2px 8px;
        line-height: 1.1;
    }
</style>

<div class="d-flex align-items-center gap-3 month-selector w-auto">
    <button type="button" class="btn btn-secondary month-prev">&lt;</button>

    <div class="month-display"></div>

    <button type="button" class="btn btn-secondary month-next">&gt;</button>

    <!-- Hidden input now belongs to the OUTER form -->
    <input type="hidden" name="month" class="month-hidden">
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.month-selector').forEach(selector => {
        const display = selector.querySelector('.month-display');
        const hidden = selector.querySelector('.month-hidden');
        const prev = selector.querySelector('.month-prev');
        const next = selector.querySelector('.month-next');

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
                hidden.form.submit(); // submit the OUTER form
            }
        }

        prev.addEventListener('click', () => {
            current.setMonth(current.getMonth() - 1);
            updateUI(true);
        });

        next.addEventListener('click', () => {
            current.setMonth(current.getMonth() + 1);
            updateUI(true);
        });

        updateUI(false);
    });
});
</script>
