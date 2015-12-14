<?php

namespace Tonic\Behat\ParallelScenarioExtension\ScenarioInfo;

use Behat\Gherkin\Keywords\ArrayKeywords;
use Behat\Gherkin\Lexer;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Parser;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class ScenarioInfoExtractorTest.
 *
 * @author kandelyabre <kandelyabre@gmail.com>
 */
class ScenarioInfoExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function provider()
    {
        $cases = [];
        $parser = $this->getParser();
        $finder = new Finder();
        foreach ($finder->in(sprintf('%s/Fixtures/ScenarioInfoExtractor', __DIR__))->directories() as $directory) {
            /** @var SplFileInfo $directory */
            $featureFile = sprintf('%s/test.feature', $directory->getPathname());
            $expectedFile = sprintf('%s/expected.json', $directory->getPathname());

            $cases[$directory->getBasename()] = [
                $parser->parse(file_get_contents($featureFile), $featureFile),
                $this->getScenarioInfo(json_decode(file_get_contents($expectedFile), true), $directory->getPathname()),
            ];
        }

        return $cases;
    }

    /**
     * @param FeatureNode $feature
     * @param array       $expected
     *
     * @dataProvider provider
     */
    public function test(FeatureNode $feature, array $expected)
    {
        $extractor = new ScenarioInfoExtractor();
        $result = $extractor->extract($feature);

        $this->assertEquals($expected, $result);
    }

    private function getScenarioInfo(array $data, $path)
    {
        foreach ($data as &$group) {
            $group = array_map(function ($data) use ($path) {
                return new ScenarioInfo(sprintf('%s/%s', $path, $data['file']), $data['line']);
            }, $group);
        }

        return $data;
    }

    /**
     * @return Parser
     */
    private function getParser()
    {
        $keywords = [
            'en' => [
                'and' => 'And',
                'background' => 'Background',
                'but' => 'But',
                'examples' => 'Examples|Scenarios',
                'feature' => 'Feature|Business Need|Ability',
                'given' => 'Given',
                'name' => 'English',
                'native' => 'English',
                'scenario' => 'Scenario',
                'scenario_outline' => 'Scenario Outline|Scenario Template',
                'then' => 'Then',
                'when' => 'When',
            ],
        ];

        return new Parser(new Lexer(new ArrayKeywords($keywords)));
    }
}
