<?php

namespace App\Policies;

use App\Models\EnumerationRecord;
use App\Models\User;

class EnumerationRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->can('manage-enumerations');
    }

    public function view(User $user, EnumerationRecord $record): bool
    {
        return $this->viewAny($user) || $record->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->can('manage-enumerations');
    }

    public function update(User $user, EnumerationRecord $record): bool
    {
        return $user->isAdmin() || $record->user_id === $user->id;
    }

    public function delete(User $user, EnumerationRecord $record): bool
    {
        return $user->isAdmin() || $record->user_id === $user->id;
    }
}
