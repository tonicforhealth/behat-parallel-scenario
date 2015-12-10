<?php

namespace Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Event;

use Symfony\Component\Process\Process;

/**
 * Class ProcessOutEvent.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessOutEvent extends AbstractProcessEvent
{
    const EVENT_NAME = 'parallel_process_runner.process.out';

    /**
     * @var string
     */
    private $outType, $outData;

    /**
     * ProcessOutEvent constructor.
     *
     * @param Process $process
     * @param string  $outType
     * @param string  $outData
     */
    public function __construct(Process $process, $outType, $outData)
    {
        parent::__construct($process);
    }

    /**
     * @return string
     */
    public function getOutType()
    {
        return $this->outType;
    }

    /**
     * @return string
     */
    public function getOutData()
    {
        return $this->outData;
    }
}
