<?php

declare(strict_types=1);

namespace Ljsharp\Subby\Tests\Services\PaymentMethods;

use Ljsharp\Subby\Contracts\PaymentMethodService;
use Ljsharp\Subby\Traits\IsPaymentMethod;

class SucceededPaymentMethod implements PaymentMethodService
{
    use IsPaymentMethod;

    /**
     * Charge desired amount.
     * @return void
     */
    public function charge()
    {
        // Nothing is charged, no exception is raised
    }
}
