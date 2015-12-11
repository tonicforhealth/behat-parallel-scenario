<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioProcess\Option;

/**
 * Interface ProcessOptionInterface.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
interface ProcessOptionInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return string
     */
    public function getOptionName();
}
