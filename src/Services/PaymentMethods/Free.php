<?php

declare(strict_types=1);

namespace Ljsharp\Subby\Services\PaymentMethods;

use Ljsharp\Subby\Contracts\PaymentMethodService;
use Ljsharp\Subby\Traits\IsPaymentMethod;

class Free implements PaymentMethodService
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
