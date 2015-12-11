<?php

namespace Tonic\Behat\ParallelScenarioExtension\Feature;

use Behat\Gherkin\Node\FeatureNode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tonic\Behat\ParallelScenarioExtension\ParallelScenarioEventType;
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
    private $eventDispatcher;
    /**
     * @var ScenarioInfoExtractor
     */
    private $scenarioInfoExtractor;
    /**
     * @var ScenarioProcessFactory
     */
    private $scenarioProcessFactory;
    /**
     * @var ParallelProcessRunner
     */
    private $parallelProcessRunner;

    /**
     * FeatureRunner constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ScenarioInfoExtractor    $scenarioInfoExtractor
     * @param ScenarioProcessFactory   $scenarioProcessFactory
     * @param ParallelProcessRunner    $parallelProcessRunner
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ScenarioInfoExtractor $scenarioInfoExtractor, ScenarioProcessFactory $scenarioProcessFactory, ParallelProcessRunner $parallelProcessRunner)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->scenarioInfoExtractor = $scenarioInfoExtractor;
        $this->scenarioProcessFactory = $scenarioProcessFactory;
        $this->parallelProcessRunner = $parallelProcessRunner;
    }

    /**
     * @param FeatureNode $featureNode
     *
     * @return int
     */
    public function run(FeatureNode $featureNode)
    {
        $result = 0;

        $this->eventDispatcher->dispatch(ParallelScenarioEventType::FEATURE_TESTED_BEFORE);
        $scenarioGroups = $this->scenarioInfoExtractor->extract($featureNode);

        foreach ($scenarioGroups as $scenarios) {
            $result = max($result, $this->runScenarios($scenarios));
        }
        $this->eventDispatcher->dispatch(ParallelScenarioEventType::FEATURE_TESTED_BEFORE);

        return $result;
    }

    /**
     * @param $maxParallelProcess
     */
    public function setMaxParallelProcess($maxParallelProcess)
    {
        $this->parallelProcessRunner->setMaxParallelProcess($maxParallelProcess);
    }

    /**
     * @param ScenarioInfo[] $scenarios
     *
     * @return int
     */
    protected function runScenarios(array $scenarios)
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
