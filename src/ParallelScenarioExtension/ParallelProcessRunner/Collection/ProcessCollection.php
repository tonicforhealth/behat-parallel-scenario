<?php

namespace Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Collection;

use Symfony\Component\Process\Process;
use Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Exception\NotProcessException;

/**
 * Class ProcessCollection.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessCollection
{
    /**
     * @var Process[]
     */
    private $processes = [];

    /**
     * @param Process $process
     *
     * @throws NotProcessException
     *
     * @return int index of last element
     */
    public function add($process)
    {
        if (!$process instanceof Process) {
            throw new NotProcessException($process);
        }

        $this->processes[] = $process;

        end($this->processes);
        $processIndex = key($this->processes);

        return $processIndex;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->processes = [];

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->processes);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->processes);
    }

    /**
     * @param string   $processStatus
     * @param int|null $limit
     *
     * @see Process::STATUS_STARTED
     * @see Process::STATUS_READY
     * @see Process::STATUS_TERMINATED
     *
     * @return Process[]
     */
    public function spliceByStatus($processStatus, $limit = null)
    {
        $processes = [];

        if (is_null($limit)) {
            $limit = $this->count();
        }

        foreach ($this->processes as $index => $process) {
            if (count($processes) >= $limit) {
                break;
            }

            if ($process->getStatus() == $processStatus) {
                unset($this->processes[$index]);
                $processes[] = $process;
            }
        }

        return $processes;
    }

    /**
     * @return Process[]
     */
    public function toArray()
    {
        return $this->processes;
    }
}
