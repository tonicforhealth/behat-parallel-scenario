<?php

namespace Tonic\Behat\ParallelScenarioExtension\ProcessManager;

use Symfony\Component\Process\Process;

/**
 * Class ProcessBeforeStartEvent.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessBeforeStartEvent extends ProcessEvent
{
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
