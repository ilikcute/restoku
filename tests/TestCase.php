<?php

namespace Tests;

use App\Models\Account;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property Tenant $tenant
 * @property User $cashier
 * @property User $user
 * @property Shift $shift
 * @property Account $account
 * @property Product $product
 */
#[\AllowDynamicProperties]
abstract class TestCase extends BaseTestCase
{
    //
}
