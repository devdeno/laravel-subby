<?php

declare(strict_types=1);

namespace Ljsharp\Subby\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToPlan
{
    /**
     * The model always belongs to a plan.
     *
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(config('subby.models.plan'), 'plan_id', 'id', 'plan');
    }

    /**
     * Scope models by plan id.
     *
     * @param Builder $builder
     * @param int $planId
     *
     * @return Builder
     */
    public function scopeByPlanId(Builder $builder, int $planId): Builder
    {
        return $builder->where('plan_id', $planId);
    }
}
