<?php

namespace Tonic\Behat\Tests\ParallelScenarioExtension\ScenarioProcess;

use PHPUnit\Framework\TestCase;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionInterface;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionOut;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionScalar;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;

/**
 * Class ScenarioProcessTest.
 * @coversDefaultClass \Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioProcessTest extends TestCase
{
    public function providerSetGetProcessOption(): array
    {
        return [
            [
                'any',
            ],

            [
                'out',
                new ProcessOptionOut('out', ['std']),
            ],
        ];
    }

    /**
     * @dataProvider providerSetGetProcessOption
     * @covers ::getProcessOption
     */
    public function testSetGetProcessOption(string $name, ProcessOptionInterface $option = null): void
    {
        $process = new ScenarioProcess(new ScenarioInfo('file', 0), '');
        if ($option) {
            $process->setProcessOption($option);
        }

        self::assertEquals($option, $process->getProcessOption($name));
    }

    /**
     * @covers ::getCommandLine
     * @covers ::setCommandLine
     */
    public function testSetCommandLine(string $commandLine = 'test'): void
    {
        $process = new ScenarioProcess(new ScenarioInfo('file', 0), '');
        $process->setCommandLine($commandLine);

        self::assertEquals($commandLine, $process->getCommandLine());
    }

    public function providerWithError(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider providerWithError
     * @covers ::withError
     */
    public function testWithError(bool $withError): void
    {
        $process = $this
            ->getMockBuilder(ScenarioProcess::class)
            ->setConstructorArgs([$this->createMock(ScenarioInfo::class), ''])
            ->onlyMethods(['getExitCode'])
            ->getMock();

        $process->method('getExitCode')->willReturn($withError);

        self::assertEquals($withError, $process->withError());
    }

    /**
     * @covers ::setProcessOption
     * @covers ::getCommandLine
     */
    public function testCommon(string $optionName = 'option', string $optionValue = 'test'): void
    {
        $process = new ScenarioProcess(new ScenarioInfo('file', 0), 'cmd');
        $process->setProcessOption(new ProcessOptionScalar($optionName, $optionValue));

        self::assertEquals(
            sprintf('cmd --%s %s', $optionName, escapeshellarg($optionValue)),
            $process->getCommandLine()
        );

        $process->setProcessOption(new ProcessOptionScalar($optionName, $optionValue.$optionValue));
        self::assertEquals(
            sprintf('cmd --%s %s', $optionName, escapeshellarg($optionValue.$optionValue)),
            $process->getCommandLine()
        );
    }

    public function providerUpdateCommandLineCall(): array
    {
        return [
            ['run'],
            ['start'],
            ['getCommandLine'],
        ];
    }

    /**
     * @dataProvider providerUpdateCommandLineCall
     * @covers ::<public>
     */
    public function testUpdateCommandLineCall(string $method): void
    {
        $process = $this->createMock(ScenarioProcess::class);
        $process->expects(self::once())->method('updateCommandLine');
        /* @var ScenarioProcess $process */
        $process->$method();
    }
}
