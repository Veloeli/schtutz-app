<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Collection;

class DocumentService
{
    public function all(): Collection
    {
        return Document::orderBy('created_at', 'desc')->get();
    }

    public function create(array $data): Document
    {
        return Document::create([
            'title' => $data['title'],
        ]);
    }

    public function update(Document $document, array $data): Document
    {
        $document->update([
            'title' => $data['title'],
        ]);

        return $document;
    }

    public function delete(Document $document): void
    {
        $document->delete();
    }
}
