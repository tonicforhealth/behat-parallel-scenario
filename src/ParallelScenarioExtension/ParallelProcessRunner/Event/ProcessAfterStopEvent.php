<?php

namespace Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Event;

/**
 * Class ProcessAfterStopEvent.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessAfterStopEvent extends AbstractProcessEvent
{
    const EVENT_NAME = 'parallel_process_runner.process.stop.after';
}
