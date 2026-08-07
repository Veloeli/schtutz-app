<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MonthSelector extends Component
{
    public string $name;
    public ?string $value;
    public $minMonth;
    public $maxMonth;

    public function __construct(string $name, ?string $value = null, $minMonth = null, $maxMonth = null)
    {
        $this->name     = $name;
        $this->value    = $value;
        $this->minMonth = $minMonth;
        $this->maxMonth = $maxMonth;
    }

    public function render()
    {
        return view('components.month-selector');
    }
}
