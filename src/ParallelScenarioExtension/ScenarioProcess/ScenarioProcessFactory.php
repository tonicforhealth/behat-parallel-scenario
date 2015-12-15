<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Process\Process;
use Tonic\Behat\ParallelScenarioExtension\Cli\ParallelScenarioController;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOption;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionArray;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionCollection;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionOut;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionScalar;

/**
 * Class ProcessExtractor.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioProcessFactory
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
     * @var ProcessOptionCollection
     */
    private $optionCollection;

    /**
     * ScenarioProcessFactory constructor.
     *
     * @param string|null $behatBinaryPath
     */
    public function __construct($behatBinaryPath = null)
    {
        $this->behatBinaryPath = is_null($behatBinaryPath) ? reset($_SERVER['argv']) : $behatBinaryPath;
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
        $options = new ProcessOptionCollection();

        foreach ($inputDefinition->getOptions() as $optionName => $inputOption) {
            $optionValue = $input->getOption($optionName);
            if ($inputOption->getDefault() != $optionValue) {
                switch (true) {
                    case in_array($optionName, $this->skipOptions):
                        $option = null;
                        break;
                    case $inputOption->isArray() && $optionName == 'out':
                        $option = new ProcessOptionOut($optionName, $optionValue);
                        break;
                    case $inputOption->isArray():
                        $option = new ProcessOptionArray($optionName, $optionValue);
                        break;
                    case $inputOption->isValueRequired():
                    case $inputOption->isValueOptional():
                        $option = new ProcessOptionScalar($optionName, $optionValue);
                        break;
                    default:
                        $option = new ProcessOption($optionName);
                }

                if ($option) {
                    $options->set($option);
                }
            }
        }

        $this->optionCollection = $options;
    }

    /**
     * @param ScenarioInfo $scenarioInfo
     *
     * @return Process
     */
    public function make(ScenarioInfo $scenarioInfo)
    {
        $fileLine = (string) $scenarioInfo;

        $options = $this->optionCollection->toArray();

        $outOption = $this->optionCollection->get('out');
        if ($outOption instanceof ProcessOptionOut) {
            $outOption->setOutSuffix(md5($fileLine));
            $options['out'] = clone $outOption;
        }

        $commandLine = sprintf('%s %s %s', PHP_BINARY, $this->behatBinaryPath, escapeshellarg($fileLine));
        $process = new ScenarioProcess($scenarioInfo, $commandLine);

        foreach ($options as $option) {
            $process->setProcessOption($option);
        }

        return $process;
    }
}
