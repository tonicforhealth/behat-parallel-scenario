<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess;

use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionScalar;
use Tonic\ParallelProcessRunner\Event\ProcessEvent;

/**
 * Class ScenarioProcessProfileBalanceTest.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioProcessProfileBalanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @see ScenarioProcessProfileBalance::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            ParallelScenarioEventType::PROCESS_BEFORE_START => 'increment',
            ParallelScenarioEventType::PROCESS_AFTER_STOP => 'decrement',
        ], ScenarioProcessProfileBalance::getSubscribedEvents());
    }

    /**
     * @see ScenarioProcessProfileBalance::increase
     * @see ScenarioProcessProfileBalance::decrease
     */
    public function test()
    {
        $profiles = [
            'profile1',
            'profile2',
        ];

        $events = $this->getEvents(9);

        $balance = new ScenarioProcessProfileBalance($profiles);
        foreach ($events as $event) {
            $balance->increment($event);
        }

        $this->assertEquals([
            'profile1' => 5,
            'profile2' => 4,
        ], $this->countProfilesFromEvents($events));

        foreach ($this->filterEventsByProfile($events, 'profile1') as $event) {
            $balance->decrement($event);
        }

        $newEvents = $this->getEvents(5);
        foreach ($newEvents as $event) {
            $balance->increment($event);
        }
        $this->assertEquals([
            'profile1' => 5,
        ], $this->countProfilesFromEvents($newEvents));
    }

    /**
     * @param ProcessEvent[] $events
     * @param string         $profile
     *
     * @return ProcessEvent[]
     */
    public function filterEventsByProfile(array $events, $profile)
    {
        return array_filter($events, function (ProcessEvent $event) use ($profile) {
            /** @var ScenarioProcess $process */
            $process = $event->getProcess();
            /** @var ProcessOptionScalar $profileOption */
            $profileOption = $process->getProcessOption('profile');

            return $profileOption->getOptionValue() == $profile;
        });
    }

    /**
     * @param int $amount
     *
     * @return ProcessEvent[]
     */
    private function getEvents($amount)
    {
        $events = [];
        while ($amount--) {
            $scenarioInfo = $this->getMock(ScenarioInfo::class, null, [], '', false);
            $process = $this->getMock(ScenarioProcess::class, null, [$scenarioInfo, (string) $scenarioInfo]);
            $event = $this->getMock(ProcessEvent::class, null, [$process]);

            $events[] = $event;
        }

        return $events;
    }

    /**
     * @param ProcessEvent[] $events
     *
     * @return array
     */
    private function countProfilesFromEvents(array $events)
    {
        $profilesCount = [];
        foreach ($events as $event) {
            /** @var ScenarioProcess $process */
            $process = $event->getProcess();
            /** @var ProcessOptionScalar $profileOption */
            $profileOption = $process->getProcessOption('profile');
            @$profilesCount[$profileOption->getOptionValue()]++;
        }

        return $profilesCount;
    }
}
