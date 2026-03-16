<div class="row">

    <div class="col-md-6 mb-3">
        <label class="form-label">Old Security</label>
        <select name="old_id" class="form-select" required>
            @foreach($allSecurities as $s)
                <option value="{{ $s->id }}"
                    @selected(isset($split) && $s->id == $split->old_id)>
                    {{ $s->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">New Security</label>
        <select name="new_id" class="form-select" required>
            @foreach($allSecurities as $s)
                <option value="{{ $s->id }}"
                    @selected(isset($split) && $s->id == $split->new_id)>
                    {{ $s->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Split Date</label>
        <input type="date"
               name="split_date"
               class="form-control"
               value="{{ $split->split_date ? $split->split_date->format('Y-m-d') : '' }}"
               required>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Split Factor</label>
        <input type="number"
               name="split_factor"
               step="0.000001"
               class="form-control"
               value="{{ formatQuantity($split->split_factor) ?? '' }}"
               required>
    </div>

</div>
