<div class="row">

    <div class="col-md-8 mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control"
               value="{{ old('name', $security->name ?? '') }}" required>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">In Use</label>
        <select name="is_in_use" class="form-select" id="is_in_use">
            <option value="0" @selected(old('is_in_use', $security->is_in_use) == 0)>No</option>
            <option value="1" @selected(old('is_in_use', $security->is_in_use) == 1)>Yes</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Asset Class</label>
        <select name="asset_class" class="form-select">
            <option value="">-- Select --</option>
            @foreach(\App\Models\Security::ASSET_CLASSES as $code => $label)
                <option value="{{ $code }}"
                    @selected(old('asset_class', $security->asset_class ?? '') === $code)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Track Value</label>
        <select name="is_tracked" class="form-select" id="is_tracked">
            <option value="0" @selected(old('is_tracked', $security->is_tracked) == 0)>No</option>
            <option value="1" @selected(old('is_tracked', $security->is_tracked) == 1)>Yes</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Tracking Currency</label>
        <select name="currency_id" class="form-select">
            <option value="">-- None --</option>
            @foreach(\App\Models\Security::where('asset_class', 'FX')->orderBy('name')->get() as $currency)
                <option value="{{ $currency->id }}"
                    @selected(old('currency_id', $security->currency_id ?? '') == $currency->id)>
                    {{ $currency->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Team</label>
        <select name="team_id" class="form-select">
            <option value="">— No team (private) —</option>

            @foreach ($teams as $team)
                <option value="{{ $team->id }}" @selected($security->team_id == $team->id)>
                    {{ $team->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                Identifiers
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Ticker</label>
                        <input type="text" name="ticker" class="form-control"
                               value="{{ old('ticker', $security->ticker ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">ISIN</label>
                        <input type="text" name="isin" class="form-control"
                               value="{{ old('isin', $security->isin ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Identifier</label>
                        <input type="text" name="identifier" class="form-control"
                               value="{{ old('identifier', $security->identifier ?? '') }}">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-3">
            <div class="card-header">
                Report Groupings
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Region</label>
                        <input type="text" name="region" class="form-control"
                               value="{{ old('region', $security->region ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control"
                               value="{{ old('country', $security->country ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Company</label>
                        <input type="text" name="company" class="form-control"
                               value="{{ old('company', $security->company ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Strategy</label>
                        <input type="text" name="strategy" class="form-control"
                               value="{{ old('strategy', $security->strategy ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Theme</label>
                        <input type="text" name="theme" class="form-control"
                               value="{{ old('theme', $security->theme ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Hedged</label>
                        <select name="is_hedged" class="form-select" id="is_hedged">
                            <option value="0" @selected(old('is_hedged', $security->is_hedged) == 0)>No</option>
                            <option value="1" @selected(old('is_hedged', $security->is_hedged) == 1)>Yes</option>
                        </select>
                    </div>

                </div>
            </div>
        </div>


        <div class="card mb-3">
            <div class="card-header">
                Option Settings
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Type</label>
                        <select name="option_type" class="form-select">
                            <option value="">-- None --</option>
                            <option value="CALL" @selected(old('option_type', $security->option_type ?? '') === 'CALL')>CALL</option>
                            <option value="PUT" @selected(old('option_type', $security->option_type ?? '') === 'PUT')>PUT</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Strike</label>
                        <input type="number" step="0.000001" name="option_strike" class="form-control"
                               value="{{ old('option_strike', $security->option_strike ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Multiplier</label>
                        <input type="number" step="0.000001" name="option_multiplier" class="form-control"
                               value="{{ old('option_multiplier', $security->option_multiplier ?? '') }}">
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">Underlying</label>
                        <select name="option_underlying_id" class="form-select">
                            <option value="">-- None --</option>
                            @foreach(\App\Models\Security::orderBy('name')->get() as $s)
                                <option value="{{ $s->id }}"
                                    @selected(old('option_underlying_id', $security->option_underlying_id ?? '') == $s->id)>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Calculate Intrinsic Value</label>
                        <select name="option_calculate" class="form-select" id="option_calculate">
                            <option value="0" @selected(old('option_calculate', $security->option_calculate) == 0)>No</option>
                            <option value="1" @selected(old('option_calculate', $security->option_calculate) == 1)>Yes</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
