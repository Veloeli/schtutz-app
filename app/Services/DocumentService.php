<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Collection;
use DateTime;

class DocumentService
{
    public function all(): Collection
    {
        return Document::orderBy('created_at', 'desc')->get();
    }

    public function visibleFor(User $user, string $month, string $filter = 'all'): Collection
    {
        // convert month from yyyy-mm to start and end date
        $start = $month . '-01';
        $end   = (new DateTime($start))->modify('last day of')->format('Y-m-d');

        // Parse filter
        [$filterType, $filterId] = $filter && str_contains($filter, '-')
            ? explode('-', $filter)
            : [null, null];

        return Document::query()
            ->whereBetween('posting_date', [$start, $end])
            ->when($filterType === 'team', function ($q) use ($filterId) {
                $q->whereHas('items.category.team', function ($q) use ($filterId) {
                    $q->where('teams.id', $filterId);
                });
            })
            ->when($filterType === 'member', function ($q) use ($filterId) {
                $q->where('user_id', $filterId);
            })
            ->with([
                'user:id,name,freeze_after',
                'items' => function ($q) {
                    $q->whereHas('category')
                      ->with([
                          'category.team',
                          'document.user:id,name,freeze_after'
                      ]);
                },
            ])
            ->withSum([
                'items as amount_sum' => fn ($q) =>
                    $q->withoutGlobalScope('visibility')
            ], 'amount')
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
