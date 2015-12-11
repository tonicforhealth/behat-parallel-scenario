<?php

namespace Tonic\Behat\ParallelScenarioExtension\Listener;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\ParallelProcessRunner\Event\ProcessEvent;

/**
 * Class OutputPrinter.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class OutputPrinter implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param OutputInterface $output
     */
    public function init($output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ParallelScenarioEventType::PROCESS_BEFORE_START => 'beforeStart',
            ParallelScenarioEventType::PROCESS_AFTER_STOP => 'afterStop',
        ];
    }

    /**
     * @param ProcessEvent $event
     */
    public function beforeStart(ProcessEvent $event)
    {
        $this->output->writeln(sprintf('START ::: %s', $event->getProcess()->getCommandLine()));
    }

    /**
     * @param ProcessEvent $event
     */
    public function afterStop(ProcessEvent $event)
    {
        /** @var ScenarioProcess $process */
        $process = $event->getProcess();
        if ($process->withError()) {
            $this->output->writeln(sprintf('<comment>%s</comment>', $process->getOutput()));
            $this->output->writeln(sprintf('<error>%s</error>', $process->getErrorOutput()));
        } else {
            $this->output->writeln(sprintf('<info>%s</info>', $process->getOutput()));
        }
    }
}
