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
    public const TAG_PARALLEL_SCENARIO = 'parallel-scenario';
    public const TAG_PARALLEL_WAIT = 'parallel-wait';
    public const TAG_PARALLEL_EXAMPLES = 'parallel-examples';

    public function extract(FeatureNode $feature): array
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

            $lastIndex = empty($allScenarios) ? 0 : \count($allScenarios) - 1;

            if (!\array_key_exists($lastIndex, $allScenarios)) {
                $allScenarios[$lastIndex] = [];
            }

            $allScenarios[$lastIndex] = array_merge($allScenarios[$lastIndex], $scenarios);

            if (!$this->isParallel($scenario)) {
                $allScenarios[] = [];
            }
        }

        return array_values(array_filter($allScenarios));
    }

    private function isParallel(ScenarioInterface $scenario): bool
    {
        return \in_array(self::TAG_PARALLEL_SCENARIO, $scenario->getTags()) || $this->isParallelExamples($scenario);
    }

    private function isParallelWait(ScenarioInterface $scenario): bool
    {
        return \in_array(self::TAG_PARALLEL_WAIT, $scenario->getTags());
    }

    private function isParallelExamples(ScenarioInterface $scenario): bool
    {
        return $scenario instanceof OutlineNode && \in_array(self::TAG_PARALLEL_EXAMPLES, $scenario->getTags());
    }
}
