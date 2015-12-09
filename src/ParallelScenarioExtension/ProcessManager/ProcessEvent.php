<?php

namespace Tonic\Behat\ParallelScenarioExtension\ProcessManager;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

/**
 * Class ProcessEvent.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessEvent extends Event
{
    /**
     * @var Process
     */
    private $process;

    /**
     * ProcessEvent constructor.
     *
     * @param Process $process
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }
}
