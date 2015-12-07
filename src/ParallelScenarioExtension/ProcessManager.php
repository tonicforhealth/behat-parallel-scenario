<?php

namespace Tonic\Behat\ParallelScenarioExtension;

use Symfony\Component\Process\Process;

/**
 * Class ProcessManager.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessManager
{
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
     * @var callable|null
     */
    protected $callback = null;

    /**
     * @var callable|null
     */
    protected $stopCallback = null;

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
            $process->start($this->callback);
            $this->activeProcesses[] = $process;
        }
    }

    protected function stopProcess()
    {
        foreach ($this->activeProcesses as $index => $process) {
            if (!$process->isRunning()) {
                unset($this->activeProcesses[$index]);
                $this->doneProcesses[] = $process;
                if ($callback = $this->stopCallback) {
                    $callback($process);
                }
            }
        }
    }

    /**
     * @param callable|null $callback
     */
    public function setCallback(callable $callback = null)
    {
        $this->callback = $callback;
    }

    /**
     * @param callable|null $stopCallback
     */
    public function setStopCallback(callable $stopCallback = null)
    {
        $this->stopCallback = $stopCallback;
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
