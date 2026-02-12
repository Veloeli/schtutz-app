<?php

namespace App\Services;

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DocumentService
{
    public function all(): Collection
    {
        return Document::orderBy('created_at', 'desc')->get();
    }

    public function forMonth(Carbon $month)
    {
        return Document::with('owner')
            ->whereBetween('posting_date', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->orderBy('posting_date', 'desc')
            ->get();
    }

    public function create(array $data): Document
    {
        return Document::create($data);
    }

    public function update(Document $document, array $data): Document
    {
        $document->update($data);
        return $document;
    }

    public function delete(Document $document): void
    {
        $document->delete();
    }
}
