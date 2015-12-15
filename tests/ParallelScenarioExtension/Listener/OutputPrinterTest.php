<?php

namespace Tonic\Behat\ParallelScenarioExtension\Listener;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\ParallelProcessRunner\Event\ProcessEvent;

/**
 * Class OutputPrinterTest.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class OutputPrinterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @see OutputPrinter::getSubscribedEvents
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([
            ParallelScenarioEventType::PROCESS_BEFORE_START => 'beforeStart',
            ParallelScenarioEventType::PROCESS_AFTER_STOP => 'afterStop',
        ], OutputPrinter::getSubscribedEvents());
    }

    /**
     * @see OutputPrinter::beforeStart
     */
    public function testBeforeStart()
    {
        $output = $this->getMock(ConsoleOutput::class, ['writeln']);
        $output->expects($this->once())->method('writeln')->with('START ::: command');

        $process = $this->getMock(ScenarioProcess::class, ['getCommandLine'], [], '', false);
        $process->expects($this->once())->method('getCommandLine')->willReturn('command');

        $event = $this->getMock(ProcessEvent::class, null, [$process]);

        /** @var OutputInterface $output */
        /** @var ProcessEvent $event */
        $printer = new OutputPrinter();
        $printer->init($output);

        $printer->beforeStart($event);
    }

    /**
     * @return array
     */
    public function providerAfterStep()
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
     * @param bool  $error
     * @param array $expected
     *
     * @see          OutputPrinter::afterStop
     *
     * @dataProvider providerAfterStep
     */
    public function testAfterStop($error, array $expected)
    {
        $output = $this->getMock(ConsoleOutput::class, ['writeln']);
        foreach ($expected as $index => $line) {
            $output->expects($this->at($index))->method('writeln')->with($line);
        }
        $output->expects($this->exactly(count($expected)))->method('writeln');

        $process = $this->getMock(ScenarioProcess::class, ['withError', 'getOutput', 'getErrorOutput'], [], '', false);
        $process->expects($this->once())->method('withError')->willReturn($error);
        $process->expects($this->once())->method('getOutput')->willReturn('output');
        if ($error) {
            $process->expects($this->once())->method('getErrorOutput')->willReturn('error.output');
        } else {
            $process->expects($this->never())->method('getErrorOutput');
        }

        $event = $this->getMock(ProcessEvent::class, null, [$process]);

        /** @var OutputInterface $output */
        /** @var ProcessEvent $event */
        $printer = new OutputPrinter();
        $printer->init($output);

        $printer->afterStop($event);
    }
}
