<?php

namespace Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Process\Process;
use Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Collection\ProcessCollection;
use Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Collection\WaitProcessCollection;
use Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Event\ProcessAfterStopEvent;
use Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Event\ProcessBeforeStartEvent;
use Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Event\ProcessOutEvent;
use Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Exception\AbstractProcessException;
use Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Exception\NotProcessException;

/**
 * Class ParallelProcessRunner.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ParallelProcessRunner
{
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;
    /**
     * @var WaitProcessCollection
     */
    protected $waitProcessCollection;
    /**
     * @var ProcessCollection
     */
    protected $activeProcessCollection;
    /**
     * @var ProcessCollection
     */
    protected $doneProcessCollection;
    /**
     * maximum processes in parallel
     *
     * @var int
     */
    protected $maxParallelProcess = 1;
    /**
     * time in microseconds to wait between processes status check
     *
     * @var int
     */
    protected $statusCheckWait = 1000;

    /**
     * ProcessManager constructor.
     *
     * @param EventDispatcher|null $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher = null)
    {
        if (!$eventDispatcher) {
            $eventDispatcher = new EventDispatcher();
        }

        $this->eventDispatcher = $eventDispatcher;
        $this->waitProcessCollection = new WaitProcessCollection();
        $this->activeProcessCollection = new ProcessCollection();
        $this->doneProcessCollection = new ProcessCollection();
    }

    /**
     * @param int $statusCheckWait
     *
     * @return $this
     */
    public function setStatusCheckWait($statusCheckWait)
    {
        $this->statusCheckWait = $statusCheckWait;

        return $this;
    }

    /**
     * @param int $maxParallelProcess
     *
     * @return $this
     */
    public function setMaxParallelProcess($maxParallelProcess)
    {
        $this->maxParallelProcess = $maxParallelProcess;

        return $this;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param Process|Process[]|ProcessCollection|array $processes
     *
     * @throws AbstractProcessException
     *
     * @return $this
     */
    public function add($processes)
    {
        $this->waitProcessCollection->add($processes);

        return $this;
    }

    /**
     * @return Process[]
     */
    public function run()
    {
        while ($this->purgeDoneProcesses()->startWaitingProcesses()->waitBeforeStatusCheck()->isRunning());

        return $this->doneProcessCollection->toArray();
    }

    /**
     * @return $this
     *
     * @throws NotProcessException
     */
    protected function startWaitingProcesses()
    {
        $required = max(0, $this->maxParallelProcess - $this->activeProcessCollection->count());

        foreach ($this->waitProcessCollection->spliceByStatus(Process::STATUS_READY, $required) as $process) {
            $processIndex = $this->activeProcessCollection->add($process);

            $this->getEventDispatcher()->dispatch(ProcessBeforeStartEvent::EVENT_NAME, new ProcessBeforeStartEvent($process, $processIndex));
            $process->start(function ($outType, $outData) use ($process) {
                $this->getEventDispatcher()->dispatch(ProcessOutEvent::EVENT_NAME, new ProcessOutEvent($process, $outType, $outData));
            });
        }

        return $this;
    }

    /**
     * @return $this
     *
     * @throws NotProcessException
     */
    protected function purgeDoneProcesses()
    {
        $processes = array_merge(
            $this->activeProcessCollection->spliceByStatus(Process::STATUS_READY),
            $this->activeProcessCollection->spliceByStatus(Process::STATUS_TERMINATED)
        );

        foreach ($processes as $process) {
            $this->doneProcessCollection->add($process);
            $this->getEventDispatcher()->dispatch(ProcessAfterStopEvent::EVENT_NAME, new ProcessAfterStopEvent($process));
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->waitProcessCollection->clear();
        $this->activeProcessCollection->clear();
        $this->doneProcessCollection->clear();

        return $this;
    }

    /**
     * @return $this
     */
    protected function waitBeforeStatusCheck()
    {
        if (!$this->activeProcessCollection->isEmpty()) {
            usleep($this->statusCheckWait);
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function isRunning()
    {
        return !$this->activeProcessCollection->isEmpty();
    }

    /**
     * @return $this
     */
    public function stop()
    {
        $this->waitProcessCollection->clear();
        foreach ($this->activeProcessCollection->toArray() as $process) {
            $process->stop(0);
        }

        return $this->purgeDoneProcesses();
    }

    public function __destruct()
    {
        $this->stop();
    }
}
