<?php

declare(strict_types=1);

namespace Ljsharp\Subby\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ljsharp\Subby\Exceptions\DuplicateException;
use Ljsharp\Subby\Traits\HasFeatures;
use Ljsharp\Subby\Traits\HasGracePeriod;
use Ljsharp\Subby\Traits\HasPricing;
use Ljsharp\Subby\Traits\HasSubscriptionPeriod;
use Ljsharp\Subby\Traits\HasTrialPeriod;
use Ljsharp\Subby\Traits\MorphsSchedules;

/**
 * Class Plan.
 */
class Plan extends Model
{
    use SoftDeletes, HasFeatures, HasPricing, HasTrialPeriod, HasSubscriptionPeriod, HasGracePeriod, MorphsSchedules;

    /**
     * @inheritdoc
     */
    protected $fillable = [
        'tag',
        'name',
        'description',
        'is_active',
        'price',
        'signup_fee',
        'currency',
        'trial_period',
        'trial_interval',
        'trial_mode',
        'grace_period',
        'grace_interval',
        'invoice_period',
        'invoice_interval',
        'tier',
    ];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'tag' => 'string',
        'is_active' => 'boolean',
        'price' => 'float',
        'signup_fee' => 'float',
        'currency' => 'string',
        'trial_period' => 'integer',
        'trial_interval' => 'string',
        'trial_mode' => 'string',
        'grace_period' => 'integer',
        'grace_interval' => 'string',
        'invoice_period' => 'integer',
        'invoice_interval' => 'string',
        'tier' => 'integer',
        'deleted_at' => 'datetime',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('subby.tables.plans'));
    }

    /**
     * Get validation rules.
     * @return string[]
     */
    public function getRules(): array
    {
        return [
            'tag' => 'required|max:150|unique:' . config('subby.tables.plans') . ',tag',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:32768',
            'is_active' => 'sometimes|boolean',
            'price' => 'required|numeric',
            'signup_fee' => 'required|numeric',
            'currency' => 'required|alpha|size:3',
            'trial_period' => 'sometimes|integer|max:100000',
            'trial_interval' => 'sometimes|in:hour,day,week,month',
            'trial_mode' => 'required|in:inside,outside',
            'grace_period' => 'sometimes|integer|max:100000',
            'grace_interval' => 'sometimes|in:hour,day,week,month',
            'invoice_period' => 'sometimes|integer|max:100000',
            'invoice_interval' => 'sometimes|in:hour,day,week,month',
            'tier' => 'nullable|integer|max:100000',
        ];
    }

    public static function create(array $attributes = [])
    {
        if (static::where('tag', $attributes['tag'])->first()) {
            throw new DuplicateException();
        }

        return static::query()->create($attributes);
    }

    /**
     * Get plan by the given tag.
     *
     * @param string $tag
     * @return null|$this
     */
    public static function getByTag(string $tag): ?self
    {
        return static::where('tag', $tag)->first();
    }

    /**
     * The plan may have many combinations.
     *
     * @return HasMany
     */
    public function combinations(): HasMany
    {
        return $this->hasMany(config('subby.models.plan_combination'), 'plan_id', 'id');
    }

    /**
     * The plan may have many features.
     *
     * @return HasMany
     */
    public function features(): HasMany
    {
        return $this->hasMany(config('subby.models.plan_feature'), 'plan_id', 'id');
    }

    /**
     * The plan may have many subscriptions.
     *
     * @return HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(config('subby.models.plan_subscription'), 'plan_id', 'id');
    }

    /**
     * Activate the plan.
     *
     * @return $this
     */
    public function activate()
    {
        $this->update(['is_active' => true]);

        return $this;
    }

    /**
     * Deactivate the plan.
     *
     * @return $this
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);

        return $this;
    }
}
