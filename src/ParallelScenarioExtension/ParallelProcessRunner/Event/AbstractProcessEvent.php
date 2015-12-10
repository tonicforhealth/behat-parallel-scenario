<?php

namespace Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Process\Process;

/**
 * Class AbstractProcessEvent.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
abstract class AbstractProcessEvent extends Event
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
