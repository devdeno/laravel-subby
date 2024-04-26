<?php

declare(strict_types=1);

namespace Ljsharp\Subby\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use InvalidArgumentException;
use Ljsharp\Subby\Exceptions\DuplicateException;
use Ljsharp\Subby\Exceptions\InvalidPlanSubscription;
use Ljsharp\Subby\Models\Plan;
use Ljsharp\Subby\Models\PlanCombination;
use Ljsharp\Subby\Models\PlanSubscription;
use Ljsharp\Subby\Services\SubscriptionPeriod;

trait HasSubscriptions
{
    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param string $related
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $localKey
     *
     * @return MorphMany
     */
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    /**
     * The subscriber may have many subscriptions.
     *
     * @return MorphMany
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(config('subby.models.plan_subscription'), 'subscriber', 'subscriber_type', 'subscriber_id');
    }

    /**
     * A model may have many active subscriptions.
     *
     * @return Collection
     */
    public function activeSubscriptions(): Collection
    {
        return $this->subscriptions->reject->isInactive();
    }

    /**
     * Get a subscription by tag.
     *
     * @param string $subscriptionTag
     *
     * @return PlanSubscription|\Illuminate\Database\Eloquent\Model|MorphMany|null
     */
    public function subscription(?string $subscriptionTag = null)
    {
        if ($subscriptionTag === null) {
            $count = $this->subscriptions()->count();

            if ($count === 1) {
                return $this->subscriptions()->first();
            } elseif ($count === 0) {
                throw new InvalidPlanSubscription($subscriptionTag);
            }
        }

        $subscriptionTag = $subscriptionTag ?? config('subby.main_subscription_tag');

        if (!$subscriptionTag) {
            throw new InvalidArgumentException('Subscription tag not provided and default config is empty.');
        }

        $subscription = $this->subscriptions()->where('tag', $subscriptionTag)->first();

        if (!$subscription) {
            throw new InvalidPlanSubscription($subscriptionTag);
        }

        return $subscription;
    }

    /**
     * Get subscribed plans.
     *
     * @return PlanSubscription|null
     */
    public function subscribedPlans()
    {
        $planIds = $this->subscriptions->reject->isInactive()->pluck('plan_id')->unique();

        return app(config('subby.models.plan'))->whereIn('id', $planIds)->get();
    }

    /**
     * Check if the subscriber is subscribed to the given plan.
     *
     * @param int $planId
     *
     * @return bool
     */
    public function isSubscribedTo(int $planId): bool
    {
        $subscription = $this->subscriptions()->where('plan_id', $planId)->first();

        return $subscription && $subscription->isActive();
    }

    /**
     * Subscribe subscriber to a new plan.
     *
     * @param string $tag Identifier tag for the subscription
     * @param Plan|PlanCombination $planCombination Plan pricing and invoice data
     * @param string|null $name Human readable name for your subscriber's subscription
     * @param string|null $description Description for the subscription
     * @param Carbon|null $startDate When will the subscription start
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @throws \Exception
     */
    public function newSubscription(?string $tag, Plan|PlanCombination $planCombination, ?string $name = null, ?string $description = null, ?Carbon $startDate = null, $paymentMethod = 'free')
    {
        $tag = $tag ?? config('subby.main_subscription_tag');

        $plan = ($planCombination instanceof PlanCombination) ? $planCombination->plan : $planCombination;

        $subscriptionPeriod = new SubscriptionPeriod($planCombination, $startDate ?? now());

        try {
            $this->subscription($tag);
        } catch (InvalidPlanSubscription $e) {
            $subscription = $this->subscriptions()->create([
                'tag' => $tag,
                'name' => $name,
                'description' => $description,
                'plan_id' => $plan->id,
                'price' => $planCombination->price,
                'currency' => $planCombination->currency,
                'tier' => $plan->tier,
                'trial_interval' => $plan->trial_interval,
                'trial_period' => $plan->trial_period,
                'grace_interval' => $plan->grace_interval,
                'grace_period' => $plan->grace_period,
                'invoice_interval' => $planCombination->invoice_interval,
                'invoice_period' => $planCombination->invoice_period,
                'payment_method' => $paymentMethod,
                'trial_ends_at' => $subscriptionPeriod->getTrialEndDate(),
                'starts_at' => $subscriptionPeriod->getStartDate(),
                'ends_at' => $subscriptionPeriod->getEndDate(),
            ]);

            $subscription->syncPlanFeatures($plan);

            return $subscription;
        }

        throw new DuplicateException();
    }
}
