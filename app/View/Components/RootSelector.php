<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Rollup;

class RootSelector extends Component
{
    public $rootRollups;
    public $selectedRoot;

    public function __construct()
    {
        $user = auth()->user();

        // Fetch all top-level roots
        $this->rootRollups = Rollup::whereNull('parent_id')->get();

        // Use your existing logic
        $this->selectedRoot = $user->resolveActiveRoot(request());
    }

    public function render()
    {
        return view('components.root-selector');
    }
}
