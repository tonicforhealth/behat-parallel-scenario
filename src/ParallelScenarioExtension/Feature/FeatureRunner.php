<?php

namespace Tonic\Behat\ParallelScenarioExtension\Feature;

use Behat\Gherkin\Node\FeatureNode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tonic\Behat\ParallelScenarioExtension\Event\AfterFeatureTestEvent;
use Tonic\Behat\ParallelScenarioExtension\Event\BeforeFeatureTestEvent;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfoExtractor;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcess;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\ScenarioProcessFactory;
use Tonic\ParallelProcessRunner\ParallelProcessRunner;

/**
 * Class FeatureRunner.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class FeatureRunner
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;
    /**
     * @var ScenarioInfoExtractor
     */
    protected $scenarioInfoExtractor;
    /**
     * @var ScenarioProcessFactory
     */
    protected $scenarioProcessFactory;
    /**
     * @var ParallelProcessRunner
     */
    protected $parallelProcessRunner;

    /**
     * FeatureRunner constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ScenarioInfoExtractor $infoExtractor, ScenarioProcessFactory $processFactory, ParallelProcessRunner $processRunner)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->scenarioInfoExtractor = $infoExtractor;
        $this->scenarioProcessFactory = $processFactory;
        $this->parallelProcessRunner = $processRunner;
    }

    public function run(FeatureNode $featureNode): int
    {
        $result = 0;

        $this->eventDispatcher->dispatch(new BeforeFeatureTestEvent());
        $scenarioGroups = $this->scenarioInfoExtractor->extract($featureNode);

        foreach ($scenarioGroups as $scenarios) {
            $result = max($result, $this->runScenarios($scenarios));
        }
        $this->eventDispatcher->dispatch(new AfterFeatureTestEvent());

        return $result;
    }

    public function setMaxParallelProcess(int $maxParallelProcess): self
    {
        $this->parallelProcessRunner->setMaxParallelProcess($maxParallelProcess);

        return $this;
    }

    /**
     * @param ScenarioInfo[] $scenarios
     */
    protected function runScenarios(array $scenarios): int
    {
        $result = 0;

        /** @var ScenarioProcess[] $processes */
        $processes = array_map(function (ScenarioInfo $scenarioInfo) {
            return $this->scenarioProcessFactory->make($scenarioInfo);
        }, $scenarios);

        $this->parallelProcessRunner->reset()->add($processes)->run();

        foreach ($processes as $process) {
            $result = max($result, $process->getExitCode());
        }

        return $result;
    }
}
