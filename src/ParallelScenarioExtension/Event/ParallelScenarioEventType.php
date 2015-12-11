<?php

namespace Tonic\Behat\ParallelScenarioExtension\Event;

use Tonic\ParallelProcessRunner\Event\ParallelProcessRunnerEventType;

/**
 * Class ParallelScenarioEventType.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ParallelScenarioEventType
{
    const FEATURE_TESTED_BEFORE = 'parallel_scenario.feature_tested.before';
    const FEATURE_TESTED_AFTER = 'parallel_scenario.feature_tested.after';

    const PROCESS_BEFORE_START = ParallelProcessRunnerEventType::PROCESS_START_BEFORE;
    const PROCESS_AFTER_STOP = ParallelProcessRunnerEventType::PROCESS_STOP_AFTER;
    const PROCESS_OUT = ParallelProcessRunnerEventType::PROCESS_OUT;
}
