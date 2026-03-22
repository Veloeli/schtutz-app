@props([
    'teams' => [],
    'members' => [],
    'teamfilter' => null,
    'name' => 'teamfilter',
    'autoSubmit' => true,
])

<div class="w-auto">
    <select name="{{ $name }}"
            class="form-select w-auto d-inline-block"
            @if($autoSubmit) onchange="this.form.submit()" @endif
    >
        <option value="all" {{ $teamfilter === 'all' ? 'selected' : '' }}>All</option>

        <optgroup label="Teams">
            @foreach ($teams as $team)
                <option value="team-{{ $team->id }}"
                    {{ $teamfilter === 'team-'.$team->id ? 'selected' : '' }}>
                    {{ $team->name }}
                </option>
            @endforeach
        </optgroup>

        <optgroup label="Members">
            @foreach ($members as $member)
                <option value="member-{{ $member->id }}"
                    {{ $teamfilter === 'member-'.$member->id ? 'selected' : '' }}>
                    {{ $member->name }}
                </option>
            @endforeach
        </optgroup>
    </select>
</div>
