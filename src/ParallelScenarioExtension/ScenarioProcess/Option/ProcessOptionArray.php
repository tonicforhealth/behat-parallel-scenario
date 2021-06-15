<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option;

/**
 * Class ProcessOptionArray.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessOptionArray extends ProcessOption
{
    /**
     * @var array
     */
    protected $optionValues = [];

    /**
     * ProcessOptionArray constructor.
     *
     * @param string $optionName
     */
    public function __construct($optionName, array $optionValues)
    {
        $this->optionValues = $optionValues;
        parent::__construct($optionName);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $options = array_map(function ($optionValue) {
            return sprintf('%s %s', parent::__toString(), escapeshellarg($optionValue));
        }, $this->optionValues);

        return implode(' ', $options);
    }
}
