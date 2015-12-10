<?php

namespace Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Collection;

use Symfony\Component\Process\Process;
use Tonic\Behat\ParallelScenarioExtension\ParallelProcessRunner\Exception\ProcessesMustBeInReadyStatusException;

/**
 * Class ProcessQueueCollection.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessQueueCollection extends ProcessCollection
{
    /**
     * {@inheritdoc}
     *
     * @param Process|Process[]|ProcessCollection|array $process
     *
     * @return array|int
     *
     * @throws ProcessesMustBeInReadyStatusException
     */
    public function add($process)
    {
        switch (true) {
            case is_array($process):
                $result = array_map(function ($process) {
                    return $this->add($process);
                }, $process);
                break;
            case $process instanceof ProcessCollection:
                $result = $this->add($process->toArray());
                break;
            case $process instanceof Process:
                if ($process->getStatus() != Process::STATUS_READY) {
                    throw new ProcessesMustBeInReadyStatusException($process);
                }
            default:
                $result = parent::add($process);
        }

        return $result;
    }
}
