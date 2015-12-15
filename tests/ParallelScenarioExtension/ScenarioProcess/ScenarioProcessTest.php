<?php


namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess;

use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionInterface;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionOut;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionScalar;

/**
 * Class ScenarioProcessTest.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioProcessTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function providerSetGetProcessOption()
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
     * @param string                      $name
     * @param ProcessOptionInterface|null $option
     *
     * @dataProvider providerSetGetProcessOption
     */
    public function testSetGetProcessOption($name, ProcessOptionInterface $option = null)
    {
        $process = new ScenarioProcess(new ScenarioInfo('file', 0), '');
        if ($option) {
            $process->setProcessOption($option);
        }

        $this->assertEquals($option, $process->getProcessOption($name));
    }

    /**
     * @param string $commandLine
     */
    public function testSetCommandLine($commandLine = 'test')
    {
        $process = new ScenarioProcess(new ScenarioInfo('file', 0), '');
        $process->setCommandLine($commandLine);

        $this->assertEquals($commandLine, $process->getCommandLine());
    }

    /**
     * @return array
     */
    public function providerWithError()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @param bool $withError
     *
     * @dataProvider providerWithError
     */
    public function testWithError($withError)
    {
        $process = $this->getMock(ScenarioProcess::class, ['getExitCode'], [], '', false);
        $process->expects($this->once())->method('getExitCode')->willReturn($withError);
        /** @var ScenarioProcess $process */
        $this->assertEquals($withError, $process->withError());
    }

    /**
     * @param string $optionName
     * @param string $optionValue
     */
    public function testCommon($optionName = 'option', $optionValue = 'test')
    {
        $process = new ScenarioProcess(new ScenarioInfo('file', 0), 'cmd');
        $process->setProcessOption(new ProcessOptionScalar($optionName, $optionValue));

        $this->assertEquals(
            sprintf('cmd --%s %s', $optionName, escapeshellarg($optionValue)),
            $process->getCommandLine()
        );

        $process->setProcessOption(new ProcessOptionScalar($optionName, $optionValue.$optionValue));
        $this->assertEquals(
            sprintf('cmd --%s %s', $optionName, escapeshellarg($optionValue.$optionValue)),
            $process->getCommandLine()
        );
    }

    /**
     * @return array
     */
    public function providerUpdateCommandLineCall()
    {
        return [
            ['run'],
            ['start'],
            ['getCommandLine'],
        ];
    }

    /**
     * @param string $method
     *
     * @dataProvider providerUpdateCommandLineCall
     */
    public function testUpdateCommandLineCall($method)
    {
        $command = sprintf('%s', PHP_BINARY);

        $process = $this->getMock(ScenarioProcess::class, ['updateCommandLine'], [new ScenarioInfo('file', 0), $command]);
        $process->expects($this->once())->method('updateCommandLine');
        /** @var ScenarioProcess $process */
        $process->$method();
    }
}
