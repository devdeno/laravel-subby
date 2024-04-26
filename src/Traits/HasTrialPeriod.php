<?php

namespace Ljsharp\Subby\Traits;

use Ljsharp\Subby\Helpers\CarbonHelper;
use Ljsharp\Subby\Services\Period;

trait HasTrialPeriod
{
    /**
     * Trial total duration in specified interval.
     * @param string $interval
     * @return int
     * @throws \Exception
     */
    public function getTrialTotalDurationIn(string $interval) :int
    {
        $trialPeriod = new Period($this->trial_interval, $this->trial_period);

        return $trialPeriod->getStartDate()->{CarbonHelper::diffIn($interval)}($trialPeriod->getEndDate());
    }

    /**
     * Check if entity has trial.
     *
     * @return bool
     */
    public function hasTrial(): bool
    {
        return $this->trial_period && $this->trial_interval;
    }
}
