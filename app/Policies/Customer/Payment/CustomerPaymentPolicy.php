<?php

namespace App\Policies\Customer\Payment;

use App\Models\Customer\Payment\CustomerPayment;
use App\Models\User;
use App\Policies\CommonPolicy;

class CustomerPaymentPolicy extends CommonPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CustomerPayment $customerPayment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CustomerPayment $customerPayment): bool
    {
        return true;
    }

    public function delete(User $user, CustomerPayment $customerPayment): bool
    {
        return true;
    }

    public function restore(User $user, CustomerPayment $customerPayment): bool
    {
        return true;
    }

    public function forceDelete(User $user, CustomerPayment $customerPayment): bool
    {
        return true;
    }

    public function restoreAny(User $user): bool
    {
        return true;
    }

    public function deleteAny(User $user): bool
    {
        return true;
    }

    public function forceDeleteAny(User $user): bool
    {
        return true;
    }
}
