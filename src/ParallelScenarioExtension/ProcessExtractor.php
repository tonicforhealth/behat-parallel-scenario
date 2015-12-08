<?php

namespace Tonic\Behat\ParallelScenarioExtension;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Process\Process;
use Tonic\Behat\ParallelScenarioExtension\Cli\ParallelScenarioController;

/**
 * Class ProcessExtractor.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessExtractor
{
    /**
     * @var string
     */
    private $behatBinaryPath;
    /**
     * @var array
     */
    private $skipOptions = [
        ParallelScenarioController::OPTION_PARALLEL_PROCESS,
    ];
    /**
     * @var array
     */
    private $options = [];

    /**
     * @var array
     */
    private $outPath = [];

    /**
     * ParallelScenarioCommandLineExtractor constructor.
     */
    public function __construct()
    {
        $this->behatBinaryPath = reset($_SERVER['argv']);
    }

    /**
     * @param array $options
     */
    public function addSkipOptions(array $options)
    {
        $this->skipOptions = array_unique(array_merge($this->skipOptions, $options));
    }

    /**
     * @param InputDefinition $inputDefinition
     * @param InputInterface  $input
     */
    public function init(InputDefinition $inputDefinition, InputInterface $input)
    {
        $options = [];

        $this->outPath = [];

        foreach ($inputDefinition->getOptions() as $optionName => $inputOption) {
            if (!in_array($optionName, $this->skipOptions)) {
                $optionValue = $input->getOption($optionName);
                if ($inputOption->getDefault() != $optionValue) {
                    switch (true) {
                        case $inputOption->isArray():
                            foreach ($optionValue as $value) {
                                $options[] = $this->getOption($optionName, $value);

                                if ($optionName == 'out' && !$this->isStandardOutput($value)) {
                                    $this->outPath[count($options) - 1] = $value;
                                }
                            }
                            break;
                        case $inputOption->isValueRequired():
                        case $inputOption->isValueOptional():
                            $options[] = $this->getOption($optionName, $optionValue);
                            break;
                        default:
                            $options[] = $this->getOption($optionName);
                    }
                }
            }
        }

        $this->options = $options;
    }

    /**
     * @param ScenarioInfo $scenarioInfo
     *
     * @return Process
     */
    public function extract(ScenarioInfo $scenarioInfo)
    {
        $options = $this->overrideOutputPath($this->options, md5((string) $scenarioInfo));
        $commandLine = sprintf('%s %s %s %s', PHP_BINARY, $this->behatBinaryPath, implode(' ', $options), $scenarioInfo);

        echo $commandLine, PHP_EOL;

        return new Process($commandLine);
    }

    /**
     * @param array $options
     * @param       $folder
     *
     * @return array
     */
    private function overrideOutputPath(array $options, $folder)
    {
        foreach ($this->outPath as $optionIndex => $optionValue) {
            $options[$optionIndex] = $this->getOption('out', sprintf('%s/%s', $optionValue, $folder));
        }

        return $options;
    }

    /**
     * @param string      $optionName
     * @param string|null $optionValue
     *
     * @return string
     */
    private function getOption($optionName, $optionValue = null)
    {
        switch (count(func_get_args())) {
            case 2:
                $option = sprintf('--%s %s', $optionName, escapeshellarg($optionValue));
                break;
            default:
                $option = sprintf('--%s', $optionName);
        }

        return $option;
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
