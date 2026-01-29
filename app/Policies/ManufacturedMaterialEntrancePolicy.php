<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\MaterialEntranceStatus;
use App\Models\ManufacturedMaterialEntrance;
use App\Models\User;

final class ManufacturedMaterialEntrancePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ManufacturedMaterialEntrance $manufacturedMaterialEntrance): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ManufacturedMaterialEntrance $manufacturedMaterialEntrance): bool
    {
        return $manufacturedMaterialEntrance->status !== MaterialEntranceStatus::Closed;
    }

    public function delete(User $user, ManufacturedMaterialEntrance $manufacturedMaterialEntrance): bool
    {
        return $manufacturedMaterialEntrance->status !== MaterialEntranceStatus::Closed;
    }

    public function restore(User $user, ManufacturedMaterialEntrance $manufacturedMaterialEntrance): bool
    {
        return true;
    }

    public function forceDelete(User $user, ManufacturedMaterialEntrance $manufacturedMaterialEntrance): bool
    {
        return $manufacturedMaterialEntrance->status !== MaterialEntranceStatus::Closed;
    }
}
