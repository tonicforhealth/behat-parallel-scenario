<?php

namespace Tonic\Behat\ParallelScenarioExtension\Cli;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class ParallelScenarioCommandLineExtractor.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ParallelScenarioCommandLineExtractor
{
    /**
     * @var InputDefinition
     */
    private $inputDefinition;
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
     * BaseCommandLineExtractor constructor.
     *
     * @param InputDefinition $inputDefinition
     */
    public function __construct(InputDefinition $inputDefinition)
    {
        $this->inputDefinition = $inputDefinition;
        $this->behatBinaryPath = reset($_SERVER['argv']);
    }

    /**
     * @param InputInterface $input
     */
    public function init(InputInterface $input)
    {
        $options = [];

        $this->outPath = [];

        foreach ($this->inputDefinition->getOptions() as $optionName => $inputOption) {
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
     * @param string $scenarioFileName
     *
     * @return string
     */
    public function getCommand($scenarioFileName)
    {
        $options = $this->overrideOutputPath($this->options, md5($scenarioFileName));
        $commandLine = sprintf('%s %s %s %s', PHP_BINARY, $this->behatBinaryPath, implode(' ', $options), $scenarioFileName);

        echo $commandLine, PHP_EOL;

        return $commandLine;
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
