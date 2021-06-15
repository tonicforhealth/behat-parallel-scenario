<?php

declare(strict_types=1);

namespace Tonic\Behat\ParallelScenarioExtension\Event;

use Behat\Testwork\Event\Event;

class BeforeFeatureTestEvent extends Event implements ParallelScenarioEventType
{
}
