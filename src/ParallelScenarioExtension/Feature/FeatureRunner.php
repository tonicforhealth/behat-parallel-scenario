<?php

namespace Tonic\Behat\ParallelScenarioExtension\Feature;

use Behat\Gherkin\Node\FeatureNode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
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
     * @param ScenarioInfoExtractor    $infoExtractor
     * @param ScenarioProcessFactory   $processFactory
     * @param ParallelProcessRunner    $processRunner
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ScenarioInfoExtractor $infoExtractor, ScenarioProcessFactory $processFactory, ParallelProcessRunner $processRunner)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->scenarioInfoExtractor = $infoExtractor;
        $this->scenarioProcessFactory = $processFactory;
        $this->parallelProcessRunner = $processRunner;
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
     * @param int $maxParallelProcess
     *
     * @return $this
     */
    public function setMaxParallelProcess($maxParallelProcess)
    {
        $this->parallelProcessRunner->setMaxParallelProcess($maxParallelProcess);

        return $this;
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
