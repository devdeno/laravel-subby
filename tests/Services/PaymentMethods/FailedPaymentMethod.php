<?php

declare(strict_types=1);

namespace Ljsharp\Subby\Tests\Services\PaymentMethods;

use Ljsharp\Subby\Contracts\PaymentMethodService;
use Ljsharp\Subby\Traits\IsPaymentMethod;

class FailedPaymentMethod implements PaymentMethodService
{
    use IsPaymentMethod;

    /**
     * Charge desired amount.
     * @return void
     */
    public function charge()
    {
        throw new \Exception('Payment failed');
    }
}
