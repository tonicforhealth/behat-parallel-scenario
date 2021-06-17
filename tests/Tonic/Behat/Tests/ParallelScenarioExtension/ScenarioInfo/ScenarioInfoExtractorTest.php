<?php

namespace Tonic\Behat\Tests\ParallelScenarioExtension\ScenarioInfo;

use Behat\Gherkin\Keywords\ArrayKeywords;
use Behat\Gherkin\Lexer;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Parser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfo;
use Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfoExtractor;

/**
 * @coversDefaultClass \Tonic\Behat\ParallelScenarioExtension\ScenarioInfo\ScenarioInfoExtractor
 * @covers ::__construct
 */
class ScenarioInfoExtractorTest extends TestCase
{
    public function provider(): array
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
                $this->getScenarioInfo(json_decode(file_get_contents($expectedFile), true, 512, \JSON_THROW_ON_ERROR), $directory->getPathname()),
            ];
        }

        return $cases;
    }

    /**
     * @dataProvider provider
     *
     * @covers ::extract
     * @covers ::isParallel
     * @covers ::isParallelWait
     * @covers ::isParallelExamples
     */
    public function testExtract(FeatureNode $feature, array $expected): void
    {
        $extractor = new ScenarioInfoExtractor();
        $result = $extractor->extract($feature);

        self::assertEquals($expected, $result);
    }

    private function getScenarioInfo(array $data, $path): array
    {
        foreach ($data as &$group) {
            $group = array_map(static function ($data) use ($path) {
                return new ScenarioInfo(sprintf('%s/%s', $path, $data['file']), $data['line']);
            }, $group);
        }

        return $data;
    }

    private function getParser(): Parser
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
