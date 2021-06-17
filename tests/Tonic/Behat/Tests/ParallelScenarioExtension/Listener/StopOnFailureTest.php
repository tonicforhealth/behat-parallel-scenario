<?php

namespace Tonic\Behat\Tests\ParallelScenarioExtension\Listener;

use PHPUnit\Framework\TestCase;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\Listener\StopOnFailure;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\ParallelProcessRunner\Event\ProcessEvent;
use Tonic\ParallelProcessRunner\ParallelProcessRunner;

/**
 * @coversDefaultClass \Tonic\Behat\ParallelScenarioExtension\Listener\StopOnFailure
 * @covers ::__construct
 */
class StopOnFailureTest extends TestCase
{
    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals([
            ParallelScenarioEventType::PROCESS_AFTER_STOP => 'stopOnFailure',
        ], StopOnFailure::getSubscribedEvents());
    }

    /**
     * @covers ::stopOnFailure
     */
    public function testStopOnFailureWithError(): void
    {
        $parallelProcessRunner = $this->createMock(ParallelProcessRunner::class);
        $parallelProcessRunner->expects(self::once())->method('stop');

        $process = $this->createMock(ScenarioProcess::class);
        $process->expects(self::once())->method('withError')->willReturn(true);

        $event = $this->createMock(ProcessEvent::class);
        $event->method('getProcess')->willReturn($process);

        $uut = $this->getMockBuilder(StopOnFailure::class)
            ->setConstructorArgs([$parallelProcessRunner])
            ->onlyMethods(['terminate'])
            ->getMock();
        $uut->expects(self::once())->method('terminate')->with(1);

        $uut->stopOnFailure($event);
    }

    /**
     * @covers ::stopOnFailure
     */
    public function testStopOnFailureWithoutError(): void
    {
        $parallelProcessRunner = $this->createMock(ParallelProcessRunner::class);
        $parallelProcessRunner->expects(self::never())->method('stop');

        $process = $this->createMock(ScenarioProcess::class);
        $process->expects(self::once())->method('withError')->willReturn(false);

        $event = $this->createMock(ProcessEvent::class);
        $event->method('getProcess')->willReturn($process);

        $uut = $this->getMockBuilder(StopOnFailure::class)
            ->setConstructorArgs([$parallelProcessRunner])
            ->onlyMethods(['terminate'])
            ->getMock();
        $uut->expects(self::never())->method('terminate');

        $uut->stopOnFailure($event);
    }
}
