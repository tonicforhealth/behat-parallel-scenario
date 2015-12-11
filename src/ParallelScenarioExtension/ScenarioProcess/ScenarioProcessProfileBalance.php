<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionScalar;
use Tonic\ParallelProcessRunner\Event\ProcessEvent;

/**
 * Class ScenarioProcessProfileBalance.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioProcessProfileBalance implements EventSubscriberInterface
{
    /**
     * @var array
     */
    protected $balance = [];

    /**
     * @param array $profiles
     */
    public function __construct(array $profiles)
    {
        $this->balance = array_fill_keys($profiles, 0);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ParallelScenarioEventType::PROCESS_BEFORE_START => 'increment',
            ParallelScenarioEventType::PROCESS_AFTER_STOP => 'decrement',
        ];
    }

    /**
     * @param ProcessEvent $event
     */
    public function increment(ProcessEvent $event)
    {
        if ($this->balance) {
            $profile = $this->getProfileNameWithMinimumBalance();
            $this->balance[$profile]++;
            /** @var ScenarioProcess $process */
            $process = $event->getProcess();
            $process->setProcessOption(new ProcessOptionScalar('profile', $profile));
        }
    }

    /**
     * @param ProcessEvent $event
     */
    public function decrement(ProcessEvent $event)
    {
        if ($this->balance) {
            /** @var ScenarioProcess $process */
            $process = $event->getProcess();
            /** @var ProcessOptionScalar $profileOption */
            $profileOption = $process->getProcessOption('profile');
            $this->balance[$profileOption->getOptionValue()]--;
        }
    }

    /**
     * @return string
     */
    protected function getProfileNameWithMinimumBalance()
    {
        return array_keys($this->balance, min($this->balance))[0];
    }
}
