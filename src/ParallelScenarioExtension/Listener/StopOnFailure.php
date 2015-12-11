<?php

namespace Tonic\Behat\ParallelScenarioExtension\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\ParallelProcessRunner\Event\ProcessAfterStopEvent;
use Tonic\ParallelProcessRunner\ParallelProcessRunner;

/**
 * Class StopOnFailure.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class StopOnFailure implements EventSubscriberInterface
{
    /**
     * @var ParallelProcessRunner
     */
    private $parallelProcessRunner;

    /**
     * StopOnFailureListener constructor.
     *
     * @param ParallelProcessRunner $parallelProcessRunner
     */
    public function __construct(ParallelProcessRunner $parallelProcessRunner)
    {
        $this->parallelProcessRunner = $parallelProcessRunner;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ParallelScenarioEventType::PROCESS_AFTER_STOP => 'stopOnFailure',
        ];
    }

    /**
     * @param ProcessAfterStopEvent $event
     */
    public function stopOnFailure(ProcessAfterStopEvent $event)
    {
        /** @var ScenarioProcess $process */
        $process = $event->getProcess();
        if ($process->withError()) {
            $this->parallelProcessRunner->stop();
            exit(1);
        }
    }
}