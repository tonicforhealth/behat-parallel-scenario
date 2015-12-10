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
     * @param Process|Process[]|ProcessCollection|array $process
     *                                                           {@inheritdoc}
     *
     * @throws ProcessesMustBeInReadyStatusException
     */
    public function add($process)
    {
        switch (true) {
            case is_array($process):
                array_walk($process, function ($process) {
                    $this->add($process);
                });
                break;
            case $process instanceof ProcessCollection:
                $this->add($process->toArray());
                break;
            case $process instanceof Process:
                if ($process->getStatus() != Process::STATUS_READY) {
                    throw new ProcessesMustBeInReadyStatusException($process);
                }
            default:
                $this->add($process);
        }

        return parent::add($process);
    }
}
