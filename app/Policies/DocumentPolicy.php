<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Services\VisibilityService;

class DocumentPolicy
{
    public function update(User $user, Document $document)
    {
        return VisibilityService::canEditDocument($user, $document);
    }

    public function delete(User $user, Document $document)
    {
        return VisibilityService::canEditDocument($user, $document);
    }
}
