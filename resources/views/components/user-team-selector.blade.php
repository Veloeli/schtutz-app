@props([
    'teams' => [],
    'members' => [],
    'filter' => null,
    'name' => 'filter',
    'autoSubmit' => true,
])

<form method="GET" class="mb-3">
    <select name="{{ $name }}"
            class="form-select w-auto d-inline-block"
            @if($autoSubmit) onchange="this.form.submit()" @endif
    >
        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All</option>

        <optgroup label="Teams">
            @foreach ($teams as $team)
                <option value="team-{{ $team->id }}"
                    {{ $filter === 'team-'.$team->id ? 'selected' : '' }}>
                    {{ $team->name }}
                </option>
            @endforeach
        </optgroup>

        <optgroup label="Members">
            @foreach ($members as $member)
                <option value="member-{{ $member->id }}"
                    {{ $filter === 'member-'.$member->id ? 'selected' : '' }}>
                    {{ $member->name }}
                </option>
            @endforeach
        </optgroup>
    </select>
</form>
