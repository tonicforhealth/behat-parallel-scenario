<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option;

/**
 * Class ProcessOptionCollection.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ProcessOptionCollection
{
    /**
     * @var ProcessOptionInterface[]
     */
    protected $options = [];

    public function set(ProcessOptionInterface $option)
    {
        $this->options[$option->getOptionName()] = $option;
    }

    /**
     * @param string $optionName
     *
     * @return ProcessOptionInterface|null
     */
    public function get($optionName)
    {
        return array_key_exists($optionName, $this->options) ? $this->options[$optionName] : null;
    }

    /**
     * @return ProcessOptionInterface[]
     */
    public function toArray()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(' ', array_map(function (ProcessOptionInterface $option) {
            return (string) $option;
        }, $this->options));
    }
}
