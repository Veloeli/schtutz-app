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

    public function visibleFor(User $user, Carbon $month): Collection
    {
        $allowedUsers = VisibilityService::allowedUserIds($user);

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        return Document::query()
            ->whereBetween('posting_date', [$start, $end])
            ->where(function ($q) use ($allowedUsers) {
                $q->whereIn('owner_id', $allowedUsers)
                  ->orWhereHas('items.category'); // category global scope applies
            })
            ->with([
                'owner',
                'items' => function ($q) {
                    $q->whereHas('category') // only items with visible categories
                      ->with('category.team')
                      ->with('document');
                },
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
