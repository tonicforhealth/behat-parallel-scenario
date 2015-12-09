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
        parent::__construct($commandline, $cwd, $env, $input, $timeout, $options);
    }
}
