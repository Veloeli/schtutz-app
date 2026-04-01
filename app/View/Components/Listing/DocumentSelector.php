<?php

namespace App\View\Components\Listing;

use Illuminate\View\Component;

class DocumentSelector extends Component
{
    public $listing;
    public $documents;

    public function __construct($listing, $documents)
    {
        $this->listing = $listing;
        $this->documents = $documents;
    }

    public function render()
    {
        return view('components.listing.document-selector');
    }
}
