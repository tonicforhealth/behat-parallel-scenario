<?php

namespace Tonic\Behat\ParallelScenarioExtension;

use Symfony\Component\Process\Process;

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
    private $profile;
    /**
     * @var string
     */
    private $commandLine;

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
        parent::__construct($this->getCommandLineWithProfile(), $cwd, $env, $input, $timeout, $options);
    }

    /**
     * @param int $workerId
     */
    public function setProfile($workerId)
    {
        $this->profile = $workerId;
        $this->setCommandLine($this->commandLine);
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandLineWithProfile()
    {
        return is_null($this->profile) ? $this->commandLine : sprintf('%s --profile=%s', $this->commandLine, $this->profile);
    }

    /**
     * {@inheritdoc}
     */
    public function setCommandLine($commandline)
    {
        $this->commandLine = $commandline;

        return parent::setCommandLine($this->getCommandLineWithProfile());
    }
}
