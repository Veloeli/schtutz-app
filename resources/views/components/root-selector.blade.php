<div class="w-auto">
    <select name="root_id" id="root_id" class="form-select" onchange="this.form.submit()">
        @foreach ($rootRollups as $root)
            <option value="{{ $root->id }}"
                @selected($selectedRoot && $selectedRoot->id === $root->id)>
                {{ $root->name }}
            </option>
        @endforeach
    </select>
</div>
