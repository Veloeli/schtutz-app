<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DocumentService
{
    public function all(): Collection
    {
        return Document::orderBy('created_at', 'desc')->get();
    }

    public function forMonth(Carbon $month): Collection
    {
        return Document::whereBetween('posting_date', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->orderBy('posting_date', 'desc')
            ->get();
    }

    public function visibleFor(User $user, Carbon $month): Collection
    {
        // Global scopes already enforce visibility
        return $this->forMonth($month);
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
