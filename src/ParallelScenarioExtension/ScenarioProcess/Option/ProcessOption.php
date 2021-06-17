<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option;

/**
 * Class ScenarioProcessOption.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessOption implements ProcessOptionInterface
{
    /**
     * @var string
     */
    protected $optionName;

    /**
     * ProcessOption constructor.
     *
     * @param string $optionName
     */
    public function __construct($optionName)
    {
        $this->optionName = $optionName;
    }

    /**
     * @return string
     */
    public function getOptionName()
    {
        return $this->optionName;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('--%s', $this->getOptionName());
    }
}
