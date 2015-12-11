<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioInfo;

/**
 * Class ScenarioInfo.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioInfo
{
    /**
     * @var string
     */
    private $file;
    /**
     * @var int
     */
    private $line;

    /**
     * ScenarioInfo constructor.
     *
     * @param string $file
     * @param int    $line
     */
    public function __construct($file, $line)
    {
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s:%d', $this->file, $this->line);
    }
}
