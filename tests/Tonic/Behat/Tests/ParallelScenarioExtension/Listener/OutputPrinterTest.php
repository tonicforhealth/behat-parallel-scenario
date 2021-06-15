<?php

namespace Tonic\Behat\Tests\ParallelScenarioExtension\Listener;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\Listener\OutputPrinter;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\ParallelProcessRunner\Event\ProcessEvent;

/**
 * @coversDefaultClass \Tonic\Behat\ParallelScenarioExtension\Listener\OutputPrinter
 * @covers ::__construct
 */
class OutputPrinterTest extends TestCase
{
    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        self::assertEquals([
            ParallelScenarioEventType::PROCESS_BEFORE_START => 'beforeStart',
            ParallelScenarioEventType::PROCESS_AFTER_STOP => 'afterStop',
        ], OutputPrinter::getSubscribedEvents());
    }

    /**
     * @covers ::beforeStart
     */
    public function testBeforeStart(): void
    {
        $output = $this->createMock(ConsoleOutput::class);
        $output->expects(self::once())->method('writeln')->with('START ::: command');

        $process = $this->createMock(ScenarioProcess::class);
        $process->expects(self::once())->method('getCommandLine')->willReturn('command');

        $event = $this->createMock(ProcessEvent::class);
        $event->method('getProcess')->willReturn($process);

        /** @var OutputInterface $output */
        /** @var ProcessEvent $event */
        $printer = new OutputPrinter();
        $printer->init($output);

        $printer->beforeStart($event);
    }

    public function providerAfterStep(): array
    {
        return [
            [
                true,
                [
                    '<comment>output</comment>',
                    '<error>error.output</error>',
                ],
            ],
            [
                false,
                [
                    '<info>output</info>',
                ],
            ],
        ];
    }

    /**
     * @covers ::afterStop
     *
     * @dataProvider providerAfterStep
     */
    public function testAfterStop(bool $error, array $expected): void
    {
        $output = $this->createMock(ConsoleOutput::class);
        foreach ($expected as $index => $line) {
            $output->expects(self::at($index))->method('writeln')->with($line);
        }
        $output->expects(self::exactly(\count($expected)))->method('writeln');

        $process = $this->createMock(ScenarioProcess::class);
        $process->expects(self::once())->method('withError')->willReturn($error);
        $process->expects(self::once())->method('getOutput')->willReturn('output');
        if ($error) {
            $process->expects(self::once())->method('getErrorOutput')->willReturn('error.output');
        } else {
            $process->expects(self::never())->method('getErrorOutput');
        }

        $event = $this->createMock(ProcessEvent::class);
        $event->method('getProcess')->willReturn($process);

        /** @var OutputInterface $output */
        /** @var ProcessEvent $event */
        $printer = new OutputPrinter();
        $printer->init($output);

        $printer->afterStop($event);
    }
}
