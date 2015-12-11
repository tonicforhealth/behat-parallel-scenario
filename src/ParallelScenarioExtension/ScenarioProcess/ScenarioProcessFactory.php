<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Process\Process;
use Tonic\Behat\ParallelScenarioExtension\Cli\ParallelScenarioController;
use Tonic\Behat\ParallelScenarioExtension\Event\ParallelScenarioEventType;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOption;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionArray;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionCollection;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionOut;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionScalar;
use Tonic\ParallelProcessRunner\Event\ProcessBeforeStartEvent;

/**
 * Class ProcessExtractor.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioProcessFactory implements EventSubscriberInterface
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
        'profile',
    ];
    /**
     * @var ProcessOptionCollection
     */
    private $optionCollection;

    /**
     * @var array
     */
    private $profiles = [];

    /**
     * @var int
     */
    private $processIndex;

    /**
     * ParallelScenarioCommandLineExtractor constructor.
     */
    public function __construct()
    {
        $this->behatBinaryPath = reset($_SERVER['argv']);
        $this->resetProcessIndex();
    }

    /**
     * @param array $profiles
     */
    public function setProfiles(array $profiles)
    {
        $this->profiles = $profiles;
    }

    public static function getSubscribedEvents()
    {
        return [
            ParallelScenarioEventType::PROCESS_BEFORE_START => 'setProfileBeforeStart',
            ParallelScenarioEventType::FEATURE_TESTED_BEFORE => 'resetProcessIndex',
        ];
    }

    /**
     * @return $this
     */
    public function resetProcessIndex()
    {
        $this->processIndex = -1;

        return $this;
    }

    /**
     * @return int
     */
    public function getProcessIndex()
    {
        return ++$this->processIndex;
    }

    /**
     * @param ProcessBeforeStartEvent $event
     */
    public function setProfileBeforeStart(ProcessBeforeStartEvent $event)
    {
        /** @var ScenarioProcess $process */
        $process = $event->getProcess();
        $process->setOption(new ProcessOptionScalar('profile', $this->getProfileByWorkerIndex($this->getProcessIndex())));
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
        if (!$this->profiles && $profile = $input->getOption('profile')) {
            $this->setProfiles([
                $profile,
            ]);
        }

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
            $process->setOption($option);
        }

        return $process;
    }

    /**
     * @param int $workerIndex
     *
     * @return string|null
     */
    private function getProfileByWorkerIndex($workerIndex)
    {
        $profile = null;
        if ($environmentsCount = count($this->profiles)) {
            $environmentIndex = $workerIndex % $environmentsCount;
            $profile = $this->profiles[$environmentIndex];
        }

        return $profile;
    }
}
