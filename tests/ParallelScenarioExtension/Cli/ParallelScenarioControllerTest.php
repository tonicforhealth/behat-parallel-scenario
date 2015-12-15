<?php

namespace Tonic\Behat\ParallelScenarioExtension\Cli;

use Behat\Gherkin\Node\FeatureNode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tonic\Behat\ParallelScenarioExtension\Feature\FeatureExtractor;
use Tonic\Behat\ParallelScenarioExtension\Feature\FeatureRunner;
use Tonic\Behat\ParallelScenarioExtension\Listener\OutputPrinter;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcessFactory;

/**
 * Class ParallelScenarioControllerTest.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ParallelScenarioControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @see ParallelScenarioController::configure
     */
    public function testConfigure()
    {
        $controller = $this->getMock(ParallelScenarioController::class, null, [], '', false);

        $command = $this->getMock(Command::class, ['addOption'], [], '', false);
        $command->expects($this->once())->method('addOption')
            ->with('parallel-process', null, InputOption::VALUE_OPTIONAL, 'Max parallel processes amount', 1);

        /** @var ParallelScenarioController $controller */
        /** @var Command $command */
        $controller->configure($command);
    }

    /**
     * @return array
     */
    public function providerExecuteMultiProcess()
    {
        return [
            [
                5,
                'locator',
                [
                    $this->getMock(FeatureNode::class, [], [], '', false),
                ],
                0,
            ],

            [
                2,
                'locator',
                [],
                0,
            ],

            [
                3,
                'locator',
                [
                    $this->getMock(FeatureNode::class, [], [], '', false),
                    $this->getMock(FeatureNode::class, [], [], '', false),
                ],
                1,
            ],
        ];
    }

    /**
     * @param int    $parallelProcess
     * @param string $paths
     * @param array  $featureNodes
     * @param int    $expectedResult
     *
     * @see          ParallelScenarioController::execute
     *
     * @dataProvider providerExecuteMultiProcess
     */
    public function testExecuteMultiProcess($parallelProcess, $paths, array $featureNodes, $expectedResult)
    {
        $inputDefinition = $this->getMock(InputDefinition::class);

        $input = $this->getMock(ArgvInput::class, ['getArgument', 'getOption']);
        $input->expects($this->once())->method('getOption')
            ->with(ParallelScenarioController::OPTION_PARALLEL_PROCESS)->willReturn($parallelProcess);
        $input->expects($this->once())->method('getArgument')
            ->with('paths')->willReturn($paths);

        $output = $this->getMock(ConsoleOutput::class);

        $featureRunner = $this->getMock(FeatureRunner::class, ['run', 'setMaxParallelProcess'], [], '', false);
        $featureRunner->expects($this->once())->method('setMaxParallelProcess')
            ->with($parallelProcess);
        foreach ($featureNodes as $index => $featureNode) {
            $featureRunner->expects($this->at($index + 1))->method('run')->with($featureNode)->willReturn($expectedResult);
        }
        $featureRunner->expects($this->exactly(count($featureNodes)))->method('run');

        $featureExtractor = $this->getMock(FeatureExtractor::class, ['extract'], [], '', false);
        $featureExtractor->expects($this->once())->method('extract')
            ->with($paths)->willReturn($featureNodes);

        $scenarioProcessFactory = $this->getMock(ScenarioProcessFactory::class, ['init'], [], '', false);

        $outputPrinter = $this->getMock(OutputPrinter::class, ['init'], [], '', false);
        $outputPrinter->expects($this->once())->method('init')
            ->with($output);

        $command = $this->getMock(Command::class, ['getDefinition', 'addOption'], [], '', false);
        $command->expects($this->once())->method('getDefinition')
            ->willReturn($inputDefinition);

        $controller = $this->getMock(ParallelScenarioController::class, null, [
            $featureRunner,
            $featureExtractor,
            $scenarioProcessFactory,
            $outputPrinter,
        ]);

        /** @var ParallelScenarioController $controller */
        /** @var Command $command */
        /** @var InputInterface $input */
        /** @var OutputInterface $output */
        $controller->configure($command);
        $result = $controller->execute($input, $output);

        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function providerExecuteSingleProcess()
    {
        return [
            [0],
            [-1],
            [1],
            [null],
        ];
    }

    /**
     * @param mixed $parallelProcess
     *
     * @see          ParallelScenarioController::execute
     * @dataProvider providerExecuteSingleProcess
     */
    public function testExecuteSingleProcess($parallelProcess)
    {
        $inputDefinition = $this->getMock(InputDefinition::class);

        $input = $this->getMock(ArgvInput::class, ['getArgument', 'getOption']);
        $input->expects($this->once())->method('getOption')
            ->with(ParallelScenarioController::OPTION_PARALLEL_PROCESS)->willReturn($parallelProcess);
        $input->expects($this->once())->method('getArgument')
            ->with('paths');

        $output = $this->getMock(ConsoleOutput::class);

        $featureRunner = $this->getMock(FeatureRunner::class, ['run', 'setMaxParallelProcess'], [], '', false);
        $featureRunner->expects($this->never())->method('setMaxParallelProcess');
        $featureRunner->expects($this->never())->method('run');

        $featureExtractor = $this->getMock(FeatureExtractor::class, ['extract'], [], '', false);
        $featureExtractor->expects($this->never())->method('extract');

        $scenarioProcessFactory = $this->getMock(ScenarioProcessFactory::class, ['init'], [], '', false);

        $outputPrinter = $this->getMock(OutputPrinter::class, ['init'], [], '', false);
        $outputPrinter->expects($this->never())->method('init');

        $command = $this->getMock(Command::class, ['getDefinition', 'addOption'], [], '', false);
        $command->expects($this->once())->method('getDefinition')
            ->willReturn($inputDefinition);

        $controller = $this->getMock(ParallelScenarioController::class, null, [
            $featureRunner,
            $featureExtractor,
            $scenarioProcessFactory,
            $outputPrinter,
        ]);

        /** @var ParallelScenarioController $controller */
        /** @var Command $command */
        /** @var InputInterface $input */
        /** @var OutputInterface $output */
        $controller->configure($command);
        $result = $controller->execute($input, $output);

        $this->assertNull($result);
    }
}
