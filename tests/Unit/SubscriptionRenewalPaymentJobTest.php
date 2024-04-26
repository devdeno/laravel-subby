<?php

namespace Ljsharp\Subby\Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ljsharp\Subby\Jobs\SubscriptionRenewalPaymentJob;
use Ljsharp\Subby\Services\PendingPaymentCollector;
use Ljsharp\Subby\Tests\TestCase;

class SubscriptionRenewalPaymentJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test a successful payment renewal.
     * @throws \Exception
     */
    public function testSuccessfulJob()
    {
        $subscription = $this->testUser->subscription('main');
        $subscription->payment_method = 'success';
        $subscription->save();

        $this->travelTo($subscription->ends_at->add(5, 'second'));

        $pendingPaymentCollector = new PendingPaymentCollector();
        $pendingPayments = $pendingPaymentCollector->collectPayments();

        $job = (new SubscriptionRenewalPaymentJob($pendingPayments[0]['collectable_id']));
        dispatch_sync($job);

        $subscription->refresh();

        $this->assertTrue($this->testUser->subscription('main')->ends_at > now());
    }

    /**
     * Test a failed payment renewal.
     * @throws \Exception
     */
    public function testFailedJob()
    {
        $date = Carbon::now()->add(10, 'day');
        $subscription = $this->testUser->subscription('main');
        $subscription->payment_method = 'fail';
        $subscription->save();

        $subscription->toPlan($this->testPlanPro)->onDate($date)->setSchedule();

        $this->travelTo($date->add(5, 'second'));

        $pendingPaymentCollector = new PendingPaymentCollector();
        $pendingPayments = $pendingPaymentCollector->collectScheduledPayments();

        $job = (new SubscriptionRenewalPaymentJob($pendingPayments[0]['collectable_id']));
        $this->expectException('\Exception');
        dispatch_sync($job);

        $this->assertTrue($this->testUser->subscription('main')->ends_at < now());
    }
}
