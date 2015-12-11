<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess;

use Symfony\Component\Process\Process;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionCollection;
use Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option\ProcessOptionInterface;

/**
 * Class ScenarioProcess.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioProcess extends Process
{
    /**
     * @var ScenarioInfo
     */
    private $scenarioInfo;
    /**
     * @var string
     */
    private $commandLine;
    /**
     * @var ProcessOptionCollection
     */
    private $optionCollection;

    /**
     * ScenarioProcess constructor.
     *
     * @param ScenarioInfo $scenarioInfo
     * @param null|string  $commandline
     * @param null         $cwd
     * @param array|null   $env
     * @param null         $input
     * @param int          $timeout
     * @param array        $options
     */
    public function __construct(ScenarioInfo $scenarioInfo, $commandline, $cwd = null, array $env = null, $input = null, $timeout = 0, array $options = array())
    {
        $this->scenarioInfo = $scenarioInfo;
        $this->commandLine = $commandline;
        $this->optionCollection = new ProcessOptionCollection();
        parent::__construct($this->getCommandLineWithOptions(), $cwd, $env, $input, $timeout, $options);
    }

    /**
     * @param ProcessOptionInterface $option
     */
    public function setProcessOption(ProcessOptionInterface $option)
    {
        $this->optionCollection->set($option);
    }

    /**
     * @param string $optionName
     *
     * @return null|ProcessOptionInterface
     */
    public function getProcessOption($optionName)
    {
        return $this->optionCollection->get($optionName);
    }

    /**
     * {@inheritdoc}
     */
    public function setCommandLine($commandline)
    {
        $this->commandLine = $commandline;
    }

    /**
     * @return bool
     */
    public function withError()
    {
        return (bool) $this->getExitCode();
    }

    /**
     * {@inheritdoc}
     */
    public function start(callable $callback = null)
    {
        $this->updateCommandLine();
        parent::start($callback);
    }

    /**
     * {@inheritdoc}
     */
    public function run($callback = null)
    {
        $this->updateCommandLine();

        return parent::run($callback);
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandLine()
    {
        $this->updateCommandLine();

        return parent::getCommandLine();
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandLineWithOptions()
    {
        return sprintf('%s %s', $this->commandLine, $this->optionCollection);
    }

    protected function updateCommandLine()
    {
        parent::setCommandLine($this->getCommandLineWithOptions());
    }
}
