<?php

namespace Tonic\Behat\Tests\ParallelScenarioExtension\Cli;

use Behat\Gherkin\Node\FeatureNode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Tonic\Behat\ParallelScenarioExtension\Cli\ParallelScenarioController;
use Tonic\Behat\ParallelScenarioExtension\Feature\FeatureExtractor;
use Tonic\Behat\ParallelScenarioExtension\Feature\FeatureRunner;
use Tonic\Behat\ParallelScenarioExtension\Listener\OutputPrinter;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcessFactory;

/**
 * @coversDefaultClass \Tonic\Behat\ParallelScenarioExtension\Cli\ParallelScenarioController
 * @covers ::__construct
 */
class ParallelScenarioControllerTest extends TestCase
{
    /** @var MockObject|FeatureRunner */
    protected $featureRunner;
    /** @var MockObject|FeatureExtractor */
    protected $featureExtractor;
    /** @var MockObject|ScenarioProcessFactory */
    protected $scenarioProcessFactory;
    /** @var MockObject|OutputPrinter */
    protected $outputPrinter;
    /** @var MockObject|InputDefinition */
    protected $inputDefinition;
    /** @var MockObject|ConsoleOutput */
    protected $consoleOutput;
    /** @var ParallelScenarioController */
    protected $uut;
    protected $input;

    /**
     * @covers ::configure
     */
    public function testConfigure(): void
    {
        $command = $this->createMock(Command::class);
        $command->expects(self::once())->method('addOption')
            ->with('parallel-process', null, InputOption::VALUE_OPTIONAL, 'Max parallel processes amount', 1);

        $this->uut->configure($command);
    }

    public function providerExecuteMultiProcess(): array
    {
        return [
            [
                5,
                'locator',
                [
                    $this->createMock(FeatureNode::class),
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
                    $this->createMock(FeatureNode::class),
                    $this->createMock(FeatureNode::class),
                ],
                1,
            ],
        ];
    }

    /**
     * @covers ::execute
     *
     * @dataProvider providerExecuteMultiProcess
     */
    public function testExecuteMultiProcess(int $parallelProcess, string $paths, array $featureNodes, int $expectedResult): void
    {
        $input = $this->createMock(ArgvInput::class);
        $input
            ->expects(self::once())
            ->method('getOption')
            ->with(ParallelScenarioController::OPTION_PARALLEL_PROCESS)
            ->willReturn($parallelProcess);

        $input
            ->expects(self::once())
            ->method('getArgument')
            ->with('paths')
            ->willReturn($paths);

        $this->featureRunner->expects(self::once())->method('setMaxParallelProcess')
            ->with($parallelProcess);

        foreach ($featureNodes as $index => $featureNode) {
            $this->featureRunner
                ->expects(self::at($index + 1))
                ->method('run')
                ->with($featureNode)
                ->willReturn($expectedResult);
        }

        $this->featureRunner
            ->expects(self::exactly(\count($featureNodes)))
            ->method('run');

        $this->featureExtractor->expects(self::once())->method('extract')
            ->with($paths)->willReturn($featureNodes);

        $this->outputPrinter
            ->expects(self::once())
            ->method('init')
            ->with($this->consoleOutput);

        $command = $this->createMock(Command::class);
        $command->expects(self::once())->method('getDefinition')
            ->willReturn($this->inputDefinition);

        $this->uut->configure($command);
        $result = $this->uut->execute($input, $this->consoleOutput);

        self::assertEquals($result, $expectedResult);
    }

    public function providerExecuteSingleProcess(): array
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
     * @covers          \ParallelScenarioController::execute
     * @dataProvider providerExecuteSingleProcess
     */
    public function testExecuteSingleProcess($parallelProcess): void
    {
        $this->input
            ->expects(self::once())
            ->method('getOption')
            ->with(ParallelScenarioController::OPTION_PARALLEL_PROCESS)
            ->willReturn($parallelProcess);
        $this->input
            ->expects(self::once())
            ->method('getArgument')
            ->with('paths');

        $this->featureRunner->expects(self::never())->method('setMaxParallelProcess');
        $this->featureRunner->expects(self::never())->method('run');
        $this->featureExtractor->expects(self::never())->method('extract');
        $this->outputPrinter->expects(self::never())->method('init');

        $command = $this->createMock(Command::class);
        $command->expects(self::once())->method('getDefinition')
            ->willReturn($this->inputDefinition);

        $this->uut->configure($command);
        $result = $this->uut->execute($this->input, $this->consoleOutput);

        self::assertNull($result);
    }

    protected function setUp(): void
    {
        $this->featureRunner = $this->createMock(FeatureRunner::class);
        $this->featureExtractor = $this->createMock(FeatureExtractor::class);
        $this->scenarioProcessFactory = $this->createMock(ScenarioProcessFactory::class);
        $this->inputDefinition = $this->createMock(InputDefinition::class);
        $this->outputPrinter = $this->createMock(OutputPrinter::class);
        $this->consoleOutput = $this->createMock(ConsoleOutput::class);

        $this->uut = new ParallelScenarioController(
            $this->featureRunner,
            $this->featureExtractor,
            $this->scenarioProcessFactory,
            $this->outputPrinter,
        );
        $this->input = $this->createMock(ArgvInput::class);
    }
}
