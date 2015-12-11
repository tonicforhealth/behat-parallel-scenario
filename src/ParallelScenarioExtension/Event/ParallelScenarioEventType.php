<?php

namespace Tonic\Behat\ParallelScenarioExtension\Event;

use Tonic\ParallelProcessRunner\Event\ProcessAfterStopEvent;
use Tonic\ParallelProcessRunner\Event\ProcessBeforeStartEvent;
use Tonic\ParallelProcessRunner\Event\ProcessOutEvent;

/**
 * Class ParallelScenarioEventType.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ParallelScenarioEventType
{
    const FEATURE_TESTED_BEFORE = 'parallel_scenario.feature_tested.before';
    const FEATURE_TESTED_AFTER = 'parallel_scenario.feature_tested.after';

    const PROCESS_BEFORE_START = ProcessBeforeStartEvent::EVENT_NAME;
    const PROCESS_AFTER_STOP = ProcessAfterStopEvent::EVENT_NAME;
    const PROCESS_OUT = ProcessOutEvent::EVENT_NAME;
}
