<?php

namespace Tonic\Behat\Tests\ParallelScenarioExtension\ScenarioProcess;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionScalar;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcessProfileBalance;
use Tonic\ParallelProcessRunner\Event\ProcessEvent;

/**
 * @coversDefaultClass ScenarioProcessProfileBalance
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioProcessProfileBalanceTest extends TestCase
{
    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals([
            ParallelScenarioEventType::PROCESS_BEFORE_START => 'increment',
            ParallelScenarioEventType::PROCESS_AFTER_STOP => 'decrement',
        ], ScenarioProcessProfileBalance::getSubscribedEvents());
    }

    /**
     * @covers ::increment
     * @covers ::decrement
     */
    public function test(): void
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

        self::assertEquals([
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
        self::assertEquals([
            'profile1' => 5,
        ], $this->countProfilesFromEvents($newEvents));
    }

    /**
     * @param ProcessEvent[] $events
     *
     * @return ProcessEvent[]
     */
    public function filterEventsByProfile(array $events, string $profile): array
    {
        return array_filter($events, static function (ProcessEvent $event) use ($profile) {
            /** @var ScenarioProcess $process */
            $process = $event->getProcess();
            /** @var ProcessOptionScalar $profileOption */
            $profileOption = $process->getProcessOption('profile');

            return $profileOption->getOptionValue() === $profile;
        });
    }

    /**
     * @return ProcessEvent[]|MockObject[]
     */
    private function getEvents(int $amount): array
    {
        $events = [];
        while ($amount--) {
            $process = $this->createMock(ScenarioProcess::class);
            $event = $this->createMock(ProcessEvent::class);

            $event->method('getProcess')->willReturn($process);

            $events[] = $event;
        }

        return $events;
    }

    /**
     * @param ProcessEvent[] $events
     */
    private function countProfilesFromEvents(array $events): array
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
