<?php

namespace App\View\Components\Listing;

use Illuminate\View\Component;

class SummaryPanel extends Component
{
    public $listing;

    public function __construct($listing)
    {
        $this->listing = $listing;
    }

    public function render()
    {
        return view('components.listing.summary-panel');
    }
}
