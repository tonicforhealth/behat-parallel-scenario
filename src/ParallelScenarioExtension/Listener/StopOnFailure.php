<?php

namespace Tonic\Behat\ParallelScenarioExtension\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\ParallelProcessRunner\Event\ProcessEvent;
use Tonic\ParallelProcessRunner\ParallelProcessRunner;

/**
 * Class StopOnFailure.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class StopOnFailure implements EventSubscriberInterface
{
    /** @var ParallelProcessRunner */
    protected $parallelProcessRunner;

    public function __construct(ParallelProcessRunner $processRunner)
    {
        $this->parallelProcessRunner = $processRunner;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ParallelScenarioEventType::PROCESS_AFTER_STOP => 'stopOnFailure',
        ];
    }

    public function stopOnFailure(ProcessEvent $event): void
    {
        /** @var ScenarioProcess $process */
        $process = $event->getProcess();
        if ($process->withError()) {
            $this->parallelProcessRunner->stop();
            $this->terminate(1);
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function terminate(int $code): void
    {
        exit($code);
    }
}
