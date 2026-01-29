<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\MaterialEntranceStatus;
use App\Models\ManufacturedMaterialOut;
use App\Models\User;

final class ManufacturedMaterialOutPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ManufacturedMaterialOut $manufacturedMaterialOut): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ManufacturedMaterialOut $manufacturedMaterialOut): bool
    {
        return $manufacturedMaterialOut->status !== MaterialEntranceStatus::Closed;
    }

    public function delete(User $user, ManufacturedMaterialOut $manufacturedMaterialOut): bool
    {
        return $manufacturedMaterialOut->status !== MaterialEntranceStatus::Closed;
    }

    public function restore(User $user, ManufacturedMaterialOut $manufacturedMaterialOut): bool
    {
        return true;
    }

    public function forceDelete(User $user, ManufacturedMaterialOut $manufacturedMaterialOut): bool
    {
        return $manufacturedMaterialOut->status !== MaterialEntranceStatus::Closed;
    }
}
