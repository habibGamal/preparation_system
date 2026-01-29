<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\MaterialEntranceStatus;
use App\Models\RawMaterialOut;
use App\Models\User;

final class RawMaterialOutPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RawMaterialOut $rawMaterialOut): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RawMaterialOut $rawMaterialOut): bool
    {
        return $rawMaterialOut->status !== MaterialEntranceStatus::Closed;
    }

    public function delete(User $user, RawMaterialOut $rawMaterialOut): bool
    {
        return $rawMaterialOut->status !== MaterialEntranceStatus::Closed;
    }

    public function restore(User $user, RawMaterialOut $rawMaterialOut): bool
    {
        return true;
    }

    public function forceDelete(User $user, RawMaterialOut $rawMaterialOut): bool
    {
        return $rawMaterialOut->status !== MaterialEntranceStatus::Closed;
    }
}
