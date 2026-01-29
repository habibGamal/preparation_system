<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\MaterialEntranceStatus;
use App\Models\RawMaterialEntrance;
use App\Models\User;

final class RawMaterialEntrancePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, RawMaterialEntrance $rawMaterialEntrance): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, RawMaterialEntrance $rawMaterialEntrance): bool
    {
        return $rawMaterialEntrance->status !== MaterialEntranceStatus::Closed;
    }

    public function delete(User $user, RawMaterialEntrance $rawMaterialEntrance): bool
    {
        return $rawMaterialEntrance->status !== MaterialEntranceStatus::Closed;
    }

    public function restore(User $user, RawMaterialEntrance $rawMaterialEntrance): bool
    {
        return true;
    }

    public function forceDelete(User $user, RawMaterialEntrance $rawMaterialEntrance): bool
    {
        return $rawMaterialEntrance->status !== MaterialEntranceStatus::Closed;
    }
}
