<?php

namespace Tonic\Behat\Tests\ParallelScenarioExtension\ScenarioInfo;

use PHPUnit\Framework\TestCase;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;

/**
 * @coversDefaultClass \Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo
 */
class ScenarioInfoTest extends TestCase
{
    /**
     * @covers ::__toString
     */
    public function testToString(): void
    {
        $scenarioInfo = new ScenarioInfo('file', '80');
        self::assertEquals('file:80', (string) $scenarioInfo);
    }
}
