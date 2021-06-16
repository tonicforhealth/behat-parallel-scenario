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
    protected $scenarioInfo;
    /**
     * @var string
     */
    protected $commandLine;
    /**
     * @var ProcessOptionCollection
     */
    protected $optionCollection;

    public function __construct(ScenarioInfo $scenarioInfo, $commandline, $cwd = null, array $env = null, $input = null, int $timeout = 0)
    {
        $this->scenarioInfo = $scenarioInfo;
        $this->commandLine = $commandline;
        $this->optionCollection = new ProcessOptionCollection();
        parent::__construct($this->getCommandLineWithOptions(), $cwd, $env, $input, $timeout);
    }

    public function setProcessOption(ProcessOptionInterface $option): void
    {
        $this->optionCollection->set($option);
    }

    /**
     * @param string $optionName
     *
     * @return ProcessOptionInterface|null
     */
    public function getProcessOption($optionName): ?ProcessOptionInterface
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
    public function withError(): bool
    {
        return (bool) $this->getExitCode();
    }

    /**
     * {@inheritdoc}
     */
    public function start(callable $callback = null, array $env = [])
    {
        $this->updateCommandLine();
        parent::start($callback);
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
    protected function getCommandLineWithOptions(): string
    {
        return implode(' ', array_filter([
            $this->commandLine,
            (string) $this->optionCollection,
        ]));
    }

    protected function updateCommandLine(): void
    {
        // TODO remove dependency
        parent::setCommandLine($this->getCommandLineWithOptions());
    }
}
