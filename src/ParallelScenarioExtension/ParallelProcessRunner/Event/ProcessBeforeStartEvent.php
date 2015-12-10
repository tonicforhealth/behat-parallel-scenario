<?php

namespace Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Event;

use Symfony\Component\Process\Process;

/**
 * Class ProcessBeforeStartEvent.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessBeforeStartEvent extends AbstractProcessEvent
{
    const EVENT_NAME = 'parallel_process_runner.process.start.before';

    /**
     * @var int
     */
    private $processIndex;

    /**
     * ProcessBeforeStartEvent constructor.
     *
     * @param Process $process
     * @param int     $processIndex
     */
    public function __construct(Process $process, $processIndex)
    {
        $this->processIndex = $processIndex;
        parent::__construct($process);
    }

    /**
     * @return int
     */
    public function getProcessIndex()
    {
        return $this->processIndex;
    }
}
