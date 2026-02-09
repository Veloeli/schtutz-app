<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MonthPicker extends Component
{
    public string $name;
    public ?string $value;

    public function __construct(string $name, ?string $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function render()
    {
        return view('components.month-picker');
    }
}
