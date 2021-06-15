<?php

namespace Tonic\Behat\Tests\ParallelScenarioExtension\Feature;

use Behat\Gherkin\Node\FeatureNode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tonic\Behat\ParallelScenarioExtension\Feature\FeatureRunner;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfoExtractor;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcessFactory;
use Tonic\ParallelProcessRunner\ParallelProcessRunner;

/**
 * @coversDefaultClass \Tonic\Behat\ParallelScenarioExtension\Feature\FeatureRunner
 * @covers ::__construct
 */
class FeatureRunnerTest extends TestCase
{
    /** @var FeatureRunner */
    protected $uut;
    /** @var MockObject|ParallelProcessRunner */
    protected $parallelProcessRunner;
    /** @var MockObject|EventDispatcherInterface */
    protected $eventDispatcher;
    /** @var MockObject|ScenarioInfoExtractor */
    protected $scenarioInfoExtractor;
    /** @var MockObject|ScenarioProcessFactory */
    protected $scenarioProcessFactor;
    /** @var MockObject|ScenarioProcessFactory */
    protected $scenarioProcessFactory;

    public function providerSetMaxParallelProcess(): array
    {
        return [
            [1],
            [2],
        ];
    }

    /**
     * @dataProvider providerSetMaxParallelProcess
     */
    public function testSetMaxParallelProcess(int $maxParallelProcess): void
    {
        $this->parallelProcessRunner
            ->expects(self::once())
            ->method('setMaxParallelProcess')
            ->with($maxParallelProcess);

        $this->uut->setMaxParallelProcess($maxParallelProcess);
    }

    public function providerRun(): array
    {
        return [
            'empty' => [
                [],
            ],
            '1 scenario in 1 group' => [
                [
                    [
                        new ScenarioInfo('file', 1),
                    ],
                ],
            ],
            '2 scenario in 1 group' => [
                [
                    [
                        new ScenarioInfo('file', 1),
                        new ScenarioInfo('file', 2),
                    ],
                ],
            ],
            '2 scenario in 2 group' => [
                [
                    [
                        new ScenarioInfo('file', 1),
                    ],
                    [
                        new ScenarioInfo('file', 1),
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerRun
     */
    public function testRun(array $scenarioGroups): void
    {
        $this->scenarioInfoExtractor->expects(self::once())->method('extract')->willReturn($scenarioGroups);

        $index = 0;
        $processGroups = [];
        foreach ($scenarioGroups as $groupId => $scenarios) {
            $processGroups[$groupId] = [];
            foreach ($scenarios as $scenarioInfo) {
                $process = $this->createMock(ScenarioProcess::class);
                $processGroups[$groupId][] = $process;

                $this->scenarioProcessFactory->expects(self::at($index))->method('make')->with($scenarioInfo)->willReturn($process);
                ++$index;
            }
        }
        $this->scenarioProcessFactory->expects(self::exactly($index))->method('make');

        $this->parallelProcessRunner->expects(self::exactly(\count($processGroups)))->method('reset')->willReturn($this->parallelProcessRunner);
        foreach ($processGroups as $index => $processes) {
            // count index with methods amount correction
            $this->parallelProcessRunner->expects(self::at($index * 3 + 1))->method('add')->with($processes)->willReturn($this->parallelProcessRunner);
        }

        $this->parallelProcessRunner->expects(self::exactly(\count($processGroups)))->method('add');
        $this->parallelProcessRunner->expects(self::exactly(\count($processGroups)))->method('run')->willReturn($this->parallelProcessRunner);

        $featureNode = $this->createMock(FeatureNode::class);
        $this->uut->run($featureNode);
    }

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->scenarioInfoExtractor = $this->createMock(ScenarioInfoExtractor::class);
        $this->scenarioProcessFactory = $this->createMock(ScenarioProcessFactory::class);
        $this->parallelProcessRunner = $this->createMock(ParallelProcessRunner::class);

        $this->uut = new FeatureRunner(
            $this->eventDispatcher,
            $this->scenarioInfoExtractor,
            $this->scenarioProcessFactory,
            $this->parallelProcessRunner
        );
    }
}
