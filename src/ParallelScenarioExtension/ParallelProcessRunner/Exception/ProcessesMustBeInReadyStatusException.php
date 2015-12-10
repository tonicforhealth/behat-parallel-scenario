<?php

namespace Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Exception;

use Symfony\Component\Process\Process;

/**
 * Class ProcessesMustBeInReadyStatusException.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessesMustBeInReadyStatusException extends AbstractProcessException
{
    /**
     * @var Process
     */
    private $process;

    /**
     * ProcessesMustBeInReadyStatusException constructor.
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
