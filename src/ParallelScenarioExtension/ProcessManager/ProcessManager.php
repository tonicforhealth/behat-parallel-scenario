<?php

namespace Tonic\Behat\ParallelScenarioExtension\ProcessManager;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Process\Process;

/**
 * Class ProcessManager.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessManager
{
    const EVENT_PROCESS_STOP = 'process_manager.process.stop';
    const EVENT_PROCESS_BEFORE_START = 'process_manager.process.start.before';
    const EVENT_PROCESS_OUT = 'process_manager.process.out';

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;
    /**
     * @var Process[]
     */
    protected $processesQueue = [];
    /**
     * @var Process[]
     */
    protected $activeProcesses = [];
    /**
     * @var Process[]
     */
    protected $doneProcesses = [];
    /**
     * @var int
     */
    protected $maxParallelProcess = 1;
    /**
     * @var int
     */
    protected $poll = 1000;

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
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param Process[] $processes
     *
     * @throws \Exception
     */
    public function runParallel(array $processes)
    {
        $this->processesQueue = $processes;
        $this->activeProcesses = [];
        $this->doneProcesses = [];

        if (!count($this->processesQueue)) {
            throw new \Exception('Can not run in parallel 0 commands');
        }

        $this->validateProcesses();

        do {
            $this->stopProcess();
            $this->starProcessesFromQueue();
            usleep($this->poll);
        } while (!empty($this->activeProcesses));
    }

    /**
     * @throws \Exception
     */
    protected function validateProcesses()
    {
        foreach ($this->processesQueue as $process) {
            if (!$process instanceof Process) {
                throw new \Exception('Process in array need to be instance of Symfony Process');
            }
        }
    }

    protected function starProcessesFromQueue()
    {
        while (!empty($this->processesQueue) && count($this->activeProcesses) < $this->maxParallelProcess) {
            /** @var Process $process */
            $process = array_shift($this->processesQueue);
            $this->eventDispatcher->dispatch(self::EVENT_PROCESS_BEFORE_START, new ProcessEvent($process));
            $process->start(function ($outType, $outData) use ($process) {
                $this->eventDispatcher->dispatch(self::EVENT_PROCESS_OUT, new ProcessOutEvent($process, $outType, $outData));
            });
            $this->activeProcesses[] = $process;
        }
    }

    protected function stopProcess()
    {
        foreach ($this->activeProcesses as $index => $process) {
            if (!$process->isRunning()) {
                unset($this->activeProcesses[$index]);
                $this->doneProcesses[] = $process;

                $this->eventDispatcher->dispatch(self::EVENT_PROCESS_STOP, new ProcessEvent($process));
            }
        }
    }

    /**
     * @param int $poll
     *
     * @return $this
     */
    public function setPoll($poll)
    {
        $this->poll = $poll;

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
}
