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

    public function visibleFor(User $user, Carbon|string $month, string $filter = 'all'): Collection
    {
        if (is_string($month)) {
            $month = Carbon::createFromFormat('Y-m', $month);
        }

        $allowedUsers = VisibilityService::allowedUserIds($user);

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        // Parse filter
        [$filterType, $filterId] = $filter && str_contains($filter, '-')
            ? explode('-', $filter)
            : [null, null];

        return Document::query()
            ->whereBetween('posting_date', [$start, $end])
            ->where(function ($q) use ($allowedUsers) {
                $q->whereIn('owner_id', $allowedUsers)
                  ->orWhereHas('items.category'); // category global scope applies
            })
            ->when($filterType === 'team', function ($q) use ($filterId) {
                $q->whereHas('items.category.team', function ($q) use ($filterId) {
                    $q->where('teams.id', $filterId);
                });
            })
            ->when($filterType === 'member', function ($q) use ($filterId) {
                $q->where('owner_id', $filterId);
            })
            ->with([
                'owner',
                'items' => function ($q) {
                    $q->whereHas('category')
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
