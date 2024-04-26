<?php

namespace Ljsharp\Subby\Tests\Unit;

use Ljsharp\Subby\Models\Plan;
use Ljsharp\Subby\Tests\TestCase;

class PlanTest extends TestCase
{
    /**
     * Test Plan creation with already existing tag in database.
     */
    public function testUnableToCreatePlanWithExistingTag()
    {
        $this->expectException('Ljsharp\Subby\Exceptions\DuplicateException');
        Plan::create([
            'tag' => 'basic',
            'name' => 'New Basic Plan',
            'description' => 'This plan cannot exist.',
            'currency' => 'EUR',
        ]);
    }
}
