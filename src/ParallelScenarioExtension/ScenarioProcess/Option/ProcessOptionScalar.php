<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option;

/**
 * Class ProcessOptionScalar.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessOptionScalar extends ProcessOption
{
    /**
     * @var string|int
     */
    private $optionValue;

    /**
     * ProcessOptionString constructor.
     *
     * @param string $optionName
     * @param mixed  $optionValue
     */
    public function __construct($optionName, $optionValue)
    {
        $this->optionValue = $optionValue;
        parent::__construct($optionName);
    }

    /**
     * @return int|string
     */
    public function getOptionValue()
    {
        return $this->optionValue;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s %s', parent::__toString(), escapeshellarg($this->getOptionValue()));
    }
}
