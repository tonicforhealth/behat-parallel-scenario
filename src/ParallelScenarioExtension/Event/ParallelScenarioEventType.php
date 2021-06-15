<?php

namespace Tonic\Behat\ParallelScenarioExtension\Event;

use Tonic\ParallelProcessRunner\Event\ParallelProcessRunnerEventType;

/**
 * Class ParallelScenarioEventType.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
interface ParallelScenarioEventType
{
    public const PROCESS_BEFORE_START = ParallelProcessRunnerEventType::PROCESS_START_BEFORE;
    public const PROCESS_AFTER_STOP = ParallelProcessRunnerEventType::PROCESS_STOP_AFTER;
    public const PROCESS_OUT = ParallelProcessRunnerEventType::PROCESS_OUT;
}
