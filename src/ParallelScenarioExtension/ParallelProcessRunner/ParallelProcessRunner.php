<?php

namespace Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Process\Process;
use Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Collection\ProcessCollection;
use Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Collection\ProcessQueueCollection;
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
     * @var ProcessQueueCollection
     */
    protected $processesQueue;
    /**
     * @var ProcessCollection
     */
    protected $activeProcesses;
    /**
     * @var ProcessCollection
     */
    protected $doneProcesses;
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
        $this->processesQueue = new ProcessQueueCollection();
        $this->activeProcesses = new ProcessCollection();
        $this->doneProcesses = new ProcessCollection();
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
        $this->processesQueue->add($processes);

        return $this;
    }

    /**
     * @return Process[]
     */
    public function run()
    {
        while ($this->stopProcesses()->startProcesses()->wait()->isRunning());

        return $this->doneProcesses->toArray();
    }

    /**
     * @return $this
     *
     * @throws NotProcessException
     */
    protected function startProcesses()
    {
        $required = max(0, $this->maxParallelProcess - $this->activeProcesses->count());

        foreach ($this->processesQueue->spliceByStatus(Process::STATUS_READY, $required) as $process) {
            $processIndex = $this->activeProcesses->add($process);

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
    protected function stopProcesses()
    {
        $processes = array_merge(
            $this->activeProcesses->spliceByStatus(Process::STATUS_READY),
            $this->activeProcesses->spliceByStatus(Process::STATUS_TERMINATED)
        );

        foreach ($processes as $process) {
            $this->doneProcesses->add($process);
            $this->getEventDispatcher()->dispatch(ProcessAfterStopEvent::EVENT_NAME, new ProcessAfterStopEvent($process));
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->processesQueue->clear();
        $this->activeProcesses->clear();
        $this->doneProcesses->clear();

        return $this;
    }

    /**
     * @return $this
     */
    protected function wait()
    {
        if (!$this->activeProcesses->isEmpty()) {
            usleep($this->statusCheckWait);
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function isRunning()
    {
        return !$this->activeProcesses->isEmpty();
    }

    /**
     * @return $this
     */
    public function stop()
    {
        $this->processesQueue->clear();
        foreach ($this->activeProcesses->toArray() as $process) {
            $process->stop(0);
        }

        return $this->stopProcesses();
    }

    public function __destruct()
    {
        $this->stop();
    }
}
