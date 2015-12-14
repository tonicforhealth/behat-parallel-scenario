<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioInfo;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioInterface;

/**
 * Class ParallelScenarioFileLineExtractor.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioInfoExtractor
{
    const TAG_PARALLEL_SCENARIO = 'parallel-scenario';
    const TAG_PARALLEL_WAIT = 'parallel-wait';
    const TAG_PARALLEL_EXAMPLES = 'parallel-examples';

    /**
     * @param FeatureNode $feature
     *
     * @return array
     */
    public function extract(FeatureNode $feature)
    {
        $allScenarios = [];

        foreach ($feature->getScenarios() as $scenario) {
            $scenarios = [];

            switch (true) {
                case $scenario instanceof OutlineNode && $this->isParallelExamples($scenario):
                    foreach ($scenario->getExamples() as $exampleNode) {
                        $scenarios[] = new ScenarioInfo($feature->getFile(), $exampleNode->getLine());
                    }
                    break;
                case $scenario instanceof OutlineNode:
                case $scenario instanceof ScenarioInterface:
                    $scenarios[] = new ScenarioInfo($feature->getFile(), $scenario->getLine());
            }

            if ($this->isParallelWait($scenario) || !$this->isParallel($scenario)) {
                $allScenarios[] = [];
            }

            $lastIndex = empty($allScenarios) ? 0 : count($allScenarios) - 1;

            if (!array_key_exists($lastIndex, $allScenarios)) {
                $allScenarios[$lastIndex] = [];
            }

            $allScenarios[$lastIndex] = array_merge($allScenarios[$lastIndex], $scenarios);

            if (!$this->isParallel($scenario)) {
                $allScenarios[] = [];
            }
        }

        return array_values(array_filter($allScenarios));
    }

    /**
     * @param ScenarioInterface $scenario
     *
     * @return bool
     */
    private function isParallel(ScenarioInterface $scenario)
    {
        return in_array(self::TAG_PARALLEL_SCENARIO, $scenario->getTags()) || $this->isParallelExamples($scenario);
    }

    /**
     * @param ScenarioInterface $scenario
     *
     * @return bool
     */
    private function isParallelWait(ScenarioInterface $scenario)
    {
        return in_array(self::TAG_PARALLEL_WAIT, $scenario->getTags());
    }

    /**
     * @param ScenarioInterface $scenario
     *
     * @return bool
     */
    private function isParallelExamples(ScenarioInterface $scenario)
    {
        return $scenario instanceof OutlineNode && in_array(self::TAG_PARALLEL_EXAMPLES, $scenario->getTags());
    }
}
