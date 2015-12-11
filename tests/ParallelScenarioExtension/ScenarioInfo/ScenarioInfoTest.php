<?php


namespace Tonic\Behat\ParallelScenarioExtension\ScenarioInfo;

/**
 * Class ScenarioInfoTest.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @see ScenarioInfo::__toString
     */
    public function testToString()
    {
        $scenarioInfo = new ScenarioInfo('file', '80');
        $this->assertEquals('file:80', (string) $scenarioInfo);
    }
}
