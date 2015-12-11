<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option;

/**
 * Class ProcessOptionOut.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessOptionOut extends ProcessOptionArray
{
    /**
     * @var string
     */
    private $outSuffix;

    /**
     * @param string $outSuffix
     */
    public function setOutSuffix($outSuffix)
    {
        $this->outSuffix = $outSuffix;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $options = array_map(function ($optionValue) {
            if (!$this->isStandardOutput($optionValue)) {
                $optionValue = sprintf('%s/%s', $optionValue, $this->outSuffix);
            }

            return sprintf('--%s %s', $this->getOptionName(), escapeshellarg($optionValue));
        }, $this->optionValues);

        return implode(' ', $options);
    }

    /**
     * Checks if provided output identifier represents standard output.
     *
     * @param string $outputId
     *
     * @see \Behat\Testwork\Output\Cli\OutputController::isStandardOutput
     *
     * @return Boolean
     */
    private function isStandardOutput($outputId)
    {
        return in_array($outputId, [
            'std',
            'null',
            'false',
        ]);
    }
}
